<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sistema de cosméticos ampliado:
 *  - Nueva categoría "efecto_avatar" (además de marco_avatar y fondo_perfil)
 *  - Desbloqueos variados: nivel, XP, reputación, participación, misión, logro
 *  - Cosméticos ocultos (misteriosos) que solo se revelan al conseguirlos
 *  - Catálogo completo con identidad propia por cosmético
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Columna para el efecto equipado ──
        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'efecto_equipado')) {
                $table->string('efecto_equipado')->nullable()->after('fondo_equipado');
            }
        });

        // ── Campos nuevos del catálogo ──
        Schema::table('cosmeticos', function (Blueprint $table) {
            if (!Schema::hasColumn('cosmeticos', 'condicion_tipo')) {
                // nivel | xp | reputacion | propuestas | comentarios | debates | secreto
                $table->string('condicion_tipo')->default('nivel')->after('xp_requerido');
            }
            if (!Schema::hasColumn('cosmeticos', 'condicion_valor')) {
                $table->integer('condicion_valor')->default(0)->after('condicion_tipo');
            }
            if (!Schema::hasColumn('cosmeticos', 'oculto')) {
                $table->boolean('oculto')->default(false)->after('condicion_valor');
            }
            if (!Schema::hasColumn('cosmeticos', 'orden')) {
                $table->integer('orden')->default(0)->after('oculto');
            }
        });

        $this->sembrarCatalogo();
    }

    /**
     * Inserta o actualiza el catálogo completo.
     * Se usa updateOrInsert para no duplicar si ya existía la clave,
     * y para no perder lo que los usuarios ya tengan desbloqueado.
     */
    private function sembrarCatalogo(): void
    {
        $now = now();

        $items = [
            // ══════════════ MARCOS ══════════════
            ['marco_basico', 'Marco Básico', 'marco_avatar', 'marco-basico',
             'Un borde limpio para empezar tu camino cívico.',
             'comun', 1, 0, 'nivel', 1, false, 1],

            ['marco_radiante', 'Marco Radiante', 'marco_avatar', 'marco-radiante',
             'Luz y energía: un reflejo recorre el borde sin descanso.',
             'raro', 5, 700, 'nivel', 5, false, 2],

            ['marco_hexa', 'Marco Hexa', 'marco_avatar', 'marco-hexa',
             'Geometría y precisión. La estructura sostiene toda buena idea.',
             'raro', 7, 1200, 'nivel', 7, false, 3],

            ['marco_dual', 'Marco Dual', 'marco_avatar', 'marco-dual',
             'Dos identidades que se mezclan: así nace el diálogo.',
             'epico', 10, 2500, 'nivel', 10, false, 4],

            ['marco_conexion', 'Marco Conexión', 'marco_avatar', 'marco-conexion',
             'Puntos que se buscan. Nadie construye comunidad en solitario.',
             'epico', 1, 0, 'debates', 5, false, 5],

            ['marco_idea', 'Marco Idea', 'marco_avatar', 'marco-idea',
             'Una chispa recorre el borde: toda propuesta empieza así.',
             'epico', 1, 0, 'propuestas', 3, false, 6],

            ['marco_aurora', 'Marco Aurora', 'marco_avatar', 'marco-aurora',
             'Una aurora que representa el crecimiento de una comunidad.',
             'legendario', 20, 9000, 'nivel', 20, false, 7],

            ['marco_civico', 'Marco Cívico', 'marco_avatar', 'marco-civico',
             'La marca de quien se ganó la confianza de los demás.',
             'legendario', 1, 0, 'reputacion', 100, false, 8],

            ['marco_constelacion', 'Marco Constelación', 'marco_avatar', 'marco-constelacion',
             'Cada ciudadano es un punto. Juntos, una constelación.',
             'legendario', 1, 0, 'secreto', 0, true, 9],

            // ══════════════ FONDOS ══════════════
            ['fondo_noche', 'Noche Estelar', 'fondo_perfil', 'fondo-noche',
             'Un cielo tranquilo para pensar en grande.',
             'comun', 1, 0, 'nivel', 1, false, 1],

            ['fondo_aurora', 'Aurora', 'fondo_perfil', 'fondo-aurora',
             'Verdes y azules en movimiento lento.',
             'comun', 3, 300, 'nivel', 3, false, 2],

            ['fondo_horizonte', 'Horizonte', 'fondo_perfil', 'fondo-horizonte',
             'Nuevos comienzos: siempre hay una luz que se abre paso.',
             'comun', 4, 400, 'nivel', 4, false, 3],

            ['fondo_ascenso', 'Ascenso', 'fondo_perfil', 'fondo-ascenso',
             'Energía que sube. Tu participación tiene dirección.',
             'raro', 1, 3000, 'xp', 3000, false, 4],

            ['fondo_flujo', 'Flujo', 'fondo_perfil', 'fondo-flujo',
             'Las ideas circulan y encuentran su cauce.',
             'raro', 8, 1500, 'nivel', 8, false, 5],

            ['fondo_cosmos', 'Cosmos', 'fondo_perfil', 'fondo-cosmos',
             'Espacio profundo. Todo lo que aún está por proponerse.',
             'epico', 15, 5000, 'nivel', 15, false, 6],

            ['fondo_conexiones', 'Conexiones', 'fondo_perfil', 'fondo-conexiones',
             'Una red viva de personas que se escuchan.',
             'epico', 1, 0, 'comentarios', 25, false, 7],

            ['fondo_nebulosa', 'Nebulosa', 'fondo_perfil', 'fondo-nebulosa',
             'Materia en formación, como una idea que aún toma forma.',
             'epico', 17, 6500, 'nivel', 17, false, 8],

            ['fondo_evolucion', 'Evolución', 'fondo_perfil', 'fondo-evolucion',
             'Puntos que se conectan, crecen y vuelven a empezar.',
             'legendario', 22, 11000, 'nivel', 22, false, 9],

            ['fondo_legado', 'Legado', 'fondo_perfil', 'fondo-legado',
             'La huella que dejas en tu comunidad.',
             'legendario', 25, 15000, 'nivel', 25, false, 10],

            // ══════════════ EFECTOS ══════════════
            ['efecto_chispa', 'Chispa', 'efecto_avatar', 'efecto-chispa',
             'Pequeños destellos que aparecen de vez en cuando.',
             'comun', 2, 150, 'nivel', 2, false, 1],

            ['efecto_idea', 'Idea', 'efecto_avatar', 'efecto-idea',
             'Destellos que suben como quien acaba de tener una idea.',
             'raro', 1, 0, 'propuestas', 1, false, 2],

            ['efecto_impulso', 'Impulso', 'efecto_avatar', 'efecto-impulso',
             'Una onda se expande: tu voz llega más lejos de lo que crees.',
             'raro', 1, 0, 'votos', 10, false, 3],

            ['efecto_conexion', 'Conexión', 'efecto_avatar', 'efecto-conexion',
             'Puntos que se enlazan y dejan pasar un pulso de luz.',
             'epico', 1, 0, 'debates', 10, false, 4],

            ['efecto_constelacion', 'Constelación', 'efecto_avatar', 'efecto-constelacion',
             'Estrellas que cambian de posición muy lentamente.',
             'epico', 14, 4500, 'nivel', 14, false, 5],

            ['efecto_ascenso', 'Ascenso', 'efecto_avatar', 'efecto-ascenso',
             'Partículas que ascienden. Crecimiento constante.',
             'epico', 1, 7000, 'xp', 7000, false, 6],

            ['efecto_aurora', 'Aurora', 'efecto_avatar', 'efecto-aurora',
             'Una corriente de luz que rodea tu avatar.',
             'legendario', 20, 9500, 'nivel', 20, false, 7],

            ['efecto_legado', 'Legado', 'efecto_avatar', 'efecto-legado',
             'Una onda dorada y partículas que quedan flotando.',
             'legendario', 1, 0, 'reputacion', 200, true, 8],
        ];

        foreach ($items as $i) {
            [$clave, $nombre, $tipo, $valor, $desc, $rareza, $nivel, $xp, $cTipo, $cValor, $oculto, $orden] = $i;
            DB::table('cosmeticos')->updateOrInsert(
                ['clave' => $clave],
                [
                    'nombre' => $nombre, 'descripcion' => $desc, 'tipo' => $tipo, 'valor' => $valor,
                    'preview' => null, 'rareza' => $rareza,
                    'nivel_requerido' => $nivel, 'xp_requerido' => $xp,
                    'condicion_tipo' => $cTipo, 'condicion_valor' => $cValor,
                    'oculto' => $oculto, 'orden' => $orden,
                    'activo' => true, 'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        // ── Migrar equipados antiguos a las claves nuevas ──
        $renombres = [
            'marco_dorado'     => 'marco_radiante',
            'marco_hexagono'   => 'marco_hexa',
            'marco_epico'      => 'marco_dual',
            'fondo_oscuro'     => 'fondo_noche',
            'fondo_fuego'      => 'fondo_ascenso',
            'fondo_cosmo'      => 'fondo_cosmos',
            'fondo_leyenda'    => 'fondo_legado',
        ];
        foreach ($renombres as $viejo => $nuevo) {
            DB::table('usuarios')->where('marco_equipado', $viejo)->update(['marco_equipado' => $nuevo]);
            DB::table('usuarios')->where('fondo_equipado', $viejo)->update(['fondo_equipado' => $nuevo]);

            // Trasladar lo que los usuarios ya tenían desbloqueado
            $idViejo = DB::table('cosmeticos')->where('clave', $viejo)->value('id');
            $idNuevo = DB::table('cosmeticos')->where('clave', $nuevo)->value('id');
            if ($idViejo && $idNuevo) {
                $yaTienen = DB::table('usuario_cosmeticos')->where('cosmetico_id', $idNuevo)
                    ->pluck('usuario_id')->all();
                DB::table('usuario_cosmeticos')->where('cosmetico_id', $idViejo)
                    ->whereNotIn('usuario_id', $yaTienen ?: [0])
                    ->update(['cosmetico_id' => $idNuevo]);
                DB::table('usuario_cosmeticos')->where('cosmetico_id', $idViejo)->delete();
                DB::table('cosmeticos')->where('id', $idViejo)->delete();
            }
        }
        // El "marco_legendario" antiguo pasa a ser el Aurora
        DB::table('usuarios')->where('marco_equipado', 'marco_legendario')
            ->update(['marco_equipado' => 'marco_aurora']);
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            if (Schema::hasColumn('usuarios', 'efecto_equipado')) $table->dropColumn('efecto_equipado');
        });
        Schema::table('cosmeticos', function (Blueprint $table) {
            foreach (['condicion_tipo', 'condicion_valor', 'oculto', 'orden'] as $c) {
                if (Schema::hasColumn('cosmeticos', $c)) $table->dropColumn($c);
            }
        });
    }
};
