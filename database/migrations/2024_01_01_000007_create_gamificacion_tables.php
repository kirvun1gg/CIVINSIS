<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Campos de gamificación en usuarios ─────────────────
        Schema::table('usuarios', function (Blueprint $table) {
            $table->unsignedBigInteger('xp')->default(0)->after('perfil_publico');
            $table->unsignedTinyInteger('nivel')->default(1)->after('xp');
            $table->integer('reputacion')->default(0)->after('nivel');
            $table->string('titulo_equipado')->nullable()->after('reputacion');
            $table->string('marco_equipado')->nullable()->after('titulo_equipado');
            $table->string('fondo_equipado')->nullable()->after('marco_equipado');
            $table->unsignedBigInteger('xp_total')->default(0)->after('fondo_equipado');
            $table->unsignedInteger('racha_dias')->default(0)->after('xp_total');
            $table->date('ultima_racha')->nullable()->after('racha_dias');
        });

        // ── 2. Historial de XP ────────────────────────────────────
        Schema::create('xp_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('accion');          // crear_propuesta, votar, comentar, etc.
            $table->integer('xp');             // puede ser negativo (penalización)
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable(); // ID de propuesta/comentario
            $table->timestamps();
        });

        // ── 3. Historial de reputación ────────────────────────────
        Schema::create('reputacion_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('de_usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->integer('puntos');
            $table->string('razon');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamps();
        });

        // ── 4. Definición de logros ───────────────────────────────
        Schema::create('logros', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();       // primera_propuesta, debate_activo, etc.
            $table->string('nombre');
            $table->text('descripcion');
            $table->string('icono');                 // emoji o clase fa
            $table->string('categoria');             // propuestas, comunidad, racha, especial
            $table->string('rareza')->default('comun'); // comun, raro, epico, legendario
            $table->integer('xp_recompensa')->default(0);
            $table->integer('reputacion_recompensa')->default(0);
            $table->json('condicion');               // {"tipo":"propuestas_creadas","valor":1}
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // ── 5. Logros desbloqueados por usuario ───────────────────
        Schema::create('usuario_logros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('logro_id')->constrained('logros')->onDelete('cascade');
            $table->timestamp('desbloqueado_at');
            $table->unique(['usuario_id', 'logro_id']);
        });

        // ── 6. Definición de insignias ────────────────────────────
        Schema::create('insignias', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->text('descripcion');
            $table->string('icono');
            $table->string('color')->default('#36c0a1');
            $table->string('categoria');   // rol, logro, evento, especial
            $table->string('rareza')->default('comun');
            $table->boolean('equipable')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── 7. Insignias desbloqueadas por usuario ────────────────
        Schema::create('usuario_insignias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('insignia_id')->constrained('insignias')->onDelete('cascade');
            $table->boolean('equipada')->default(false);
            $table->timestamp('desbloqueado_at');
            $table->unique(['usuario_id', 'insignia_id']);
        });

        // ── 8. Definición de títulos ──────────────────────────────
        Schema::create('titulos', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->string('color')->default('#36c0a1');
            $table->string('rareza')->default('comun');
            $table->string('condicion_tipo');  // nivel, logro, manual, reputacion
            $table->integer('condicion_valor')->default(0);
            $table->integer('xp_requerido')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── 9. Títulos desbloqueados por usuario ──────────────────
        Schema::create('usuario_titulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('titulo_id')->constrained('titulos')->onDelete('cascade');
            $table->boolean('equipado')->default(false);
            $table->timestamp('desbloqueado_at');
            $table->unique(['usuario_id', 'titulo_id']);
        });

        // ── 10. Cosméticos (marcos, fondos) ──────────────────────
        Schema::create('cosmeticos', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('tipo');       // marco_avatar, fondo_perfil
            $table->string('valor');      // CSS class o valor directo
            $table->string('preview')->nullable(); // CSS inline para preview
            $table->string('rareza')->default('comun');
            $table->integer('nivel_requerido')->default(1);
            $table->integer('xp_requerido')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── 11. Cosméticos desbloqueados por usuario ──────────────
        Schema::create('usuario_cosmeticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cosmetico_id')->constrained('cosmeticos')->onDelete('cascade');
            $table->boolean('equipado')->default(false);
            $table->timestamp('desbloqueado_at');
            $table->unique(['usuario_id', 'cosmetico_id']);
        });

        // ── 12. Misiones diarias/semanales ────────────────────────
        Schema::create('misiones', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->text('descripcion');
            $table->string('tipo');         // diaria, semanal, especial
            $table->string('accion');       // crear_propuesta, comentar, votar, etc.
            $table->integer('cantidad')->default(1);
            $table->integer('xp_recompensa')->default(50);
            $table->integer('reputacion_recompensa')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ── 13. Progreso de misiones por usuario ──────────────────
        Schema::create('usuario_misiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('mision_id')->constrained('misiones')->onDelete('cascade');
            $table->integer('progreso')->default(0);
            $table->boolean('completada')->default(false);
            $table->timestamp('completada_at')->nullable();
            $table->date('periodo');        // fecha del día/semana
            $table->unique(['usuario_id', 'mision_id', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['xp','nivel','reputacion','titulo_equipado',
                'marco_equipado','fondo_equipado','xp_total','racha_dias','ultima_racha']);
        });
        Schema::dropIfExists('usuario_misiones');
        Schema::dropIfExists('misiones');
        Schema::dropIfExists('usuario_cosmeticos');
        Schema::dropIfExists('cosmeticos');
        Schema::dropIfExists('usuario_titulos');
        Schema::dropIfExists('titulos');
        Schema::dropIfExists('usuario_insignias');
        Schema::dropIfExists('insignias');
        Schema::dropIfExists('usuario_logros');
        Schema::dropIfExists('logros');
        Schema::dropIfExists('reputacion_historial');
        Schema::dropIfExists('xp_historial');
    }
};
