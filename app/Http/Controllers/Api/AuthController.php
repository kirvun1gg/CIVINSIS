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
            default             => $this->json(false, 'Acción no válida'),
        };
    }

    private function login(Request $request)
    {
        $email = trim((string) $request->input('email'));
        $pass  = (string) $request->input('password');

        if ($email === '' || $pass === '') return $this->json(false, 'Por favor completa todos los campos');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->json(false, 'El formato del correo no es válido');

        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($pass, $user->password)) return $this->json(false, 'Credenciales incorrectas');
        if (!$user->activo) return $this->json(false, 'Tu cuenta ha sido desactivada');

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

        return $this->json(true, 'Inicio de sesión exitoso', [
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
            return $this->json(false, 'Por favor completa todos los campos');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->json(false, 'El formato del correo no es válido');
        if (strlen($pass) < 8)  return $this->json(false, 'La contraseña debe tener al menos 8 caracteres');
        if ($pass !== $confirm) return $this->json(false, 'Las contraseñas no coinciden');
        if (User::where('email', $email)->exists()) return $this->json(false, 'Este correo ya está registrado');

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

        return $this->json(true, '¡Cuenta creada exitosamente!', [
            'redirect' => 'inicio.php',
            'nombre'   => $nombre,
        ]);
    }

    private function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return $this->json(true, 'Sesión cerrada correctamente', ['redirect' => 'index.php']);
    }

    private function perfil()
    {
        $u = Auth::user();
        if (!$u) return $this->json(false, 'No autenticado');

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
        ])]);
    }

    private function actualizarPerfil(Request $request)
    {
        $u = Auth::user();
        if (!$u) return $this->json(false, 'No autenticado');

        $nombre   = trim((string) $request->input('nombre'));
        $apellido = trim((string) $request->input('apellido'));
        $email    = trim((string) $request->input('email'));

        if ($nombre === '' || $apellido === '' || $email === '') return $this->json(false, 'Datos incompletos');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $this->json(false, 'Email inválido');
        if (User::where('email', $email)->where('id', '!=', $u->id)->exists())
            return $this->json(false, 'Ese correo ya está en uso');

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

        return $this->json(true, 'Perfil actualizado correctamente', ['usuario' => $u->fresh()->toArray()]);
    }

    private function cambiarPassword(Request $request)
    {
        $u = Auth::user();
        if (!$u) return $this->json(false, 'No autenticado');

        $actual = (string) $request->input('pass_actual');
        $nueva  = (string) $request->input('pass_nueva');

        if ($actual === '' || $nueva === '') return $this->json(false, 'Completa todos los campos');
        if (strlen($nueva) < 8) return $this->json(false, 'La contraseña nueva debe tener al menos 8 caracteres');
        if (!Hash::check($actual, $u->password)) return $this->json(false, 'La contraseña actual es incorrecta');

        $u->update(['password' => Hash::make($nueva)]);
        return $this->json(true, 'Contraseña actualizada correctamente');
    }

    private function actualizarAvatar(Request $request)
    {
        $u = Auth::user();
        if (!$u) return $this->json(false, 'No autenticado');

        $avatar = (string) $request->input('avatar');
        if ($avatar === '') return $this->json(false, 'No se recibió imagen');
        if (!preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $avatar))
            return $this->json(false, 'Formato de imagen no válido');
        if (strlen($avatar) > 2_800_000) return $this->json(false, 'La imagen es demasiado grande');

        $u->update(['avatar' => $avatar]);
        return $this->json(true, 'Avatar actualizado correctamente', ['avatar' => $avatar]);
    }

    // ── Acciones de administración ──────────────────────────
    private function adminUsuarios()
    {
        $u = Auth::user();
        if (!$u) return $this->json(false, 'No autenticado');
        if ($u->rol_nombre !== 'admin') return $this->json(false, 'Sin permisos');

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
        $u = Auth::user();
        if (!$u || $u->rol_nombre !== 'admin') return $this->json(false, 'Sin permisos');

        $userId  = (int) $request->input('usuario_id');
        $rolName = $request->input('rol');
        if (!in_array($rolName, ['usuario', 'moderador', 'admin'], true) || !$userId)
            return $this->json(false, 'Datos inválidos');

        $rol = Role::where('nombre', $rolName)->first();
        if (!$rol) return $this->json(false, 'Rol no encontrado');

        User::where('id', $userId)->update(['rol_id' => $rol->id]);
        return $this->json(true, 'Rol actualizado');
    }

    private function eliminarUsuario(Request $request)
    {
        $u = Auth::user();
        if (!$u || $u->rol_nombre !== 'admin') return $this->json(false, 'Sin permisos');

        $userId = (int) $request->input('id');
        if (!$userId) return $this->json(false, 'ID inválido');
        if ($userId === $u->id) return $this->json(false, 'No puedes eliminarte a ti mismo');

        User::where('id', $userId)->delete();
        return $this->json(true, 'Usuario eliminado');
    }
}
