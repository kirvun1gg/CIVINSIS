<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\Titulo;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    use ApiResponse;

    const CATEGORIAS = [
        'xp'          => ['label' => 'Más XP',                 'icono' => 'fas fa-bolt',              'unidad' => 'XP'],
        'reputacion'  => ['label' => 'Mayor reputación',        'icono' => 'fas fa-star',              'unidad' => 'rep.'],
        'logros'      => ['label' => 'Más logros',              'icono' => 'fas fa-trophy',            'unidad' => 'logros'],
        'desafios'    => ['label' => 'Más desafíos completados','icono' => 'fas fa-flag-checkered',    'unidad' => 'desafíos'],
        'debatientes' => ['label' => 'Mejores debatientes',     'icono' => 'fas fa-comments',          'unidad' => 'votos'],
        'propuestas'  => ['label' => 'Propuestas más populares','icono' => 'fas fa-layer-group',       'unidad' => 'votos'],
        'activos_mes' => ['label' => 'Más activos del mes',     'icono' => 'fas fa-fire',              'unidad' => 'acciones'],
    ];

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'categorias' => $this->categorias(),
            'listar'     => $this->listar($request),
            default      => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function categorias()
    {
        $out = [];
        foreach (self::CATEGORIAS as $clave => $meta) $out[] = array_merge(['clave' => $clave], $meta);
        return $this->json(true, 'OK', ['categorias' => $out]);
    }

    private function listar(Request $request)
    {
        $categoria = (string) $request->input('categoria', 'xp');
        if (!array_key_exists($categoria, self::CATEGORIAS)) $categoria = 'xp';
        $limit = min(50, max(5, (int) $request->input('limit', 20)));

        $resultado = match ($categoria) {
            'xp'          => $this->rankingUsuarios('xp_total', $limit),
            'reputacion'  => $this->rankingUsuarios('reputacion', $limit),
            'logros'      => $this->rankingLogros($limit),
            'desafios'    => $this->rankingDesafios($limit),
            'debatientes' => $this->rankingDebatientes($limit),
            'propuestas'  => $this->rankingPropuestas($limit),
            'activos_mes' => $this->rankingActivosMes($limit),
        };

        $miPosicion = null;
        if (Auth::check() && $categoria !== 'propuestas') {
            $idx = collect($resultado)->search(fn ($r) => ($r['id'] ?? null) === Auth::id());
            if ($idx !== false) $miPosicion = $idx + 1;
        }

        return $this->json(true, 'OK', [
            'categoria'   => $categoria,
            'meta'        => self::CATEGORIAS[$categoria],
            'ranking'     => $resultado,
            'mi_posicion' => $miPosicion,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  Rankings de usuarios (formato común)
    // ─────────────────────────────────────────────────────────
    private function rankingUsuarios(string $campo, int $limit): array
    {
        $rows = DB::table('usuarios')->where('activo', true)
            ->orderByDesc($campo)->limit($limit)
            ->select('id', 'nombre', 'apellido', 'avatar', 'nivel', 'xp_total', 'reputacion', 'titulo_equipado')
            ->get();

        return $rows->map(fn ($u) => $this->formatoUsuario($u, $u->$campo))->values()->all();
    }

    private function rankingLogros(int $limit): array
    {
        $rows = DB::table('usuarios as u')
            ->join('usuario_logros as ul', 'ul.usuario_id', '=', 'u.id')
            ->where('u.activo', true)
            ->select('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.xp_total', 'u.reputacion', 'u.titulo_equipado', DB::raw('COUNT(*) as valor'))
            ->groupBy('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.xp_total', 'u.reputacion', 'u.titulo_equipado')
            ->orderByDesc('valor')->limit($limit)->get();

        return $rows->map(fn ($u) => $this->formatoUsuario($u, (int) $u->valor))->values()->all();
    }

    private function rankingDesafios(int $limit): array
    {
        $rows = DB::table('usuarios as u')
            ->join('usuario_desafios as ud', 'ud.usuario_id', '=', 'u.id')
            ->where('u.activo', true)->where('ud.completado', true)
            ->select('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.xp_total', 'u.reputacion', 'u.titulo_equipado', DB::raw('COUNT(*) as valor'))
            ->groupBy('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.xp_total', 'u.reputacion', 'u.titulo_equipado')
            ->orderByDesc('valor')->limit($limit)->get();

        return $rows->map(fn ($u) => $this->formatoUsuario($u, (int) $u->valor))->values()->all();
    }

    /** "Mejores debatientes": ordenados por la suma de votos recibidos en sus respuestas de debate. */
    private function rankingDebatientes(int $limit): array
    {
        $rows = DB::table('usuarios as u')
            ->join('debate_respuestas as dr', 'dr.usuario_id', '=', 'u.id')
            ->where('u.activo', true)->where('dr.censurado', false)
            ->select('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.xp_total', 'u.reputacion', 'u.titulo_equipado', DB::raw('SUM(dr.votos) as valor'), DB::raw('COUNT(*) as respuestas'))
            ->groupBy('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.xp_total', 'u.reputacion', 'u.titulo_equipado')
            ->orderByDesc('valor')->orderByDesc('respuestas')->limit($limit)->get();

        return $rows->map(fn ($u) => $this->formatoUsuario($u, (int) $u->valor))->values()->all();
    }

    /** Actividad del mes en curso: propuestas + comentarios + respuestas de debate + votos emitidos. */
    private function rankingActivosMes(int $limit): array
    {
        $inicioMes = now()->startOfMonth();

        $sub = DB::table('usuarios as u')->where('u.activo', true)
            ->select('u.id', 'u.nombre', 'u.apellido', 'u.avatar', 'u.nivel', 'u.xp_total', 'u.reputacion', 'u.titulo_equipado')
            ->selectRaw('(
                (SELECT COUNT(*) FROM propuestas WHERE propuestas.usuario_id = u.id AND propuestas.created_at >= ?) +
                (SELECT COUNT(*) FROM comentarios WHERE comentarios.usuario_id = u.id AND comentarios.created_at >= ?) +
                (SELECT COUNT(*) FROM debate_respuestas WHERE debate_respuestas.usuario_id = u.id AND debate_respuestas.created_at >= ?) +
                (SELECT COUNT(*) FROM votos WHERE votos.usuario_id = u.id AND votos.created_at >= ?)
            ) as valor', [$inicioMes, $inicioMes, $inicioMes, $inicioMes])
            ->havingRaw('valor > 0')
            ->orderByDesc('valor')->limit($limit)->get();

        return $sub->map(fn ($u) => $this->formatoUsuario($u, (int) $u->valor))->values()->all();
    }

    private function formatoUsuario($u, $valor): array
    {
        $titulo = $u->titulo_equipado ? Titulo::where('clave', $u->titulo_equipado)->first() : null;
        return [
            'id'       => $u->id,
            'nombre'   => trim($u->nombre . ' ' . ($u->apellido ?? '')),
            'avatar'   => $u->avatar,
            'nivel'    => $u->nivel,
            'xp'       => $u->xp_total,
            'reputacion' => $u->reputacion,
            'valor'    => $valor,
            'titulo'   => $titulo ? ['nombre' => $titulo->nombre, 'color' => $titulo->color, 'rareza' => $titulo->rareza] : null,
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  Propuestas más populares (ranking de contenido, no de usuarios)
    // ─────────────────────────────────────────────────────────
    private function rankingPropuestas(int $limit): array
    {
        $rows = Proposal::with(['categoria', 'autor'])
            ->where('censurada', false)
            ->orderByDesc('votos')->limit($limit)->get();

        return $rows->map(fn ($p) => [
            'id'         => $p->id,
            'titulo'     => $p->titulo,
            'autor'      => $p->autor ? trim($p->autor->nombre . ' ' . $p->autor->apellido) : 'Anónimo',
            'categoria'  => $p->categoria->nombre ?? '',
            'categoria_color' => $p->categoria->color ?? '#36c0a1',
            'categoria_icono' => $p->categoria->icono ?? 'fas fa-tag',
            'valor'      => (int) $p->votos,
            'vistas'     => (int) $p->vistas,
            'enlace'     => 'propuesta.php?id=' . $p->id,
        ])->values()->all();
    }
}
