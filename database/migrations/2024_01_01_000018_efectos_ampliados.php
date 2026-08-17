<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sistema de EFECTOS ampliado a 16.
 *
 * Un efecto NO es un adorno permanente: es un evento visual que ocurre
 * cuando el usuario hace algo. Por eso cada uno guarda ademas el evento
 * que lo dispara (columna `evento`), que puede cambiarse despues sin
 * tocar codigo.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Evento que dispara cada efecto (configurable desde la BD)
        Schema::table('cosmeticos', function (Blueprint $table) {
            if (!Schema::hasColumn('cosmeticos', 'evento')) {
                $table->string('evento')->nullable()->after('condicion_valor');
            }
        });

        $now = now();

        // clave, nombre, valor(css), evento, descripcion, rareza, nivel, xp, cond, valor, oculto, orden
        $efectos = [
            // ── COMUNES ──
            ['efecto_chispa', 'Chispa', 'efecto-chispa', 'mision_completada',
             'Una chispa brota, destella y se deshace en partículas.',
             'comun', 2, 150, 'nivel', 2, false, 1],

            ['efecto_rebote', 'Rebote', 'efecto-rebote', 'voto_emitido',
             'Una esfera sale, vuelve y se apaga con un destello.',
             'comun', 3, 250, 'nivel', 3, false, 2],

            // ── RAROS ──
            ['efecto_idea', 'Idea', 'efecto-idea', 'propuesta_creada',
             'Partículas dispersas que convergen, destellan y se separan.',
             'raro', 1, 0, 'propuestas', 1, false, 3],

            ['efecto_impulso', 'Impulso', 'efecto-impulso', 'voto_recibido',
             'Una onda de energía se expande y se desvanece.',
             'raro', 1, 0, 'votos', 10, false, 4],

            ['efecto_eco', 'Eco', 'efecto-eco', 'comentario_recibido',
             'Tres pulsos encadenados: cada acción tiene respuesta.',
             'raro', 6, 900, 'nivel', 6, false, 5],

            ['efecto_orbita', 'Órbita', 'efecto-orbita', 'racha_dia',
             'Partículas que orbitan, aceleran y salen despedidas.',
             'raro', 8, 1600, 'nivel', 8, false, 6],

            // ── ÉPICOS ──
            ['efecto_pulso', 'Pulso', 'efecto-pulso', 'interaccion_recibida',
             'Una señal se transmite hacia fuera y remata en un pulso.',
             'epico', 1, 0, 'debates', 8, false, 7],

            ['efecto_estrella_fugaz', 'Estrella Fugaz', 'efecto-estrella-fugaz', 'subida_nivel',
             'Una estrella cruza el perfil dejando rastro.',
             'epico', 14, 4500, 'nivel', 14, false, 8],

            ['efecto_enlace', 'Enlace', 'efecto-enlace', 'comentario_creado',
             'Una señal viaja hasta la comunidad y algo responde allí.',
             'epico', 1, 0, 'debates', 12, false, 9],

            ['efecto_ascenso', 'Ascenso', 'efecto-ascenso', 'xp_ganado',
             'Partículas que suben, se desvían y destellan arriba.',
             'epico', 1, 7000, 'xp', 7000, false, 10],

            ['efecto_reaccion', 'Reacción', 'efecto-reaccion', 'propuesta_apoyada',
             'Un punto genera una onda, y la onda genera más puntos.',
             'epico', 1, 0, 'comentarios', 30, false, 11],

            ['efecto_convergencia', 'Convergencia', 'efecto-convergencia', 'desafio_completado',
             'Ideas separadas que se encuentran y crean algo nuevo.',
             'epico', 18, 7500, 'nivel', 18, false, 12],

            // ── LEGENDARIOS ──
            ['efecto_aurora', 'Aurora', 'efecto-aurora', 'logro_desbloqueado',
             'Una onda de luz nace detrás del avatar y cambia de color.',
             'legendario', 20, 9500, 'nivel', 20, false, 13],

            ['efecto_metamorfosis', 'Metamorfosis', 'efecto-metamorfosis', 'nivel_importante',
             'Destello, órbita, convergencia y transformación.',
             'legendario', 1, 0, 'reputacion', 200, true, 14],

            ['efecto_onda_civica', 'Onda Cívica', 'efecto-onda-civica', 'impacto_comunitario',
             'Tu acción se propaga y la comunidad reacciona en cadena.',
             'legendario', 1, 0, 'reputacion', 250, false, 15],

            ['efecto_destello', 'Destello', 'efecto-destello', 'momento_excepcional',
             'Una concentración de luz breve, elegante y poderosa.',
             'legendario', 26, 16000, 'nivel', 26, true, 16],
        ];

        foreach ($efectos as $e) {
            [$clave, $nombre, $valor, $evento, $desc, $rareza, $nivel, $xp, $cTipo, $cValor, $oculto, $orden] = $e;
            DB::table('cosmeticos')->updateOrInsert(
                ['clave' => $clave],
                [
                    'nombre' => $nombre, 'descripcion' => $desc,
                    'tipo' => 'efecto_avatar', 'valor' => $valor, 'preview' => null,
                    'rareza' => $rareza, 'nivel_requerido' => $nivel, 'xp_requerido' => $xp,
                    'condicion_tipo' => $cTipo, 'condicion_valor' => $cValor,
                    'evento' => $evento, 'oculto' => $oculto, 'orden' => $orden,
                    'activo' => true, 'created_at' => $now, 'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('cosmeticos')->whereIn('clave', [
            'efecto_rebote', 'efecto_eco', 'efecto_orbita',
            'efecto_reaccion', 'efecto_convergencia',
            'efecto_onda_civica', 'efecto_destello',
        ])->delete();

        Schema::table('cosmeticos', function (Blueprint $table) {
            if (Schema::hasColumn('cosmeticos', 'evento')) $table->dropColumn('evento');
        });
    }
};
