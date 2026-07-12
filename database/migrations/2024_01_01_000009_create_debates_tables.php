<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Debates ────────────────────────────────────────────
        Schema::create('debates', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            $table->string('estado')->default('activo'); // activo|cerrado
            $table->unsignedInteger('participantes')->default(0); // usuarios únicos que respondieron
            $table->unsignedInteger('respuestas_count')->default(0);

            $table->boolean('censurado')->default(false);
            $table->string('razon_censura')->nullable();

            $table->text('resumen_ia')->nullable();
            $table->timestamp('resumen_generado_at')->nullable();

            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });

        // ── Respuestas dentro de un debate ───────────────────────
        Schema::create('debate_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debate_id')->constrained('debates')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            // Responder a otro usuario (hilo)
            $table->foreignId('parent_id')->nullable()->constrained('debate_respuestas')->nullOnDelete();
            // Citar otra respuesta (sin formar hilo, solo referencia visual)
            $table->foreignId('cita_id')->nullable()->constrained('debate_respuestas')->nullOnDelete();

            $table->text('contenido');
            $table->text('contenido_original')->nullable();
            $table->boolean('censurado')->default(false);
            $table->string('razon_censura')->nullable();

            $table->unsignedInteger('votos')->default(0);
            $table->boolean('destacada')->default(false); // destacada por un admin/moderador

            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });

        // ── Votos de respuestas (1 voto por usuario por respuesta) ─
        Schema::create('debate_votos_respuesta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('respuesta_id')->constrained('debate_respuestas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['respuesta_id', 'usuario_id']);
        });

        // ── Extender el enum de moderacion_alertas para incluir debates ─
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE moderacion_alertas MODIFY tipo ENUM('comentario','propuesta','debate','debate_respuesta') NOT NULL"
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('debate_votos_respuesta');
        Schema::dropIfExists('debate_respuestas');
        Schema::dropIfExists('debates');

        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE moderacion_alertas MODIFY tipo ENUM('comentario','propuesta') NOT NULL"
        );
    }
};
