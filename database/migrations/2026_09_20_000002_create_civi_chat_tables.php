<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Memoria de chat de CIVI, por usuario — igual que 'notificaciones', sin FK
        // física hacia 'usuarios' (evita el error 1824 visto en algunos entornos WAMP).
        Schema::create('civi_conversaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('titulo')->nullable(); // se genera del primer mensaje del usuario
            $table->timestamps();

            $table->index('usuario_id');
            $table->index(['usuario_id', 'updated_at']);
        });

        // 'onDelete cascade' se deja declarado por documentación/compatibilidad
        // futura, pero en la práctica las tablas de este proyecto corren sobre
        // MyISAM y el motor lo ignora en silencio: el borrado en cascada real
        // lo hace App\Models\CiviConversacion::boot() (evento 'deleting').
        Schema::create('civi_mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversacion_id')->constrained('civi_conversaciones')->onDelete('cascade');
            $table->enum('rol', ['user', 'assistant']);
            $table->text('contenido');
            $table->timestamps();

            $table->index('conversacion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('civi_mensajes');
        Schema::dropIfExists('civi_conversaciones');
    }
};
