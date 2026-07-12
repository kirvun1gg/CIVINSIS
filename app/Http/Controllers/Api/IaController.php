<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Debate;
use App\Models\DebateRespuesta;
use App\Models\ModeracionAlerta;
use App\Models\Proposal;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IaController extends Controller
{
    use ApiResponse;

    public function handle(Request $request)
    {
        $accion = $request->input('accion', 'chat');

        return match ($accion) {
            'chat'              => $this->chat($request),
            'mejorar'           => $this->mejorar($request),
            'ideas'             => $this->ideas($request),
            'sugerir_mejoras'   => $this->sugerirMejoras($request),
            'moderar'           => $this->moderar($request),
            'alertas'           => $this->alertas($request),
            'marcar_revisado'   => $this->marcarRevisado($request),
            'aprobar'           => $this->aprobar($request),
            default             => $this->json(false, 'Acción no reconocida'),
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  SYSTEM PROMPT — CIVI abierta y amigable
    // ─────────────────────────────────────────────────────────────
    private function systemPrompt(): string
    {
        $cats   = Categoria::pluck('nombre')->implode(', ');
        $nombre = Auth::check() ? Auth::user()->nombre : 'visitante';

        return <<<TXT
Eres "CIVI", una IA amigable, inteligente y versátil integrada en CIVINSIS, una plataforma
salvadoreña de participación ciudadana. Estás hablando con {$nombre}.

Tu especialidad es la participación ciudadana, propuestas comunitarias y todo lo relacionado
con CIVINSIS (categorías disponibles: {$cats}). Sin embargo, eres una IA completa: puedes
responder preguntas generales sobre cultura, ciencia, historia, política, tecnología,
matemáticas, noticias y cualquier tema de conocimiento general.

Comportamiento:
- Si la pregunta es sobre CIVINSIS o participación ciudadana, prioriza ese contexto y motiva
  al usuario a participar.
- Si la pregunta es general (política, cultura, ciencia, etc.), respóndela con precisión
  y naturalidad, como cualquier asistente de IA haría.
- Si no sabes algo con certeza, dilo honestamente.
- Responde SIEMPRE en español, con tono cercano, juvenil y directo.
- Sé conciso (máximo ~150 palabras) salvo que te pidan más detalle.
- Nunca generes contenido ofensivo, violento o inapropiado.
TXT;
    }

    // ─────────────────────────────────────────────────────────────
    //  CHAT general
    // ─────────────────────────────────────────────────────────────
    private function chat(Request $request)
    {
        $mensaje   = trim((string) $request->input('mensaje', ''));
        $historial = $request->input('historial', []);
        if ($mensaje === '') return $this->json(false, 'Escribe un mensaje');

        $messages = [['role' => 'system', 'content' => $this->systemPrompt()]];
        if (is_array($historial)) {
            foreach (array_slice($historial, -8) as $h) {
                $role       = ($h['role'] ?? '') === 'user' ? 'user' : 'assistant';
                $messages[] = ['role' => $role, 'content' => (string) ($h['content'] ?? '')];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $mensaje];

        $respuesta = $this->llamarGroq($messages);
        return $this->json(true, 'OK', ['respuesta' => $respuesta['texto'], 'fuente' => $respuesta['fuente']]);
    }

    // ─────────────────────────────────────────────────────────────
    //  MEJORAR texto de propuesta
    // ─────────────────────────────────────────────────────────────
    private function mejorar(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if ($texto === '') return $this->json(false, 'No hay texto que mejorar');

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user',   'content' => "Mejora la redacción de esta propuesta ciudadana para que sea clara, "
                . "concreta y convincente. Devuelve SOLO el texto mejorado, sin comentarios:\n\n$texto"],
        ];
        $respuesta = $this->llamarGroq($messages);
        return $this->json(true, 'OK', ['respuesta' => $respuesta['texto'], 'fuente' => $respuesta['fuente']]);
    }

    // ─────────────────────────────────────────────────────────────
    //  IDEAS de propuestas
    // ─────────────────────────────────────────────────────────────
    private function ideas(Request $request)
    {
        $categoria = trim((string) $request->input('categoria', ''));
        $extra     = $categoria !== '' ? "de la categoría \"$categoria\"" : 'de cualquier categoría';

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user',   'content' => "Dame 3 ideas breves de propuestas ciudadanas $extra. "
                . "Formato: lista numerada, una línea cada una."],
        ];
        $respuesta = $this->llamarGroq($messages);
        return $this->json(true, 'OK', ['respuesta' => $respuesta['texto'], 'fuente' => $respuesta['fuente']]);
    }

    // ─────────────────────────────────────────────────────────────
    //  SUGERIR MEJORAS — CIVI analiza la propuesta + comentarios de
    //  la comunidad y sugiere cómo mejorarla. Solo el autor puede pedirlo.
    // ─────────────────────────────────────────────────────────────
    private function sugerirMejoras(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');

        $id = (int) $request->input('id');
        $p  = Proposal::find($id);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');
        if ($p->usuario_id !== Auth::id()) return $this->json(false, 'Solo el autor puede pedir sugerencias para esta propuesta');

        $comentarios = \App\Models\Comentario::where('propuesta_id', $id)->where('censurado', false)
            ->orderByDesc('fecha_creacion')->limit(15)->pluck('contenido')->implode("\n- ");
        if ($comentarios === '') $comentarios = '(todavía no hay comentarios de la comunidad)';

        $prompt = <<<TXT
Esta es una propuesta ciudadana en CIVINSIS:
Título: {$p->titulo}
Descripción: {$p->descripcion}
Contenido: {$p->contenido}

Estos son los comentarios que la comunidad ha dejado sobre ella:
- {$comentarios}

Basándote en la propuesta y en los comentarios, dame de 2 a 4 sugerencias concretas y breves
de cómo el autor podría mejorarla antes de pasar a votación. Ve directo a las sugerencias,
sin introducción ni despedida, en formato de lista corta. Tono cercano y motivador.
TXT;

        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user',   'content' => $prompt],
        ];
        $respuesta = $this->llamarGroq($messages, 500);
        return $this->json(true, 'OK', ['sugerencias' => $respuesta['texto'], 'fuente' => $respuesta['fuente']]);
    }
    private function moderar(Request $request)
    {
        // Solo admins y moderadores pueden llamar esto manualmente,
        // pero también lo llamamos internamente desde ProposalController.
        $tipo  = $request->input('tipo', 'comentario'); // comentario | propuesta
        $id    = (int) $request->input('id');
        if (!$id) return $this->json(false, 'ID inválido');

        if ($tipo === 'comentario') {
            $item = Comentario::find($id);
            if (!$item) return $this->json(false, 'Comentario no encontrado');
            $texto = $item->contenido;
        } else {
            $item = Proposal::find($id);
            if (!$item) return $this->json(false, 'Propuesta no encontrada');
            $texto = $item->titulo . ' ' . $item->descripcion . ' ' . $item->contenido;
        }

        $resultado = $this->analizarContenido($texto);

        if ($resultado['inapropiado']) {
            $this->aplicarCensura($tipo, $item, $texto, $resultado);
            return $this->json(true, 'Contenido censurado', [
                'censurado' => true,
                'razon'     => $resultado['razon'],
                'severidad' => $resultado['severidad'],
            ]);
        }

        return $this->json(true, 'Contenido apropiado', ['censurado' => false]);
    }

    /**
     * Llama a Groq para analizar si el texto es inapropiado.
     * Devuelve un array con: inapropiado (bool), razon (string), severidad (string), texto_censurado (string).
     */
    private function analizarContenido(string $texto): array
    {
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

        $messages = [
            ['role' => 'system', 'content' => 'Eres un moderador de contenido. Responde SOLO en JSON válido.'],
            ['role' => 'user',   'content' => $prompt],
        ];

        try {
            $respuesta = $this->llamarGroq($messages, 300);
            $json      = json_decode($respuesta['texto'], true);

            if (is_array($json) && isset($json['inapropiado'])) {
                return [
                    'inapropiado'    => (bool) $json['inapropiado'],
                    'razon'          => $json['razon'] ?? 'Contenido inapropiado',
                    'severidad'      => in_array($json['severidad'] ?? '', ['baja', 'media', 'alta'])
                                        ? $json['severidad'] : 'media',
                    'texto_censurado' => $json['texto_censurado'] ?? $texto,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Error en moderación IA: ' . $e->getMessage());
        }

        // Si la IA falla, dejamos pasar (no censuramos por error)
        return ['inapropiado' => false, 'razon' => '', 'severidad' => 'baja', 'texto_censurado' => $texto];
    }

    /**
     * Aplica la censura en BD y crea la alerta para el panel admin.
     */
    private function aplicarCensura(string $tipo, $item, string $textoOriginal, array $resultado): void
    {
        if ($tipo === 'comentario') {
            $item->contenido_original = $textoOriginal;
            $item->contenido          = $resultado['texto_censurado'];
            $item->censurado          = true;
            $item->razon_censura      = $resultado['razon'];
            $item->save();
        } else {
            // En propuestas solo marcamos como censurada y guardamos la razón;
            // el título/descripción se mantiene para que el admin la revise.
            $item->censurada     = true;
            $item->razon_censura = $resultado['razon'];
            $item->estado        = 'en_revision';
            $item->save();
        }

        // Crear alerta para el panel de administración
        ModeracionAlerta::create([
            'tipo'               => $tipo,
            'referencia_id'      => $item->id,
            'contenido_original' => $textoOriginal,
            'razon'              => $resultado['razon'],
            'severidad'          => $resultado['severidad'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  ALERTAS — lista alertas para el panel admin
    // ─────────────────────────────────────────────────────────────
    private function alertas(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');

        $soloSinRevisar = $request->boolean('sin_revisar', false);

        $query = ModeracionAlerta::orderByDesc('created_at');
        if ($soloSinRevisar) $query->where('revisado', false);

        $alertas = $query->limit(100)->get()->map(fn ($a) => [
            'id'                 => $a->id,
            'tipo'               => $a->tipo,
            'referencia_id'      => $a->referencia_id,
            'contenido_original' => $a->contenido_original,
            'razon'              => $a->razon,
            'severidad'          => $a->severidad,
            'revisado'           => $a->revisado,
            'revisado_at'        => optional($a->revisado_at)->toDateTimeString(),
            'fecha'              => optional($a->created_at)->format('d/m/Y H:i'),
        ]);

        $pendientes = ModeracionAlerta::where('revisado', false)->count();

        return $this->json(true, 'OK', ['alertas' => $alertas, 'pendientes' => $pendientes]);
    }

    // ─────────────────────────────────────────────────────────────
    //  MARCAR REVISADO — el admin cierra una alerta
    // ─────────────────────────────────────────────────────────────
    private function marcarRevisado(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');

        $id    = (int) $request->input('id');
        $alerta = ModeracionAlerta::find($id);
        if (!$alerta) return $this->json(false, 'Alerta no encontrada');

        $alerta->revisado      = true;
        $alerta->revisado_at   = now();
        $alerta->revisado_por  = Auth::id();
        $alerta->save();

        return $this->json(true, 'Alerta marcada como revisada');
    }

    // ─────────────────────────────────────────────────────────────
    //  APROBAR — publica/restaura el contenido pese a la alerta de IA
    //  (ej. una propuesta que quedó en revisión, un comentario que
    //  quedó censurado, etc.) y cierra la alerta.
    // ─────────────────────────────────────────────────────────────
    private function aprobar(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');

        $id = (int) $request->input('id'); // ID de la alerta (no del contenido)
        $alerta = ModeracionAlerta::find($id);
        if (!$alerta) return $this->json(false, 'Alerta no encontrada');

        switch ($alerta->tipo) {
            case 'propuesta':
                $item = Proposal::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'La propuesta ya no existe');
                $item->censurada     = false;
                $item->razon_censura = null;
                $item->estado        = 'activa';
                $item->save();
                break;

            case 'comentario':
                $item = Comentario::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'El comentario ya no existe');
                $item->censurado      = false;
                $item->razon_censura  = null;
                $item->contenido      = $item->contenido_original ?: $item->contenido;
                $item->save();
                break;

            case 'debate':
                $item = Debate::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'El debate ya no existe');
                $item->censurado     = false;
                $item->razon_censura = null;
                $item->save();
                break;

            case 'debate_respuesta':
                $item = DebateRespuesta::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'La respuesta ya no existe');
                $item->censurado      = false;
                $item->razon_censura  = null;
                $item->contenido      = $item->contenido_original ?: $item->contenido;
                $item->save();
                break;

            default:
                return $this->json(false, 'Tipo de contenido no reconocido');
        }

        $alerta->revisado      = true;
        $alerta->revisado_at   = now();
        $alerta->revisado_por  = Auth::id();
        $alerta->save();

        return $this->json(true, 'Contenido publicado. La alerta quedó cerrada.');
    }

    // ─────────────────────────────────────────────────────────────
    //  LLAMADA A GROQ
    // ─────────────────────────────────────────────────────────────
    private function llamarGroq(array $messages, int $maxTokens = 700): array
    {
        $key = config('services.groq.key');

        if (empty($key)) {
            return ['texto' => $this->respaldo($messages), 'fuente' => 'local'];
        }

        try {
            $http = Http::timeout(30)->withToken($key)->acceptJson();

            if (app()->environment('local')) {
                $http = $http->withoutVerifying();
            }

            $resp = $http->post(config('services.groq.url'), [
                'model'       => config('services.groq.model'),
                'messages'    => $messages,
                'temperature' => 0.7,
                'max_tokens'  => $maxTokens,
            ]);

            if ($resp->successful()) {
                $texto = $resp->json('choices.0.message.content');
                if ($texto) return ['texto' => trim($texto), 'fuente' => 'groq'];
            }
            Log::warning('Groq respondió error', ['status' => $resp->status(), 'body' => $resp->body()]);
        } catch (\Throwable $e) {
            Log::error('Error llamando a Groq: ' . $e->getMessage());
        }

        return ['texto' => $this->respaldo($messages), 'fuente' => 'local'];
    }

    // ─────────────────────────────────────────────────────────────
    //  RESPALDO local (sin API key)
    // ─────────────────────────────────────────────────────────────
    private function respaldo(array $messages): string
    {
        $ultimo = strtolower((string) end($messages)['content']);

        if (str_contains($ultimo, 'idea')) {
            $cat = Categoria::inRandomOrder()->first();
            return "Aquí van 3 ideas para empezar:\n"
                . "1. Una jornada comunitaria de " . ($cat->nombre ?? 'mejora') . " en tu barrio.\n"
                . "2. Un programa de voluntariado juvenil con metas medibles.\n"
                . "3. Una campaña de concientización en redes y escuelas.\n\n"
                . "💡 Consejo: agrega un objetivo concreto y a quién beneficia.";
        }

        if (str_contains($ultimo, 'mejora') || str_contains($ultimo, 'redacc')) {
            return "Para que tu propuesta convenza, recuerda: 1) un título claro, "
                . "2) qué problema resuelve, 3) cómo lo lograrías y 4) a quién beneficia. "
                . "Sé concreto y usa datos si los tienes.";
        }

        $total = Proposal::where('estado', 'activa')->count();
        return "¡Hola! Soy CIVI 🤖, tu asistente en CIVINSIS. Puedo ayudarte con propuestas, "
            . "responder preguntas generales o explicarte cómo funciona la plataforma. "
            . "Ahora mismo hay $total propuestas activas. ¿En qué te ayudo?";
    }
}
