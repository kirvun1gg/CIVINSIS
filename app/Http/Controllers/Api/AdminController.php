<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Cosmetico;
use App\Models\Debate;
use App\Models\DebateRespuesta;
use App\Models\Desafio;
use App\Models\Insignia;
use App\Models\Mision;
use App\Models\Proposal;
use App\Models\Titulo;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Herramientas del panel administrativo:
 *  - Estadísticas completas de la plataforma
 *  - Destacar propuestas, comentarios y debates
 *  - Moderación: suspender usuarios, ocultar contenido, limpiar spam
 */
class AdminController extends Controller
{
    use ApiResponse;

    /** Tipos de contenido que se pueden destacar/ocultar y su modelo. */
    private const TIPOS = [
        'propuesta'        => [Proposal::class,        'destacada', 'censurada'],
        'comentario'       => [Comentario::class,      'destacado', 'censurado'],
        'debate'           => [Debate::class,          'destacado', 'censurado'],
        'debate_respuesta' => [DebateRespuesta::class, 'destacada', 'censurado'],
    ];

    /** Rarezas comunes a varias entidades. */
    private const RAREZAS = ['comun' => 'Común', 'raro' => 'Raro', 'epico' => 'Épico', 'legendario' => 'Legendario'];

    /** Acciones que la plataforma dispara de verdad (XP_ACCIONES del servicio). */
    private const ACCIONES_MISION = [
        'crear_propuesta'  => 'Crear una propuesta',
        'comentar'         => 'Comentar',
        'votar'            => 'Votar',
        'recibir_voto'     => 'Recibir un voto',
        'crear_debate'     => 'Crear un debate',
        'responder_debate' => 'Responder en un debate',
        'racha_diaria'     => 'Mantener la racha',
    ];

    /**
     * Entidades de gamificación gestionables desde el panel.
     * Cada campo se describe una vez y el formulario se genera solo.
     */
    private function entidades(): array
    {
        $t = fn ($n, $l, $extra = []) => array_merge(['name' => $n, 'label' => $l, 'tipo' => 'text'], $extra);

        return [
            'desafio' => [
                'modelo' => Desafio::class, 'label' => 'Desafíos', 'singular' => 'desafío', 'icono' => 'fa-flag-checkered',
                'titulo' => 'titulo', 'orden' => 'orden',
                'campos' => [
                    $t('titulo', 'Título', ['req' => true]),
                    $t('descripcion', 'Descripción', ['tipo' => 'textarea', 'req' => true]),
                    $t('dificultad', 'Dificultad', ['tipo' => 'select', 'opciones' => ['facil' => 'Fácil', 'medio' => 'Medio', 'dificil' => 'Difícil']]),
                    $t('categoria_id', 'Categoría', ['tipo' => 'select', 'fuente' => 'categorias']),
                    $t('icono', 'Icono (Font Awesome)', ['ph' => 'fas fa-bullseye']),
                    $t('xp_recompensa', 'XP de recompensa', ['tipo' => 'number']),
                    $t('reputacion_recompensa', 'Reputación', ['tipo' => 'number']),
                    $t('orden', 'Orden', ['tipo' => 'number']),
                    $t('activo', 'Activo', ['tipo' => 'bool']),
                ],
            ],
            'mision' => [
                'modelo' => Mision::class, 'label' => 'Misiones', 'singular' => 'misión', 'icono' => 'fa-bullseye',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', 'Clave única', ['req' => true, 'ph' => 'diaria_comentar']),
                    $t('nombre', 'Nombre', ['req' => true]),
                    $t('descripcion', 'Descripción', ['tipo' => 'textarea', 'req' => true]),
                    $t('tipo', 'Tipo', ['tipo' => 'select', 'opciones' => ['diaria' => 'Diaria', 'semanal' => 'Semanal', 'especial' => 'Especial']]),
                    $t('accion', 'Acción que la completa', ['tipo' => 'select', 'opciones' => self::ACCIONES_MISION]),
                    $t('cantidad', 'Cantidad necesaria', ['tipo' => 'number']),
                    $t('xp_recompensa', 'XP de recompensa', ['tipo' => 'number']),
                    $t('reputacion_recompensa', 'Reputación', ['tipo' => 'number']),
                    $t('activo', 'Activa', ['tipo' => 'bool']),
                ],
            ],
            'insignia' => [
                'modelo' => Insignia::class, 'label' => 'Insignias', 'singular' => 'insignia', 'icono' => 'fa-certificate',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', 'Clave única', ['req' => true]),
                    $t('nombre', 'Nombre', ['req' => true]),
                    $t('descripcion', 'Descripción', ['tipo' => 'textarea', 'req' => true]),
                    $t('icono', 'Icono (emoji o Font Awesome)', ['ph' => '🏅 o fas fa-medal']),
                    $t('color', 'Color', ['tipo' => 'color']),
                    $t('categoria', 'Categoría', ['tipo' => 'select', 'opciones' => ['rol' => 'Rol', 'logro' => 'Logro', 'evento' => 'Evento', 'especial' => 'Especial']]),
                    $t('rareza', 'Rareza', ['tipo' => 'select', 'opciones' => self::RAREZAS]),
                    $t('equipable', 'Equipable', ['tipo' => 'bool']),
                    $t('activo', 'Activa', ['tipo' => 'bool']),
                ],
            ],
            'titulo' => [
                'modelo' => Titulo::class, 'label' => 'Títulos', 'singular' => 'título', 'icono' => 'fa-ranking-star',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', 'Clave única', ['req' => true]),
                    $t('nombre', 'Nombre', ['req' => true]),
                    $t('color', 'Color', ['tipo' => 'color']),
                    $t('rareza', 'Rareza', ['tipo' => 'select', 'opciones' => self::RAREZAS]),
                    $t('condicion_tipo', 'Se obtiene por', ['tipo' => 'select', 'opciones' => ['nivel' => 'Alcanzar un nivel', 'logro' => 'Un logro', 'reputacion' => 'Reputación', 'manual' => 'Asignación manual']]),
                    $t('condicion_valor', 'Valor de la condición', ['tipo' => 'number']),
                    $t('xp_requerido', 'XP requerido', ['tipo' => 'number']),
                    $t('activo', 'Activo', ['tipo' => 'bool']),
                ],
            ],
            'cosmetico' => [
                'modelo' => Cosmetico::class, 'label' => 'Cosméticos', 'singular' => 'cosmético', 'icono' => 'fa-palette',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', 'Clave única', ['req' => true]),
                    $t('nombre', 'Nombre', ['req' => true]),
                    $t('descripcion', 'Descripción', ['tipo' => 'textarea']),
                    $t('tipo', 'Tipo', ['tipo' => 'select', 'opciones' => ['marco_avatar' => 'Marco de avatar', 'fondo_perfil' => 'Fondo de perfil']]),
                    $t('valor', 'Valor (clase CSS)', ['req' => true, 'ph' => 'marco-dorado']),
                    $t('preview', 'Vista previa (CSS en línea)', ['ph' => 'background:linear-gradient(...)']),
                    $t('rareza', 'Rareza', ['tipo' => 'select', 'opciones' => self::RAREZAS]),
                    $t('nivel_requerido', 'Nivel requerido', ['tipo' => 'number']),
                    $t('xp_requerido', 'XP requerido', ['tipo' => 'number']),
                    $t('activo', 'Activo', ['tipo' => 'bool']),
                ],
            ],
        ];
    }

    public function handle(Request $request)
    {
        if (!$this->esAdmin()) return $this->json(false, 'Sin permisos');

        $accion = $request->input('accion', '');

        return match ($accion) {
            'estadisticas'  => $this->estadisticas(),
            'destacar'      => $this->destacar($request),
            'ocultar'       => $this->ocultar($request),
            'suspender'     => $this->suspender($request),
            'reactivar'     => $this->reactivar($request),
            'spam_listar'   => $this->spamListar($request),
            'spam_eliminar' => $this->spamEliminar($request),
            'gestion_esquema'  => $this->gestionEsquema(),
            'gestion_listar'   => $this->gestionListar($request),
            'gestion_guardar'  => $this->gestionGuardar($request),
            'gestion_eliminar' => $this->gestionEliminar($request),
            default         => $this->json(false, 'Acción no reconocida'),
        };
    }

    private function esAdmin(): bool
    {
        return Auth::check() && in_array(Auth::user()->rol_nombre, ['admin', 'moderador']);
    }

    // ═════════════════════════════════════════════════════════════
    //  ESTADÍSTICAS COMPLETAS
    // ═════════════════════════════════════════════════════════════
    private function estadisticas()
    {
        $hace30 = now()->subDays(30);

        // ── Usuarios ──
        $usuariosTotal   = User::count();
        $usuariosActivos = User::where('activo', true)->count();
        $usuariosRecientes = User::where('ultimo_acceso', '>=', $hace30)->count();
        $suspendidos     = User::where('activo', false)->count();
        $nuevos30        = User::where('created_at', '>=', $hace30)->count();

        // ── Contenido ──
        $propuestas      = Proposal::count();
        $propDestacadas  = Proposal::where('destacada', true)->count();
        $propVotacion    = Proposal::where('progreso', 'votacion')->count();
        $propCensuradas  = Proposal::where('censurada', true)->count();
        $prop30          = Proposal::where('fecha_creacion', '>=', $hace30)->count();

        $debates         = Debate::count();
        $debatesActivos  = Debate::where('estado', 'activo')->count();
        $respuestas      = DebateRespuesta::count();

        $comentarios     = Comentario::count();
        $comCensurados   = Comentario::where('censurado', true)->count();
        $com30           = Comentario::where('fecha_creacion', '>=', $hace30)->count();

        // ── Gamificación ──
        $xpTotal    = (int) User::sum('xp_total');
        $repMedia   = round((float) User::avg('reputacion'), 1);
        $nivelMedio = round((float) User::avg('nivel'), 1);
        $logros     = (int) DB::table('usuario_logros')->count();
        $insignias  = (int) DB::table('usuario_insignias')->count();
        $desafios   = (int) DB::table('usuario_desafios')->where('completado', true)->count();

        // ── Moderación pendiente ──
        $alertas = (int) DB::table('moderacion_alertas')->where('revisado', false)->count();

        // ── Actividad por categoría (para el gráfico) ──
        $porCategoria = DB::table('propuestas')
            ->join('categorias', 'categorias.id', '=', 'propuestas.categoria_id')
            ->select('categorias.nombre', 'categorias.color', DB::raw('COUNT(*) as total'))
            ->groupBy('categorias.nombre', 'categorias.color')
            ->orderByDesc('total')->limit(8)->get();

        return $this->json(true, 'OK', [
            'usuarios' => [
                'total'      => $usuariosTotal,
                'activos'    => $usuariosActivos,
                'recientes'  => $usuariosRecientes,
                'suspendidos'=> $suspendidos,
                'nuevos_30'  => $nuevos30,
            ],
            'contenido' => [
                'propuestas'            => $propuestas,
                'propuestas_destacadas' => $propDestacadas,
                'propuestas_votacion'   => $propVotacion,
                'propuestas_censuradas' => $propCensuradas,
                'propuestas_30'         => $prop30,
                'debates'               => $debates,
                'debates_activos'       => $debatesActivos,
                'respuestas_debate'     => $respuestas,
                'comentarios'           => $comentarios,
                'comentarios_censurados'=> $comCensurados,
                'comentarios_30'        => $com30,
            ],
            'gamificacion' => [
                'xp_total'           => $xpTotal,
                'reputacion_media'   => $repMedia,
                'nivel_medio'        => $nivelMedio,
                'logros_desbloqueados'    => $logros,
                'insignias_desbloqueadas' => $insignias,
                'desafios_completados'    => $desafios,
            ],
            'moderacion' => [
                'alertas_pendientes' => $alertas,
            ],
            'por_categoria' => $porCategoria,
        ]);
    }

    // ═════════════════════════════════════════════════════════════
    //  DESTACAR CONTENIDO
    // ═════════════════════════════════════════════════════════════
    private function destacar(Request $request)
    {
        $tipo = (string) $request->input('tipo', '');
        $id   = (int) $request->input('id');

        if (!isset(self::TIPOS[$tipo])) return $this->json(false, 'Tipo no soportado');
        [$modelo, $campoDestacar] = self::TIPOS[$tipo];

        $item = $modelo::find($id);
        if (!$item) return $this->json(false, 'No se encontró el contenido');

        // Si llega "valor" lo respetamos; si no, alternamos.
        $valor = $request->has('valor')
            ? filter_var($request->input('valor'), FILTER_VALIDATE_BOOLEAN)
            : !$item->{$campoDestacar};

        $item->{$campoDestacar} = $valor;
        $item->save();

        return $this->json(true, $valor ? 'Contenido destacado' : 'Se quitó el destacado', [
            'destacado' => $valor,
        ]);
    }

    // ═════════════════════════════════════════════════════════════
    //  OCULTAR CONTENIDO (moderación)
    // ═════════════════════════════════════════════════════════════
    private function ocultar(Request $request)
    {
        $tipo  = (string) $request->input('tipo', '');
        $id    = (int) $request->input('id');
        $razon = trim((string) $request->input('razon', '')) ?: 'Ocultado por moderación';

        if (!isset(self::TIPOS[$tipo])) return $this->json(false, 'Tipo no soportado');
        [$modelo, , $campoOcultar] = self::TIPOS[$tipo];

        $item = $modelo::find($id);
        if (!$item) return $this->json(false, 'No se encontró el contenido');

        $valor = $request->has('valor')
            ? filter_var($request->input('valor'), FILTER_VALIDATE_BOOLEAN)
            : !$item->{$campoOcultar};

        // Al ocultar un texto guardamos el original para poder restaurarlo
        if ($valor && in_array($tipo, ['comentario', 'debate_respuesta'])) {
            if (empty($item->contenido_original)) $item->contenido_original = $item->contenido;
            $item->contenido = '[Contenido retirado por un moderador]';
        }
        if (!$valor && in_array($tipo, ['comentario', 'debate_respuesta']) && $item->contenido_original) {
            $item->contenido = $item->contenido_original;
        }

        $item->{$campoOcultar} = $valor;
        if ($valor) $item->razon_censura = $razon;
        if ($tipo === 'propuesta') $item->estado = $valor ? 'en_revision' : 'activa';
        $item->save();

        return $this->json(true, $valor ? 'Contenido ocultado' : 'Contenido restaurado', [
            'oculto' => $valor,
        ]);
    }

    // ═════════════════════════════════════════════════════════════
    //  SUSPENDER / REACTIVAR USUARIOS
    // ═════════════════════════════════════════════════════════════
    private function suspender(Request $request)
    {
        $id    = (int) $request->input('id');
        $razon = trim((string) $request->input('razon', '')) ?: 'Incumplimiento de las normas de la comunidad';

        $u = User::find($id);
        if (!$u) return $this->json(false, 'Usuario no encontrado');
        if ($u->id === Auth::id()) return $this->json(false, 'No puedes suspenderte a ti mismo');
        if ($u->rol_nombre === 'admin') return $this->json(false, 'No puedes suspender a un administrador');

        $u->activo           = false;
        $u->razon_suspension = $razon;
        $u->suspendido_at    = now();
        $u->save();

        return $this->json(true, 'Usuario suspendido');
    }

    private function reactivar(Request $request)
    {
        $u = User::find((int) $request->input('id'));
        if (!$u) return $this->json(false, 'Usuario no encontrado');

        $u->activo           = true;
        $u->razon_suspension = null;
        $u->suspendido_at    = null;
        $u->save();

        return $this->json(true, 'Usuario reactivado');
    }

    // ═════════════════════════════════════════════════════════════
    //  LIMPIEZA DE SPAM
    //  Detecta comentarios sospechosos: muy repetidos, con enlaces,
    //  o publicados en ráfaga por el mismo usuario.
    // ═════════════════════════════════════════════════════════════
    private function spamListar(Request $request)
    {
        $sospechosos = collect();

        // 1) Comentarios con texto idéntico repetido por el mismo usuario
        $repetidos = DB::table('comentarios')
            ->select('usuario_id', 'contenido', DB::raw('COUNT(*) as veces'), DB::raw('MIN(id) as primer_id'))
            ->groupBy('usuario_id', 'contenido')
            ->having('veces', '>=', 3)
            ->orderByDesc('veces')->limit(40)->get();

        foreach ($repetidos as $r) {
            $ids = Comentario::where('usuario_id', $r->usuario_id)
                ->where('contenido', $r->contenido)->pluck('id')->all();
            $sospechosos->push([
                'motivo'   => "Texto repetido {$r->veces} veces",
                'usuario'  => optional(User::find($r->usuario_id))->nombre ?? '—',
                'extracto' => mb_substr($r->contenido, 0, 120),
                'ids'      => $ids,
                'total'    => count($ids),
            ]);
        }

        // 2) Comentarios con enlaces (típico del spam)
        $conEnlaces = Comentario::where('contenido', 'like', '%http%')
            ->orderByDesc('id')->limit(30)->get();
        foreach ($conEnlaces as $c) {
            $sospechosos->push([
                'motivo'   => 'Contiene un enlace externo',
                'usuario'  => optional($c->usuario)->nombre ?? '—',
                'extracto' => mb_substr($c->contenido, 0, 120),
                'ids'      => [$c->id],
                'total'    => 1,
            ]);
        }

        return $this->json(true, 'OK', [
            'items' => $sospechosos->values(),
            'total' => $sospechosos->sum('total'),
        ]);
    }

    private function spamEliminar(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || !$ids) return $this->json(false, 'No se indicó qué eliminar');

        $ids = array_map('intval', $ids);
        $n = Comentario::whereIn('id', $ids)->delete();

        return $this->json(true, "Se eliminaron {$n} comentarios", ['eliminados' => $n]);
    }

    // ═════════════════════════════════════════════════════════════
    //  GESTIÓN DE GAMIFICACIÓN (desafíos, misiones, insignias,
    //  títulos y cosméticos) — un solo motor para las cinco.
    // ═════════════════════════════════════════════════════════════

    /** Devuelve la definición de campos para que el panel arme los formularios. */
    private function gestionEsquema()
    {
        $out = [];
        foreach ($this->entidades() as $clave => $e) {
            $campos = $e['campos'];
            // resolver las opciones dinámicas (categorías)
            foreach ($campos as $i => $c) {
                if (($c['fuente'] ?? '') === 'categorias') {
                    $campos[$i]['opciones'] = Categoria::orderBy('nombre')->pluck('nombre', 'id')->all();
                    $campos[$i]['vacio']    = 'Sin categoría';
                }
            }
            $out[$clave] = [
                'label'    => $e['label'],
                'singular' => $e['singular'],
                'icono'    => $e['icono'],
                'titulo' => $e['titulo'],
                'campos' => $campos,
            ];
        }
        return $this->json(true, 'OK', ['entidades' => $out]);
    }

    private function entidad(string $clave): ?array
    {
        return $this->entidades()[$clave] ?? null;
    }

    private function gestionListar(Request $request)
    {
        $e = $this->entidad((string) $request->input('entidad', ''));
        if (!$e) return $this->json(false, 'Entidad no reconocida');

        $q = $e['modelo']::query();
        if (!empty($e['orden'])) $q->orderBy($e['orden']);
        $items = $q->orderByDesc('id')->limit(200)->get();

        $campos = array_column($e['campos'], 'name');
        $lista  = $items->map(function ($it) use ($campos) {
            $fila = ['id' => $it->id];
            foreach ($campos as $c) $fila[$c] = $it->{$c};
            return $fila;
        });

        return $this->json(true, 'OK', ['items' => $lista, 'total' => $lista->count()]);
    }

    private function gestionGuardar(Request $request)
    {
        $clave = (string) $request->input('entidad', '');
        $e = $this->entidad($clave);
        if (!$e) return $this->json(false, 'Entidad no reconocida');

        $id     = (int) $request->input('id');
        $datos  = $request->input('datos', []);
        if (!is_array($datos)) return $this->json(false, 'Datos inválidos');

        $item = $id ? $e['modelo']::find($id) : new $e['modelo']();
        if ($id && !$item) return $this->json(false, 'No se encontró el registro');

        foreach ($e['campos'] as $c) {
            $n = $c['name'];
            if (!array_key_exists($n, $datos)) continue;
            $v = $datos[$n];

            if (($c['tipo'] ?? '') === 'bool')   $v = filter_var($v, FILTER_VALIDATE_BOOLEAN);
            if (($c['tipo'] ?? '') === 'number') $v = (int) $v;
            if ($n === 'categoria_id')           $v = $v !== '' && $v !== null ? (int) $v : null;
            if (is_string($v))                   $v = trim($v);

            // Campos obligatorios: no permitir vaciarlos
            if (!empty($c['req']) && ($v === '' || $v === null)) {
                return $this->json(false, "El campo «{$c['label']}» es obligatorio");
            }
            $item->{$n} = $v;
        }

        // La clave debe ser única (varias tablas la tienen con índice unique)
        if (in_array('clave', array_column($e['campos'], 'name')) && $item->clave) {
            $dup = $e['modelo']::where('clave', $item->clave)
                ->when($item->id, fn ($q) => $q->where('id', '!=', $item->id))->exists();
            if ($dup) return $this->json(false, 'Ya existe otro registro con esa clave');
        }

        $item->save();

        return $this->json(true, $id ? 'Cambios guardados' : 'Creado correctamente', ['id' => $item->id]);
    }

    private function gestionEliminar(Request $request)
    {
        $e = $this->entidad((string) $request->input('entidad', ''));
        if (!$e) return $this->json(false, 'Entidad no reconocida');

        $item = $e['modelo']::find((int) $request->input('id'));
        if (!$item) return $this->json(false, 'No se encontró el registro');

        // Si ya lo tienen usuarios, desactivar en vez de borrar (evita romper su perfil)
        $pivotes = [
            Insignia::class  => 'usuario_insignias',
            Titulo::class    => 'usuario_titulos',
            Cosmetico::class => 'usuario_cosmeticos',
            Mision::class    => 'usuario_misiones',
            Desafio::class   => 'usuario_desafios',
        ];
        $tabla = $pivotes[$e['modelo']] ?? null;
        $col   = [
            'usuario_insignias' => 'insignia_id', 'usuario_titulos' => 'titulo_id',
            'usuario_cosmeticos' => 'cosmetico_id', 'usuario_misiones' => 'mision_id',
            'usuario_desafios' => 'desafio_id',
        ][$tabla] ?? null;

        if ($tabla && $col && DB::table($tabla)->where($col, $item->id)->exists()) {
            $item->activo = false;
            $item->save();
            return $this->json(true, 'Ya lo tienen usuarios: se desactivó en vez de eliminarlo', ['desactivado' => true]);
        }

        $item->delete();
        return $this->json(true, 'Eliminado correctamente', ['desactivado' => false]);
    }
}
