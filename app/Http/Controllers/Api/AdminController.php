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
use App\Services\TranslationService;
use App\Support\ApiResponse;
use App\Support\CatalogoTraducido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

    /** Rarezas comunes a varias entidades (traducidas: __() no se puede usar en una const). */
    private function rarezas(): array
    {
        return [
            'comun' => __('civinsis.admin.gam.rareza_comun'),
            'raro' => __('civinsis.admin.gam.rareza_raro'),
            'epico' => __('civinsis.admin.gam.rareza_epico'),
            'legendario' => __('civinsis.admin.gam.rareza_legendario'),
        ];
    }

    /** Acciones que la plataforma dispara de verdad (XP_ACCIONES del servicio). */
    private function accionesMision(): array
    {
        return [
            'crear_propuesta'  => __('civinsis.admin.gam.accion_crear_propuesta'),
            'comentar'         => __('civinsis.admin.gam.accion_comentar'),
            'votar'            => __('civinsis.admin.gam.accion_votar'),
            'recibir_voto'     => __('civinsis.admin.gam.accion_recibir_voto'),
            'crear_debate'     => __('civinsis.admin.gam.accion_crear_debate'),
            'responder_debate' => __('civinsis.admin.gam.accion_responder_debate'),
            'racha_diaria'     => __('civinsis.admin.gam.accion_racha_diaria'),
        ];
    }

    /**
     * Entidades de gamificación gestionables desde el panel.
     * Cada campo se describe una vez y el formulario se genera solo.
     */
    private function entidades(): array
    {
        $t = fn ($n, $l, $extra = []) => array_merge(['name' => $n, 'label' => $l, 'tipo' => 'text'], $extra);

        $g = fn (string $k) => __('civinsis.admin.gam.' . $k);

        return [
            'desafio' => [
                'modelo' => Desafio::class, 'label' => __('civinsis.admin.desafios_titulo'), 'singular' => $g('singular_desafio'), 'icono' => 'fa-flag-checkered',
                'titulo' => 'titulo', 'orden' => 'orden',
                'campos' => [
                    $t('titulo', __('civinsis.comun.col_titulo'), ['req' => true]),
                    $t('descripcion', __('civinsis.admin.col_descripcion'), ['tipo' => 'textarea', 'req' => true]),
                    $t('dificultad', $g('campo_dificultad'), ['tipo' => 'select', 'opciones' => ['facil' => $g('op_dificultad_facil'), 'medio' => $g('op_dificultad_medio'), 'dificil' => $g('op_dificultad_dificil')]]),
                    $t('categoria_id', __('civinsis.comun.col_categoria'), ['tipo' => 'select', 'fuente' => 'categorias']),
                    $t('icono', __('civinsis.admin.campo_icono'), ['ph' => 'fas fa-bullseye']),
                    $t('xp_recompensa', $g('campo_xp_recompensa'), ['tipo' => 'number']),
                    $t('reputacion_recompensa', $g('campo_reputacion'), ['tipo' => 'number']),
                    $t('orden', $g('campo_orden'), ['tipo' => 'number']),
                    $t('activo', $g('campo_activo'), ['tipo' => 'bool']),
                ],
            ],
            'mision' => [
                'modelo' => Mision::class, 'label' => __('civinsis.admin.gam.label_misiones'), 'singular' => $g('singular_mision'), 'icono' => 'fa-bullseye',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', $g('campo_clave_unica'), ['req' => true, 'ph' => 'diaria_comentar']),
                    $t('nombre', __('civinsis.admin.col_nombre'), ['req' => true]),
                    $t('descripcion', __('civinsis.admin.col_descripcion'), ['tipo' => 'textarea', 'req' => true]),
                    $t('tipo', $g('campo_tipo'), ['tipo' => 'select', 'opciones' => ['diaria' => $g('op_tipo_mision_diaria'), 'semanal' => $g('op_tipo_mision_semanal'), 'especial' => $g('op_tipo_mision_especial')]]),
                    $t('accion', $g('campo_accion_completa'), ['tipo' => 'select', 'opciones' => $this->accionesMision()]),
                    $t('cantidad', $g('campo_cantidad_necesaria'), ['tipo' => 'number']),
                    $t('xp_recompensa', $g('campo_xp_recompensa'), ['tipo' => 'number']),
                    $t('reputacion_recompensa', $g('campo_reputacion'), ['tipo' => 'number']),
                    $t('activo', $g('campo_activa'), ['tipo' => 'bool']),
                ],
            ],
            'insignia' => [
                'modelo' => Insignia::class, 'label' => __('civinsis.admin.gam.label_insignias'), 'singular' => $g('singular_insignia'), 'icono' => 'fa-certificate',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', $g('campo_clave_unica'), ['req' => true]),
                    $t('nombre', __('civinsis.admin.col_nombre'), ['req' => true]),
                    $t('descripcion', __('civinsis.admin.col_descripcion'), ['tipo' => 'textarea', 'req' => true]),
                    $t('icono', $g('campo_icono_emoji_fa'), ['ph' => $g('ph_insignia_icono')]),
                    $t('color', __('civinsis.admin.col_color'), ['tipo' => 'color']),
                    $t('categoria', __('civinsis.comun.col_categoria'), ['tipo' => 'select', 'opciones' => ['rol' => $g('op_cat_insignia_rol'), 'logro' => $g('op_cat_insignia_logro'), 'evento' => $g('op_cat_insignia_evento'), 'especial' => $g('op_cat_insignia_especial')]]),
                    $t('rareza', $g('campo_rareza'), ['tipo' => 'select', 'opciones' => $this->rarezas()]),
                    $t('equipable', $g('campo_equipable'), ['tipo' => 'bool']),
                    $t('activo', $g('campo_activa'), ['tipo' => 'bool']),
                ],
            ],
            'titulo' => [
                'modelo' => Titulo::class, 'label' => __('civinsis.admin.gam.label_titulos'), 'singular' => $g('singular_titulo'), 'icono' => 'fa-ranking-star',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', $g('campo_clave_unica'), ['req' => true]),
                    $t('nombre', __('civinsis.admin.col_nombre'), ['req' => true]),
                    $t('color', __('civinsis.admin.col_color'), ['tipo' => 'color']),
                    $t('rareza', $g('campo_rareza'), ['tipo' => 'select', 'opciones' => $this->rarezas()]),
                    $t('condicion_tipo', $g('campo_condicion_tipo'), ['tipo' => 'select', 'opciones' => ['nivel' => $g('cond_nivel'), 'logro' => $g('cond_logro'), 'reputacion' => $g('campo_reputacion'), 'manual' => $g('cond_manual')]]),
                    $t('condicion_valor', $g('campo_condicion_valor'), ['tipo' => 'number']),
                    $t('xp_requerido', $g('campo_xp_requerido'), ['tipo' => 'number']),
                    $t('activo', $g('campo_activo'), ['tipo' => 'bool']),
                ],
            ],
            'cosmetico' => [
                'modelo' => Cosmetico::class, 'label' => __('civinsis.admin.gam.label_cosmeticos'), 'singular' => $g('singular_cosmetico'), 'icono' => 'fa-palette',
                'titulo' => 'nombre',
                'campos' => [
                    $t('clave', $g('campo_clave_unica'), ['req' => true]),
                    $t('nombre', __('civinsis.admin.col_nombre'), ['req' => true]),
                    $t('descripcion', __('civinsis.admin.col_descripcion'), ['tipo' => 'textarea']),
                    $t('tipo', $g('campo_tipo'), ['tipo' => 'select', 'opciones' => ['marco_avatar' => $g('op_cosmetico_marco'), 'fondo_perfil' => $g('op_cosmetico_fondo')]]),
                    $t('valor', $g('campo_valor_clase_css'), ['req' => true, 'ph' => 'marco-dorado']),
                    $t('preview', $g('campo_preview_css'), ['ph' => 'background:linear-gradient(...)']),
                    $t('rareza', $g('campo_rareza'), ['tipo' => 'select', 'opciones' => $this->rarezas()]),
                    $t('nivel_requerido', $g('campo_nivel_requerido'), ['tipo' => 'number']),
                    $t('xp_requerido', $g('campo_xp_requerido'), ['tipo' => 'number']),
                    $t('activo', $g('campo_activo'), ['tipo' => 'bool']),
                ],
            ],
        ];
    }

    public function handle(Request $request)
    {
        if (!$this->esAdmin()) return $this->json(false, __('civinsis.toast.comunes.sin_permisos'));

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
            default         => $this->json(false, __('civinsis.toast.comunes.accion_no_reconocida')),
        };
    }

    private function esAdmin(): bool
    {
        return Auth::check() && in_array(auth_user()->rol_nombre, ['admin', 'moderador']);
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
            ->select('categorias.id', 'categorias.nombre', 'categorias.color', DB::raw('COUNT(*) as total'))
            ->groupBy('categorias.id', 'categorias.nombre', 'categorias.color')
            ->orderByDesc('total')->limit(8)->get();

        // Esta es una consulta agregada en crudo (no pasa por el modelo
        // Categoria), así que 'nombre' llega en español sin traducir — se
        // resuelve aparte con el mismo mecanismo (DeepL + caché) que usa
        // el resto de la plataforma.
        $localeStats = App::getLocale();
        if ($localeStats !== 'es' && $porCategoria->isNotEmpty()) {
            $catModels = Categoria::whereIn('id', $porCategoria->pluck('id'))->get()->keyBy('id');
            app(TranslationService::class)->warmMany($catModels, ['nombre'], $localeStats);
            $porCategoria->each(function ($row) use ($catModels) {
                $cat = $catModels->get($row->id);
                if ($cat) $row->nombre = $cat->translated('nombre');
            });
        }

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

        if (!isset(self::TIPOS[$tipo])) return $this->json(false, __('civinsis.toast.admin.tipo_no_soportado'));
        [$modelo, $campoDestacar] = self::TIPOS[$tipo];

        $item = $modelo::find($id);
        if (!$item) return $this->json(false, __('civinsis.toast.admin.contenido_no_encontrado'));

        // Si llega "valor" lo respetamos; si no, alternamos.
        $valor = $request->has('valor')
            ? filter_var($request->input('valor'), FILTER_VALIDATE_BOOLEAN)
            : !$item->{$campoDestacar};

        $item->{$campoDestacar} = $valor;
        $item->save();

        return $this->json(true, $valor ? __('civinsis.toast.admin.contenido_destacado') : __('civinsis.toast.admin.destacado_quitado'), [
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

        if (!isset(self::TIPOS[$tipo])) return $this->json(false, __('civinsis.toast.admin.tipo_no_soportado'));
        [$modelo, , $campoOcultar] = self::TIPOS[$tipo];

        $item = $modelo::find($id);
        if (!$item) return $this->json(false, __('civinsis.toast.admin.contenido_no_encontrado'));

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

        return $this->json(true, $valor ? __('civinsis.toast.admin.contenido_ocultado') : __('civinsis.toast.admin.contenido_restaurado'), [
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
        if (!$u) return $this->json(false, __('civinsis.toast.admin.usuario_no_encontrado'));
        if ($u->id === Auth::id()) return $this->json(false, __('civinsis.toast.admin.no_puedes_suspenderte'));
        if ($u->rol_nombre === 'admin') return $this->json(false, __('civinsis.toast.admin.no_puedes_suspender_admin'));

        $u->activo           = false;
        $u->razon_suspension = $razon;
        $u->suspendido_at    = now();
        $u->save();

        return $this->json(true, __('civinsis.toast.admin.usuario_suspendido'));
    }

    private function reactivar(Request $request)
    {
        $u = User::find((int) $request->input('id'));
        if (!$u) return $this->json(false, __('civinsis.toast.admin.usuario_no_encontrado'));

        $u->activo           = true;
        $u->razon_suspension = null;
        $u->suspendido_at    = null;
        $u->save();

        return $this->json(true, __('civinsis.toast.admin.usuario_reactivado'));
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
        if (!is_array($ids) || !$ids) return $this->json(false, __('civinsis.toast.admin.no_indico_que_eliminar'));

        $ids = array_map('intval', $ids);
        $n = Comentario::whereIn('id', $ids)->delete();

        return $this->json(true, __('civinsis.toast.admin.comentarios_eliminados', ['n' => $n]), ['eliminados' => $n]);
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
                    $campos[$i]['vacio']    = __('civinsis.admin.gam.sin_categoria');
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

    /** Tabla del catálogo traducible (ver App\Support\CatalogoTraducido) por cada entidad gestionable aquí. */
    private const CATALOGO_TABLA = [
        'titulo'    => 'titulos',
        'mision'    => 'misiones',
        'insignia'  => 'insignias',
        'cosmetico' => 'cosmeticos',
        // 'desafio' no tiene columna 'clave' ni catálogo de traducción todavía.
    ];

    private function gestionListar(Request $request)
    {
        $claveEntidad = (string) $request->input('entidad', '');
        $e = $this->entidad($claveEntidad);
        if (!$e) return $this->json(false, __('civinsis.toast.admin.entidad_no_reconocida'));

        $q = $e['modelo']::query();
        if (!empty($e['orden'])) $q->orderBy($e['orden']);
        $items = $q->orderByDesc('id')->limit(200)->get();

        $campos = array_column($e['campos'], 'name');
        $tablaCatalogo = self::CATALOGO_TABLA[$claveEntidad] ?? null;

        $lista  = $items->map(function ($it) use ($campos, $tablaCatalogo) {
            $fila = ['id' => $it->id];
            foreach ($campos as $c) $fila[$c] = $it->{$c};

            // 'nombre'/'descripcion' se dejan tal cual (son el valor real que
            // se edita); se agrega la versión traducida aparte, igual que en
            // CategoriaController::listar(), para no arriesgar sobrescribir
            // el original en español al guardar una edición.
            if ($tablaCatalogo && !empty($fila['clave'])) {
                if (array_key_exists('nombre', $fila)) {
                    $fila['nombre_traducido'] = CatalogoTraducido::campo($tablaCatalogo, $fila['clave'], 'nombre', $fila['nombre']);
                }
                if (array_key_exists('descripcion', $fila)) {
                    $fila['descripcion_traducido'] = CatalogoTraducido::campo($tablaCatalogo, $fila['clave'], 'descripcion', $fila['descripcion']);
                }
            }

            return $fila;
        });

        return $this->json(true, 'OK', ['items' => $lista, 'total' => $lista->count()]);
    }

    private function gestionGuardar(Request $request)
    {
        $clave = (string) $request->input('entidad', '');
        $e = $this->entidad($clave);
        if (!$e) return $this->json(false, __('civinsis.toast.admin.entidad_no_reconocida'));

        $id     = (int) $request->input('id');
        $datos  = $request->input('datos', []);
        if (!is_array($datos)) return $this->json(false, __('civinsis.toast.comunes.datos_invalidos'));

        $item = $id ? $e['modelo']::find($id) : new $e['modelo']();
        if ($id && !$item) return $this->json(false, __('civinsis.toast.admin.registro_no_encontrado'));

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
                return $this->json(false, __('civinsis.toast.admin.campo_obligatorio', ['campo' => $c['label']]));
            }
            $item->{$n} = $v;
        }

        // La clave debe ser única (varias tablas la tienen con índice unique)
        if (in_array('clave', array_column($e['campos'], 'name')) && $item->clave) {
            $dup = $e['modelo']::where('clave', $item->clave)
                ->when($item->id, fn ($q) => $q->where('id', '!=', $item->id))->exists();
            if ($dup) return $this->json(false, __('civinsis.toast.admin.clave_duplicada'));
        }

        $item->save();

        return $this->json(true, $id ? __('civinsis.toast.admin.cambios_guardados') : __('civinsis.toast.admin.creado_correctamente'), ['id' => $item->id]);
    }

    private function gestionEliminar(Request $request)
    {
        $e = $this->entidad((string) $request->input('entidad', ''));
        if (!$e) return $this->json(false, __('civinsis.toast.admin.entidad_no_reconocida'));

        $item = $e['modelo']::find((int) $request->input('id'));
        if (!$item) return $this->json(false, __('civinsis.toast.admin.registro_no_encontrado'));

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
            return $this->json(true, __('civinsis.toast.admin.desactivado_en_vez_de_eliminar'), ['desactivado' => true]);
        }

        $item->delete();
        return $this->json(true, __('civinsis.toast.admin.eliminado_correctamente'), ['desactivado' => false]);
    }
}
