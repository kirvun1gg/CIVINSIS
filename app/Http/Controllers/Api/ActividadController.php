<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Debate;
use App\Models\DebateRespuesta;
use App\Models\Desafio;
use App\Models\Notificacion;
use App\Models\Proposal;
use App\Models\UsuarioDesafio;
use App\Support\ApiResponse;
use App\Services\GamificacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActividadController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        $accion = $request->input('accion', 'panel');
        return match ($accion) {
            'panel' => $this->panel($request),
            default => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function panel(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');
        $user = Auth::user();

        return $this->json(true, 'OK', [
            'saludo'              => $this->saludo($user),
            'stats'               => $this->stats($user),
            'mision_activa'       => $this->misionActiva($user),
            'desafio_recomendado' => $this->desafioRecomendado($user),
            'ultimo_logro'        => $this->ultimoLogro($user),
            'respuestas_recibidas'=> $this->respuestasRecibidas($user),
            'propuestas_recomendadas' => $this->propuestasRecomendadas($user),
            'debates_recomendados'    => $this->debatesRecomendados($user),
            'actividad_reciente'  => $this->actividadReciente($user),
        ]);
    }

    private function saludo($user): array
    {
        $hora = (int) now()->format('H');
        $franja = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
        return [
            'texto'  => $franja,
            'nombre' => $user->nombre,
        ];
    }

    private function stats($user): array
    {
        $gam = app(GamificacionService::class);
        return [
            'nivel'              => $user->nivel,
            'xp'                 => $user->xp_total,
            'porcentaje_nivel'   => $gam->porcentajeNivel($user),
            'xp_siguiente_nivel' => $gam->xpParaSiguienteNivel($user->nivel),
            'reputacion'         => $user->reputacion,
            'racha_dias'         => $user->racha_dias ?? 0,
        ];
    }

    /** Primera misión activa no completada del usuario. */
    private function misionActiva($user): ?array
    {
        $hoy = now()->toDateString();
        $sem = now()->startOfWeek()->toDateString();

        $misiones = DB::table('misiones')->where('activo', true)->get();
        foreach ($misiones as $m) {
            $periodo = $m->tipo === 'diaria' ? $hoy : $sem;
            $p = DB::table('usuario_misiones')->where('usuario_id', $user->id)
                ->where('mision_id', $m->id)->where('periodo', $periodo)->first();
            $completada = (bool) ($p->completada ?? false);
            if (!$completada) {
                return [
                    'nombre'      => $m->nombre,
                    'descripcion' => $m->descripcion,
                    'tipo'        => $m->tipo,
                    'progreso'    => (int) ($p->progreso ?? 0),
                    'cantidad'    => (int) $m->cantidad,
                    'xp'          => (int) $m->xp_recompensa,
                ];
            }
        }
        return null;
    }

    /** Un desafío que el usuario aún no ha completado. */
    private function desafioRecomendado($user): ?array
    {
        $completados = UsuarioDesafio::where('usuario_id', $user->id)
            ->where('completado', true)->pluck('desafio_id')->toArray();

        $d = Desafio::with('categoria')->where('activo', true)
            ->whereNotIn('id', $completados)->inRandomOrder()->first();
        if (!$d) return null;

        return [
            'id'          => $d->id,
            'titulo'      => $d->titulo,
            'descripcion' => $d->descripcion,
            'dificultad'  => $d->dificultad,
            'icono'       => $d->icono,
            'xp'          => $d->xp_recompensa,
        ];
    }

    private function ultimoLogro($user): ?array
    {
        $ul = DB::table('usuario_logros')->where('usuario_id', $user->id)
            ->orderByDesc('desbloqueado_at')->first();
        if (!$ul) return null;

        $logro = DB::table('logros')->where('id', $ul->logro_id)->first();
        if (!$logro) return null;

        return [
            'nombre'      => $logro->nombre,
            'descripcion' => $logro->descripcion,
            'icono'       => $logro->icono,
            'color'       => $logro->color ?? '#ffb300',
        ];
    }

    /** Últimas respuestas/comentarios recibidos en las propuestas del usuario. */
    private function respuestasRecibidas($user): array
    {
        $misPropuestas = Proposal::where('usuario_id', $user->id)->pluck('id');
        if ($misPropuestas->isEmpty()) return [];

        return Comentario::with('usuario')
            ->whereIn('propuesta_id', $misPropuestas)
            ->where('usuario_id', '!=', $user->id)
            ->where('censurado', false)
            ->orderByDesc('created_at')->limit(5)->get()
            ->map(function ($c) {
                $u = $c->usuario;
                return [
                    'autor'        => $u ? trim($u->nombre . ' ' . $u->apellido) : 'Alguien',
                    'avatar'       => $u->avatar ?? null,
                    'texto'        => mb_strimwidth($c->contenido, 0, 90, '…'),
                    'propuesta_id' => $c->propuesta_id,
                    'fecha'        => optional($c->created_at)->diffForHumans(),
                ];
            })->values()->all();
    }

    /** Propuestas activas de otros, priorizando las que están en votación. */
    private function propuestasRecomendadas($user): array
    {
        return Proposal::with(['categoria', 'autor'])
            ->where('usuario_id', '!=', $user->id)
            ->where('censurada', false)
            ->orderByRaw("CASE WHEN progreso = 'votacion' THEN 0 ELSE 1 END")
            ->orderByDesc('votos')->orderByDesc('created_at')
            ->limit(4)->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'titulo'          => $p->titulo,
                'categoria'       => $p->categoria->nombre ?? '',
                'categoria_icono' => $p->categoria->icono ?? 'fas fa-tag',
                'categoria_color' => $p->categoria->color ?? '#36c0a1',
                'progreso'        => $p->progreso ?? 'idea',
                'votos'           => (int) $p->votos,
            ])->values()->all();
    }

    /** Debates activos con más participación reciente. */
    private function debatesRecomendados($user): array
    {
        return Debate::with('categoria')
            ->where('censurado', false)->where('estado', 'activo')
            ->orderByDesc('respuestas_count')->orderByDesc('fecha_creacion')
            ->limit(4)->get()
            ->map(fn ($d) => [
                'id'              => $d->id,
                'titulo'          => $d->titulo,
                'categoria'       => $d->categoria->nombre ?? '',
                'categoria_icono' => $d->categoria->icono ?? 'fas fa-tag',
                'categoria_color' => $d->categoria->color ?? '#36c0a1',
                'respuestas'      => (int) $d->respuestas_count,
            ])->values()->all();
    }

    /** Actividad reciente del propio usuario (propuestas, comentarios, debates). */
    private function actividadReciente($user): array
    {
        $items = collect();

        Proposal::where('usuario_id', $user->id)->latest('created_at')->limit(4)->get()
            ->each(fn ($p) => $items->push([
                'tipo' => 'propuesta', 'icono' => 'fas fa-lightbulb', 'color' => '#36c0a1',
                'texto' => 'Creaste la propuesta «' . mb_strimwidth($p->titulo, 0, 45, '…') . '»',
                'enlace' => 'propuesta.php?id=' . $p->id,
                'fecha' => $p->created_at, 'fecha_humana' => optional($p->created_at)->diffForHumans(),
            ]));

        Comentario::where('usuario_id', $user->id)->latest('created_at')->limit(4)->get()
            ->each(fn ($c) => $items->push([
                'tipo' => 'comentario', 'icono' => 'fas fa-comment', 'color' => '#3b82f6',
                'texto' => 'Comentaste en una propuesta',
                'enlace' => 'propuesta.php?id=' . $c->propuesta_id,
                'fecha' => $c->created_at, 'fecha_humana' => optional($c->created_at)->diffForHumans(),
            ]));

        DebateRespuesta::where('usuario_id', $user->id)->latest('fecha_creacion')->limit(4)->get()
            ->each(fn ($r) => $items->push([
                'tipo' => 'debate', 'icono' => 'fas fa-comments', 'color' => '#8b5cf6',
                'texto' => 'Participaste en un debate',
                'enlace' => 'debate.php?id=' . $r->debate_id,
                'fecha' => $r->fecha_creacion, 'fecha_humana' => optional($r->fecha_creacion)->diffForHumans(),
            ]));

        return $items->sortByDesc('fecha')->take(6)->map(function ($i) {
            unset($i['fecha']);
            return $i;
        })->values()->all();
    }
}
