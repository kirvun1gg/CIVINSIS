<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Campos de moderación en comentarios ──────────────────
        Schema::table('comentarios', function (Blueprint $table) {
            $table->text('contenido_original')->nullable()->after('contenido');
            $table->boolean('censurado')->default(false)->after('contenido_original');
            $table->string('razon_censura')->nullable()->after('censurado');
        });

        // ── Campos de moderación en propuestas ───────────────────
        Schema::table('propuestas', function (Blueprint $table) {
            $table->boolean('censurada')->default(false)->after('estado');
            $table->string('razon_censura')->nullable()->after('censurada');
        });

        // ── Tabla de alertas de moderación (panel admin) ─────────
        Schema::create('moderacion_alertas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['comentario', 'propuesta']);
            $table->unsignedBigInteger('referencia_id');  // ID del comentario o propuesta
            $table->text('contenido_original');           // Texto que activó la alerta
            $table->string('razon');                      // Descripción del problema detectado
            $table->enum('severidad', ['baja', 'media', 'alta'])->default('media');
            $table->boolean('revisado')->default(false);  // El admin ya lo revisó
            $table->timestamp('revisado_at')->nullable();
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderacion_alertas');

        Schema::table('propuestas', function (Blueprint $table) {
            $table->dropColumn(['censurada', 'razon_censura']);
        });

        Schema::table('comentarios', function (Blueprint $table) {
            $table->dropColumn(['contenido_original', 'censurado', 'razon_censura']);
        });
    }
};
