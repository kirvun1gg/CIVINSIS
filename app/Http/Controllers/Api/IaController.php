<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Debate;
use App\Models\DebateRespuesta;
use App\Models\Desafio;
use App\Models\ModeracionAlerta;
use App\Models\Proposal;
use App\Models\UsuarioDesafio;
use App\Models\Voto;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'censurar'          => $this->censurar($request),
            // ── Entrenador cívico (Fase IA) ──────────────────────────
            'redactar'          => $this->redactar($request),
            'ortografia'        => $this->ortografia($request),
            'argumentos'        => $this->argumentos($request),
            'similares'         => $this->similares($request),
            'resumir_propuesta' => $this->resumirPropuesta($request),
            'titulos'           => $this->titulos($request),
            'categoria'         => $this->categoriaAuto($request),
            'explicar'          => $this->explicar($request),
            'reporte'           => $this->reporte($request),
            // ── CIVI mentor (cerebro del entrenador cívico) ──────────
            'coach'             => $this->coach($request),
            'nudge'             => $this->nudge($request),
            'tono'              => $this->tono($request),
            'revisar_tono'      => $this->revisarTono($request),
            'crecimiento'       => $this->crecimiento($request),
            'recomendar'        => $this->recomendar($request),
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
    // ═════════════════════════════════════════════════════════════
    //  ENTRENADOR CÍVICO — funciones de IA integradas en la plataforma
    // ═════════════════════════════════════════════════════════════

    /** System prompt corto para herramientas (respeta formato pedido). */
    private function sysTool(): string
    {
        $cats = Categoria::pluck('nombre')->implode(', ');
        return "Eres CIVI, el asistente de redacción y entrenador cívico de CIVINSIS, "
            . "una plataforma salvadoreña de participación ciudadana juvenil "
            . "(categorías: {$cats}). Respondes SIEMPRE en español, con precisión, y sigues "
            . "EXACTAMENTE el formato pedido, sin introducciones ni despedidas.";
    }

    /** Atajo: una llamada a Groq con system + user. */
    private function pedir(string $system, string $userPrompt, int $max = 700): array
    {
        return $this->llamarGroq([
            ['role' => 'system', 'content' => $system],
            ['role' => 'user',   'content' => $userPrompt],
        ], $max);
    }

    // ── Ayudar a redactar: de una idea suelta a un borrador completo ──
    private function redactar(Request $request)
    {
        $idea = trim((string) $request->input('idea', ''));
        if (mb_strlen($idea) < 6) return $this->json(false, 'Cuéntame tu idea en una frase');

        $prompt = <<<TXT
Un ciudadano quiere crear una propuesta pero solo tiene esta idea inicial:
"{$idea}"

Redacta una propuesta ciudadana completa y convincente. Devuelve SOLO un JSON válido (sin markdown ni texto extra) con este formato exacto:
{"titulo":"...","descripcion":"...","contenido":"..."}
- titulo: claro y atractivo, máximo 90 caracteres.
- descripcion: 2-3 oraciones, máximo 400 caracteres.
- contenido: desarrollo en 4 párrafos cortos que cubran problema, solución, impacto y recursos (usa saltos de línea entre párrafos).
TXT;

        $r    = $this->pedir($this->sysTool(), $prompt, 900);
        $json = json_decode($r['texto'], true);
        if (is_array($json) && isset($json['titulo'])) {
            return $this->json(true, 'OK', ['borrador' => [
                'titulo'      => (string) ($json['titulo'] ?? ''),
                'descripcion' => (string) ($json['descripcion'] ?? ''),
                'contenido'   => (string) ($json['contenido'] ?? ''),
            ], 'fuente' => $r['fuente']]);
        }
        // Respaldo: si no vino JSON, entregamos todo como contenido
        return $this->json(true, 'OK', ['borrador' => [
            'titulo' => '', 'descripcion' => '', 'contenido' => $r['texto'],
        ], 'fuente' => $r['fuente']]);
    }

    // ── Corregir SOLO ortografía y gramática (sin cambiar el sentido) ──
    private function ortografia(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if ($texto === '') return $this->json(false, 'No hay texto que corregir');

        $prompt = "Corrige ÚNICAMENTE la ortografía, tildes, puntuación y errores gramaticales "
            . "del siguiente texto. NO cambies el significado, el estilo ni agregues contenido. "
            . "Devuelve SOLO el texto corregido:\n\n{$texto}";
        $r = $this->pedir($this->sysTool(), $prompt, 800);
        return $this->json(true, 'OK', ['respuesta' => $r['texto'], 'fuente' => $r['fuente']]);
    }

    // ── Reforzar argumentos (más persuasivo, sin inventar datos) ──
    private function argumentos(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if ($texto === '') return $this->json(false, 'No hay texto que reforzar');

        $prompt = "Refuerza los argumentos de esta propuesta ciudadana: hazla más persuasiva, "
            . "agrega razones concretas y ejemplos plausibles, y responde a objeciones típicas. "
            . "NO inventes cifras estadísticas falsas. Mantén el tema y la voz del autor. "
            . "Devuelve SOLO el texto mejorado:\n\n{$texto}";
        $r = $this->pedir($this->sysTool(), $prompt, 850);
        return $this->json(true, 'OK', ['respuesta' => $r['texto'], 'fuente' => $r['fuente']]);
    }

    // ── Detectar propuestas similares (evitar duplicados) ──
    private function similares(Request $request)
    {
        $titulo  = trim((string) $request->input('titulo', ''));
        $desc    = trim((string) $request->input('descripcion', ''));
        $excluir = (int) $request->input('excluir_id', 0);
        $base    = trim($titulo . ' ' . $desc);
        if (mb_strlen($base) < 8) return $this->json(false, 'Escribe un título y una descripción primero');

        $stop = ['para', 'como', 'este', 'esta', 'esto', 'pero', 'porque', 'cuando', 'donde',
                 'sobre', 'entre', 'desde', 'hacia', 'todos', 'todas', 'nuestro', 'nuestra',
                 'propuesta', 'comunidad', 'ciudad', 'personas'];
        $tokenizar = function (string $s) use ($stop): array {
            $w = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($s), -1, PREG_SPLIT_NO_EMPTY);
            return array_values(array_diff(array_unique(array_filter($w, fn($x) => mb_strlen($x) >= 4)), $stop));
        };

        $baseTok = $tokenizar($base);
        if (!$baseTok) return $this->json(true, 'OK', ['similares' => []]);

        $catMap = Categoria::pluck('nombre', 'id');
        $cands  = Proposal::where('censurada', false)
            ->when($excluir, fn($q) => $q->where('id', '!=', $excluir))
            ->orderByDesc('fecha_creacion')->limit(120)
            ->get(['id', 'titulo', 'descripcion', 'categoria_id']);

        $rank = [];
        foreach ($cands as $c) {
            $ct = $tokenizar($c->titulo . ' ' . $c->descripcion);
            if (!$ct) continue;
            $inter = count(array_intersect($baseTok, $ct));
            if ($inter < 2) continue;
            $union = count(array_unique(array_merge($baseTok, $ct)));
            $rank[] = [
                'id'         => $c->id,
                'titulo'     => $c->titulo,
                'similitud'  => $union ? (int) round($inter / $union * 100) : 0,
                'categoria'  => $catMap[$c->categoria_id] ?? null,
            ];
        }
        usort($rank, fn($a, $b) => $b['similitud'] <=> $a['similitud']);
        return $this->json(true, 'OK', ['similares' => array_slice($rank, 0, 5)]);
    }

    // ── Resumir una propuesta larga ──
    private function resumirPropuesta(Request $request)
    {
        $id = (int) $request->input('id');
        $p  = Proposal::find($id);
        if (!$p) return $this->json(false, 'Propuesta no encontrada');

        $texto = trim(strip_tags((string) $p->contenido));
        if (mb_strlen($texto) < 200) return $this->json(false, 'Esta propuesta ya es corta; no necesita resumen');

        $prompt = "Resume esta propuesta ciudadana en 3 o 4 puntos clave (lista) y una frase final de "
            . "conclusión. Español, claro y neutral.\nTítulo: {$p->titulo}\n\n{$texto}";
        $r = $this->pedir($this->sysTool(), $prompt, 350);
        return $this->json(true, 'OK', ['resumen' => $r['texto'], 'fuente' => $r['fuente']]);
    }

    // ── Sugerir títulos ──
    private function titulos(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if ($texto === '') {
            $texto = trim(($request->input('titulo', '') . ' ' . $request->input('descripcion', '')));
        }
        if (mb_strlen($texto) < 8) return $this->json(false, 'Escribe primero una descripción');

        $prompt = "Propón 5 títulos posibles para esta propuesta ciudadana. Claros, atractivos y de "
            . "máximo 90 caracteres. Devuelve SOLO una lista, un título por línea, sin numeración ni "
            . "comillas:\n\n{$texto}";
        $r      = $this->pedir($this->sysTool(), $prompt, 220);
        $lineas = array_values(array_filter(array_map(
            fn($l) => trim(preg_replace('/^[\d\.\)\-\•\*\s"]+/u', '', $l)),
            explode("\n", $r['texto'])
        ), fn($l) => $l !== ''));
        return $this->json(true, 'OK', ['titulos' => array_slice($lineas, 0, 5), 'fuente' => $r['fuente']]);
    }

    // ── Detectar categoría automáticamente ──
    private function categoriaAuto(Request $request)
    {
        $titulo = trim((string) $request->input('titulo', ''));
        $desc   = trim((string) $request->input('descripcion', ''));
        $texto  = trim($titulo . ' ' . $desc);
        if (mb_strlen($texto) < 8) return $this->json(false, 'Escribe un título y una descripción primero');

        $cats  = Categoria::get(['id', 'nombre']);
        $lista = $cats->pluck('nombre')->implode(', ');
        $prompt = "Elige la categoría MÁS adecuada para esta propuesta ciudadana, de esta lista exacta: "
            . "{$lista}.\nResponde SOLO el nombre exacto de la categoría, sin ningún otro texto.\n\n"
            . "Propuesta: {$texto}";
        $r      = $this->pedir($this->sysTool(), $prompt, 30);
        $nombre = trim($r['texto']);

        $match = $cats->first(fn($c) => mb_strtolower($c->nombre) === mb_strtolower($nombre))
            ?? $cats->first(fn($c) => $nombre !== '' && mb_stripos($nombre, $c->nombre) !== false);

        if (!$match) return $this->json(true, 'OK', ['detectada' => false, 'sugerida' => $nombre]);
        return $this->json(true, 'OK', [
            'detectada'        => true,
            'categoria_id'     => $match->id,
            'categoria_nombre' => $match->nombre,
        ]);
    }

    // ── Explicar un concepto ciudadano ──
    private function explicar(Request $request)
    {
        $concepto = trim((string) $request->input('concepto', ''));
        if ($concepto === '') return $this->json(false, '¿Qué concepto quieres que te explique?');

        $prompt = "Explica de forma sencilla, breve (máximo 120 palabras) y con un ejemplo cercano a "
            . "El Salvador el concepto ciudadano: \"{$concepto}\". Si no fuera un concepto cívico, "
            . "explícalo igual y relaciónalo con la participación ciudadana.";
        $r = $this->pedir($this->sysTool(), $prompt, 300);
        return $this->json(true, 'OK', ['respuesta' => $r['texto'], 'fuente' => $r['fuente']]);
    }

    // ── Reporte personalizado del ciudadano ──
    private function reporte(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');
        $u = Auth::user();

        $numProp        = Proposal::where('usuario_id', $u->id)->count();
        $votosRecibidos = (int) Proposal::where('usuario_id', $u->id)->sum('votos');
        $numDebates     = Debate::where('usuario_id', $u->id)->count();
        $numResp        = DebateRespuesta::where('usuario_id', $u->id)->count();

        $gam     = app(\App\Services\GamificacionService::class);
        $perfil  = $gam->perfilCompleto($u);
        $misComp = collect($perfil['misiones'] ?? [])->where('completada', true)->count();

        $stats = [
            'propuestas'      => $numProp,
            'votos_recibidos' => $votosRecibidos,
            'debates'         => $numDebates,
            'aportes'         => $numResp,
            'nivel'           => $perfil['nivel'] ?? 1,
            'reputacion'      => $perfil['reputacion'] ?? 0,
            'racha'           => $perfil['racha_dias'] ?? 0,
            'logros'          => $perfil['total_logros'] ?? 0,
        ];

        $datos = "Nivel {$stats['nivel']}, reputación {$stats['reputacion']}, racha {$stats['racha']} días. "
            . "Propuestas creadas: {$stats['propuestas']}. Votos recibidos: {$stats['votos_recibidos']}. "
            . "Debates iniciados: {$stats['debates']}. Aportes en debates: {$stats['aportes']}. "
            . "Logros desbloqueados: {$stats['logros']}. Misiones completadas: {$misComp}.";

        $prompt = "Eres CIVI, el entrenador cívico de {$u->nombre}. Con estos datos de su actividad "
            . "en CIVINSIS, escribe un reporte personalizado, motivador y breve (máximo 140 palabras) que: "
            . "1) reconozca sus logros, 2) señale un punto a mejorar, 3) sugiera 1-2 acciones concretas "
            . "para su próxima participación. Tono cercano y juvenil.\n\nDatos: {$datos}";
        $r = $this->pedir($this->sysTool(), $prompt, 420);

        return $this->json(true, 'OK', ['reporte' => $r['texto'], 'stats' => $stats, 'fuente' => $r['fuente']]);
    }

    // ═════════════════════════════════════════════════════════════
    //  CIVI MENTOR — el cerebro del entrenador cívico
    //  Lee la actividad REAL del usuario y produce guía personalizada.
    // ═════════════════════════════════════════════════════════════

    private const ASPECTOS_POS = [
        'creativa'    => 'creativas',
        'argumentada' => 'bien argumentadas',
        'comunidad'   => 'beneficiosas para la comunidad',
        'factible'    => 'factibles',
        'innovadora'  => 'innovadoras',
    ];

    /** Agrega toda la actividad del usuario en señales medibles. */
    private function perfilActividad($u): array
    {
        $propQ          = Proposal::where('usuario_id', $u->id);
        $numProp        = (clone $propQ)->count();
        $votosRecibidos = (int) (clone $propQ)->sum('votos');
        $propIds        = (clone $propQ)->pluck('id');

        $numCom     = Comentario::where('usuario_id', $u->id)->count();
        $numDeb     = Debate::where('usuario_id', $u->id)->count();
        $numAportes = DebateRespuesta::where('usuario_id', $u->id)->count();

        // Valoraciones inteligentes positivas recibidas + aspecto más fuerte
        $valPos = 0; $aspectoFuerte = null;
        if ($propIds->isNotEmpty()) {
            $rows = Voto::whereIn('propuesta_id', $propIds)
                ->whereIn('aspecto', array_keys(self::ASPECTOS_POS))
                ->select('aspecto', DB::raw('COUNT(*) as total'))
                ->groupBy('aspecto')->get();
            $valPos = (int) $rows->sum('total');
            $aspectoFuerte = optional($rows->sortByDesc('total')->first())->aspecto;
        }

        // Categoría favorita (de sus propuestas)
        $catFav = null;
        if ($numProp > 0) {
            $catId = (clone $propQ)->select('categoria_id', DB::raw('COUNT(*) as t'))
                ->whereNotNull('categoria_id')->groupBy('categoria_id')
                ->orderByDesc('t')->value('categoria_id');
            $catFav = $catId ? optional(Categoria::find($catId))->nombre : null;
        }

        // Inactividad
        $inactivo = $u->ultimo_acceso ? (int) $u->ultimo_acceso->diffInDays(now()) : null;

        // Gamificación
        $gam     = app(\App\Services\GamificacionService::class);
        $perfil  = $gam->perfilCompleto($u);
        $nivel   = $perfil['nivel'] ?? 1;
        $xp      = $perfil['xp'] ?? 0;
        $xpSig   = $perfil['xp_siguiente_nivel'] ?? ($xp + 100);
        $xpFalt  = max(0, $xpSig - $xp);
        $mis     = collect($perfil['misiones'] ?? []);
        $misComp = $mis->where('completada', true)->count();
        $misCerca = $mis->where('completada', false)
            ->map(function ($m) {
                $m['ratio'] = ($m['cantidad'] ?? 0) > 0 ? ($m['progreso'] ?? 0) / $m['cantidad'] : 0;
                return $m;
            })->sortByDesc('ratio')->first();

        // Estilo de participación (dominante)
        $estilo = 'nuevo';
        $max = max($numProp, $numCom, $numAportes);
        if ($max > 0) {
            if ($numCom === $max)         $estilo = 'comentarista';
            if ($numProp === $max)        $estilo = 'proponente';
            if ($numAportes === $max)     $estilo = 'debatiente';
            $spread = [$numProp, $numCom, $numAportes];
            sort($spread);
            if ($spread[2] > 0 && ($spread[2] - $spread[0]) <= 2) $estilo = 'equilibrado';
        }

        return [
            'nivel' => $nivel, 'xp' => $xp, 'xp_faltante' => $xpFalt,
            'pct_nivel' => $perfil['porcentaje_nivel'] ?? 0,
            'reputacion' => $perfil['reputacion'] ?? 0, 'racha' => $perfil['racha_dias'] ?? 0,
            'propuestas' => $numProp, 'comentarios' => $numCom,
            'debates' => $numDeb, 'aportes' => $numAportes,
            'votos_recibidos' => $votosRecibidos, 'valoraciones_positivas' => $valPos,
            'aspecto_fuerte' => $aspectoFuerte, 'categoria_favorita' => $catFav,
            'inactividad_dias' => $inactivo, 'estilo' => $estilo,
            'logros' => $perfil['total_logros'] ?? 0, 'misiones_completadas' => $misComp,
            'mision_cerca' => $misCerca ? [
                'nombre' => $misCerca['nombre'] ?? '', 'progreso' => $misCerca['progreso'] ?? 0,
                'cantidad' => $misCerca['cantidad'] ?? 0,
            ] : null,
        ];
    }

    /** Objetivo adaptativo: el siguiente paso ideal según el comportamiento. */
    private function construirObjetivo(array $s): array
    {
        if ($s['propuestas'] === 0) {
            return ['clave' => 'primera_propuesta', 'titulo' => 'Comparte tu primera propuesta',
                'descripcion' => 'Ya conoces la plataforma; es momento de proponer tu propia idea.',
                'cta_texto' => 'Crear propuesta', 'cta_url' => 'crear.php'];
        }
        if ($s['comentarios'] === 0) {
            return ['clave' => 'primeros_comentarios', 'titulo' => 'Comenta en 3 propuestas',
                'descripcion' => 'Aportar en las ideas de otros amplía tu participación y tu reputación.',
                'cta_texto' => 'Ver propuestas', 'cta_url' => 'dashboard.php'];
        }
        if ($s['aportes'] === 0) {
            return ['clave' => 'primer_debate', 'titulo' => 'Da tu opinión en un debate',
                'descripcion' => 'Los debates son el mejor lugar para practicar tus argumentos.',
                'cta_texto' => 'Ir a debates', 'cta_url' => 'debates.php'];
        }
        if ($s['xp_faltante'] > 0 && $s['xp_faltante'] <= 60) {
            return ['clave' => 'subir_nivel', 'titulo' => "Solo te faltan {$s['xp_faltante']} XP para el nivel " . ($s['nivel'] + 1),
                'descripcion' => 'Un comentario valioso o un voto en una propuesta te acercan.',
                'cta_texto' => 'Participar', 'cta_url' => 'dashboard.php'];
        }
        if ($s['mision_cerca'] && $s['mision_cerca']['cantidad'] > 0
            && $s['mision_cerca']['progreso'] / $s['mision_cerca']['cantidad'] >= 0.5) {
            $falta = $s['mision_cerca']['cantidad'] - $s['mision_cerca']['progreso'];
            return ['clave' => 'mision_cerca', 'titulo' => "Completa la misión: {$s['mision_cerca']['nombre']}",
                'descripcion' => "Te falta muy poco ({$falta}) para desbloquearla.",
                'cta_texto' => 'Ver misiones', 'cta_url' => 'progreso.php'];
        }
        // Diversificar según estilo
        if ($s['estilo'] === 'comentarista') {
            return ['clave' => 'diversificar_propuesta', 'titulo' => 'Convierte una idea en propuesta',
                'descripcion' => 'Comentas muy bien; comparte una idea propia y llévala más lejos.',
                'cta_texto' => 'Crear propuesta', 'cta_url' => 'crear.php'];
        }
        if ($s['estilo'] === 'proponente') {
            return ['clave' => 'diversificar_debate', 'titulo' => 'Abre o participa en un debate',
                'descripcion' => 'Tienes buenas ideas; llévalas a un debate para enriquecerlas.',
                'cta_texto' => 'Ir a debates', 'cta_url' => 'debates.php'];
        }
        return ['clave' => 'seguir', 'titulo' => 'Sigue construyendo comunidad',
            'descripcion' => 'Vas muy bien. Elige un desafío nuevo y mantén tu racha.',
            'cta_texto' => 'Ver desafíos', 'cta_url' => 'desafios.php'];
    }

    /** Señales de crecimiento motivadoras (máx. 3). */
    private function construirProgreso(array $s): array
    {
        $out = [];
        if ($s['xp_faltante'] > 0 && $s['xp_faltante'] <= 120) {
            $out[] = ['icono' => 'fa-bolt', 'texto' => "Solo te faltan {$s['xp_faltante']} XP para subir al nivel " . ($s['nivel'] + 1) . '.'];
        }
        if ($s['mision_cerca'] && $s['mision_cerca']['cantidad'] > 0) {
            $falta = $s['mision_cerca']['cantidad'] - $s['mision_cerca']['progreso'];
            if ($falta > 0 && $falta <= $s['mision_cerca']['cantidad']) {
                $out[] = ['icono' => 'fa-bullseye', 'texto' => "Te falta {$falta} para completar «{$s['mision_cerca']['nombre']}»."];
            }
        }
        if ($s['racha'] >= 2) {
            $out[] = ['icono' => 'fa-fire', 'texto' => "Llevas {$s['racha']} días seguidos participando. ¡No rompas la racha!"];
        }
        if ($s['valoraciones_positivas'] > 0 && $s['aspecto_fuerte']) {
            $lbl = self::ASPECTOS_POS[$s['aspecto_fuerte']] ?? 'valiosas';
            $out[] = ['icono' => 'fa-star', 'texto' => "La comunidad valora tus propuestas como {$lbl}."];
        }
        return array_slice($out, 0, 3);
    }

    /** Hechos deterministas del "Análisis de CIVI" (fallback sin IA). */
    private function hechosAnalisis(array $s): array
    {
        $f = [];
        $estilos = ['comentarista' => 'comentando', 'proponente' => 'creando propuestas',
                    'debatiente' => 'debatiendo', 'equilibrado' => 'de forma equilibrada',
                    'nuevo' => 'explorando la plataforma'];
        $f[] = 'Participas principalmente ' . ($estilos[$s['estilo']] ?? 'explorando') . '.';
        if ($s['categoria_favorita']) $f[] = "El tema donde más participas es {$s['categoria_favorita']}.";
        if ($s['aspecto_fuerte']) {
            $lbl = self::ASPECTOS_POS[$s['aspecto_fuerte']] ?? 'valiosas';
            $f[] = "Tus propuestas destacan por ser {$lbl}.";
        }
        if ($s['aportes'] === 0)          $f[] = 'Podrías crecer participando más en debates.';
        elseif ($s['propuestas'] === 0)   $f[] = 'Aún no has creado tu primera propuesta.';
        return $f;
    }

    // ── Acción principal del mentor: panel completo personalizado ──
    private function coach(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');
        $u = Auth::user();

        $s        = $this->perfilActividad($u);
        $objetivo = $this->construirObjetivo($s);
        $progreso = $this->construirProgreso($s);
        $hechos   = $this->hechosAnalisis($s);

        // Narración cálida del análisis (IA), con respaldo determinista.
        $datos = "Estilo: {$s['estilo']}. Nivel {$s['nivel']}, {$s['xp']} XP, reputación {$s['reputacion']}, "
            . "racha {$s['racha']} días. Propuestas {$s['propuestas']}, comentarios {$s['comentarios']}, "
            . "aportes en debates {$s['aportes']}, votos recibidos {$s['votos_recibidos']}, "
            . "valoraciones positivas {$s['valoraciones_positivas']}"
            . ($s['aspecto_fuerte'] ? " (destacan como {$s['aspecto_fuerte']})" : '') . ". "
            . ($s['categoria_favorita'] ? "Categoría favorita: {$s['categoria_favorita']}. " : '')
            . "Logros {$s['logros']}, misiones completadas {$s['misiones_completadas']}.";

        $prompt = "Eres CIVI, mentor cívico de {$u->nombre}. Con estos datos reales de su actividad, "
            . "escribe un análisis breve (máx. 90 palabras, 2-3 frases) cálido y personalizado que reconozca "
            . "su estilo, un punto fuerte y un área de mejora. Tono cercano y motivador, sin listas ni saludos. "
            . "Datos: {$datos}";
        $r = $this->pedir($this->sysTool(), $prompt, 220);
        $analisis = ($r['fuente'] === 'groq' && mb_strlen(trim($r['texto'])) > 10)
            ? trim($r['texto'])
            : implode(' ', $hechos);

        $nombre = $u->nombre;
        $saludo = $s['estilo'] === 'nuevo'
            ? "¡Hola, {$nombre}! Empecemos a construir tu camino cívico."
            : "¡Hola de nuevo, {$nombre}! Esto es lo que veo en tu progreso.";

        return $this->json(true, 'OK', [
            'saludo'    => $saludo,
            'stats'     => $s,
            'objetivo'  => $objetivo,
            'progreso'  => $progreso,
            'analisis'  => $analisis,
            'hechos'    => $hechos,
            'fuente'    => $r['fuente'],
        ]);
    }

    // ── Nudge contextual: aparece SOLO cuando aporta algo (no invasivo) ──
    private function nudge(Request $request)
    {
        if (!Auth::check()) return $this->json(true, 'OK', ['mostrar' => false]);
        $u        = Auth::user();
        $contexto = (string) $request->input('contexto', '');
        $s        = $this->perfilActividad($u);

        $n = null; // ['texto','cta_texto','cta_url','prioridad']
        $set = function ($texto, $cta, $url, $pri) use (&$n) {
            if (!$n || $pri > $n['prioridad']) $n = compact('texto') + ['cta_texto' => $cta, 'cta_url' => $url, 'prioridad' => $pri];
        };

        // Reglas por contexto — CIVI solo habla si detecta una oportunidad real
        if ($s['propuestas'] === 0 && in_array($contexto, ['dashboard', 'propuestas', 'inicio', 'perfil'])) {
            $set('He notado que aún no has creado tu primera propuesta. ¿La construimos juntos?', 'Crear propuesta', 'crear.php', 3);
        }
        if ($s['propuestas'] === 0 && $s['comentarios'] >= 3) {
            $set('Tus comentarios reciben buenas valoraciones. Tal vez ya sea momento de compartir una idea propia.', 'Crear propuesta', 'crear.php', 4);
        }
        if ($s['aportes'] === 0 && in_array($contexto, ['debates', 'debate'])) {
            $set('Aún no has opinado en ningún debate. Tu punto de vista puede enriquecerlo.', 'Participar', 'debates.php', 2);
        }
        if ($s['xp_faltante'] > 0 && $s['xp_faltante'] <= 40) {
            $set("Estás a solo {$s['xp_faltante']} XP de subir al nivel " . ($s['nivel'] + 1) . '. ¡Un aporte más!', 'Participar', 'dashboard.php', 3);
        }
        if ($s['inactividad_dias'] !== null && $s['inactividad_dias'] >= 4 && $s['categoria_favorita']) {
            $set("Han aparecido novedades en {$s['categoria_favorita']}, tu tema favorito. ¿Les echas un vistazo?", 'Ver propuestas', 'dashboard.php', 2);
        }

        if (!$n) return $this->json(true, 'OK', ['mostrar' => false]);
        unset($n['prioridad']);
        return $this->json(true, 'OK', ['mostrar' => true] + $n);
    }

    // ── Reformular un comentario en tono constructivo (educar, no censurar) ──
    private function tono(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if ($texto === '') return $this->json(false, 'No hay comentario que reformular');

        $prompt = "Reescribe este comentario de una plataforma de participación ciudadana para que "
            . "sea respetuoso y constructivo, SIN perder la crítica o el punto de vista de la persona. "
            . "Quita insultos, agresividad y mayúsculas de grito. Mantén el mismo idioma y sé breve. "
            . "Devuelve SOLO el comentario reformulado:\n\n{$texto}";
        $r = $this->pedir($this->sysTool(), $prompt, 300);
        return $this->json(true, 'OK', ['respuesta' => $r['texto'], 'fuente' => $r['fuente']]);
    }

    // ── Señales de crecimiento en vivo (tras una acción del usuario) ──
    private function crecimiento(Request $request)
    {
        if (!Auth::check()) return $this->json(true, 'OK', ['disponible' => false]);
        $s = $this->perfilActividad(Auth::user());
        return $this->json(true, 'OK', [
            'disponible'   => true,
            'nivel'        => $s['nivel'],
            'xp'           => $s['xp'],
            'xp_faltante'  => $s['xp_faltante'],
            'racha'        => $s['racha'],
            'logros'       => $s['logros'],
            'mision_cerca' => $s['mision_cerca'],
        ]);
    }

    // ── Recomendaciones de contenido personalizadas (con el "por qué") ──
    private function recomendar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, 'Debes iniciar sesión');
        $u = Auth::user();

        // Categoría favorita (por sus propuestas y comentarios)
        $catFavId = Proposal::where('usuario_id', $u->id)->whereNotNull('categoria_id')
            ->select('categoria_id', DB::raw('COUNT(*) as t'))->groupBy('categoria_id')
            ->orderByDesc('t')->value('categoria_id');
        if (!$catFavId) {
            $catFavId = Comentario::where('comentarios.usuario_id', $u->id)
                ->join('propuestas', 'propuestas.id', '=', 'comentarios.propuesta_id')
                ->whereNotNull('propuestas.categoria_id')
                ->select('propuestas.categoria_id', DB::raw('COUNT(*) as t'))
                ->groupBy('propuestas.categoria_id')->orderByDesc('t')->value('propuestas.categoria_id');
        }
        $catFavNombre = $catFavId ? optional(Categoria::find($catFavId))->nombre : null;

        // Lo que ya tocó (para no recomendárselo)
        $votadas    = Voto::where('usuario_id', $u->id)->pluck('propuesta_id')->all();
        $comentadas = Comentario::where('usuario_id', $u->id)->pluck('propuesta_id')->all();
        $propExcluir = array_unique(array_merge($votadas, $comentadas));
        $debatidos  = DebateRespuesta::where('usuario_id', $u->id)->pluck('debate_id')->all();
        $desafHechos = UsuarioDesafio::where('usuario_id', $u->id)->where('completado', true)->pluck('desafio_id')->all();

        // ── Propuestas: favorita + en votación + con apoyo, sin las suyas ni las ya tocadas ──
        $props = Proposal::with('categoria')->where('censurada', false)
            ->where('usuario_id', '!=', $u->id)
            ->when($propExcluir, fn($q) => $q->whereNotIn('id', $propExcluir))
            ->orderByRaw('CASE WHEN categoria_id = ? THEN 0 ELSE 1 END', [$catFavId ?: 0])
            ->orderByRaw("CASE WHEN progreso = 'votacion' THEN 0 ELSE 1 END")
            ->orderByDesc('votos')->limit(3)->get()
            ->map(function ($p) use ($catFavId) {
                $razon = $p->categoria_id === $catFavId && $catFavId
                    ? 'Porque te interesa ' . ($p->categoria->nombre ?? 'este tema')
                    : ($p->progreso === 'votacion'
                        ? 'Está en votación y tu voto cuenta ahora'
                        : 'Está ganando apoyo en la comunidad');
                return [
                    'id' => $p->id, 'titulo' => $p->titulo, 'razon' => $razon,
                    'categoria' => $p->categoria->nombre ?? '',
                    'icono' => $p->categoria->icono ?? 'fas fa-tag',
                    'color' => $p->categoria->color ?? '#36c0a1',
                    'url' => 'propuesta.php?id=' . $p->id,
                ];
            })->values();

        // ── Debates: activos, favorito, sin los suyos ni los ya respondidos ──
        $debs = Debate::with('categoria')->where('censurado', false)->where('estado', 'activo')
            ->where('usuario_id', '!=', $u->id)
            ->when($debatidos, fn($q) => $q->whereNotIn('id', $debatidos))
            ->orderByRaw('CASE WHEN categoria_id = ? THEN 0 ELSE 1 END', [$catFavId ?: 0])
            ->latest('fecha_creacion')->limit(2)->get()
            ->map(function ($d) use ($catFavId) {
                $razon = $d->categoria_id === $catFavId && $catFavId
                    ? 'Sobre ' . ($d->categoria->nombre ?? 'tu tema favorito') . ', donde más participas'
                    : 'Un debate activo donde tu opinión sumaría';
                return [
                    'id' => $d->id, 'titulo' => $d->titulo, 'razon' => $razon,
                    'categoria' => $d->categoria->nombre ?? '',
                    'icono' => $d->categoria->icono ?? 'fas fa-comments',
                    'color' => $d->categoria->color ?? '#ef7e22',
                    'url' => 'debate.php?id=' . $d->id,
                ];
            })->values();

        // ── Desafío: activo, sin completar, favorito primero ──
        $des = Desafio::with('categoria')->where('activo', true)
            ->when($desafHechos, fn($q) => $q->whereNotIn('id', $desafHechos))
            ->orderByRaw('CASE WHEN categoria_id = ? THEN 0 ELSE 1 END', [$catFavId ?: 0])
            ->orderByDesc('xp_recompensa')->first();
        $desafio = $des ? [
            'id' => $des->id, 'titulo' => $des->titulo,
            'razon' => ($des->categoria_id === $catFavId && $catFavId)
                ? 'Un reto de ' . ($des->categoria->nombre ?? 'tu tema') . ' para ti'
                : 'Un reto para ganar ' . ($des->xp_recompensa ?? 0) . ' XP',
            'xp' => $des->xp_recompensa ?? 0,
            'url' => 'crear.php?desafio_id=' . $des->id,
        ] : null;

        // Intro narrada (con respaldo determinista)
        $intro = $catFavNombre
            ? "Basándome en tu interés por {$catFavNombre} y en tu actividad, te sugiero:"
            : 'Basándome en tu actividad en la comunidad, te sugiero:';
        if ($props->isEmpty() && $debs->isEmpty() && !$desafio) {
            $intro = '¡Vas al día! Por ahora no tengo nada nuevo que recomendarte. Sigue participando.';
        }

        return $this->json(true, 'OK', [
            'intro'      => $intro,
            'categoria'  => $catFavNombre,
            'propuestas' => $props,
            'debates'    => $debs,
            'desafio'    => $desafio,
        ]);
    }

    // ── Juzgar el tono de un comentario en vivo (IA; heurística si no hay key) ──
    private function revisarTono(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if (mb_strlen($texto) < 4) return $this->json(true, 'OK', ['agresivo' => false]);

        // Sin IA disponible → respaldo heurístico
        if (!config('services.groq.key')) {
            return $this->json(true, 'OK', ['agresivo' => $this->tonoHeuristico($texto), 'fuente' => 'local']);
        }

        // Con IA → juicio contextual real (reutiliza el analizador de moderación)
        $r = $this->analizarContenido($texto);
        return $this->json(true, 'OK', [
            'agresivo' => (bool) ($r['inapropiado'] ?? false),
            'motivo'   => $r['razon'] ?? '',
            'fuente'   => 'groq',
        ]);
    }

    /** Respaldo simple cuando no hay IA configurada. */
    private function tonoHeuristico(string $texto): bool
    {
        $t = mb_strtolower($texto);
        $palabras = ['idiota', 'estupido', 'estúpido', 'tonto', 'tonta', 'imbecil', 'imbécil',
            'inutil', 'inútil', 'basura', 'callate', 'cállate', 'maldito', 'pendejo', 'baboso',
            'ignorante', 'tarado', 'burro', 'asqueroso', 'ridiculo', 'ridículo', 'payaso', 'mierda', 'estupidez'];
        foreach ($palabras as $w) {
            if (preg_match('/\b' . preg_quote($w, '/') . '\b/u', $t)) return true;
        }
        $letras = preg_replace('/[^\p{L}]/u', '', $texto);
        if (mb_strlen($letras) >= 8 && mb_strtoupper($letras) === $letras) return true;
        if (substr_count($texto, '!') >= 3) return true;
        return false;
    }

    // ── Censurar desde el panel: el moderador decide ocultar el contenido ──
    private function censurar(Request $request)
    {
        if (!Auth::check() || !in_array(Auth::user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, 'Sin permisos');

        $id = (int) $request->input('id'); // ID de la alerta (no del contenido)
        $alerta = ModeracionAlerta::find($id);
        if (!$alerta) return $this->json(false, 'Alerta no encontrada');

        $razon = $alerta->razon ?: 'Contenido inapropiado';

        switch ($alerta->tipo) {
            case 'comentario':
                $item = Comentario::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'El comentario ya no existe');
                if (!$item->contenido_original) $item->contenido_original = $item->contenido;
                $item->contenido     = '[Comentario retirado por un moderador]';
                $item->censurado     = true;
                $item->razon_censura = $razon;
                $item->save();
                break;

            case 'debate_respuesta':
                $item = DebateRespuesta::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'La respuesta ya no existe');
                if (!$item->contenido_original) $item->contenido_original = $item->contenido;
                $item->contenido     = '[Respuesta retirada por un moderador]';
                $item->censurado     = true;
                $item->razon_censura = $razon;
                $item->save();
                break;

            case 'propuesta':
                $item = Proposal::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'La propuesta ya no existe');
                $item->censurada     = true;
                $item->razon_censura = $razon;
                $item->estado        = 'en_revision';
                $item->save();
                break;

            case 'debate':
                $item = Debate::find($alerta->referencia_id);
                if (!$item) return $this->json(false, 'El debate ya no existe');
                $item->censurado     = true;
                $item->razon_censura = $razon;
                $item->save();
                break;

            default:
                return $this->json(false, 'Tipo no soportado');
        }

        $alerta->revisado     = true;
        $alerta->revisado_at  = now();
        $alerta->revisado_por = Auth::id();
        $alerta->save();

        return $this->json(true, 'Contenido censurado y alerta cerrada');
    }

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
