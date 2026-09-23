<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\CiviConversacion;
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
use Illuminate\Support\Facades\App;
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
            'chat'                => $this->chat($request),
            // ── Memoria de chat de CIVI (página exclusiva) ───────────
            'chat_conversaciones' => $this->chatConversaciones($request),
            'chat_conversacion'   => $this->chatConversacion($request),
            'chat_renombrar'      => $this->chatRenombrar($request),
            'chat_eliminar'       => $this->chatEliminar($request),
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
            default             => $this->json(false, __('civinsis.toast.comunes.accion_no_reconocida')),
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  SYSTEM PROMPT — CIVI abierta y amigable
    // ─────────────────────────────────────────────────────────────
    /** Nombre del idioma actual para instruir a la IA (App::getLocale() ya refleja el idioma del usuario). */
    private function idiomaRespuesta(): string
    {
        return match (App::getLocale()) {
            'en' => 'inglés (English)',
            'fr' => 'francés (français)',
            default => 'español',
        };
    }

    private function systemPrompt(): string
    {
        $cats   = Categoria::pluck('nombre')->implode(', ');
        $nombre = Auth::check() ? auth_user()->nombre : 'visitante';
        $idioma = $this->idiomaRespuesta();

        return <<<TXT
Eres "CIVI", el entrenador cívico de CIVINSIS, una plataforma salvadoreña de participación
ciudadana juvenil. Hablas con {$nombre}.

Tu misión es acompañar a la persona para que participe más y mejor, y para que aprenda sobre
ciudadanía, democracia y participación de forma natural, divertida y personalizada.

Tu personalidad:
- Eres un mentor cercano y motivador, como un buen profesor. Nunca robótico.
- Lenguaje amigable: ni demasiado formal ni infantil.
- Frases cortas, positivas, motivadoras y siempre respetuosas.

Cuando expliques un concepto (democracia, voto, propuesta ciudadana, debate, participación,
derechos, transparencia, etc.):
- Explícalo como un profesor cercano, NUNCA como Wikipedia.
- Usa ejemplos sencillos y cotidianos, adaptados a un público joven.
- Ve al grano; evita definiciones largas o técnicas. Si ayuda, cierra con una pregunta o un
  pequeño reto que invite a participar.

Sobre CIVINSIS (categorías: {$cats}): anima a crear propuestas, participar en debates, votar
y completar misiones y desafíos. Cada respuesta debería, con naturalidad, acercar a la persona
a participar más.

También puedes responder preguntas generales (ciencia, historia, cultura, tecnología) con
precisión y naturalidad. Si no sabes algo con certeza, dilo con honestidad.

Reglas: responde SIEMPRE en {$idioma}, sin importar en qué idioma te escriban; sé conciso
(~120 palabras salvo que pidan más detalle); nunca generes contenido ofensivo, violento o
inapropiado.
TXT;
    }

    // ─────────────────────────────────────────────────────────────
    //  CHAT general
    // ─────────────────────────────────────────────────────────────
    private function chat(Request $request)
    {
        $mensaje = trim((string) $request->input('mensaje', ''));
        if ($mensaje === '') return $this->json(false, __('civinsis.toast.ia.escribe_mensaje'));

        // Con sesión: la conversación vive en BD y ES la memoria (por usuario,
        // persistente entre visitas — ver app/Models/CiviConversacion.php).
        // Sin sesión (invitado): memoria efímera tal como la manda el cliente,
        // como funcionaba antes de este cambio.
        $conversacion = null;
        if (Auth::check()) {
            $conversacion = $this->obtenerOCrearConversacion($request);
            if (!$conversacion) return $this->json(false, __('civinsis.toast.ia.conversacion_no_encontrada'));
        }

        // System prompt base + contexto real del usuario (para que CIVI entienda su progreso)
        $system = $this->systemPrompt();
        if (Auth::check()) {
            $s   = $this->perfilActividad(auth_user());
            $obj = $this->construirObjetivo($s);
            $catFav = $s['categoria_favorita'] ? "Tema favorito: {$s['categoria_favorita']}. " : '';
            $system .= "\n\nContexto real de esta persona (úsalo para personalizar tus respuestas, "
                . "sin recitarlo de golpe): nivel {$s['nivel']}, {$s['xp']} XP, racha {$s['racha']} días. "
                . "Estilo de participación: {$s['estilo']}. {$catFav}"
                . "Propuestas: {$s['propuestas']}, comentarios: {$s['comentarios']}, aportes en debates: {$s['aportes']}. "
                . "Su siguiente objetivo ideal sería: {$obj['titulo']}. "
                . "Si viene al caso, anímale hacia ese objetivo con naturalidad; no fuerces el tema.";
        }

        $messages = [['role' => 'system', 'content' => $system]];

        if ($conversacion) {
            foreach ($conversacion->mensajes()->orderByDesc('created_at')->limit(16)->get()->reverse() as $m) {
                $messages[] = ['role' => $m->rol, 'content' => $m->contenido];
            }
        } else {
            $historial = $request->input('historial', []);
            if (is_array($historial)) {
                foreach (array_slice($historial, -8) as $h) {
                    $role       = ($h['role'] ?? '') === 'user' ? 'user' : 'assistant';
                    $messages[] = ['role' => $role, 'content' => (string) ($h['content'] ?? '')];
                }
            }
        }
        $messages[] = ['role' => 'user', 'content' => $mensaje];

        $respuesta = $this->llamarGroq($messages);
        $extra = ['respuesta' => $respuesta['texto'], 'fuente' => $respuesta['fuente']];

        if ($conversacion) {
            $conversacion->mensajes()->create(['rol' => 'user', 'contenido' => $mensaje]);
            $conversacion->mensajes()->create(['rol' => 'assistant', 'contenido' => $respuesta['texto']]);
            if (!$conversacion->titulo) {
                $conversacion->titulo = CiviConversacion::tituloDesde($mensaje);
            }
            $conversacion->touch();
            $conversacion->save();
            $extra['conversacion_id'] = $conversacion->id;
            $extra['titulo']          = $conversacion->titulo;
        }

        return $this->json(true, 'OK', $extra);
    }

    /** Conversación indicada por el cliente (si es del usuario) o una nueva. */
    private function obtenerOCrearConversacion(Request $request): ?CiviConversacion
    {
        $id = (int) $request->input('conversacion_id', 0);
        if ($id) {
            return CiviConversacion::where('id', $id)->where('usuario_id', Auth::id())->first();
        }
        return CiviConversacion::create(['usuario_id' => Auth::id()]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Memoria de chat — historial de conversaciones por usuario
    // ─────────────────────────────────────────────────────────────
    private function chatConversaciones(Request $request)
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $conversaciones = CiviConversacion::where('usuario_id', Auth::id())
            ->whereHas('mensajes')
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($c) => [
                'id'         => $c->id,
                'titulo'     => $c->titulo ?: __('civinsis.civi_pagina.conversacion_sin_titulo'),
                'fecha'      => optional($c->updated_at)->diffForHumans(),
                'updated_at' => optional($c->updated_at)->toDateTimeString(),
            ]);

        return $this->json(true, 'OK', ['conversaciones' => $conversaciones]);
    }

    private function chatConversacion(Request $request)
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $id = (int) $request->input('id');
        $c  = CiviConversacion::where('id', $id)->where('usuario_id', Auth::id())->first();
        if (!$c) return $this->json(false, __('civinsis.toast.ia.conversacion_no_encontrada'));

        $mensajes = $c->mensajes()->get()->map(fn ($m) => [
            'id'        => $m->id,
            'rol'       => $m->rol,
            'contenido' => $m->contenido,
        ]);

        return $this->json(true, 'OK', ['id' => $c->id, 'titulo' => $c->titulo, 'mensajes' => $mensajes]);
    }

    private function chatRenombrar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $id     = (int) $request->input('id');
        $titulo = trim((string) $request->input('titulo', ''));
        if ($titulo === '') return $this->json(false, __('civinsis.toast.ia.titulo_vacio'));

        $c = CiviConversacion::where('id', $id)->where('usuario_id', Auth::id())->first();
        if (!$c) return $this->json(false, __('civinsis.toast.ia.conversacion_no_encontrada'));

        $c->titulo = mb_substr($titulo, 0, 60);
        $c->save();

        return $this->json(true, 'OK', ['titulo' => $c->titulo]);
    }

    private function chatEliminar(Request $request)
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.no_autenticado'));

        $id = (int) $request->input('id');
        $c  = CiviConversacion::where('id', $id)->where('usuario_id', Auth::id())->first();
        if (!$c) return $this->json(false, __('civinsis.toast.ia.conversacion_no_encontrada'));

        $c->delete();

        return $this->json(true, __('civinsis.toast.ia.conversacion_eliminada'));
    }

    // ─────────────────────────────────────────────────────────────
    //  MEJORAR texto de propuesta
    // ─────────────────────────────────────────────────────────────
    private function mejorar(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if ($texto === '') return $this->json(false, __('civinsis.toast.ia.no_hay_texto_mejorar'));

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
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.debes_iniciar_sesion'));

        $id = (int) $request->input('id');
        $p  = Proposal::find($id);
        if (!$p) return $this->json(false, __('civinsis.toast.comunes.propuesta_no_encontrada'));
        if ($p->usuario_id !== Auth::id()) return $this->json(false, __('civinsis.toast.ia.solo_autor_sugerencias'));

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
        if (!$id) return $this->json(false, __('civinsis.toast.comunes.id_invalido'));

        if ($tipo === 'comentario') {
            $item = Comentario::find($id);
            if (!$item) return $this->json(false, __('civinsis.toast.ia.comentario_no_encontrado'));
            $texto = $item->contenido;
        } else {
            $item = Proposal::find($id);
            if (!$item) return $this->json(false, __('civinsis.toast.comunes.propuesta_no_encontrada'));
            $texto = $item->titulo . ' ' . $item->descripcion . ' ' . $item->contenido;
        }

        $resultado = $this->analizarContenido($texto);

        if ($resultado['inapropiado']) {
            $this->aplicarCensura($tipo, $item, $texto, $resultado);
            return $this->json(true, __('civinsis.toast.admin.contenido_censurado'), [
                'censurado' => true,
                'razon'     => $resultado['razon'],
                'severidad' => $resultado['severidad'],
            ]);
        }

        return $this->json(true, __('civinsis.toast.ia.contenido_apropiado'), ['censurado' => false]);
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
        if (!Auth::check() || !in_array(auth_user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        $soloSinRevisar = $request->boolean('sin_revisar', false);

        $query = ModeracionAlerta::orderByDesc('created_at');
        if ($soloSinRevisar) $query->where('revisado', false);

        $alertas = $query->limit(100)->get()->map(fn ($a) => array_merge([
            'id'                 => $a->id,
            'tipo'               => $a->tipo,
            'referencia_id'      => $a->referencia_id,
            'contenido_original' => $a->contenido_original,
            'razon'              => $a->razon,
            'severidad'          => $a->severidad,
            'revisado'           => $a->revisado,
            'revisado_at'        => optional($a->revisado_at)->toDateTimeString(),
            'fecha'              => optional($a->created_at)->format('d/m/Y H:i'),
        ], $this->enlaceYAutorAlerta($a)));

        $pendientes = ModeracionAlerta::where('revisado', false)->count();

        return $this->json(true, 'OK', ['alertas' => $alertas, 'pendientes' => $pendientes]);
    }

    /**
     * Enlace correcto para "ver contenido" + autor real del ítem, según el
     * tipo de alerta. Antes el front-end siempre enlazaba a
     * "propuesta.php?id=<referencia_id>", pero para un comentario
     * referencia_id es el ID del COMENTARIO, no el de la propuesta — de ahí
     * que "ver" cayera en una propuesta random o inexistente. También se usa
     * para saber a quién penalizar al censurar.
     */
    private function enlaceYAutorAlerta(ModeracionAlerta $a): array
    {
        switch ($a->tipo) {
            case 'propuesta':
                $item = Proposal::find($a->referencia_id);
                return [
                    'existe'    => (bool) $item,
                    'link'      => $item ? 'propuesta.php?id=' . $item->id : null,
                    'autor_id'  => $item->usuario_id ?? null,
                ];
            case 'comentario':
                $item = Comentario::find($a->referencia_id);
                return [
                    'existe'    => (bool) $item,
                    'link'      => $item ? 'propuesta.php?id=' . $item->propuesta_id . '#comentario-' . $item->id : null,
                    'autor_id'  => $item->usuario_id ?? null,
                ];
            case 'debate':
                $item = Debate::find($a->referencia_id);
                return [
                    'existe'    => (bool) $item,
                    'link'      => $item ? 'debate.php?id=' . $item->id : null,
                    'autor_id'  => $item->usuario_id ?? null,
                ];
            case 'debate_respuesta':
                $item = DebateRespuesta::find($a->referencia_id);
                return [
                    'existe'    => (bool) $item,
                    'link'      => $item ? 'debate.php?id=' . $item->debate_id . '#respuesta-' . $item->id : null,
                    'autor_id'  => $item->usuario_id ?? null,
                ];
            default:
                return ['existe' => false, 'link' => null, 'autor_id' => null];
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  MARCAR REVISADO — el admin cierra una alerta
    // ─────────────────────────────────────────────────────────────
    private function marcarRevisado(Request $request)
    {
        if (!Auth::check() || !in_array(auth_user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        $id    = (int) $request->input('id');
        $alerta = ModeracionAlerta::find($id);
        if (!$alerta) return $this->json(false, __('civinsis.toast.ia.alerta_no_encontrada'));

        $alerta->revisado      = true;
        $alerta->revisado_at   = now();
        $alerta->revisado_por  = Auth::id();
        $alerta->save();

        return $this->json(true, __('civinsis.toast.admin.alerta_revisada'));
    }

    // ─────────────────────────────────────────────────────────────
    //  APROBAR — publica/restaura el contenido pese a la alerta de IA
    //  (ej. una propuesta que quedó en revisión, un comentario que
    //  quedó censurado, etc.) y cierra la alerta.
    // ─────────────────────────────────────────────────────────────
    private function aprobar(Request $request)
    {
        if (!Auth::check() || !in_array(auth_user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        $id = (int) $request->input('id'); // ID de la alerta (no del contenido)
        $alerta = ModeracionAlerta::find($id);
        if (!$alerta) return $this->json(false, __('civinsis.toast.ia.alerta_no_encontrada'));

        switch ($alerta->tipo) {
            case 'propuesta':
                $item = Proposal::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.propuesta_ya_no_existe'));
                $item->censurada     = false;
                $item->razon_censura = null;
                $item->estado        = 'activa';
                $item->save();
                break;

            case 'comentario':
                $item = Comentario::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.comentario_ya_no_existe'));
                $item->censurado      = false;
                $item->razon_censura  = null;
                $item->contenido      = $item->contenido_original ?: $item->contenido;
                $item->save();
                break;

            case 'debate':
                $item = Debate::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.debate_ya_no_existe'));
                $item->censurado     = false;
                $item->razon_censura = null;
                $item->save();
                break;

            case 'debate_respuesta':
                $item = DebateRespuesta::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.respuesta_ya_no_existe'));
                $item->censurado      = false;
                $item->razon_censura  = null;
                $item->contenido      = $item->contenido_original ?: $item->contenido;
                $item->save();
                break;

            default:
                return $this->json(false, __('civinsis.toast.ia.tipo_no_reconocido'));
        }

        $alerta->revisado      = true;
        $alerta->revisado_at   = now();
        $alerta->revisado_por  = Auth::id();
        $alerta->save();

        return $this->json(true, __('civinsis.toast.ia.contenido_publicado_alerta_cerrada'));
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
        $idioma = $this->idiomaRespuesta();
        return "Eres CIVI, el asistente de redacción y entrenador cívico de CIVINSIS, "
            . "una plataforma salvadoreña de participación ciudadana juvenil "
            . "(categorías: {$cats}). Respondes SIEMPRE en {$idioma}, con precisión, y sigues "
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
        if (mb_strlen($idea) < 6) return $this->json(false, __('civinsis.toast.crear_ia.cuentame_idea'));

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
        if ($texto === '') return $this->json(false, __('civinsis.toast.ia.no_hay_texto_corregir'));

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
        if ($texto === '') return $this->json(false, __('civinsis.toast.ia.no_hay_texto_reforzar'));

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
        if (mb_strlen($base) < 8) return $this->json(false, __('civinsis.toast.ia.escribe_titulo_descripcion'));

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
        if (!$p) return $this->json(false, __('civinsis.toast.comunes.propuesta_no_encontrada'));

        $texto = trim(strip_tags((string) $p->contenido));
        if (mb_strlen($texto) < 200) return $this->json(false, __('civinsis.toast.ia.propuesta_ya_corta'));

        $prompt = "Resume esta propuesta ciudadana en 3 o 4 puntos clave (lista) y una frase final de "
            . "conclusión. En {$this->idiomaRespuesta()}, claro y neutral.\nTítulo: {$p->titulo}\n\n{$texto}";
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
        if (mb_strlen($texto) < 8) return $this->json(false, __('civinsis.toast.ia.escribe_primero_descripcion'));

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
        if (mb_strlen($texto) < 8) return $this->json(false, __('civinsis.toast.ia.escribe_titulo_descripcion'));

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
        if ($concepto === '') return $this->json(false, __('civinsis.toast.ia.que_concepto'));

        $prompt = "Explica de forma sencilla, breve (máximo 120 palabras) y con un ejemplo cercano a "
            . "El Salvador el concepto ciudadano: \"{$concepto}\". Si no fuera un concepto cívico, "
            . "explícalo igual y relaciónalo con la participación ciudadana.";
        $r = $this->pedir($this->sysTool(), $prompt, 300);
        return $this->json(true, 'OK', ['respuesta' => $r['texto'], 'fuente' => $r['fuente']]);
    }

    // ── Reporte personalizado del ciudadano ──
    private function reporte(Request $request)
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.debes_iniciar_sesion'));
        $u = auth_user();

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

    // Claves válidas (los valores no se usan como texto: la etiqueta
    // traducida vive en civinsis.civi_coach.aspectos_plural, ver abajo).
    private const ASPECTOS_POS = [
        'creativa'    => 1,
        'argumentada' => 1,
        'comunidad'   => 1,
        'factible'    => 1,
        'innovadora'  => 1,
    ];

    /** Adjetivo plural traducido para "tus propuestas destacan por ser ___". */
    private function aspectoPositivoLabel(?string $clave): string
    {
        $llave = 'civinsis.civi_coach.aspectos_plural.' . ($clave ?? '');
        $valor = __($llave);
        return $valor === $llave ? __('civinsis.civi_coach.aspectos_plural.default') : $valor;
    }

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
            return $this->objetivo('primera_propuesta', 'crear.php');
        }
        if ($s['comentarios'] === 0) {
            return $this->objetivo('primeros_comentarios', 'dashboard.php');
        }
        if ($s['aportes'] === 0) {
            return $this->objetivo('primer_debate', 'debates.php');
        }
        if ($s['xp_faltante'] > 0 && $s['xp_faltante'] <= 60) {
            return $this->objetivo('subir_nivel', 'dashboard.php', ['xp' => $s['xp_faltante'], 'nivel' => $s['nivel'] + 1]);
        }
        if ($s['mision_cerca'] && $s['mision_cerca']['cantidad'] > 0
            && $s['mision_cerca']['progreso'] / $s['mision_cerca']['cantidad'] >= 0.5) {
            $falta = $s['mision_cerca']['cantidad'] - $s['mision_cerca']['progreso'];
            return $this->objetivo('mision_cerca', 'progreso.php', ['falta' => $falta, 'mision' => $s['mision_cerca']['nombre']]);
        }
        // Diversificar según estilo
        if ($s['estilo'] === 'comentarista') {
            return $this->objetivo('diversificar_propuesta', 'crear.php');
        }
        if ($s['estilo'] === 'proponente') {
            return $this->objetivo('diversificar_debate', 'debates.php');
        }
        return $this->objetivo('seguir', 'desafios.php');
    }

    /** Arma un objetivo del coach resolviendo titulo/descripcion/cta por clave (civi_coach.objetivo.*). */
    private function objetivo(string $clave, string $url, array $params = []): array
    {
        return [
            'clave'       => $clave,
            'titulo'      => __("civinsis.civi_coach.objetivo.{$clave}.titulo", $params),
            'descripcion' => __("civinsis.civi_coach.objetivo.{$clave}.descripcion", $params),
            'cta_texto'   => __("civinsis.civi_coach.objetivo.{$clave}.cta"),
            'cta_url'     => $url,
        ];
    }

    /** Señales de crecimiento motivadoras (máx. 3). */
    private function construirProgreso(array $s): array
    {
        $out = [];
        if ($s['xp_faltante'] > 0 && $s['xp_faltante'] <= 120) {
            $out[] = ['icono' => 'fa-bolt', 'texto' => __('civinsis.civi_coach.progreso.xp_faltante', ['xp' => $s['xp_faltante'], 'nivel' => $s['nivel'] + 1])];
        }
        if ($s['mision_cerca'] && $s['mision_cerca']['cantidad'] > 0) {
            $falta = $s['mision_cerca']['cantidad'] - $s['mision_cerca']['progreso'];
            if ($falta > 0 && $falta <= $s['mision_cerca']['cantidad']) {
                $out[] = ['icono' => 'fa-bullseye', 'texto' => __('civinsis.civi_coach.progreso.mision_cerca', ['falta' => $falta, 'mision' => $s['mision_cerca']['nombre']])];
            }
        }
        if ($s['racha'] >= 2) {
            $out[] = ['icono' => 'fa-fire', 'texto' => __('civinsis.civi_coach.progreso.racha', ['dias' => $s['racha']])];
        }
        if ($s['valoraciones_positivas'] > 0 && $s['aspecto_fuerte']) {
            $out[] = ['icono' => 'fa-star', 'texto' => __('civinsis.civi_coach.progreso.valoraciones', ['aspecto' => $this->aspectoPositivoLabel($s['aspecto_fuerte'])])];
        }
        return array_slice($out, 0, 3);
    }

    /** Hechos deterministas del "Análisis de CIVI" (fallback sin IA). */
    private function hechosAnalisis(array $s): array
    {
        $f = [];
        $estiloTraducido = __('civinsis.civi_coach.estilos.' . $s['estilo']);
        $f[] = __('civinsis.civi_coach.hechos.participas', ['estilo' => $estiloTraducido]);
        if ($s['categoria_favorita']) $f[] = __('civinsis.civi_coach.hechos.tema_favorito', ['categoria' => $s['categoria_favorita']]);
        if ($s['aspecto_fuerte']) {
            $f[] = __('civinsis.civi_coach.hechos.destacan', ['aspecto' => $this->aspectoPositivoLabel($s['aspecto_fuerte'])]);
        }
        if ($s['aportes'] === 0)          $f[] = __('civinsis.civi_coach.hechos.crecer_debates');
        elseif ($s['propuestas'] === 0)   $f[] = __('civinsis.civi_coach.hechos.sin_propuestas');
        return $f;
    }

    // ── Acción principal del mentor: panel completo personalizado ──
    private function coach(Request $request)
    {
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.debes_iniciar_sesion'));
        $u = auth_user();

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

        $saludoClave = $s['estilo'] === 'nuevo' ? 'saludo_nuevo' : 'saludo_regreso';
        $saludo = __("civinsis.civi_coach.{$saludoClave}", ['nombre' => $u->nombre]);

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
        $u        = auth_user();
        $contexto = (string) $request->input('contexto', '');
        $s        = $this->perfilActividad($u);

        $n = null; // ['texto','cta_texto','cta_url','prioridad']
        $set = function (string $clave, string $url, int $pri, array $params = []) use (&$n) {
            if ($n && $pri <= $n['prioridad']) return;
            $n = [
                'texto'     => __("civinsis.civi_coach.nudge.{$clave}.texto", $params),
                'cta_texto' => __("civinsis.civi_coach.nudge.{$clave}.cta"),
                'cta_url'   => $url,
                'prioridad' => $pri,
            ];
        };

        // Reglas por contexto — CIVI solo habla si detecta una oportunidad real
        if ($s['propuestas'] === 0 && in_array($contexto, ['dashboard', 'propuestas', 'inicio', 'perfil'])) {
            $set('primera_propuesta', 'crear.php', 3);
        }
        if ($s['propuestas'] === 0 && $s['comentarios'] >= 3) {
            $set('buenos_comentarios', 'crear.php', 4);
        }
        if ($s['aportes'] === 0 && in_array($contexto, ['debates', 'debate'])) {
            $set('primer_debate', 'debates.php', 2);
        }
        if ($s['xp_faltante'] > 0 && $s['xp_faltante'] <= 40) {
            $set('xp_cerca', 'dashboard.php', 3, ['xp' => $s['xp_faltante'], 'nivel' => $s['nivel'] + 1]);
        }
        if ($s['inactividad_dias'] !== null && $s['inactividad_dias'] >= 4 && $s['categoria_favorita']) {
            $set('inactividad', 'dashboard.php', 2, ['categoria' => $s['categoria_favorita']]);
        }

        if (!$n) return $this->json(true, 'OK', ['mostrar' => false]);
        unset($n['prioridad']);
        return $this->json(true, 'OK', ['mostrar' => true] + $n);
    }

    // ── Reformular un comentario en tono constructivo (educar, no censurar) ──
    private function tono(Request $request)
    {
        $texto = trim((string) $request->input('texto', ''));
        if ($texto === '') return $this->json(false, __('civinsis.toast.ia.no_hay_comentario_reformular'));

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
        $s = $this->perfilActividad(auth_user());
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
        if (!Auth::check()) return $this->json(false, __('civinsis.toast.comunes.debes_iniciar_sesion'));
        $u = auth_user();

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
        $catFavModelo = $catFavId ? Categoria::find($catFavId) : null;
        $catFavNombre = $catFavModelo ? $catFavModelo->translated('nombre') : null;

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
                $catNombre = $p->categoria ? $p->categoria->translated('nombre') : null;
                $razon = $p->categoria_id === $catFavId && $catFavId
                    ? __('civinsis.recomienda.propuesta_razon_categoria', ['categoria' => $catNombre ?? __('civinsis.recomienda.propuesta_categoria_generica')])
                    : ($p->progreso === 'votacion'
                        ? __('civinsis.recomienda.propuesta_razon_votacion')
                        : __('civinsis.recomienda.propuesta_razon_apoyo'));
                return [
                    'id' => $p->id, 'titulo' => $p->translated('titulo'), 'razon' => $razon,
                    'categoria' => $catNombre ?? '',
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
                $catNombre = $d->categoria ? $d->categoria->translated('nombre') : null;
                $razon = $d->categoria_id === $catFavId && $catFavId
                    ? __('civinsis.recomienda.debate_razon_categoria', ['categoria' => $catNombre ?? __('civinsis.recomienda.debate_categoria_generica')])
                    : __('civinsis.recomienda.debate_razon_generico');
                return [
                    'id' => $d->id, 'titulo' => $d->translated('titulo'), 'razon' => $razon,
                    'categoria' => $catNombre ?? '',
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
        $desCatNombre = $des && $des->categoria ? $des->categoria->translated('nombre') : null;
        $desafio = $des ? [
            'id' => $des->id, 'titulo' => $des->translated('titulo'),
            'razon' => ($des->categoria_id === $catFavId && $catFavId)
                ? __('civinsis.recomienda.desafio_razon_categoria', ['categoria' => $desCatNombre ?? __('civinsis.recomienda.desafio_categoria_generica')])
                : __('civinsis.recomienda.desafio_razon_xp', ['xp' => $des->xp_recompensa ?? 0]),
            'xp' => $des->xp_recompensa ?? 0,
            'url' => 'crear.php?desafio_id=' . $des->id,
        ] : null;

        // Intro narrada (con respaldo determinista)
        $intro = $catFavNombre
            ? __('civinsis.recomienda.intro_con_categoria', ['categoria' => $catFavNombre])
            : __('civinsis.recomienda.intro_generico');
        if ($props->isEmpty() && $debs->isEmpty() && !$desafio) {
            $intro = __('civinsis.recomienda.intro_vacio');
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
        if (!Auth::check() || !in_array(auth_user()->rol_nombre, ['admin', 'moderador']))
            return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

        $id = (int) $request->input('id'); // ID de la alerta (no del contenido)
        $alerta = ModeracionAlerta::find($id);
        if (!$alerta) return $this->json(false, __('civinsis.toast.ia.alerta_no_encontrada'));

        $razon = $alerta->razon ?: 'Contenido inapropiado';
        $autorId = null;

        switch ($alerta->tipo) {
            case 'comentario':
                $item = Comentario::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.comentario_ya_no_existe'));
                if (!$item->contenido_original) $item->contenido_original = $item->contenido;
                $item->contenido     = '[Comentario retirado por un moderador]';
                $item->censurado     = true;
                $item->razon_censura = $razon;
                $item->save();
                $autorId = $item->usuario_id;
                break;

            case 'debate_respuesta':
                $item = DebateRespuesta::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.respuesta_ya_no_existe'));
                if (!$item->contenido_original) $item->contenido_original = $item->contenido;
                $item->contenido     = '[Respuesta retirada por un moderador]';
                $item->censurado     = true;
                $item->razon_censura = $razon;
                $item->save();
                $autorId = $item->usuario_id;
                break;

            case 'propuesta':
                $item = Proposal::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.propuesta_ya_no_existe'));
                $item->censurada     = true;
                $item->razon_censura = $razon;
                $item->estado        = 'en_revision';
                $item->save();
                $autorId = $item->usuario_id;
                break;

            case 'debate':
                $item = Debate::find($alerta->referencia_id);
                if (!$item) return $this->json(false, __('civinsis.toast.ia.debate_ya_no_existe'));
                $item->censurado     = true;
                $item->razon_censura = $razon;
                $item->save();
                $autorId = $item->usuario_id;
                break;

            default:
                return $this->json(false, __('civinsis.toast.admin.tipo_no_soportado'));
        }

        // Penalización de reputación: la gravedad detectada por la IA decide
        // cuánto pierde el autor. Queda registrada en reputacion_historial
        // con visto=false para que el propio usuario reciba un aviso (modal)
        // la próxima vez que use la plataforma — ver GamificacionController::
        // penalizacionesPendientes().
        $puntosPorSeveridad = ['baja' => -5, 'media' => -10, 'alta' => -20];
        $penalizacion = $puntosPorSeveridad[$alerta->severidad] ?? -10;
        if ($autorId) {
            $autor = \App\Models\User::find($autorId);
            if ($autor) {
                app(\App\Services\GamificacionService::class)->otorgarReputacion(
                    $autor, 'Contenido censurado: ' . $razon, $penalizacion, null, $alerta->referencia_id
                );
            }
        }

        $alerta->revisado     = true;
        $alerta->revisado_at  = now();
        $alerta->revisado_por = Auth::id();
        $alerta->save();

        return $this->json(true, __('civinsis.toast.ia.contenido_censurado_alerta_cerrada'));
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
