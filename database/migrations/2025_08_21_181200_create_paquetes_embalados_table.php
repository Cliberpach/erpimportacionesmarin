<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaquetesEmbaladosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paquetes_embalados', function (Blueprint $table) {
            $table->id();

            $table->string('departamento_id', 2);
            $table->string('provincia_id', 4);
            $table->string('distrito_id', 6);

            $table->string('departamento');
            $table->string('provincia');
            $table->string('distrito');

            $table->unsignedInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->string('cliente_nombre');
            $table->string('cliente_celular');

            $table->unsignedInteger('tipo_pago_envio_id');
            $table->foreign('tipo_pago_envio_id')->references('id')->on('tabladetalles');
            $table->string('tipo_pago_envio');

            $table->unsignedBigInteger('empresa_envio_id');
            $table->foreign('empresa_envio_id')->references('id')->on('empresas_envio');
            $table->string('empresa_envio_nombre');

            $table->unsignedBigInteger('sede_envio_id')->comment('SEDE DE LA EMPRESA DE ENVIO');
            $table->foreign('sede_envio_id')->references('id')->on('empresa_envio_sedes');
            $table->string('sede_envio_nombre');

            $table->string('destinatario_tipo_doc', 30);
            $table->string('destinatario_nro_doc');
            $table->string('destinatario_nombre');

            $table->char('entrega_domicilio', 2);
            $table->string('direccion_entrega')->nullable();

            $table->unsignedInteger('registrador_id');
            $table->foreign('registrador_id')->references('id')->on('users');
            $table->string('registrador_nombre', 200);

            $table->longText('qr_ruta');
            $table->longText('qr_nombre');

            $table->unsignedBigInteger('reparto_id')->nullable();
            $table->string('qr_codigo',20)->unique();

            $table->enum('estado', ['PENDIENTE', 'DESPACHADO'])->default('PENDIENTE');

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
        Schema::dropIfExists('paquetes_embalados');
    }
}
