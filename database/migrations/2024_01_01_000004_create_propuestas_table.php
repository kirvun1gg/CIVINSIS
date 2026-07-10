<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuestas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->longText('contenido')->nullable();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();

            // ── Personalización de la tarjeta (ampliada) ──────────
            $table->string('diseno')->default('default');   // default|dark|gradient|minimal|neon|glass|sunset|ocean|retro|aurora|cyber|pastel
            $table->string('color_acento')->nullable();      // override de color de la tarjeta
            $table->string('icono_extra')->nullable();       // icono decorativo opcional
            $table->boolean('efecto_categoria')->default(true); // activar el efecto hover por categoría
            $table->boolean('destacada')->default(false);    // tarjeta destacada (glow)
            $table->longText('imagen')->nullable();          // base64

            $table->unsignedInteger('votos')->default(0);
            $table->unsignedInteger('vistas')->default(0);
            $table->string('estado')->default('activa');     // activa|en_revision|aprobada|rechazada
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuestas');
    }
};
