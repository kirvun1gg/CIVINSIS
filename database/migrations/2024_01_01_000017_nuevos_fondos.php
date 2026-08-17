<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Amplía el catálogo de FONDOS con cinco conceptos nuevos.
 * Los diez existentes conservan su clave y su nombre: solo cambia
 * su implementación visual (public/js/fondos.js).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // clave, nombre, valor(css), descripcion, rareza, nivel, xp, cond_tipo, cond_valor, oculto, orden
        $nuevos = [
            ['fondo_perspectiva', 'Perspectiva', 'fondo-perspectiva',
             'Geometría que cambia de punto de vista: otra forma de mirar el mismo problema.',
             'raro', 9, 2000, 'nivel', 9, false, 11],

            ['fondo_sinergia', 'Sinergia', 'fondo-sinergia',
             'Piezas sueltas que se acercan, se funden y vuelven a separarse.',
             'epico', 1, 0, 'debates', 15, false, 12],

            ['fondo_inspiracion', 'Inspiración', 'fondo-inspiracion',
             'Partículas que de vez en cuando convergen y encienden una idea.',
             'epico', 16, 6000, 'nivel', 16, false, 13],

            ['fondo_horizonte_infinito', 'Horizonte Infinito', 'fondo-horizonte-infinito',
             'Líneas que avanzan hacia un punto de fuga: posibilidades sin límite.',
             'legendario', 24, 13000, 'nivel', 24, false, 14],

            ['fondo_legado_vivo', 'Legado Vivo', 'fondo-legado-vivo',
             'Una señal genera ondas, las ondas generan nuevas señales.',
             'legendario', 1, 0, 'reputacion', 300, true, 15],
        ];

        foreach ($nuevos as $f) {
            [$clave, $nombre, $valor, $desc, $rareza, $nivel, $xp, $cTipo, $cValor, $oculto, $orden] = $f;
            DB::table('cosmeticos')->updateOrInsert(
                ['clave' => $clave],
                [
                    'nombre' => $nombre, 'descripcion' => $desc,
                    'tipo' => 'fondo_perfil', 'valor' => $valor, 'preview' => null,
                    'rareza' => $rareza, 'nivel_requerido' => $nivel, 'xp_requerido' => $xp,
                    'condicion_tipo' => $cTipo, 'condicion_valor' => $cValor,
                    'oculto' => $oculto, 'orden' => $orden,
                    'activo' => true, 'created_at' => $now, 'updated_at' => $now,
                ]
            );
        }

        // Descripciones actualizadas de los rediseñados
        $textos = [
            'fondo_noche'      => 'Un cielo en calma con estrellas a distinta profundidad.',
            'fondo_aurora'     => 'Corrientes de luz que se mezclan y ondulan sin repetirse.',
            'fondo_horizonte'  => 'Un amanecer abstracto: siempre hay algo que empieza.',
            'fondo_ascenso'    => 'Energía que se eleva, se desvía y destella al llegar arriba.',
            'fondo_flujo'      => 'Corrientes de información con pulsos que viajan por ellas.',
            'fondo_cosmos'     => 'Espacio profundo con parallax: un universo dentro del perfil.',
            'fondo_conexiones' => 'Una comunidad donde las ideas saltan de persona a persona.',
            'fondo_nebulosa'   => 'Materia cósmica en formación, lenta y orgánica.',
            'fondo_evolucion'  => 'Un punto que se ramifica hasta volverse una estructura compleja.',
            'fondo_legado'     => 'Una huella luminosa que recorre el fondo y tarda en apagarse.',
        ];
        foreach ($textos as $clave => $desc) {
            DB::table('cosmeticos')->where('clave', $clave)
                ->update(['descripcion' => $desc, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        DB::table('cosmeticos')->whereIn('clave', [
            'fondo_perspectiva', 'fondo_sinergia', 'fondo_inspiracion',
            'fondo_horizonte_infinito', 'fondo_legado_vivo',
        ])->delete();
    }
};
