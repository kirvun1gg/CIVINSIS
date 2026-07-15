<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debate;
use App\Models\DebateRespuesta;
use App\Models\Proposal;
use App\Models\Titulo;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TendenciasController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        $accion = $request->input('accion', 'todo');
        return match ($accion) {
            'todo'  => $this->todo($request),
            default => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function todo(Request $request)
    {
        return $this->json(true, 'OK', [
            'debates_activos'       => $this->debatesActivos(),
            'propuestas_creciendo'  => $this->propuestasCreciendo(),
            'usuarios_destacados'   => $this->usuariosDestacados(),
            'comentarios_valorados' => $this->comentariosValorados(),
            'desafios_completados'  => $this->desafiosCompletados(),
        ]);
    }

    /** 🔥 Debates con más actividad (respuestas recientes). */
    private function debatesActivos(): array
    {
        $hace7 = now()->subDays(7);

        // Respuestas recientes por debate
        $recientes = DebateRespuesta::where('fecha_creacion', '>=', $hace7)
            ->where('censurado', false)
            ->select('debate_id', DB::raw('COUNT(*) as recientes'))
            ->groupBy('debate_id')->pluck('recientes', 'debate_id')->toArray();

        $debates = Debate::with('categoria')->where('censurado', false)->where('estado', 'activo')
            ->orderByDesc('respuestas_count')->limit(20)->get();

        return $debates->map(function ($d) use ($recientes) {
            return [
                'id'              => $d->id,
                'titulo'          => $d->titulo,
                'categoria'       => $d->categoria->nombre ?? '',
                'categoria_icono' => $d->categoria->icono ?? 'fas fa-tag',
                'categoria_color' => $d->categoria->color ?? '#36c0a1',
                'respuestas'      => (int) $d->respuestas_count,
                'participantes'   => (int) $d->participantes,
                'recientes'       => (int) ($recientes[$d->id] ?? 0),
            ];
        })->sortByDesc(fn ($d) => $d['recientes'] * 10 + $d['respuestas'])
          ->take(5)->values()->all();
    }

    /** 📈 Propuestas en crecimiento (más valoraciones/vistas recientes). */
    private function propuestasCreciendo(): array
    {
        $hace7 = now()->subDays(7);

        // Valoraciones recientes por propuesta
        $votosRecientes = DB::table('votos')->where('created_at', '>=', $hace7)
            ->select('propuesta_id', DB::raw('COUNT(*) as recientes'))
            ->groupBy('propuesta_id')->pluck('recientes', 'propuesta_id')->toArray();

        $propuestas = Proposal::with(['categoria', 'autor'])
            ->where('censurada', false)
            ->orderByDesc('votos')->orderByDesc('vistas')->limit(25)->get();

        return $propuestas->map(function ($p) use ($votosRecientes) {
            return [
                'id'              => $p->id,
                'titulo'          => $p->titulo,
                'autor'           => $p->autor ? trim($p->autor->nombre . ' ' . $p->autor->apellido) : 'Anónimo',
                'categoria'       => $p->categoria->nombre ?? '',
                'categoria_icono' => $p->categoria->icono ?? 'fas fa-tag',
                'categoria_color' => $p->categoria->color ?? '#36c0a1',
                'votos'           => (int) $p->votos,
                'vistas'          => (int) $p->vistas,
                'progreso'        => $p->progreso ?? 'idea',
                'recientes'       => (int) ($votosRecientes[$p->id] ?? 0),
            ];
        })->sortByDesc(fn ($p) => $p['recientes'] * 5 + $p['votos'])
          ->take(5)->values()->all();
    }

    /** ⭐ Usuarios destacados (más actividad + reputación el último mes). */
    private function usuariosDestacados(): array
    {
        $inicioMes = now()->startOfMonth();

        $rows = DB::table('usuarios as u')->where('u.activo', true)
            ->select('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.reputacion', 'u.titulo_equipado')
            ->selectRaw('(
                (SELECT COUNT(*) FROM propuestas WHERE propuestas.usuario_id = u.id AND propuestas.created_at >= ?) +
                (SELECT COUNT(*) FROM comentarios WHERE comentarios.usuario_id = u.id AND comentarios.created_at >= ?) +
                (SELECT COUNT(*) FROM debate_respuestas WHERE debate_respuestas.usuario_id = u.id AND debate_respuestas.created_at >= ?)
            ) as actividad', [$inicioMes, $inicioMes, $inicioMes])
            ->orderByDesc('u.reputacion')
            ->having('actividad', '>', 0)
            ->orderByDesc('actividad')
            ->limit(5)->get();

        return $rows->map(function ($u) {
            $titulo = $u->titulo_equipado ? Titulo::where('clave', $u->titulo_equipado)->first() : null;
            return [
                'id'         => $u->id,
                'nombre'     => trim($u->nombre . ' ' . ($u->apellido ?? '')),
                'avatar'     => $u->avatar,
                'nivel'      => $u->nivel,
                'reputacion' => $u->reputacion,
                'actividad'  => (int) $u->actividad,
                'titulo'     => $titulo ? ['nombre' => $titulo->nombre, 'color' => $titulo->color, 'rareza' => $titulo->rareza] : null,
            ];
        })->values()->all();
    }

    /** 💬 Comentarios más valorados (por votos recibidos en respuestas de debate). */
    private function comentariosValorados(): array
    {
        $rows = DebateRespuesta::with(['usuario', 'debate'])
            ->where('censurado', false)->where('votos', '>', 0)
            ->orderByDesc('votos')->limit(5)->get();

        return $rows->map(function ($r) {
            $u = $r->usuario;
            return [
                'autor'     => $u ? trim($u->nombre . ' ' . $u->apellido) : 'Anónimo',
                'avatar'    => $u->avatar ?? null,
                'texto'     => mb_strimwidth($r->contenido, 0, 120, '…'),
                'votos'     => (int) $r->votos,
                'debate_id' => $r->debate_id,
                'debate'    => $r->debate ? mb_strimwidth($r->debate->titulo, 0, 50, '…') : '',
            ];
        })->values()->all();
    }

    /** 🎯 Desafíos más completados. */
    private function desafiosCompletados(): array
    {
        $conteos = DB::table('usuario_desafios')->where('completado', true)
            ->select('desafio_id', DB::raw('COUNT(*) as total'))
            ->groupBy('desafio_id')->orderByDesc('total')->limit(5)->get();

        if ($conteos->isEmpty()) return [];

        $desafios = DB::table('desafios')->whereIn('id', $conteos->pluck('desafio_id'))->get()->keyBy('id');

        return $conteos->map(function ($c) use ($desafios) {
            $d = $desafios[$c->desafio_id] ?? null;
            if (!$d) return null;
            return [
                'id'     => $d->id,
                'titulo' => $d->titulo,
                'icono'  => $d->icono,
                'total'  => (int) $c->total,
            ];
        })->filter()->values()->all();
    }
}
