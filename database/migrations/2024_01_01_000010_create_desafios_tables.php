<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Desafíos (permanentes, inspiran nuevas propuestas) ───
        Schema::create('desafios', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('dificultad')->default('facil'); // facil|medio|dificil
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('icono')->default('fas fa-bullseye');

            $table->unsignedInteger('xp_recompensa')->default(40);
            $table->unsignedInteger('reputacion_recompensa')->default(5);
            $table->foreignId('insignia_id')->nullable()->constrained('insignias')->nullOnDelete();

            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        // ── Progreso del usuario en cada desafío ─────────────────
        Schema::create('usuario_desafios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->unsignedBigInteger('desafio_id');
            $table->foreignId('propuesta_id')->nullable()->constrained('propuestas')->nullOnDelete();
            $table->boolean('completado')->default(false);
            $table->timestamp('completado_at')->nullable();
            $table->timestamps();
            $table->unique(['usuario_id', 'desafio_id']);
            $table->index('desafio_id');
        });

        // ── Vincular una propuesta con el desafío que la originó ─
        // (sin FK física: en algunos entornos WAMP/MariaDB crear la
        //  restricción justo después de crear la tabla referenciada
        //  falla con el error 1824. La relación se mantiene a nivel
        //  de Eloquent, que es suficiente para este caso de uso).
        Schema::table('propuestas', function (Blueprint $table) {
            $table->unsignedBigInteger('desafio_id')->nullable()->after('categoria_id');
            $table->index('desafio_id');
        });

        // ── Semilla de desafíos permanentes ───────────────────────
        $cat = fn (string $nombre) => DB::table('categorias')->where('nombre', $nombre)->value('id');
        $insigniaPropulsor = DB::table('insignias')->where('clave', 'propulsor')->value('id');

        DB::table('desafios')->insert([
            [
                'titulo' => '¿Cómo reducirías el desperdicio de agua?',
                'descripcion' => 'Piensa en una idea concreta para que tu comunidad consuma agua de forma más responsable.',
                'dificultad' => 'facil', 'categoria_id' => $cat('Medio Ambiente'), 'icono' => 'fas fa-droplet',
                'xp_recompensa' => 40, 'reputacion_recompensa' => 5, 'insignia_id' => null,
                'orden' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'titulo' => '¿Cómo mejorarías la seguridad vial?',
                'descripcion' => 'Propón una solución para que las calles de tu comunidad sean más seguras para peatones y conductores.',
                'dificultad' => 'medio', 'categoria_id' => $cat('Seguridad'), 'icono' => 'fas fa-road',
                'xp_recompensa' => 50, 'reputacion_recompensa' => 8, 'insignia_id' => null,
                'orden' => 2, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'titulo' => '¿Cómo incentivarías el reciclaje?',
                'descripcion' => 'Diseña una propuesta que motive a más personas a reciclar en su día a día.',
                'dificultad' => 'facil', 'categoria_id' => $cat('Medio Ambiente'), 'icono' => 'fas fa-recycle',
                'xp_recompensa' => 40, 'reputacion_recompensa' => 5, 'insignia_id' => null,
                'orden' => 3, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'titulo' => '¿Cómo mejorarías la educación pública?',
                'descripcion' => 'Comparte una idea que ayude a elevar la calidad de la educación en las escuelas públicas.',
                'dificultad' => 'dificil', 'categoria_id' => $cat('Educación'), 'icono' => 'fas fa-graduation-cap',
                'xp_recompensa' => 70, 'reputacion_recompensa' => 12, 'insignia_id' => $insigniaPropulsor,
                'orden' => 4, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'titulo' => '¿Cómo ayudarías a personas con discapacidad?',
                'descripcion' => 'Propón mejoras de accesibilidad o inclusión para personas con discapacidad en tu comunidad.',
                'dificultad' => 'medio', 'categoria_id' => $cat('Salud'), 'icono' => 'fas fa-universal-access',
                'xp_recompensa' => 60, 'reputacion_recompensa' => 10, 'insignia_id' => $insigniaPropulsor,
                'orden' => 5, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::table('propuestas', function (Blueprint $table) {
            $table->dropIndex(['desafio_id']);
            $table->dropColumn('desafio_id');
        });
        Schema::dropIfExists('usuario_desafios');
        Schema::dropIfExists('desafios');
    }
};
