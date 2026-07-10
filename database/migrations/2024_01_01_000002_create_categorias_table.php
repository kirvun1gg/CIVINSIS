<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('icono')->default('fas fa-tag');
            $table->string('color')->default('#36c0a1');
            $table->string('descripcion')->nullable();
            // efecto = clave del efecto hover temático (medio-ambiente, educacion, etc.)
            $table->string('efecto')->default('default');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
