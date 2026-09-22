<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Las notificaciones se guardaban con el texto final ya renderizado en el
// idioma del ACTOR que disparaba la acción (ej. un admin cambiando la fase
// de una propuesta), no del destinatario que la lee después. Eso las dejaba
// congeladas en ese idioma para siempre. A partir de ahora se guarda una
// clave + parámetros y el mensaje se traduce al vuelo con el idioma del
// usuario que las lista (ver Notificacion::mensajeTraducido()).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->string('clave')->nullable()->after('tipo');
            $table->json('datos')->nullable()->after('clave');
        });

        // Se evita ->change() (requeriría doctrine/dbal, no instalado en el
        // proyecto) y se usa SQL nativo para permitir NULL en 'mensaje':
        // las notificaciones nuevas ya no guardan texto final, solo clave+datos.
        DB::statement('ALTER TABLE notificaciones MODIFY mensaje VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropColumn(['clave', 'datos']);
        });

        DB::statement("UPDATE notificaciones SET mensaje = '' WHERE mensaje IS NULL");
        DB::statement('ALTER TABLE notificaciones MODIFY mensaje VARCHAR(255) NOT NULL');
    }
};
