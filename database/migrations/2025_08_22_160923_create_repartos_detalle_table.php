<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepartosDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repartos_detalle', function (Blueprint $table) {

            $table->unsignedBigInteger('reparto_id');
            $table->foreign('reparto_id')->references('id')->on('repartos');

            $table->unsignedBigInteger('paquete_embalado_id');
            $table->foreign('paquete_embalado_id')->references('id')->on('paquetes_embalados');
            
            $table->primary(['reparto_id', 'paquete_embalado_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('repartos_detalle');
    }
}
