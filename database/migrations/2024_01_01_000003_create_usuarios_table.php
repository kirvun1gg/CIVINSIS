<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->foreignId('rol_id')->default(3)->constrained('roles');
            $table->boolean('activo')->default(true);

            // ── Personalización de perfil (ampliada) ──────────────
            $table->longText('avatar')->nullable();          // base64 o ruta
            $table->text('bio')->nullable();
            $table->string('color_perfil')->default('#36c0a1');   // color de acento
            $table->string('color_banner')->default('#0f1c19');   // color del banner
            $table->string('banner_imagen')->nullable();          // banner base64 (opcional)
            $table->string('tema_perfil')->default('verde');      // verde|naranja|azul|morado|rosa|dark
            $table->string('marco_avatar')->default('circulo');   // circulo|hexagono|cuadrado|estrella
            $table->string('insignia')->nullable();               // emoji/icono decorativo
            $table->string('frase')->nullable();                  // frase/lema corto
            $table->string('ubicacion')->nullable();
            $table->string('sitio_web')->nullable();
            $table->string('social_twitter')->nullable();
            $table->string('social_instagram')->nullable();
            $table->string('social_github')->nullable();
            $table->boolean('perfil_publico')->default(true);

            $table->timestamp('ultimo_acceso')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Tabla de reseteo de contraseñas (la usa el auth de Laravel)
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('usuarios');
    }
};
