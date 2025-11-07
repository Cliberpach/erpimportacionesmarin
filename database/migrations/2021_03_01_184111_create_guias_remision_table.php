<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuiasRemisionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->Increments('id');

            $table->unsignedInteger('documento_id')->nullable();
            $table->unsignedInteger('nota_salida_id')->nullable();
            $table->decimal('cantidad_productos', 15, 2)->unsigned();
            $table->decimal('peso_productos', 15, 3)->unsigned()->default(1.000);
            $table->text('tienda')->nullable();
            $table->string('ruc_transporte_oficina', 191)->nullable();
            $table->string('nombre_transporte_oficina', 191)->nullable();
            $table->string('ruc_transporte_domicilio', 191)->nullable();
            $table->string('nombre_transporte_domicilio', 191)->nullable();
            $table->string('direccion_llegada', 191)->nullable();
            $table->mediumText('observacion')->nullable();
            $table->enum('estado', ['REGISTRADO', 'ACEPTADO', 'NULO'])->default('REGISTRADO');
            $table->enum('sunat', ['0', '1', '2'])->default('0');
            $table->bigInteger('correlativo')->nullable();
            $table->string('serie', 191)->nullable();
            $table->string('ruta_comprobante_archivo', 191)->nullable();
            $table->string('nombre_comprobante_archivo', 191)->nullable();
            $table->string('dni_conductor', 191)->nullable();
            $table->string('placa_vehiculo', 191)->nullable();
            $table->string('ubigeo_partida', 191)->nullable();
            $table->string('ubigeo_llegada', 191)->nullable();
            $table->date('fecha_emision')->nullable();
            $table->text('ruc_empresa')->nullable();
            $table->text('empresa')->nullable();
            $table->text('direccion_empresa')->nullable();
            $table->unsignedInteger('empresa_id');
            $table->text('tipo_documento_cliente')->nullable();
            $table->text('documento_cliente')->nullable();
            $table->text('cliente')->nullable();
            $table->text('direccion_cliente')->nullable();
            $table->unsignedInteger('cliente_id');
            $table->unsignedInteger('motivo_traslado');
            $table->unsignedInteger('user_id')->nullable();
            $table->enum('regularize', ['0', '1'])->default('0');
            $table->text('ticket')->nullable();
            $table->text('cdrzip_name')->nullable();
            $table->text('ruta_cdr')->nullable();
            $table->string('despatch_name', 191)->nullable();
            $table->string('response_code', 10)->nullable();
            $table->longText('ruta_xml')->nullable();
            $table->string('cdr_response_id', 20)->nullable();
            $table->longText('cdr_response_description')->nullable();
            $table->longText('cdr_response_notes')->nullable();
            $table->longText('cdr_response_reference')->nullable();
            $table->string('response_success', 50)->nullable();
            $table->longText('response_error')->nullable();
            $table->longText('ruta_qr')->nullable();
            $table->unsignedInteger('almacen_id')->nullable();
            $table->unsignedInteger('motivo_traslado_id')->nullable();
            $table->string('motivo_traslado_simbolo', 20)->nullable();
            $table->string('motivo_traslado_nombre', 200)->nullable();
            $table->string('modalidad_traslado_simbolo', 20)->nullable();
            $table->string('modalidad_traslado_nombre', 200)->nullable();
            $table->date('fecha_traslado')->nullable();
            $table->decimal('peso', 16, 4)->nullable();
            $table->string('unidad', 20)->nullable();
            $table->tinyInteger('categoria_M1L')->default(0);
            $table->unsignedInteger('vehiculo_id')->nullable();
            $table->unsignedInteger('conductor_id')->nullable();
            $table->unsignedBigInteger('punto_partida_id')->nullable();
            $table->unsignedBigInteger('punto_llegada_id')->nullable();
            $table->string('direccion_partida', 200)->nullable();
            $table->unsignedBigInteger('sede_id')->nullable();
            $table->unsignedInteger('registrador_id')->nullable();
            $table->string('registrador_nombre', 200)->nullable();
            $table->unsignedInteger('traslado_id')->nullable();
            $table->string('estado_sunat', 200)->nullable();
            $table->string('cdr_response_code', 10)->nullable();
            $table->unsignedBigInteger('sede_usa_guia')->nullable();
            $table->unsignedBigInteger('sede_genera_guia')->nullable();

            $table->string('transportista_num_doc',20)->nullable();
            $table->string('transportista_rzn_social',160)->nullable();
            $table->string('transportista_nro_mtc',20)->nullable();

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
        Schema::dropIfExists('guias_remision');
    }
}
