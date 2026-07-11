<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GamificacionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GamificacionController extends Controller
{
    use ApiResponse;

    public function __construct(private GamificacionService $gam) {}

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');
        return match($accion) {
            'perfil'         => $this->perfil(),
            'perfil_publico' => $this->perfilPublico($request),
            'equipar'        => $this->equipar($request),
            'misiones'       => $this->misiones(),
            'logros'         => $this->logros(),
            'ranking'        => $this->ranking($request),
            'historial_xp'   => $this->historialXP(),
            default          => $this->json(false, 'Acción no válida'),
        };
    }

    private function perfil()
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');
        $data = $this->gam->perfilCompleto(Auth::user());
        return $this->json(true, 'OK', $data);
    }

    private function perfilPublico(Request $request)
    {
        $id = (int) $request->input('id');
        if (!$id) return $this->json(false, 'ID inválido');

        $user = \App\Models\User::find($id);
        if (!$user) return $this->json(false, 'Usuario no encontrado');

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
        if (!Auth::check()) return $this->json(false, 'No autenticado');
        $tipo  = $request->input('tipo');   // titulo|marco|fondo|insignia
        $clave = $request->input('clave');
        if (!$tipo || !$clave) return $this->json(false, 'Datos incompletos');
        $ok = $this->gam->equiparItem(Auth::user(), $tipo, $clave);
        return $this->json($ok, $ok ? 'Ítem equipado' : 'No puedes equipar ese ítem');
    }

    private function misiones()
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');
        $user = Auth::user();
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
        $resultado = $todos->map(fn($l) => array_merge((array)$l, ['desbloqueado' => in_array($l->id, $desbloqueados)]));
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
                    'titulo'=>$titulo?['nombre'=>$titulo->nombre,'color'=>$titulo->color]:null,
                ];
            });
        return $this->json(true, 'OK', ['ranking' => $ranking]);
    }

    private function historialXP()
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');
        $historial = DB::table('xp_historial')
            ->where('usuario_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();
        return $this->json(true, 'OK', ['historial' => $historial]);
    }
}
