<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\ModeracionAlerta;
use App\Models\Proposal;
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

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'listar'             => $this->listar($request),
            'detalle'            => $this->detalle($request),
            'crear'              => $this->crear($request),
            'editar'             => $this->editar($request),
            'eliminar'           => $this->eliminar($request),
            'votar'              => $this->votar($request),
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

        $propuestas = $items->map(function ($p) use ($votadas) {
            $row = $this->formato($p);
            $row['ya_vote'] = in_array($p->id, $votadas);
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
        $data['fecha_formateada'] = optional($p->fecha_creacion ?? $p->created_at)->format('d/m/Y \a \l\a\s H:i');

        return $this->json(true, 'OK', ['propuesta' => $data]);
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
            'diseno'           => $diseno,
            'color_acento'     => $request->input('color_acento'),
            'icono_extra'      => $request->input('icono_extra'),
            'efecto_categoria' => $request->boolean('efecto_categoria', true),
            'destacada'        => $request->boolean('destacada', false),
            'imagen'           => $imagen,
        ]);

        // Moderación automática con IA
        $this->moderarConIA('propuesta', $p->id, $titulo . ' ' . $descripcion);

        // Gamificación: XP + reputación por crear propuesta
        try {
            $gam = app(GamificacionService::class);
            $gam->otorgarXP(Auth::user(), 'crear_propuesta', $p->id);
            $gam->otorgarReputacion(Auth::user(), 'Creaste una propuesta', 5, null, $p->id);
        } catch (\Throwable $e) { \Illuminate\Support\Facades\Log::error('Gam error: '.$e->getMessage()); }

        return $this->json(true, '¡Propuesta publicada exitosamente!', ['id' => $p->id]);
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

    private function votar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión para votar');
        $pid = (int) $request->input('propuesta_id');
        if (!$pid) return $this->json(false, 'ID de propuesta inválido');

        $p = Proposal::find($pid);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');

        $voto = Voto::where('propuesta_id', $pid)->where('usuario_id', Auth::id())->first();
        if ($voto) {
            $voto->delete();
            if ($p->votos > 0) $p->decrement('votos');
            $accion = 'removido';
        } else {
            Voto::create(['propuesta_id' => $pid, 'usuario_id' => Auth::id()]);
            $p->increment('votos');
            $accion = 'agregado';
        }

        return $this->json(true, 'Voto ' . $accion, ['votos' => $p->fresh()->votos, 'accion' => $accion]);
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

            // Aplicar censura
            if ($tipo === 'comentario') {
                $item = Comentario::find($id);
                if ($item) {
                    $item->contenido_original = $texto;
                    $item->contenido          = $json['texto_censurado'] ?? $texto;
                    $item->censurado          = true;
                    $item->razon_censura      = $json['razon'] ?? 'Contenido inapropiado';
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
