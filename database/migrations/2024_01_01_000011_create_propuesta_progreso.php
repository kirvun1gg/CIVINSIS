<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propuestas', function (Blueprint $table) {
            $table->string('progreso')->default('idea')->after('estado'); // idea|discusion|mejoras|votacion|destacada
            $table->timestamp('progreso_actualizado_at')->nullable()->after('progreso');
            // false = el autor todavía no ha visto el cambio de fase más reciente
            $table->boolean('progreso_visto')->default(true)->after('progreso_actualizado_at');
        });

        // Historial de cada cambio de fase (alimenta el timeline visual)
        Schema::create('propuesta_progreso_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('propuesta_id');
            $table->string('progreso');
            $table->unsignedBigInteger('usuario_id')->nullable(); // admin/moderador que hizo el cambio (null = automático)
            $table->timestamp('fecha')->useCurrent();
            $table->timestamps();
            $table->index('propuesta_id');
        });

        // Backfill: las propuestas que ya existían arrancan en fase "Idea"
        // con fecha igual a su fecha de creación, para que el timeline no quede vacío.
        $ahora = now();
        DB::table('propuestas')->update([
            'progreso' => 'idea',
            'progreso_actualizado_at' => $ahora,
        ]);

        $filas = DB::table('propuestas')->select('id', 'fecha_creacion', 'created_at')->get();
        $historial = [];
        foreach ($filas as $p) {
            $historial[] = [
                'propuesta_id' => $p->id,
                'progreso'     => 'idea',
                'usuario_id'   => null,
                'fecha'        => $p->fecha_creacion ?? $p->created_at ?? $ahora,
                'created_at'   => $ahora,
                'updated_at'   => $ahora,
            ];
        }
        if ($historial) DB::table('propuesta_progreso_historial')->insert($historial);
    }

    public function down(): void
    {
        Schema::dropIfExists('propuesta_progreso_historial');
        Schema::table('propuestas', function (Blueprint $table) {
            $table->dropColumn(['progreso', 'progreso_actualizado_at', 'progreso_visto']);
        });
    }
};
