<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnviosVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('envios_ventas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('documento_id');
            $table->foreign('documento_id')->references('id')->on('cotizacion_documento');

            $table->string('departamento_id', 2);
            $table->string('provincia_id', 4);
            $table->string('distrito_id', 6);

            $table->unsignedInteger('tipo_envio_id');
            $table->foreign('tipo_envio_id')->references('id')->on('tabladetalles');
            $table->string('tipo_envio');

            $table->unsignedInteger('tipo_pago_envio_id');
            $table->foreign('tipo_pago_envio_id')->references('id')->on('tabladetalles');
            $table->string('tipo_pago_envio');

            $table->unsignedInteger('origen_venta_id');
            $table->foreign('origen_venta_id')->references('id')->on('tabladetalles');
            $table->string('origen_venta')->nullable();

            $table->string('departamento');
            $table->string('provincia');
            $table->string('distrito');

            $table->unsignedBigInteger('empresa_envio_id');
            $table->foreign('empresa_envio_id')->references('id')->on('empresas_envio');
            $table->string('empresa_envio_nombre');

            $table->unsignedBigInteger('sede_envio_id')->comment('SEDE DE LA EMPRESA DE ENVIO');
            $table->foreign('sede_envio_id')->references('id')->on('empresa_envio_sedes');
            $table->string('sede_envio_nombre');

            $table->string('destinatario_tipo_doc', 30);
            $table->string('destinatario_nro_doc');
            $table->string('destinatario_nombre');
            
            $table->unsignedInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->string('cliente_nombre');
            $table->string('cliente_celular');

            $table->decimal('monto_envio', 10, 2)->unsigned();
            $table->char('entrega_domicilio', 2);
            $table->string('direccion_entrega')->nullable();
            $table->string('documento_nro');
            $table->date('fecha_envio')->nullable();
            $table->date('fecha_envio_propuesta')->nullable();
            $table->text('obs_rotulo')->nullable();
            $table->text('obs_despacho')->nullable();
            $table->string('usuario_nombre', 70)->nullable();

            $table->unsignedInteger('user_vendedor_id');
            $table->foreign('user_vendedor_id')->references('id')->on('users');
            $table->string('user_vendedor_nombre', 260);

            $table->unsignedInteger('user_despachador_id')->nullable();
            $table->foreign('user_despachador_id')->references('id')->on('users');
            $table->string('user_despachador_nombre', 260)->nullable();

            $table->unsignedInteger('almacen_id');
            $table->foreign('almacen_id')->references('id')->on('almacenes');

            $table->unsignedBigInteger('sede_id')->comment('SEDE ORIGEN');
            $table->foreign('sede_id')->references('id')->on('empresa_sedes');

            $table->unsignedBigInteger('sede_despachadora_id')->comment('SEDE DESPACHADORA');
            $table->foreign('sede_despachadora_id')->references('id')->on('empresa_sedes');

            $table->string('almacen_nombre', 160);

            $table->enum('estado', ['PENDIENTE', 'EMBALADO', 'DESPACHADO'])->default('PENDIENTE');
            $table->enum('modo', ['VENTA', 'ATENCION', 'RESERVA'])->default('VENTA');

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
        Schema::dropIfExists('envios_ventas');
    }
}
