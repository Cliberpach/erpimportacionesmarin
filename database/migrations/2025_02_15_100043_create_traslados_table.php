<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrasladosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('almacen_origen_id');
            $table->unsignedInteger('almacen_destino_id');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('sede_origen_id');
            $table->unsignedBigInteger('sede_destino_id');
            $table->date('fecha_traslado')->nullable();
            $table->date('fecha_envio')->nullable();

            $table->unsignedInteger('registrador_id');
            $table->string('registrador_nombre', 160);
            $table->unsignedInteger('aprobador_id');
            $table->string('aprobador_nombre', 160);
            $table->unsignedInteger('usuario_envio_id');
            $table->string('usuario_envio_nombre', 160);
            $table->unsignedInteger('anulacion_usuario_id');
            $table->string('anulacion_usuario_nombre', 160);

            $table->unsignedInteger('guia_id');

            $table->dateTime('anulacion_fecha');

            $table->timestamps();

            $table->enum('estado', ['PENDIENTE', 'ENVIADO', 'RECIBIDO', 'ANULADO'])->default('PENDIENTE');

            $table->foreign('almacen_origen_id')->references('id')->on('almacenes');
            $table->foreign('almacen_destino_id')->references('id')->on('almacenes');
            $table->foreign('sede_origen_id')->references('id')->on('empresa_sedes');
            $table->foreign('sede_destino_id')->references('id')->on('empresa_sedes');
            $table->foreign('registrador_id')->references('id')->on('users');
            $table->foreign('aprobador_id')->references('id')->on('users');
            $table->foreign('guia_id')->references('id')->on('guias_remision');
            $table->foreign('anulacion_usuario_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('traslados');
    }
}
