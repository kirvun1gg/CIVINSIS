<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rediseño de la categoría EFECTOS.
 *
 * Regla nueva: un MARCO permanece, un EFECTO ocurre.
 * Se retiran los efectos que duplicaban a su marco (Conexión, Constelación)
 * y se sustituyen por eventos breves que aportan algo distinto:
 *   Conexión     → Enlace          (una señal viaja hacia la comunidad)
 *   Constelación → Estrella Fugaz  (una estrella cruza el perfil)
 *   Legado       → Metamorfosis    (transformación con destello)
 * Y se añade Pulso (épico).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // clave, nombre, valor(css), descripcion, rareza, nivel, xp, cond_tipo, cond_valor, oculto, orden
        $efectos = [
            ['efecto_chispa', 'Chispa', 'efecto-chispa',
             'Una chispa brota, destella y se deshace en partículas.',
             'comun', 2, 150, 'nivel', 2, false, 1],

            ['efecto_idea', 'Idea', 'efecto-idea',
             'Partículas dispersas que convergen, destellan y se dispersan: así nace una idea.',
             'raro', 1, 0, 'propuestas', 1, false, 2],

            ['efecto_impulso', 'Impulso', 'efecto-impulso',
             'Una onda de energía sale del avatar y se desvanece.',
             'raro', 1, 0, 'votos', 10, false, 3],

            ['efecto_pulso', 'Pulso', 'efecto-pulso',
             'Una señal viaja hacia afuera y rebota en una segunda onda.',
             'epico', 1, 0, 'debates', 8, false, 4],

            ['efecto_estrella_fugaz', 'Estrella Fugaz', 'efecto-estrella-fugaz',
             'Una estrella cruza el perfil dejando un rastro luminoso.',
             'epico', 14, 4500, 'nivel', 14, false, 5],

            ['efecto_enlace', 'Enlace', 'efecto-enlace',
             'Envías una señal a la comunidad y algo responde al otro lado.',
             'epico', 1, 0, 'debates', 12, false, 6],

            ['efecto_ascenso', 'Ascenso', 'efecto-ascenso',
             'Partículas que suben, destellan al llegar arriba y desaparecen.',
             'epico', 1, 7000, 'xp', 7000, false, 7],

            ['efecto_aurora', 'Aurora', 'efecto-aurora',
             'Una onda de luz nace detrás del avatar y cambia de color al expandirse.',
             'legendario', 20, 9500, 'nivel', 20, false, 8],

            ['efecto_metamorfosis', 'Metamorfosis', 'efecto-metamorfosis',
             'Destello, órbita y transformación: el progreso hecho luz.',
             'legendario', 1, 0, 'reputacion', 200, true, 9],
        ];

        foreach ($efectos as $e) {
            [$clave, $nombre, $valor, $desc, $rareza, $nivel, $xp, $cTipo, $cValor, $oculto, $orden] = $e;
            DB::table('cosmeticos')->updateOrInsert(
                ['clave' => $clave],
                [
                    'nombre' => $nombre, 'descripcion' => $desc,
                    'tipo' => 'efecto_avatar', 'valor' => $valor, 'preview' => null,
                    'rareza' => $rareza, 'nivel_requerido' => $nivel, 'xp_requerido' => $xp,
                    'condicion_tipo' => $cTipo, 'condicion_valor' => $cValor,
                    'oculto' => $oculto, 'orden' => $orden,
                    'activo' => true, 'created_at' => $now, 'updated_at' => $now,
                ]
            );
        }

        // ── Sustituir los efectos retirados conservando lo desbloqueado ──
        $sustituciones = [
            'efecto_conexion'     => 'efecto_enlace',
            'efecto_constelacion' => 'efecto_estrella_fugaz',
            'efecto_legado'       => 'efecto_metamorfosis',
        ];

        foreach ($sustituciones as $viejo => $nuevo) {
            DB::table('usuarios')->where('efecto_equipado', $viejo)
                ->update(['efecto_equipado' => $nuevo]);

            $idViejo = DB::table('cosmeticos')->where('clave', $viejo)->value('id');
            $idNuevo = DB::table('cosmeticos')->where('clave', $nuevo)->value('id');
            if (!$idViejo || !$idNuevo) continue;

            // Quien ya tuviera el viejo, se queda con el nuevo
            $yaTienen = DB::table('usuario_cosmeticos')->where('cosmetico_id', $idNuevo)
                ->pluck('usuario_id')->all();
            DB::table('usuario_cosmeticos')->where('cosmetico_id', $idViejo)
                ->whereNotIn('usuario_id', $yaTienen ?: [0])
                ->update(['cosmetico_id' => $idNuevo]);
            DB::table('usuario_cosmeticos')->where('cosmetico_id', $idViejo)->delete();
            DB::table('cosmeticos')->where('id', $idViejo)->delete();
        }
    }

    public function down(): void
    {
        DB::table('cosmeticos')->whereIn('clave', [
            'efecto_pulso', 'efecto_estrella_fugaz', 'efecto_enlace', 'efecto_metamorfosis',
        ])->delete();
    }
};
