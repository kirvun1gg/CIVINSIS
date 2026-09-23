<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\GamificacionService;

class AuthController extends Controller
{
    use ApiResponse;

    /** Punto de entrada único — despacha según la acción (igual que el legacy). */
    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'login'             => $this->login($request),
            'registro'          => $this->registro($request),
            'logout'            => $this->logout($request),
            'perfil'            => $this->perfil(),
            'actualizar_perfil' => $this->actualizarPerfil($request),
            'cambiar_password'  => $this->cambiarPassword($request),
            'actualizar_avatar' => $this->actualizarAvatar($request),
            'admin_usuarios'    => $this->adminUsuarios(),
            'cambiar_rol'       => $this->cambiarRol($request),
            'eliminar_usuario'  => $this->eliminarUsuario($request),
            default             => $this->json(false, __('civinsis.toast.comunes.accion_no_valida')),
        };
    }

    private function login(Request $request)
    {
        $email = trim((string) $request->input('email'));
        $pass  = (string) $request->input('password');

        if ($email === '' || $pass === '') return $this->json(false, __('civinsis.toast.auth.por_favor_completa_campos'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->json(false, __('civinsis.toast.auth.formato_correo_no_valido'));

        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($pass, $user->password)) return $this->json(false, __('civinsis.toast.auth.credenciales_incorrectas'));
        if (!$user->activo) return $this->json(false, __('civinsis.toast.auth.cuenta_desactivada'));

        // Gamificación: título y cosméticos iniciales
        try {
            $gam = app(GamificacionService::class);
            $gam->desbloquearTitulosPublic($user);
            $gam->desbloquearCosmeticosPublic($user);
        } catch (\Throwable $e) {}

        Auth::login($user, true);
        $request->session()->regenerate();
        $user->update(['ultimo_acceso' => now()]);

        // Gamificación: racha diaria
        try {
            app(GamificacionService::class)->actualizarRacha($user);
        } catch (\Throwable $e) {}

        return $this->json(true, __('civinsis.toast.auth.inicio_sesion_exitoso'), [
            'redirect' => 'inicio.php',
            'nombre'   => $user->nombre,
        ]);
    }

    private function registro(Request $request)
    {
        $nombre   = trim((string) $request->input('nombre'));
        $apellido = trim((string) $request->input('apellido'));
        $email    = trim((string) $request->input('email'));
        $pass     = (string) $request->input('password');
        $confirm  = (string) $request->input('confirm_password');

        if ($nombre === '' || $apellido === '' || $email === '' || $pass === '')
            return $this->json(false, __('civinsis.toast.auth.por_favor_completa_campos'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->json(false, __('civinsis.toast.auth.formato_correo_no_valido'));
        if (strlen($pass) < 8)  return $this->json(false, __('civinsis.toast.auth.password_debe_tener_8'));
        if ($pass !== $confirm) return $this->json(false, __('civinsis.toast.comunes.contrasenas_no_coinciden'));
        if (User::where('email', $email)->exists()) return $this->json(false, __('civinsis.toast.auth.correo_ya_registrado'));

        $rolId = optional(Role::where('nombre', 'usuario')->first())->id ?? 3;

        $user = User::create([
            'nombre'   => $nombre,
            'apellido' => $apellido,
            'email'    => $email,
            'password' => Hash::make($pass),
            'rol_id'   => $rolId,
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return $this->json(true, __('civinsis.toast.auth.cuenta_creada_exitosamente'), [
            'redirect' => 'inicio.php',
            'nombre'   => $nombre,
        ]);
    }

    private function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return $this->json(true, __('civinsis.toast.auth.sesion_cerrada'), ['redirect' => 'index.php']);
    }

    private function perfil()
    {
        $u = auth_user();
        if (!$u) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $u->loadCount('propuestas');
        $stats = [
            'votos_recibidos' => (int) $u->propuestas()->sum('votos'),
            'vistas_totales'  => (int) $u->propuestas()->sum('vistas'),
            'desafios_completados' => (int) \App\Models\UsuarioDesafio::where('usuario_id', $u->id)->where('completado', true)->count(),
        ];

        return $this->json(true, 'OK', ['usuario' => array_merge($u->toArray(), [
            'rol'             => $u->rol_nombre,
            'propuestas'      => $u->propuestas_count,
            'votos_recibidos' => $stats['votos_recibidos'],
            'vistas_totales'  => $stats['vistas_totales'],
            'desafios_completados' => $stats['desafios_completados'],
            'fecha_registro'  => optional($u->created_at)->toDateTimeString(),
            // $u->toArray() no incluye los accessors (getMarcoClaseAttribute,
            // etc.) al no estar en $appends del modelo, así que sin esto el
            // marco/efecto llegaban "undefined" aquí y perfil.js caía a una
            // conversión ingenua (marco_equipado con "_" -> "-") que no
            // siempre coincide con la clase real del catálogo de cosméticos
            // — por eso el marco/fondo/efecto no se veían hasta abrir el
            // panel de gamificación, que sí usa estos accessors.
            'marco_clase'     => $u->marco_clase,
            'efecto_clase'    => $u->efecto_clase,
            'fondo_clase'     => $u->fondo_clase,
        ])]);
    }

    private function actualizarPerfil(Request $request)
    {
        $u = auth_user();
        if (!$u) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $nombre   = trim((string) $request->input('nombre'));
        $apellido = trim((string) $request->input('apellido'));
        $email    = trim((string) $request->input('email'));

        if ($nombre === '' || $apellido === '' || $email === '') return $this->json(false, __('civinsis.toast.comunes.datos_incompletos'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->json(false, __('civinsis.toast.comunes.email_invalido'));
        if (User::where('email', $email)->where('id', '!=', $u->id)->exists())
            return $this->json(false, __('civinsis.toast.auth.correo_en_uso'));

        // Campos de personalización ampliada (todos opcionales)
        $u->fill([
            'nombre'          => $nombre,
            'apellido'        => $apellido,
            'email'           => $email,
            'bio'             => (string) $request->input('bio', $u->bio),
            'color_perfil'    => (string) $request->input('color_perfil', $u->color_perfil),
            'color_banner'    => (string) $request->input('color_banner', $u->color_banner),
            'tema_perfil'     => (string) $request->input('tema_perfil', $u->tema_perfil),
            'marco_avatar'    => (string) $request->input('marco_avatar', $u->marco_avatar),
            'insignia'        => $request->input('insignia', $u->insignia),
            'frase'           => $request->input('frase', $u->frase),
            'ubicacion'       => $request->input('ubicacion', $u->ubicacion),
            'sitio_web'       => $request->input('sitio_web', $u->sitio_web),
            'social_twitter'  => $request->input('social_twitter', $u->social_twitter),
            'social_instagram'=> $request->input('social_instagram', $u->social_instagram),
            'social_github'   => $request->input('social_github', $u->social_github),
        ]);
        if ($request->has('perfil_publico')) {
            $u->perfil_publico = (bool) $request->boolean('perfil_publico');
        }
        $u->save();

        return $this->json(true, __('civinsis.toast.perfil.actualizado_correctamente'), ['usuario' => $u->fresh()->toArray()]);
    }

    private function cambiarPassword(Request $request)
    {
        $u = auth_user();
        if (!$u) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $actual   = (string) $request->input('pass_actual');
        $nueva    = (string) $request->input('pass_nueva');
        $esGoogle = !empty($u->google_id);

        // Las cuentas vinculadas con Google reciben una contraseña aleatoria
        // que el usuario nunca conoce (ver GoogleAuthController::callback), así
        // que no tiene sentido pedirle que confirme una contraseña "actual".
        if ($nueva === '' || (!$esGoogle && $actual === '')) return $this->json(false, __('civinsis.toast.auth.completa_campos'));
        if (strlen($nueva) < 8) return $this->json(false, __('civinsis.toast.perfil.password_nueva_min_caracteres'));
        if (!$esGoogle && !Hash::check($actual, $u->password)) return $this->json(false, __('civinsis.toast.perfil.password_actual_incorrecta'));

        $u->update(['password' => Hash::make($nueva)]);
        return $this->json(true, __('civinsis.toast.perfil.password_actualizada_correctamente'));
    }

    private function actualizarAvatar(Request $request)
    {
        $u = auth_user();
        if (!$u) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $avatar = (string) $request->input('avatar');
        if ($avatar === '') return $this->json(false, __('civinsis.toast.perfil.no_recibio_imagen'));
        if (!preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $avatar))
            return $this->json(false, __('civinsis.toast.perfil.formato_imagen_invalido'));
        if (strlen($avatar) > 2_800_000) return $this->json(false, __('civinsis.toast.perfil.imagen_demasiado_grande'));

        $analisis = app(\App\Services\ImageModerationService::class)->analizar($avatar);
        if ($analisis['inapropiada']) {
            \Illuminate\Support\Facades\Log::warning('Avatar rechazado por moderación IA', [
                'usuario_id' => $u->id, 'razon' => $analisis['razon'],
            ]);
            return $this->json(false, __('civinsis.toast.perfil.imagen_no_apropiada', ['razon' => $analisis['razon']]));
        }

        $u->update(['avatar' => $avatar]);
        return $this->json(true, __('civinsis.toast.perfil.avatar_actualizado'), ['avatar' => $avatar]);
    }

    // ── Acciones de administración ──────────────────────────
    private function adminUsuarios()
    {
        $u = auth_user();
        if (!$u) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));
        if ($u->rol_nombre !== 'admin') return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        $usuarios = User::with('rol')->orderByDesc('id')->get()->map(fn ($x) => [
            'id'             => $x->id,
            'nombre'         => $x->nombre,
            'apellido'       => $x->apellido,
            'email'          => $x->email,
            'rol'            => $x->rol_nombre,
            'activo'         => $x->activo,
            'fecha_registro' => optional($x->created_at)->toDateTimeString(),
        ]);

        return $this->json(true, 'OK', ['usuarios' => $usuarios]);
    }

    private function cambiarRol(Request $request)
    {
        $u = auth_user();
        if (!$u || $u->rol_nombre !== 'admin') return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        $userId  = (int) $request->input('usuario_id');
        $rolName = $request->input('rol');
        if (!in_array($rolName, ['usuario', 'moderador', 'admin'], true) || !$userId)
            return $this->json(false, __('civinsis.toast.comunes.datos_invalidos'));

        $rol = Role::where('nombre', $rolName)->first();
        if (!$rol) return $this->json(false, __('civinsis.toast.admin.rol_no_encontrado'));

        User::where('id', $userId)->update(['rol_id' => $rol->id]);
        return $this->json(true, __('civinsis.toast.admin.rol_actualizado'));
    }

    private function eliminarUsuario(Request $request)
    {
        $u = auth_user();
        if (!$u || $u->rol_nombre !== 'admin') return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        $userId = (int) $request->input('id');
        if (!$userId) return $this->json(false, __('civinsis.toast.comunes.id_invalido'));
        if ($userId === $u->id) return $this->json(false, __('civinsis.toast.admin.no_puedes_eliminarte'));

        User::where('id', $userId)->delete();
        return $this->json(true, __('civinsis.toast.admin.usuario_eliminado'));
    }
}
