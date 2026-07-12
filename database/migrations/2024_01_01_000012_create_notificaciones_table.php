<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sin FK física hacia 'usuarios' por seguridad (mismo criterio que en
        // desafios/debates): evita el error 1824 visto en algunos entornos WAMP.
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('tipo');           // progreso_propuesta, voto, comentario, logro, etc.
            $table->string('icono')->default('fas fa-bell');
            $table->string('color')->default('#36c0a1');
            $table->string('mensaje');
            $table->string('enlace')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->index('usuario_id');
            $table->index(['usuario_id', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
