<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GamificacionService;
use App\Support\ApiResponse;
use App\Support\CatalogoTraducido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GamificacionController extends Controller
{
    use ApiResponse;

    public function __construct(private GamificacionService $gam) {}

    /**
     * Registra que el usuario ha visitado una seccion o consultado una
     * categoria. Alimenta los desbloqueos de exploracion.
     * Se llama desde el frontend, es idempotente y muy barato.
     */
    private function explorar(Request $request)
    {
        $u = auth_user();
        if (!$u) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $tipo  = $request->input('tipo');       // seccion | categoria
        $valor = trim((string) $request->input('valor'));
        if (!in_array($tipo, ['seccion', 'categoria'], true) || $valor === '') {
            return $this->json(false, __('civinsis.toast.comunes.datos_incompletos'));
        }

        $svc = new \App\Services\GamificacionService();
        $svc->registrarExploracion($u, $tipo, mb_strtolower($valor));
        // un dia activo se registra solo, al haber cualquier actividad
        $svc->registrarExploracion($u, 'dia_activo', now()->toDateString());

        return $this->json(true, 'ok');
    }

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');
        return match($accion) {
            'explorar' => $this->explorar($request),
            'perfil'         => $this->perfil(),
            'perfil_publico' => $this->perfilPublico($request),
            'equipar'        => $this->equipar($request),
            'misiones'       => $this->misiones(),
            'logros'         => $this->logros(),
            'ranking'        => $this->ranking($request),
            'historial_xp'   => $this->historialXP(),
            'penalizaciones_pendientes' => $this->penalizacionesPendientes(),
            default          => $this->json(false, __('civinsis.toast.comunes.accion_no_valida')),
        };
    }

    /**
     * Penalizaciones de reputación (censura de moderación) que el usuario
     * todavía no ha visto. Se marcan como vistas al leerlas, así el modal de
     * aviso ("perdiste reputación") solo aparece una vez por penalización.
     */
    private function penalizacionesPendientes()
    {
        $u = auth_user();
        if (!$u) return $this->json(true, 'OK', ['penalizaciones' => []]);

        $pendientes = DB::table('reputacion_historial')
            ->where('usuario_id', $u->id)
            ->where('visto', false)
            ->where('puntos', '<', 0)
            ->orderBy('created_at')
            ->get(['id', 'puntos', 'razon', 'created_at']);

        if ($pendientes->isEmpty()) return $this->json(true, 'OK', ['penalizaciones' => []]);

        DB::table('reputacion_historial')
            ->whereIn('id', $pendientes->pluck('id'))
            ->update(['visto' => true]);

        return $this->json(true, 'OK', ['penalizaciones' => $pendientes->map(fn ($p) => [
            'puntos' => (int) $p->puntos,
            'razon'  => $p->razon,
            'fecha'  => (string) $p->created_at,
        ])]);
    }

    private function perfil()
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));
        $data = $this->gam->perfilCompleto(auth_user());
        return $this->json(true, 'OK', $data);
    }

    private function perfilPublico(Request $request)
    {
        $id = (int) $request->input('id');
        if (!$id) return $this->json(false, __('civinsis.toast.comunes.id_invalido'));

        $user = \App\Models\User::find($id);
        if (!$user) return $this->json(false, __('civinsis.toast.admin.usuario_no_encontrado'));

        // El dueño del perfil siempre puede verlo; cualquier otra persona
        // solo si lo dejó público (antes esto nunca se comprobaba, así que
        // un perfil "privado" era visible para cualquiera igual).
        $esPropio = Auth::check() && Auth::id() === $user->id;
        if (!$user->perfil_publico && !$esPropio) {
            return $this->json(false, __('civinsis.toast.gamificacion.perfil_privado'), ['privado' => true]);
        }

        // Datos completos de gamificación
        $data = $this->gam->perfilCompleto($user);

        // Datos básicos del usuario (públicos)
        $data['usuario'] = [
            'id'      => $user->id,
            'nombre'  => trim($user->nombre . ' ' . $user->apellido),
            'avatar'  => $user->avatar,
            'bio'     => $user->bio,
            'rol'     => $user->rol_nombre ?? 'usuario',
            'miembro_desde' => optional($user->created_at)->format('M Y'),
        ];

        // Estadísticas públicas
        $data['stats'] = [
            'propuestas' => \App\Models\Proposal::where('usuario_id', $id)->count(),
            'votos'      => \App\Models\Proposal::where('usuario_id', $id)->sum('votos'),
            'comentarios'=> \App\Models\Comentario::where('usuario_id', $id)->count(),
        ];

        // Solo mostrar insignias/logros/cosméticos equipados (no todo el inventario privado)
        // Los logros sí son públicos, las misiones no
        unset($data['misiones']);

        return $this->json(true, 'OK', $data);
    }

    private function equipar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));
        $tipo  = $request->input('tipo');   // titulo|marco|fondo|insignia
        $clave = $request->input('clave');
        if (!$tipo || !$clave) return $this->json(false, __('civinsis.toast.comunes.datos_incompletos'));
        $ok = $this->gam->equiparItem(auth_user(), $tipo, $clave);
        return $this->json($ok, $ok ? __('civinsis.toast.gamificacion.item_equipado') : __('civinsis.toast.perfil.error_equipar_item'));
    }

    private function misiones()
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));
        $user = auth_user();
        $hoy  = now()->toDateString();
        $sem  = now()->startOfWeek()->toDateString();

        $misiones = DB::table('misiones')->where('activo', true)->get()->map(function($m) use($user,$hoy,$sem) {
            $periodo = $m->tipo === 'diaria' ? $hoy : $sem;
            $p = DB::table('usuario_misiones')->where('usuario_id',$user->id)->where('mision_id',$m->id)->where('periodo',$periodo)->first();
            return ['id'=>$m->id,'nombre'=>$m->nombre,'descripcion'=>$m->descripcion,'tipo'=>$m->tipo,
                    'cantidad'=>$m->cantidad,'xp'=>$m->xp_recompensa,'progreso'=>$p->progreso??0,'completada'=>(bool)($p->completada??false)];
        });
        return $this->json(true, 'OK', ['misiones' => $misiones]);
    }

    private function logros()
    {
        $todos = DB::table('logros')->where('activo', true)->orderBy('orden')->get();
        $desbloqueados = Auth::check()
            ? DB::table('usuario_logros')->where('usuario_id', Auth::id())->pluck('logro_id')->toArray()
            : [];
        $resultado = $todos->map(function($l) use ($desbloqueados) {
            $a = (array) $l;
            $a['nombre'] = CatalogoTraducido::campo('logros', $l->clave, 'nombre', $l->nombre);
            $a['descripcion'] = CatalogoTraducido::campo('logros', $l->clave, 'descripcion', $l->descripcion);
            $a['desbloqueado'] = in_array($l->id, $desbloqueados);
            return $a;
        });
        return $this->json(true, 'OK', ['logros' => $resultado]);
    }

    private function ranking(Request $request)
    {
        $tipo = $request->input('tipo', 'xp'); // xp|reputacion|nivel
        $campo = match($tipo) { 'reputacion' => 'reputacion', 'nivel' => 'nivel', default => 'xp_total' };

        $ranking = DB::table('usuarios')
            ->where('activo', true)
            ->orderByDesc($campo)
            ->limit(50)
            ->select('id','nombre','apellido','avatar','nivel','xp_total','reputacion','titulo_equipado','marco_equipado')
            ->get()
            ->map(function($u) use($campo) {
                $titulo = DB::table('titulos')->where('clave', $u->titulo_equipado)->first();
                return [
                    'id'=>$u->id,'nombre'=>$u->nombre.' '.($u->apellido??''),
                    'avatar'=>$u->avatar,'nivel'=>$u->nivel,'xp'=>$u->xp_total,
                    'reputacion'=>$u->reputacion,'marco'=>$u->marco_equipado,
                    'titulo'=>$titulo?['nombre'=>CatalogoTraducido::campo('titulos', $titulo->clave, 'nombre', $titulo->nombre),'color'=>$titulo->color]:null,
                ];
            });
        return $this->json(true, 'OK', ['ranking' => $ranking]);
    }

    private function historialXP()
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));
        $historial = DB::table('xp_historial')
            ->where('usuario_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();
        return $this->json(true, 'OK', ['historial' => $historial]);
    }
}
