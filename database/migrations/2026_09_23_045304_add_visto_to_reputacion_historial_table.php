<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVistoToReputacionHistorialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reputacion_historial', function (Blueprint $table) {
            // Permite mostrarle al usuario un modal la primera vez que ve
            // una penalización (puntos negativos) sin repetirlo en cada carga.
            $table->boolean('visto')->default(false)->after('razon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reputacion_historial', function (Blueprint $table) {
            $table->dropColumn('visto');
        });
    }
}
