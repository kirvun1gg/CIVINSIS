<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Debate;
use App\Models\DebateRespuesta;
use App\Models\DebateVotoRespuesta;
use App\Models\ModeracionAlerta;
use App\Models\Titulo;
use App\Services\GamificacionService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DebateController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        $accion = $request->input('accion', '');

        return match ($accion) {
            'listar'          => $this->listar($request),
            'detalle'         => $this->detalle($request),
            'crear'           => $this->crear($request),
            'respuestas'      => $this->respuestas($request),
            'responder'       => $this->responder($request),
            'votar_respuesta' => $this->votarRespuesta($request),
            'destacar'        => $this->destacar($request),
            'cerrar'          => $this->cerrar($request),
            'resumen_ia'      => $this->resumenIA($request),
            default           => $this->json(false, 'Acción no reconocida'),
        };
    }

    // ─────────────────────────────────────────────────────────
    //  LISTAR debates (con filtros y paginación)
    // ─────────────────────────────────────────────────────────
    private function listar(Request $request)
    {
        $pagina    = max(1, (int) $request->input('pagina', 1));
        $porPagina = 9;
        $categoria = (int) $request->input('categoria_id', 0);
        $estado    = trim((string) $request->input('estado', ''));
        $buscar    = trim((string) $request->input('buscar', ''));
        $orden     = trim((string) $request->input('orden', 'recientes'));

        $q = Debate::with(['categoria', 'autor'])->where('censurado', false);

        if ($categoria) $q->where('categoria_id', $categoria);
        if (in_array($estado, ['activo', 'cerrado'])) $q->where('estado', $estado);
        if ($buscar !== '') {
            $q->where(function ($w) use ($buscar) {
                $w->where('titulo', 'like', "%{$buscar}%")
                  ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $q = match ($orden) {
            'populares'    => $q->orderByDesc('respuestas_count'),
            'participacion'=> $q->orderByDesc('participantes'),
            default        => $q->orderByDesc('fecha_creacion'),
        };

        $total   = (clone $q)->count();
        $debates = $q->forPage($pagina, $porPagina)->get()->map(fn ($d) => $this->formato($d));

        return $this->json(true, 'OK', [
            'debates'     => $debates,
            'total'       => $total,
            'pagina'      => $pagina,
            'total_paginas' => (int) ceil($total / $porPagina),
        ]);
    }

    private function detalle(Request $request)
    {
        $id = (int) $request->input('id');
        $d  = Debate::with(['categoria', 'autor'])->find($id);
        if (!$d || $d->censurado) return $this->json(false, 'Debate no encontrado');

        return $this->json(true, 'OK', ['debate' => $this->formato($d)]);
    }

    // ─────────────────────────────────────────────────────────
    //  CREAR debate
    // ─────────────────────────────────────────────────────────
    private function crear(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión para crear un debate');

        $titulo      = trim((string) $request->input('titulo'));
        $descripcion = trim((string) $request->input('descripcion'));
        $categoriaId = (int) $request->input('categoria_id');

        if ($titulo === '' || mb_strlen($titulo) < 10) {
            return $this->json(false, 'La pregunta del debate debe tener al menos 10 caracteres');
        }
        if ($descripcion === '') {
            return $this->json(false, 'Agrega una descripción para dar contexto al debate');
        }
        if (!$categoriaId) return $this->json(false, 'Selecciona una categoría');

        $d = Debate::create([
            'titulo'       => $titulo,
            'descripcion'  => $descripcion,
            'categoria_id' => $categoriaId,
            'usuario_id'   => Auth::id(),
            'estado'       => 'activo',
        ]);

        $this->moderarConIA('debate', $d->id, $titulo . ' ' . $descripcion);

        try {
            $gam = app(GamificacionService::class);
            $gam->otorgarXP(Auth::user(), 'crear_debate', $d->id);
            $gam->otorgarReputacion(Auth::user(), 'Iniciaste un debate', 4, null, $d->id);
        } catch (\Throwable $e) { Log::error('Gam error (debate): ' . $e->getMessage()); }

        return $this->json(true, '¡Debate publicado! Ya pueden empezar a opinar.', ['id' => $d->id]);
    }

    private function cerrar(Request $request)
    {
        if (!Auth::check() || !Auth::user()->esAdmin()) return $this->json(false, 'No autorizado');

        $d = Debate::find((int) $request->input('id'));
        if (!$d) return $this->json(false, 'Debate no encontrado');

        $d->estado = $d->estado === 'activo' ? 'cerrado' : 'activo';
        $d->save();

        return $this->json(true, $d->estado === 'cerrado' ? 'Debate cerrado' : 'Debate reabierto', ['estado' => $d->estado]);
    }

    // ─────────────────────────────────────────────────────────
    //  RESPUESTAS
    // ─────────────────────────────────────────────────────────
    private function respuestas(Request $request)
    {
        $debateId = (int) $request->input('debate_id');
        $orden    = trim((string) $request->input('orden', 'relevantes'));
        if (!$debateId) return $this->json(false, 'ID inválido');

        $q = DebateRespuesta::with(['usuario', 'cita.usuario'])
            ->where('debate_id', $debateId)
            ->whereNull('parent_id'); // el hilo raíz; los hijos se anidan en el formato

        $q = match ($orden) {
            'recientes' => $q->orderByDesc('fecha_creacion'),
            'votadas'   => $q->orderByDesc('votos')->orderByDesc('fecha_creacion'),
            default     => $q->orderByDesc('destacada')->orderByDesc('votos')->orderByDesc('fecha_creacion'), // relevantes
        };

        $respuestas = $q->get()->map(fn ($r) => $this->formatoRespuesta($r, $orden));

        return $this->json(true, 'OK', [
            'respuestas' => $respuestas,
            'total'      => $respuestas->count(),
        ]);
    }

    private function responder(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión para participar');

        $debateId  = (int) $request->input('debate_id');
        $contenido = trim((string) $request->input('contenido'));
        $parentId  = $request->input('parent_id') ? (int) $request->input('parent_id') : null;
        $citaId    = $request->input('cita_id') ? (int) $request->input('cita_id') : null;

        if (!$debateId || $contenido === '') return $this->json(false, 'La respuesta no puede estar vacía');
        if (mb_strlen($contenido) > 1500) return $this->json(false, 'La respuesta es demasiado larga (máximo 1500 caracteres)');

        $debate = Debate::find($debateId);
        if (!$debate) return $this->json(false, 'Debate no encontrado');
        if ($debate->estado === 'cerrado') return $this->json(false, 'Este debate está cerrado y ya no acepta respuestas');

        $r = DebateRespuesta::create([
            'debate_id'  => $debateId,
            'usuario_id' => Auth::id(),
            'parent_id'  => $parentId,
            'cita_id'    => $citaId,
            'contenido'  => $contenido,
        ]);
        $r->load(['usuario', 'cita.usuario']);

        $this->moderarConIA('debate_respuesta', $r->id, $contenido);

        // Actualiza contadores cacheados del debate
        $debate->respuestas_count = $debate->respuestas()->count();
        $debate->participantes    = $debate->respuestas()->distinct('usuario_id')->count('usuario_id');
        $debate->save();

        try {
            $gam = app(GamificacionService::class);
            $gam->otorgarXP(Auth::user(), 'responder_debate', $r->id);
            $gam->otorgarReputacion(Auth::user(), 'Participaste en un debate', 2, null, $r->id);
        } catch (\Throwable $e) { Log::error('Gam error (respuesta debate): ' . $e->getMessage()); }

        return $this->json(true, 'Respuesta publicada', ['respuesta' => $this->formatoRespuesta($r)]);
    }

    private function votarRespuesta(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión para votar');

        $id = (int) $request->input('respuesta_id');
        $r  = DebateRespuesta::find($id);
        if (!$r) return $this->json(false, 'Respuesta no encontrada');

        $voto = DebateVotoRespuesta::where('respuesta_id', $id)->where('usuario_id', Auth::id())->first();

        if ($voto) {
            $voto->delete();
            $r->votos = max(0, $r->votos - 1);
            $r->save();
            return $this->json(true, 'Voto retirado', ['votos' => $r->votos, 'votado' => false]);
        }

        DebateVotoRespuesta::create(['respuesta_id' => $id, 'usuario_id' => Auth::id()]);
        $r->increment('votos');

        if ($r->usuario_id !== Auth::id()) {
            try {
                $gam = app(GamificacionService::class);
                $autor = $r->usuario;
                if ($autor) {
                    $gam->otorgarXP($autor, 'recibir_voto_respuesta', $r->id);
                    $gam->otorgarReputacion($autor, 'Recibiste un voto en un debate', 1, Auth::id(), $r->id);
                }
            } catch (\Throwable $e) {}
        }

        return $this->json(true, 'Voto registrado', ['votos' => $r->votos, 'votado' => true]);
    }

    private function destacar(Request $request)
    {
        if (!Auth::check() || !Auth::user()->esAdmin()) return $this->json(false, 'No autorizado');

        $r = DebateRespuesta::find((int) $request->input('respuesta_id'));
        if (!$r) return $this->json(false, 'Respuesta no encontrada');

        $r->destacada = !$r->destacada;
        $r->save();

        return $this->json(true, $r->destacada ? 'Respuesta destacada' : 'Ya no está destacada', ['destacada' => $r->destacada]);
    }

    // ─────────────────────────────────────────────────────────
    //  RESUMEN con IA (solo cuando hay suficientes respuestas)
    // ─────────────────────────────────────────────────────────
    private function resumenIA(Request $request)
    {
        $id = (int) $request->input('debate_id');
        $d  = Debate::find($id);
        if (!$d) return $this->json(false, 'Debate no encontrado');

        $total = $d->respuestas()->count();
        if ($total < 5) {
            return $this->json(false, 'Necesitas al menos 5 respuestas para generar un resumen', ['minimo' => 5, 'actual' => $total]);
        }

        // Reutiliza el resumen cacheado si no hay respuestas nuevas desde que se generó
        $ultimaRespuesta = $d->respuestas()->latest('fecha_creacion')->value('fecha_creacion');
        if ($d->resumen_ia && $d->resumen_generado_at && $ultimaRespuesta && $d->resumen_generado_at->gte($ultimaRespuesta)) {
            return $this->json(true, 'OK', ['resumen' => $d->resumen_ia, 'cacheado' => true]);
        }

        $textos = $d->respuestas()->where('censurado', false)->orderByDesc('votos')->limit(40)
            ->pluck('contenido')->implode("\n- ");

        $key = config('services.groq.key');
        if (empty($key)) {
            return $this->json(false, 'El resumen con IA no está disponible en este momento');
        }

        try {
            $prompt = <<<TXT
Eres un asistente que resume debates ciudadanos de forma neutral y equilibrada.

Pregunta del debate: "{$d->titulo}"

Respuestas de los participantes:
- {$textos}

Genera un resumen breve (máximo 120 palabras) en español que identifique:
1. Los puntos de vista principales (a favor / en contra / matices).
2. Si hay algún consenso o punto en común.
No tomes partido. No inventes datos. Responde solo con el resumen, sin encabezados.
TXT;

            $http = Http::timeout(25)->withToken($key)->acceptJson();
            if (app()->environment('local')) $http = $http->withoutVerifying();

            $resp = $http->post(config('services.groq.url'), [
                'model'       => config('services.groq.model'),
                'messages'    => [
                    ['role' => 'system', 'content' => 'Resumes debates ciudadanos de forma neutral y concisa.'],
                    ['role' => 'user',   'content' => $prompt],
                ],
                'temperature' => 0.4,
                'max_tokens'  => 300,
            ]);

            if (!$resp->successful()) return $this->json(false, 'No se pudo generar el resumen, intenta más tarde');

            $texto = trim((string) $resp->json('choices.0.message.content'));
            if ($texto === '') return $this->json(false, 'No se pudo generar el resumen, intenta más tarde');

            $d->resumen_ia          = $texto;
            $d->resumen_generado_at = now();
            $d->save();

            return $this->json(true, 'OK', ['resumen' => $texto, 'cacheado' => false]);
        } catch (\Throwable $e) {
            Log::error('Error generando resumen IA de debate: ' . $e->getMessage());
            return $this->json(false, 'No se pudo generar el resumen, intenta más tarde');
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Formato / helpers
    // ─────────────────────────────────────────────────────────
    private function formato(Debate $d): array
    {
        $cat = $d->categoria;
        $autor = $d->autor;
        return [
            'id'                => $d->id,
            'titulo'            => $d->titulo,
            'descripcion'       => $d->descripcion,
            'categoria_id'      => $d->categoria_id,
            'categoria'         => $cat->nombre ?? '',
            'categoria_icono'   => $cat->icono ?? 'fas fa-tag',
            'categoria_color'   => $cat->color ?? '#36c0a1',
            'autor'             => $autor ? trim($autor->nombre . ' ' . $autor->apellido) : 'Anónimo',
            'autor_id'          => $d->usuario_id,
            'autor_avatar'      => $autor->avatar ?? null,
            'autor_nivel'       => $autor->nivel ?? 1,
            'autor_titulo'      => $this->tituloData($autor),
            'estado'            => $d->estado,
            'participantes'     => (int) $d->participantes,
            'respuestas_count'  => (int) $d->respuestas_count,
            'tiene_resumen'     => (bool) $d->resumen_ia,
            'fecha_creacion'    => optional($d->fecha_creacion)->toDateTimeString(),
            'fecha_formateada'  => optional($d->fecha_creacion)->format('d/m/Y'),
        ];
    }

    private function formatoRespuesta(DebateRespuesta $r, string $orden = 'relevantes'): array
    {
        $u = $r->usuario;
        $votada = Auth::check() ? DebateVotoRespuesta::where('respuesta_id', $r->id)->where('usuario_id', Auth::id())->exists() : false;

        $cita = null;
        if ($r->cita) {
            $cu = $r->cita->usuario;
            $cita = [
                'id'        => $r->cita->id,
                'autor'     => $cu ? trim($cu->nombre . ' ' . $cu->apellido) : 'Anónimo',
                'contenido' => mb_strimwidth($r->cita->contenido, 0, 180, '…'),
            ];
        }

        // Hijos directos (respuestas dentro del hilo)
        $hijosQ = $r->hijos()->with(['usuario', 'cita.usuario'])->orderBy('fecha_creacion');
        $hijos  = $hijosQ->get()->map(fn ($h) => $this->formatoRespuesta($h, 'recientes'));

        return [
            'id'               => $r->id,
            'contenido'        => $r->contenido,
            'autor'            => $u ? trim($u->nombre . ' ' . $u->apellido) : 'Anónimo',
            'autor_id'         => $r->usuario_id,
            'avatar'           => $u->avatar ?? null,
            'autor_nivel'      => $u->nivel ?? 1,
            'autor_titulo'     => $this->tituloData($u),
            'parent_id'        => $r->parent_id,
            'cita'             => $cita,
            'votos'            => (int) $r->votos,
            'votada'           => $votada,
            'destacada'        => (bool) $r->destacada,
            'fecha_creacion'   => optional($r->fecha_creacion)->toDateTimeString(),
            'fecha_formateada' => optional($r->fecha_creacion)->format('d/m/Y H:i'),
            'respuestas'       => $hijos,
        ];
    }

    private function tituloData($u): ?array
    {
        if (!$u || !$u->titulo_equipado) return null;
        $t = Titulo::where('clave', $u->titulo_equipado)->first();
        return $t ? ['nombre' => $t->nombre, 'color' => $t->color, 'rareza' => $t->rareza] : null;
    }

    /** Moderación automática con IA, replica el patrón de ProposalController. */
    private function moderarConIA(string $tipo, int $id, string $texto): void
    {
        $key = config('services.groq.key');
        if (empty($key)) return;

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
            if (app()->environment('local')) $http = $http->withoutVerifying();

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

            if ($tipo === 'debate_respuesta') {
                $item = DebateRespuesta::find($id);
                if ($item) {
                    $item->contenido_original = $texto;
                    $item->contenido          = $json['texto_censurado'] ?? $texto;
                    $item->censurado          = true;
                    $item->razon_censura      = $json['razon'] ?? 'Contenido inapropiado';
                    $item->save();
                }
            } else { // debate
                $item = Debate::find($id);
                if ($item) {
                    $item->censurado     = true;
                    $item->razon_censura = $json['razon'] ?? 'Contenido inapropiado';
                    $item->save();
                }
            }

            $severidad = in_array($json['severidad'] ?? '', ['baja', 'media', 'alta']) ? $json['severidad'] : 'media';

            ModeracionAlerta::create([
                'tipo'               => $tipo,
                'referencia_id'      => $id,
                'contenido_original' => $texto,
                'razon'              => $json['razon'] ?? 'Contenido inapropiado',
                'severidad'          => $severidad,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en moderación automática (debate): ' . $e->getMessage());
        }
    }
}
