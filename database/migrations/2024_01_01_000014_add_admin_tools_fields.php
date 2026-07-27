<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Herramientas del panel administrativo:
 *  - Destacar comentarios y debates (las propuestas y las respuestas de
 *    debate ya tenían su campo "destacada").
 *  - Datos de suspensión de usuarios (la columna "activo" ya existía;
 *    aquí se guarda el motivo y cuándo se suspendió).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comentarios', function (Blueprint $table) {
            if (!Schema::hasColumn('comentarios', 'destacado')) {
                $table->boolean('destacado')->default(false)->after('contenido');
            }
        });

        Schema::table('debates', function (Blueprint $table) {
            if (!Schema::hasColumn('debates', 'destacado')) {
                $table->boolean('destacado')->default(false)->after('estado');
            }
        });

        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'razon_suspension')) {
                $table->string('razon_suspension')->nullable()->after('activo');
            }
            if (!Schema::hasColumn('usuarios', 'suspendido_at')) {
                $table->timestamp('suspendido_at')->nullable()->after('razon_suspension');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comentarios', function (Blueprint $table) {
            if (Schema::hasColumn('comentarios', 'destacado')) $table->dropColumn('destacado');
        });

        Schema::table('debates', function (Blueprint $table) {
            if (Schema::hasColumn('debates', 'destacado')) $table->dropColumn('destacado');
        });

        Schema::table('usuarios', function (Blueprint $table) {
            foreach (['razon_suspension', 'suspendido_at'] as $col) {
                if (Schema::hasColumn('usuarios', $col)) $table->dropColumn($col);
            }
        });
    }
};
