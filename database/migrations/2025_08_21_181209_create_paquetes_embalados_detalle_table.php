<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaquetesEmbaladosDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paquetes_embalados_detalle', function (Blueprint $table) {

            $table->unsignedBigInteger('paquete_embalado_id');
            $table->foreign('paquete_embalado_id')->references('id')->on('paquetes_embalados');

            $table->unsignedBigInteger('envio_venta_id');
            $table->foreign('envio_venta_id')->references('id')->on('envios_ventas');

            $table->primary(['paquete_embalado_id', 'envio_venta_id'], 'pk_paquetes_embalados_detalle');

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
        Schema::dropIfExists('paquetes_embalados_detalle');
    }
}
