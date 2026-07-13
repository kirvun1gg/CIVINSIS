<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // El voto simple se reemplaza por valoraciones de aspectos. Cada fila
        // de 'votos' pasa a representar (usuario, propuesta, aspecto). Un mismo
        // usuario puede marcar varios aspectos de una propuesta.
        Schema::table('votos', function (Blueprint $table) {
            $table->string('aspecto', 30)->nullable()->after('usuario_id');
        });

        // Marcar los votos existentes como aspecto 'general' para no perderlos
        DB::table('votos')->whereNull('aspecto')->update(['aspecto' => 'general']);

        // Quitar cualquier índice único antiguo sobre (propuesta_id, usuario_id).
        // Detectamos su nombre real desde el esquema para no depender del default.
        $indices = DB::select("SHOW INDEX FROM votos WHERE Non_unique = 0 AND Column_name IN ('propuesta_id','usuario_id')");
        $nombres = array_unique(array_map(fn ($i) => $i->Key_name, $indices));
        foreach ($nombres as $nombre) {
            if ($nombre === 'PRIMARY') continue;
            try { DB::statement("ALTER TABLE votos DROP INDEX `{$nombre}`"); } catch (\Throwable $e) {}
        }

        // Nuevo índice único que sí admite varios aspectos por usuario
        try {
            Schema::table('votos', function (Blueprint $table) {
                $table->unique(['propuesta_id', 'usuario_id', 'aspecto'], 'votos_prop_user_aspecto_unique');
            });
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        try {
            Schema::table('votos', function (Blueprint $table) {
                $table->dropUnique('votos_prop_user_aspecto_unique');
            });
        } catch (\Throwable $e) {}

        Schema::table('votos', function (Blueprint $table) {
            $table->dropColumn('aspecto');
        });
    }
};
