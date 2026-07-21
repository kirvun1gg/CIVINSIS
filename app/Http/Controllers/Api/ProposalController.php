<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Desafio;
use App\Models\ModeracionAlerta;
use App\Models\Notificacion;
use App\Models\Proposal;
use App\Models\PropuestaProgreso;
use App\Models\UsuarioDesafio;
use App\Models\Voto;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Services\GamificacionService;
use Illuminate\Support\Facades\Log;

class ProposalController extends Controller
{
    use ApiResponse;

    /** Metadata visual de cada fase del ciclo de vida de una propuesta. */
    const PROGRESO_STAGES = [
        'idea'      => ['label' => 'Idea',      'color' => '#8892a4', 'icono' => 'fas fa-lightbulb',
            'descripcion' => 'La propuesta fue publicada y espera ser descubierta por la comunidad.'],
        'discusion' => ['label' => 'Discusión',  'color' => '#4a9eff', 'icono' => 'fas fa-comments',
            'descripcion' => 'La comunidad está comentando y debatiendo sobre la propuesta.'],
        'mejoras'   => ['label' => 'Mejoras',    'color' => '#ef7e22', 'icono' => 'fas fa-pen-ruler',
            'descripcion' => 'Se están incorporando sugerencias para fortalecer la propuesta.'],
        'votacion'  => ['label' => 'Votación',   'color' => '#36c0a1', 'icono' => 'fas fa-square-poll-vertical',
            'descripcion' => 'La propuesta está abierta a votación ciudadana.'],
        'destacada' => ['label' => 'Destacada',  'color' => '#ffe066', 'icono' => 'fas fa-star',
            'descripcion' => 'Un moderador destacó esta propuesta por su calidad e impacto.'],
    ];

    /** Cantidad de comentarios de la comunidad (sin contar al autor) para sugerirle mejorar o pasar a votación. */
    const UMBRAL_SUGERENCIA_MEJORA = 5;

    /** Aspectos de la votación inteligente. Cada usuario elige UNO solo (positivo o negativo). */
    const ASPECTOS = [
        // Positivos (+1 reputación)
        'creativa'    => ['label' => 'Creativa',                 'icono' => '💡', 'signo' => 1],
        'argumentada' => ['label' => 'Bien argumentada',         'icono' => '📖', 'signo' => 1],
        'comunidad'   => ['label' => 'Beneficia a la comunidad', 'icono' => '🌍', 'signo' => 1],
        'factible'    => ['label' => 'Factible',                 'icono' => '✔️', 'signo' => 1],
        'innovadora'  => ['label' => 'Innovadora',               'icono' => '🚀', 'signo' => 1],
        // Negativos (-1 reputación)
        'poco_clara'  => ['label' => 'Poco clara',               'icono' => '😕', 'signo' => -1],
        'inviable'    => ['label' => 'Inviable',                 'icono' => '🚧', 'signo' => -1],
        'poco_util'   => ['label' => 'Poco útil',                'icono' => '🤷', 'signo' => -1],
    ];

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'listar'             => $this->listar($request),
            'detalle'            => $this->detalle($request),
            'crear'              => $this->crear($request),
            'editar'             => $this->editar($request),
            'cambiar_progreso'   => $this->cambiarProgreso($request),
            'decidir_fase'       => $this->decidirFase($request),
            'eliminar'           => $this->eliminar($request),
            'votar'              => $this->valorar($request),
            'valorar'            => $this->valorar($request),
            'comentar'           => $this->comentar($request),
            'comentarios'        => $this->comentarios($request),
            'top'                => $this->top($request),
            'mis_propuestas'     => $this->misPropuestas(),
            'admin_comentarios'  => $this->adminComentarios(),
            'eliminar_comentario'=> $this->eliminarComentario($request),
            'admin_editar'       => $this->adminEditar($request),
            default              => $this->json(false, 'Acción no reconocida'),
        };
    }

    /** Da formato a una propuesta para el frontend, incluyendo datos de autor + tarjeta. */
    private function formato(Proposal $p): array
    {
        $cat = $p->categoria;
        $autor = $p->autor;
        return [
            'id'               => $p->id,
            'titulo'           => $p->titulo,
            'descripcion'      => $p->descripcion,
            'contenido'        => $p->contenido,
            'votos'            => (int) $p->votos,
            'vistas'           => (int) $p->vistas,
            'estado'           => $p->estado,
            'diseno'           => $p->diseno ?: 'default',
            'color_acento'     => $p->color_acento,
            'icono_extra'      => $p->icono_extra,
            'efecto_categoria' => (bool) $p->efecto_categoria,
            'destacada'        => (bool) $p->destacada,
            'progreso'         => $p->progreso ?: 'idea',
            'progreso_label'   => self::PROGRESO_STAGES[$p->progreso ?? 'idea']['label'] ?? 'Idea',
            'imagen'           => $p->imagen,
            'categoria_id'     => $p->categoria_id,
            'categoria'        => $cat->nombre ?? '',
            'categoria_icono'  => $cat->icono ?? 'fas fa-tag',
            'categoria_color'  => $cat->color ?? '#36c0a1',
            'categoria_efecto' => $cat->efecto ?? 'default',
            'autor'            => $autor ? trim($autor->nombre . ' ' . $autor->apellido) : 'Anónimo',
            'autor_id'         => $p->usuario_id,
            'autor_avatar'     => $autor->avatar ?? null,
            'autor_bio'        => $autor->bio ?? null,
            'autor_nivel'      => $autor->nivel ?? 1,
            'autor_titulo'     => $this->tituloData($autor),
            'autor_marco'      => $autor->marco_equipado ?? null,
            'fecha_creacion'   => optional($p->fecha_creacion)->toDateTimeString(),
            'fecha_formateada' => optional($p->fecha_creacion ?? $p->created_at)->format('d/m/Y'),
        ];
    }

    private function listar(Request $request)
    {
        $categoria = (int) $request->input('categoria', 0);
        $busqueda  = trim((string) $request->input('q', ''));
        $orden     = in_array($request->input('orden'), ['votos', 'fecha', 'vistas']) ? $request->input('orden') : 'fecha';
        $pagina    = max(1, (int) $request->input('pagina', 1));
        $porPagina = 9;

        $query = Proposal::with(['categoria', 'autor'])->where('estado', 'activa');
        if ($categoria > 0) $query->where('categoria_id', $categoria);
        if ($busqueda !== '') {
            $query->where(function ($q) use ($busqueda) {
                $q->where('titulo', 'like', "%$busqueda%")
                  ->orWhere('descripcion', 'like', "%$busqueda%");
            });
        }
        $query->orderBy(match ($orden) {
            'votos'  => 'votos',
            'vistas' => 'vistas',
            default  => 'fecha_creacion',
        }, 'desc');

        $total = (clone $query)->count();
        $items = $query->forPage($pagina, $porPagina)->get();

        $votadas = [];
        if (Auth::check()) {
            $votadas = Voto::where('usuario_id', Auth::id())->pluck('propuesta_id')->all();
        }

        // Aspecto POSITIVO más valorado por cada propuesta de la página (una sola consulta)
        $ids = $items->pluck('id')->all();
        $topAspectos = [];
        if ($ids) {
            $positivos = array_keys(array_filter(self::ASPECTOS, fn ($a) => $a['signo'] > 0));
            $filas = Voto::whereIn('propuesta_id', $ids)->whereIn('aspecto', $positivos)
                ->select('propuesta_id', 'aspecto', DB::raw('COUNT(*) as total'))
                ->groupBy('propuesta_id', 'aspecto')->get();
            foreach ($filas as $f) {
                if (!isset($topAspectos[$f->propuesta_id]) || $f->total > $topAspectos[$f->propuesta_id]['total']) {
                    $topAspectos[$f->propuesta_id] = ['aspecto' => $f->aspecto, 'total' => (int) $f->total];
                }
            }
        }

        $propuestas = $items->map(function ($p) use ($votadas, $topAspectos) {
            $row = $this->formato($p);
            $row['ya_vote'] = in_array($p->id, $votadas);
            $top = $topAspectos[$p->id] ?? null;
            $row['aspecto_top'] = ($top && isset(self::ASPECTOS[$top['aspecto']]))
                ? ['clave' => $top['aspecto'], 'label' => self::ASPECTOS[$top['aspecto']]['label'], 'icono' => self::ASPECTOS[$top['aspecto']]['icono'], 'total' => $top['total']]
                : null;
            return $row;
        });

        return $this->json(true, 'OK', [
            'propuestas'    => $propuestas,
            'total'         => $total,
            'paginas'       => (int) ceil($total / $porPagina),
            'pagina_actual' => $pagina,
        ]);
    }

    private function detalle(Request $request)
    {
        $id = (int) $request->input('id');
        if (!$id) return $this->json(false, 'ID inválido');

        $p = Proposal::with(['categoria', 'autor'])->find($id);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');

        $p->increment('vistas');

        $data = $this->formato($p);
        $data['ya_vote'] = false;
        if (Auth::check()) {
            $data['ya_vote'] = Voto::where('propuesta_id', $id)->where('usuario_id', Auth::id())->exists();
            $data['es_autor'] = ($p->usuario_id === Auth::id());
        }
        $data['aspectos'] = $this->conteoAspectos($id);
        $data['mi_voto']  = $this->miVoto($id);
        $data['fecha_formateada'] = optional($p->fecha_creacion ?? $p->created_at)->format('d/m/Y \a \l\a\s H:i');
        $data['progreso_timeline'] = $this->timelineProgreso($p);

        // ¿Corresponde mostrarle al autor la sugerencia de "mejorar o pasar a votación"?
        $data['comentarios_comunidad'] = Comentario::where('propuesta_id', $p->id)
            ->where('usuario_id', '!=', $p->usuario_id)->where('censurado', false)->count();
        $data['mostrar_decision_mejora'] = ($data['es_autor'] ?? false)
            && $p->progreso === 'discusion'
            && $data['comentarios_comunidad'] >= self::UMBRAL_SUGERENCIA_MEJORA;

        return $this->json(true, 'OK', ['propuesta' => $data]);
    }

    /** Arma el timeline completo (5 fases) marcando cuáles se alcanzaron y cuándo. */
    private function timelineProgreso(Proposal $p): array
    {
        $historial = PropuestaProgreso::where('propuesta_id', $p->id)->orderBy('fecha')->get()->keyBy('progreso');
        $orden = array_keys(self::PROGRESO_STAGES);
        $actualIdx = array_search($p->progreso ?: 'idea', $orden);
        if ($actualIdx === false) $actualIdx = 0;

        $timeline = [];
        foreach ($orden as $i => $clave) {
            $meta = self::PROGRESO_STAGES[$clave];
            $entrada = $historial->get($clave);
            $timeline[] = [
                'clave'       => $clave,
                'label'       => $meta['label'],
                'color'       => $meta['color'],
                'icono'       => $meta['icono'],
                'descripcion' => $meta['descripcion'],
                'alcanzada'   => $i <= $actualIdx,
                'actual'      => $i === $actualIdx,
                'fecha'       => $entrada ? optional($entrada->fecha)->format('d/m/Y') : null,
            ];
        }
        return $timeline;
    }

    private function crear(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');

        $titulo      = trim((string) $request->input('titulo'));
        $descripcion = trim((string) $request->input('descripcion'));
        $contenido   = strip_tags((string) $request->input('contenido'), '<b><i><u><strong><em><h2><h3><ul><ol><li><blockquote><a><br><p><code>');
        $categoria   = (int) $request->input('categoria_id');
        $diseno      = (string) $request->input('diseno', 'default');
        $imagen      = (string) $request->input('imagen_base64', '');
        $desafioId   = $request->input('desafio_id') ? (int) $request->input('desafio_id') : null;

        if ($titulo === '' || $descripcion === '' || !$categoria)
            return $this->json(false, 'Por favor completa todos los campos obligatorios');

        if ($imagen !== '' && !preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $imagen)) $imagen = '';
        if (strlen($imagen) > 5_000_000) $imagen = '';

        $p = Proposal::create([
            'titulo'           => $titulo,
            'descripcion'      => $descripcion,
            'contenido'        => $contenido,
            'categoria_id'     => $categoria,
            'usuario_id'       => Auth::id(),
            'desafio_id'       => $desafioId,
            'diseno'           => $diseno,
            'color_acento'     => $request->input('color_acento'),
            'icono_extra'      => $request->input('icono_extra'),
            'efecto_categoria' => $request->boolean('efecto_categoria', true),
            'destacada'        => $request->boolean('destacada', false),
            'imagen'           => $imagen,
        ]);

        // Moderación automática con IA
        $this->moderarConIA('propuesta', $p->id, $titulo . ' ' . $descripcion);

        // Ciclo de vida: arranca en fase "Idea"
        PropuestaProgreso::create(['propuesta_id' => $p->id, 'progreso' => 'idea', 'usuario_id' => null, 'fecha' => now()]);

        // Gamificación: XP + reputación por crear propuesta
        try {
            $gam = app(GamificacionService::class);
            $gam->otorgarXP(Auth::user(), 'crear_propuesta', $p->id);
            $gam->otorgarReputacion(Auth::user(), 'Creaste una propuesta', 5, null, $p->id);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::error('Gam error: '.$e->getMessage()); }

        // Completar desafío vinculado (si esta propuesta nació de uno)
        if ($desafioId) $this->completarDesafio($desafioId, $p);

        return $this->json(true, '¡Propuesta publicada exitosamente!', ['id' => $p->id]);
    }

    /** Marca el desafío como completado por el usuario y otorga sus recompensas (solo la primera vez). */
    private function completarDesafio(int $desafioId, Proposal $p): void
    {
        $desafio = Desafio::find($desafioId);
        if (!$desafio) return;

        $progreso = UsuarioDesafio::firstOrCreate(
            ['usuario_id' => Auth::id(), 'desafio_id' => $desafioId],
            ['completado' => false]
        );
        if ($progreso->completado) return; // ya se había completado antes

        $progreso->completado     = true;
        $progreso->completado_at  = now();
        $progreso->propuesta_id   = $p->id;
        $progreso->save();

        try {
            $gam = app(GamificacionService::class);
            $gam->otorgarXP(Auth::user(), 'completar_desafio', $desafioId, $desafio->xp_recompensa);
            if ($desafio->reputacion_recompensa > 0) {
                $gam->otorgarReputacion(Auth::user(), 'Completaste un desafío', $desafio->reputacion_recompensa, null, $desafioId);
            }
            if ($desafio->insignia_id) {
                \Illuminate\Support\Facades\DB::table('usuario_insignias')->insertOrIgnore([
                    'usuario_id' => Auth::id(), 'insignia_id' => $desafio->insignia_id, 'desbloqueado_at' => now(),
                ]);
            }
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::error('Gam error (desafío): '.$e->getMessage()); }
    }

    private function editar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');

        $id          = (int) $request->input('id');
        $titulo      = trim((string) $request->input('titulo'));
        $descripcion = trim((string) $request->input('descripcion'));
        $contenido   = (string) $request->input('contenido');
        $categoria   = (int) $request->input('categoria_id');

        if (!$id || $titulo === '' || $descripcion === '' || !$categoria)
            return $this->json(false, 'Datos incompletos');

        $p = Proposal::find($id);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');
        if ($p->usuario_id !== Auth::id() && Auth::user()->rol_nombre !== 'admin')
            return $this->json(false, 'No tienes permiso para editar esta propuesta');

        $p->fill([
            'titulo'           => $titulo,
            'descripcion'      => $descripcion,
            'contenido'        => $contenido,
            'categoria_id'     => $categoria,
            'diseno'           => $request->input('diseno', $p->diseno),
            'color_acento'     => $request->input('color_acento', $p->color_acento),
            'icono_extra'      => $request->input('icono_extra', $p->icono_extra),
            'efecto_categoria' => $request->boolean('efecto_categoria', $p->efecto_categoria),
            'destacada'        => $request->boolean('destacada', $p->destacada),
        ]);
        if ($request->filled('imagen_base64')) {
            $img = (string) $request->input('imagen_base64');
            if (preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $img)) $p->imagen = $img;
        }
        $p->save();

        return $this->json(true, 'Propuesta actualizada correctamente');
    }

    private function eliminar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');
        $p = Proposal::find((int) $request->input('id'));
        if (!$p) return $this->json(false, 'Propuesta no encontrada');
        if ($p->usuario_id !== Auth::id() && Auth::user()->rol_nombre !== 'admin')
            return $this->json(false, 'No tienes permiso para eliminar esta propuesta');

        $p->delete();
        return $this->json(true, 'Propuesta eliminada correctamente');
    }

    /**
     * Votación inteligente: cada usuario emite UN SOLO voto por propuesta,
     * eligiendo un aspecto positivo o uno negativo (son excluyentes). Volver a
     * tocar el mismo aspecto lo retira; tocar otro cambia su voto.
     * propuestas.votos guarda el saldo neto (positivos - negativos) para que
     * rankings y widgets sigan funcionando.
     */
    private function valorar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión para valorar');

        $pid     = (int) $request->input('propuesta_id');
        $aspecto = (string) $request->input('aspecto');

        if (!$pid) return $this->json(false, 'ID de propuesta inválido');
        if (!array_key_exists($aspecto, self::ASPECTOS)) return $this->json(false, 'Aspecto inválido');

        $p = Proposal::find($pid);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');
        if ($p->usuario_id === Auth::id()) return $this->json(false, 'No puedes valorar tu propia propuesta');
        if (($p->progreso ?? 'idea') !== 'votacion') {
            return $this->json(false, 'Esta propuesta solo puede valorarse cuando está en la fase de Votación');
        }

        // Voto previo de este usuario (solo puede haber uno)
        $votoPrevio = Voto::where('propuesta_id', $pid)->where('usuario_id', Auth::id())->first();
        $signoPrevio = $votoPrevio && isset(self::ASPECTOS[$votoPrevio->aspecto])
            ? self::ASPECTOS[$votoPrevio->aspecto]['signo'] : 0;
        $signoNuevo  = self::ASPECTOS[$aspecto]['signo'];

        if ($votoPrevio && $votoPrevio->aspecto === $aspecto) {
            // Tocar el mismo aspecto = retirar el voto
            $votoPrevio->delete();
            $accion = 'removido';
            $deltaReputacion = -$signoPrevio;
        } elseif ($votoPrevio) {
            // Cambiar de aspecto (puede cambiar de signo)
            $votoPrevio->aspecto = $aspecto;
            $votoPrevio->save();
            $accion = 'cambiado';
            $deltaReputacion = $signoNuevo - $signoPrevio;
        } else {
            // Primer voto
            Voto::create(['propuesta_id' => $pid, 'usuario_id' => Auth::id(), 'aspecto' => $aspecto]);
            $accion = 'agregado';
            $deltaReputacion = $signoNuevo;
        }

        // Recalcular saldo neto de la propuesta (positivos - negativos)
        $p->votos = $this->saldoNeto($pid);
        $p->save();

        // Ajustar reputación del autor según el cambio neto
        if ($deltaReputacion !== 0) {
            try {
                $autor = $p->autor;
                if ($autor) {
                    $motivo = $deltaReputacion > 0 ? 'Tu propuesta fue valorada positivamente' : 'Tu propuesta recibió una valoración negativa';
                    app(GamificacionService::class)->otorgarReputacion($autor, $motivo, $deltaReputacion, Auth::id(), $p->id);
                }
            } catch (\Throwable $e) {}
        }

        return $this->json(true, 'Valoración ' . $accion, [
            'accion'      => $accion,
            'aspecto'     => $aspecto,
            'votos'       => $p->votos,
            'aspectos'    => $this->conteoAspectos($pid),
            'mi_voto'     => $this->miVoto($pid),
        ]);
    }

    /** Saldo neto de valoraciones: (positivos) - (negativos). */
    private function saldoNeto(int $pid): int
    {
        $conteos = Voto::where('propuesta_id', $pid)->whereNotNull('aspecto')
            ->select('aspecto', DB::raw('COUNT(*) as total'))
            ->groupBy('aspecto')->pluck('total', 'aspecto')->toArray();

        $neto = 0;
        foreach ($conteos as $aspecto => $total) {
            $signo = self::ASPECTOS[$aspecto]['signo'] ?? 1; // 'general' (histórico) cuenta como positivo
            $neto += $signo * (int) $total;
        }
        return $neto;
    }

    /** Conteo de cada aspecto para una propuesta. */
    private function conteoAspectos(int $pid): array
    {
        $conteos = Voto::where('propuesta_id', $pid)->whereNotNull('aspecto')
            ->select('aspecto', DB::raw('COUNT(*) as total'))
            ->groupBy('aspecto')->pluck('total', 'aspecto')->toArray();

        $out = [];
        foreach (self::ASPECTOS as $clave => $meta) {
            $out[] = [
                'clave' => $clave,
                'label' => $meta['label'],
                'icono' => $meta['icono'],
                'signo' => $meta['signo'],
                'total' => (int) ($conteos[$clave] ?? 0),
            ];
        }
        return $out;
    }

    /** El aspecto único que el usuario actual eligió en esta propuesta (o null). */
    private function miVoto(int $pid): ?string
    {
        if (!Auth::check()) return null;
        return Voto::where('propuesta_id', $pid)->where('usuario_id', Auth::id())
            ->whereNotNull('aspecto')->value('aspecto');
    }

    private function comentar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión para comentar');
        $pid       = (int) $request->input('propuesta_id');
        $contenido = trim((string) $request->input('contenido'));

        if (!$pid || $contenido === '') return $this->json(false, 'El comentario no puede estar vacío');
        if (strlen($contenido) > 1000) return $this->json(false, 'El comentario es demasiado largo (máximo 1000 caracteres)');

        $c = Comentario::create(['propuesta_id' => $pid, 'usuario_id' => Auth::id(), 'contenido' => $contenido]);
        $c->load('usuario');

        // Moderación automática con IA
        $this->moderarConIA('comentario', $c->id, $contenido);

        // Progreso automático: el primer comentario de alguien que NO sea el autor
        // mueve la propuesta de "Idea" a "Discusión" (los comentarios del propio
        // autor no cuentan para esto).
        $p = Proposal::find($pid);
        if ($p && $p->usuario_id !== Auth::id() && $p->progreso === 'idea') {
            $p->progreso = 'discusion';
            $p->progreso_actualizado_at = now();
            $p->save();

            PropuestaProgreso::create([
                'propuesta_id' => $p->id, 'progreso' => 'discusion', 'usuario_id' => null, 'fecha' => now(),
            ]);

            $meta = self::PROGRESO_STAGES['discusion'];
            try {
                Notificacion::crear(
                    $p->usuario_id, 'progreso_propuesta',
                    "¡Tu propuesta «{$p->titulo}» recibió su primer comentario y pasó a la fase \"Discusión\"!",
                    'propuesta.php?id=' . $p->id, $meta['icono'], $meta['color']
                );
            } catch (\Throwable $e) {}
        }

        // Gamificación: XP por comentar
        try {
            $gam = app(GamificacionService::class);
            $gam->otorgarXP(Auth::user(), 'comentar', $c->id);
            $gam->otorgarReputacion(Auth::user(), 'Comentaste en una propuesta', 2, null, $c->id);
        } catch (\Throwable $e) {}

        return $this->json(true, 'Comentario publicado', ['comentario' => $this->formatoComentario($c)]);
    }

    private function comentarios(Request $request)
    {
        $id = (int) $request->input('id');
        if (!$id) return $this->json(false, 'ID inválido');

        $comentarios = Comentario::with('usuario')->where('propuesta_id', $id)
            ->orderByDesc('fecha_creacion')->get()
            ->map(fn ($c) => $this->formatoComentario($c));

        return $this->json(true, 'OK', ['comentarios' => $comentarios, 'total' => $comentarios->count()]);
    }

    private function formatoComentario(Comentario $c): array
    {
        $u = $c->usuario;
        return [
            'id'               => $c->id,
            'contenido'        => $c->contenido,
            'autor'            => $u ? trim($u->nombre . ' ' . $u->apellido) : 'Anónimo',
            'autor_id'         => $c->usuario_id,
            'avatar'           => $u->avatar ?? null,
            'autor_nivel'      => $u->nivel ?? 1,
            'autor_titulo'     => $this->tituloData($u),
            'autor_marco'      => $u->marco_equipado ?? null,
            'fecha_creacion'   => optional($c->fecha_creacion ?? $c->created_at)->toDateTimeString(),
            'fecha_formateada' => optional($c->fecha_creacion ?? $c->created_at)->format('d/m/Y H:i'),
        ];
    }

    /** Devuelve datos del título equipado de un usuario (nombre + color). */
    private function tituloData($u): ?array
    {
        if (!$u || !$u->titulo_equipado) return null;
        $t = \App\Models\Titulo::where('clave', $u->titulo_equipado)->first();
        return $t ? ['nombre' => $t->nombre, 'color' => $t->color, 'rareza' => $t->rareza] : null;
    }

    private function top(Request $request)
    {
        $limit = (int) $request->input('limit', 5);
        $items = Proposal::with(['categoria', 'autor'])
            ->where('estado', 'activa')->where('votos', '>', 0)
            ->orderByDesc('votos')->limit($limit)->get()
            ->map(fn ($p) => $this->formato($p));

        return $this->json(true, 'OK', ['propuestas' => $items]);
    }

    private function misPropuestas()
    {
        if (!Auth::check()) return $this->json(false, 'No autenticado');
        $items = Proposal::with('categoria')->where('usuario_id', Auth::id())
            ->orderByDesc('fecha_creacion')->get()->map(fn ($p) => $this->formato($p));

        return $this->json(true, 'OK', ['propuestas' => $items]);
    }

    // ── Admin ───────────────────────────────────────────────
    private function adminComentarios()
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');

        $comentarios = Comentario::with('usuario')->orderByDesc('fecha_creacion')->limit(100)->get()
            ->map(fn ($c) => array_merge($this->formatoComentario($c), ['propuesta_id' => $c->propuesta_id]));

        return $this->json(true, 'OK', ['comentarios' => $comentarios]);
    }

    private function eliminarComentario(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');
        $id = (int) $request->input('id');
        if (!$id) return $this->json(false, 'ID inválido');
        Comentario::where('id', $id)->delete();
        return $this->json(true, 'Comentario eliminado');
    }

    /** Cambia la fase del ciclo de vida de una propuesta (acción de admin/moderador). */
    private function cambiarProgreso(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');

        $id       = (int) $request->input('id');
        $progreso = (string) $request->input('progreso');

        if (!array_key_exists($progreso, self::PROGRESO_STAGES)) return $this->json(false, 'Fase inválida');

        $p = Proposal::find($id);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');

        if ($p->progreso === $progreso) {
            return $this->json(true, 'La propuesta ya estaba en esa fase', ['progreso' => $progreso]);
        }

        $p->progreso = $progreso;
        $p->progreso_actualizado_at = now();
        $p->progreso_visto = false; // dispara la notificación al autor la próxima vez que la vea
        // "Destacada" es a la vez la última fase y el efecto visual de tarjeta destacada
        $p->destacada = ($progreso === 'destacada');
        $p->save();

        PropuestaProgreso::create([
            'propuesta_id' => $p->id, 'progreso' => $progreso, 'usuario_id' => Auth::id(), 'fecha' => now(),
        ]);

        $meta = self::PROGRESO_STAGES[$progreso];
        Notificacion::crear(
            $p->usuario_id,
            'progreso_propuesta',
            "Tu propuesta «{$p->titulo}» avanzó a la fase \"{$meta['label']}\"",
            'propuesta.php?id=' . $p->id,
            $meta['icono'],
            $meta['color']
        );

        $label = self::PROGRESO_STAGES[$progreso]['label'];
        return $this->json(true, "Propuesta movida a la fase «{$label}»", ['progreso' => $progreso]);
    }

    /**
     * El AUTOR (no solo admin/moderador) decide qué hacer cuando ya hay
     * suficientes comentarios de la comunidad: mejorar la propuesta o
     * dejarla como está y pasar directo a votación.
     */
    private function decidirFase(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');

        $id      = (int) $request->input('id');
        $destino = (string) $request->input('destino'); // 'mejoras' | 'votacion'

        if (!in_array($destino, ['mejoras', 'votacion'], true)) return $this->json(false, 'Opción inválida');

        $p = Proposal::find($id);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');
        if ($p->usuario_id !== Auth::id()) return $this->json(false, 'Solo el autor puede decidir esto');
        if ($p->progreso !== 'discusion') return $this->json(false, 'Esta propuesta ya no está en fase de discusión');

        $comentariosComunidad = Comentario::where('propuesta_id', $p->id)
            ->where('usuario_id', '!=', $p->usuario_id)->where('censurado', false)->count();
        if ($comentariosComunidad < self::UMBRAL_SUGERENCIA_MEJORA) {
            return $this->json(false, 'Todavía no hay suficientes comentarios de la comunidad para esta decisión');
        }

        $p->progreso = $destino;
        $p->progreso_actualizado_at = now();
        $p->save();

        PropuestaProgreso::create([
            'propuesta_id' => $p->id, 'progreso' => $destino, 'usuario_id' => Auth::id(), 'fecha' => now(),
        ]);

        $label = self::PROGRESO_STAGES[$destino]['label'];
        return $this->json(true, "¡Listo! Tu propuesta pasó a la fase «{$label}»", ['progreso' => $destino]);
    }

    private function adminEditar(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');
        $id     = (int) $request->input('id');
        $titulo = trim((string) $request->input('titulo'));
        $estado = in_array($request->input('estado'), ['activa', 'en_revision', 'aprobada', 'rechazada'])
            ? $request->input('estado') : 'activa';
        if (!$id || $titulo === '') return $this->json(false, 'Datos inválidos');
        Proposal::where('id', $id)->update(['titulo' => $titulo, 'estado' => $estado]);
        return $this->json(true, 'Propuesta actualizada');
    }

    // ─────────────────────────────────────────────────────────────
    //  Moderación automática con IA (llamada interna)
    // ─────────────────────────────────────────────────────────────
    private function moderarConIA(string $tipo, int $id, string $texto): void
    {
        $key = config('services.groq.key');
        if (empty($key)) return; // Sin API key no hay moderación IA

        try {
            $prompt = <<<TXT
Eres un moderador de contenido para una plataforma de participación ciudadana salvadoreña dirigida a jóvenes.

Analiza el siguiente texto y determina si contiene:
- Malas palabras o lenguaje obsceno
- Discurso de odio o discriminación
- Amenazas o violencia
- Acoso o insultos personales
- Spam o contenido malicioso
- Contenido sexual explícito

Texto a analizar:
"{$texto}"

Responde ÚNICAMENTE en este formato JSON exacto (sin markdown, sin explicaciones extra):
{
  "inapropiado": true o false,
  "razon": "descripción breve del problema o 'ninguno'",
  "severidad": "baja|media|alta",
  "texto_censurado": "el mismo texto pero con palabras inapropiadas reemplazadas por ***"
}
TXT;

            $http = Http::timeout(20)->withToken($key)->acceptJson();
            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $resp = $http->post(config('services.groq.url'), [
                'model'       => config('services.groq.model'),
                'messages'    => [
                    ['role' => 'system', 'content' => 'Eres un moderador de contenido. Responde SOLO en JSON válido.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => 0.1,
                'max_tokens'  => 300,
            ]);

            if (!$resp->successful()) return;

            $json = json_decode($resp->json('choices.0.message.content'), true);
            if (!is_array($json) || empty($json['inapropiado'])) return;

            // Los COMENTARIOS ya no se censuran automáticamente: se publican
            // visibles y solo generan una alerta para que un moderador los revise
            // (abajo). Guardamos el original por si el moderador decide censurar luego.
            // Las PROPUESTAS sí pasan a revisión por su mayor visibilidad.
            if ($tipo === 'comentario') {
                $item = Comentario::find($id);
                if ($item) {
                    $item->contenido_original = $texto;
                    $item->save();
                }
            } else {
                $item = Proposal::find($id);
                if ($item) {
                    $item->censurada     = true;
                    $item->razon_censura = $json['razon'] ?? 'Contenido inapropiado';
                    $item->estado        = 'en_revision';
                    $item->save();
                }
            }

            // Crear alerta en el panel admin
            $severidad = in_array($json['severidad'] ?? '', ['baja', 'media', 'alta'])
                ? $json['severidad'] : 'media';

            ModeracionAlerta::create([
                'tipo'               => $tipo,
                'referencia_id'      => $id,
                'contenido_original' => $texto,
                'razon'              => $json['razon'] ?? 'Contenido inapropiado',
                'severidad'          => $severidad,
            ]);

        } catch (\Throwable $e) {
            Log::error('Error en moderación automática: ' . $e->getMessage());
        }
    }
}
