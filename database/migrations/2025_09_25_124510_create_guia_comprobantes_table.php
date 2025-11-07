<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuiaComprobantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guia_comprobantes', function (Blueprint $table) {

            $table->unsignedInteger('guia_remision_id');
            $table->foreign('guia_remision_id')->references('id')->on('guias_remision');

            $table->unsignedBigInteger('comprobante_id');
            $table->foreign('comprobante_id')->references('id')->on('cotizacion_documento');

            $table->string('comprobante_serie',160);

            $table->primary(['guia_remision_id', 'comprobante_id'], 'pk_guia_comprobantes');

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
        Schema::dropIfExists('guia_comprobantes');
    }
}
