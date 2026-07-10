<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propuesta_id')->constrained('propuestas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['propuesta_id', 'usuario_id']);
        });

        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propuesta_id')->constrained('propuestas')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->text('contenido');
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });

        Schema::create('contacto_mensajes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email');
            $table->string('asunto');
            $table->text('mensaje');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->boolean('leido')->default(false);
            $table->text('respuesta')->nullable();
            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto_mensajes');
        Schema::dropIfExists('comentarios');
        Schema::dropIfExists('votos');
    }
};
