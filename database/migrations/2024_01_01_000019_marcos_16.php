<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sistema de MARCOS ampliado a 16.
 *
 * Los desbloqueos no son todos "alcanza nivel X": se reparten en
 * categorias que empujan a explorar y participar en CIVINSIS
 * (exploracion, consistencia, comunidad, coleccion, descubrimiento).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Rareza "mitica" y campos de exploracion/descubrimiento
        Schema::table('cosmeticos', function (Blueprint $table) {
            if (!Schema::hasColumn('cosmeticos', 'pista')) {
                // texto que se muestra mientras el marco sigue oculto
                $table->string('pista')->nullable()->after('oculto');
            }
        });

        // Registro de comportamientos que no existian: secciones visitadas,
        // dias activos y categorias exploradas.
        if (!Schema::hasTable('usuario_exploracion')) {
            Schema::create('usuario_exploracion', function (Blueprint $table) {
                $table->id();
                $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
                $table->string('tipo');          // seccion | categoria | dia_activo
                $table->string('valor');         // 'debates', 'medioambiente', '2026-08-19'
                $table->timestamps();
                $table->unique(['usuario_id', 'tipo', 'valor']);
            });
        }

        $now = now();

        // clave, nombre, valor(css), descripcion, rareza, cond_tipo, cond_valor, oculto, pista, orden
        $marcos = [
            ['marco_noche_estelar', 'Noche Estelar', 'marco-noche-estelar',
             'Un fragmento del cielo nocturno te acompaña.',
             'poco_comun', 'nivel', 4, false, null, 1],

            ['marco_aurora', 'Aurora', 'marco-aurora',
             'Cintas de luz que se deforman a su propio ritmo.',
             'raro', 'dias_activos', 7, false, null, 2],

            ['marco_horizonte', 'Horizonte', 'marco-horizonte',
             'Una luz recorre la línea, se detiene y vuelve a empezar.',
             'comun', 'nivel', 2, false, null, 3],

            ['marco_ascenso', 'Ascenso', 'marco-ascenso',
             'Partículas que suben por caminos distintos. Progreso, no prisa.',
             'poco_comun', 'xp', 2500, false, null, 4],

            ['marco_flujo', 'Flujo', 'marco-flujo',
             'Líneas que cruzan, se desvían y desaparecen.',
             'poco_comun', 'comentarios', 15, false, null, 5],

            ['marco_evolucion', 'Evolución', 'marco-evolucion',
             'Fragmentos que se reorganizan una y otra vez.',
             'epico', 'categorias', 5, false, null, 6],

            ['marco_conexiones', 'Conexiones', 'marco-conexiones',
             'Una red que nunca se queda quieta.',
             'raro', 'debates', 10, false, null, 7],

            ['marco_legado', 'Legado', 'marco-legado',
             'Una línea recorre su camino y deja rastro.',
             'epico', 'reputacion', 120, false, null, 8],

            ['marco_pulso_civico', 'Pulso Cívico', 'marco-pulso-civico',
             'Una señal que se propaga en tres tiempos.',
             'raro', 'votos_emitidos', 25, false, null, 9],

            ['marco_constelacion', 'Constelación', 'marco-constelacion',
             'Estrellas que se unen un instante y vuelven a separarse.',
             'epico', 'secciones', 6, false, null, 10],

            ['marco_nexo', 'Nexo', 'marco-nexo',
             'Nodos que se encuentran, destellan y siguen su camino.',
             'epico', 'propuestas', 5, false, null, 11],

            ['marco_inspiracion', 'Inspiración', 'marco-inspiracion',
             'Fragmentos que forman una figura y se dispersan.',
             'raro', 'propuestas', 2, false, null, 12],

            ['marco_orbita', 'Órbita', 'marco-orbita',
             'Elementos que orbitan, se escapan y regresan.',
             'epico', 'dias_activos', 14, false, null, 13],

            ['marco_fragmentos', 'Fragmentos', 'marco-fragmentos',
             'Piezas que se organizan en una estructura y se separan.',
             'legendario', 'coleccion', 12, true,
             'Reúne cosméticos de todas las categorías.', 14],

            ['marco_vortice', 'Vórtice', 'marco-vortice',
             'Flujos que aceleran, se cruzan y desaparecen.',
             'legendario', 'secreto_vortice', 1, true,
             'Algo ocurre cuando participas de las tres formas en un mismo día.', 15],

            ['marco_legado_vivo', 'Legado Vivo', 'marco-legado-vivo',
             'Puntos, conexiones, estructura, resplandor. Tu huella.',
             'mitico', 'secreto_legado', 1, true,
             'Solo quienes dejan huella en la comunidad lo encuentran.', 16],
        ];

        foreach ($marcos as $m) {
            [$clave, $nombre, $valor, $desc, $rareza, $cTipo, $cValor, $oculto, $pista, $orden] = $m;
            DB::table('cosmeticos')->updateOrInsert(
                ['clave' => $clave],
                [
                    'nombre' => $nombre, 'descripcion' => $desc,
                    'tipo' => 'marco_avatar', 'valor' => $valor, 'preview' => null,
                    'rareza' => $rareza,
                    'nivel_requerido' => $cTipo === 'nivel' ? $cValor : 1,
                    'xp_requerido' => $cTipo === 'xp' ? $cValor : 0,
                    'condicion_tipo' => $cTipo, 'condicion_valor' => $cValor,
                    'oculto' => $oculto, 'pista' => $pista, 'orden' => $orden,
                    'activo' => true, 'created_at' => $now, 'updated_at' => $now,
                ]
            );
        }

        // ── Migrar los marcos antiguos a las claves nuevas ──
        $renombres = [
            'marco_basico'  => 'marco_horizonte',
            'marco_radiante' => 'marco_ascenso',
            'marco_hexa'    => 'marco_flujo',
            'marco_dual'    => 'marco_nexo',
            'marco_conexion' => 'marco_conexiones',
            'marco_idea'    => 'marco_inspiracion',
            'marco_civico'  => 'marco_pulso_civico',
        ];
        foreach ($renombres as $viejo => $nuevo) {
            DB::table('usuarios')->where('marco_equipado', $viejo)
                ->update(['marco_equipado' => $nuevo]);

            $idV = DB::table('cosmeticos')->where('clave', $viejo)->value('id');
            $idN = DB::table('cosmeticos')->where('clave', $nuevo)->value('id');
            if (!$idV || !$idN) continue;

            $ya = DB::table('usuario_cosmeticos')->where('cosmetico_id', $idN)
                ->pluck('usuario_id')->all();
            DB::table('usuario_cosmeticos')->where('cosmetico_id', $idV)
                ->whereNotIn('usuario_id', $ya ?: [0])
                ->update(['cosmetico_id' => $idN]);
            DB::table('usuario_cosmeticos')->where('cosmetico_id', $idV)->delete();
            DB::table('cosmeticos')->where('id', $idV)->delete();
        }
    }

    public function down(): void
    {
        DB::table('cosmeticos')->whereIn('clave', [
            'marco_noche_estelar', 'marco_horizonte', 'marco_pulso_civico',
            'marco_nexo', 'marco_orbita', 'marco_fragmentos',
            'marco_vortice', 'marco_legado_vivo',
        ])->delete();

        Schema::dropIfExists('usuario_exploracion');
        Schema::table('cosmeticos', function (Blueprint $table) {
            if (Schema::hasColumn('cosmeticos', 'pista')) $table->dropColumn('pista');
        });
    }
};
