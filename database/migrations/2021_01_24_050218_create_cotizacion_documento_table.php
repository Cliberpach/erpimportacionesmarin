<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotizacionDocumentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cotizacion_documento', function (Blueprint $table) {

            $table->id();

            //EMPRESA
            $table->BigInteger('ruc_empresa');
            $table->string('empresa');
            $table->mediumText('direccion_fiscal_empresa');
            $table->unsignedInteger('empresa_id'); //OBTENER NUMERACION DE LA EMPRESA

            $table->unsignedInteger('almacen_id');
            $table->foreign('almacen_id')->references('id')->on('almacenes');

            $table->unsignedBigInteger('sede_id');
            $table->foreign('sede_id')->references('id')->on('empresa_sedes');

            $table->string('almacen_nombre',160);

            //CLIENTE
            $table->string('tipo_documento_cliente');
            $table->BigInteger('documento_cliente');
            $table->mediumText('direccion_cliente')->nullable();
            $table->string('cliente');
            $table->unsignedInteger('cliente_id'); //OBTENER TIENDAS DEL CLIENTE

            $table->unsignedBigInteger('resumen_id')->nullable();

            $table->unsignedBigInteger('convert_de_id')->nullable();
            $table->string('convert_de_serie')->nullable();

            $table->unsignedBigInteger('convert_en_id')->nullable();
            $table->string('convert_en_serie')->nullable();

            $table->unsignedBigInteger('pedido_id')->nullable();
            $table->foreign('pedido_id')->references('id')->on('pedidos');

            $table->unsignedBigInteger('guia_id')->nullable();

            $table->unsignedBigInteger('regularizado_en_id')->nullable();
            $table->unsignedBigInteger('regularizado_de_id')->nullable();
            $table->string('regularizado_en_serie')->nullable();
            $table->string('regularizado_de_serie')->nullable();

            $table->enum('tipo_doc_venta_pedido', ['ATENCION', 'FACTURACION'])->nullable();

            $table->date('fecha_documento');
            $table->date('fecha_vencimiento');
            $table->date('fecha_atencion')->nullable();

            $table->string('tipo_venta_id');
            $table->string('tipo_venta_nombre',160);

            $table->unsignedDecimal('sub_total', 15, 2);
            $table->unsignedDecimal('monto_embalaje',15,2)->nullable();
            $table->unsignedDecimal('monto_envio',15,2)->nullable();
            $table->unsignedDecimal('total', 15, 2);
            $table->unsignedDecimal('total_igv', 15, 2);
            $table->unsignedDecimal('total_pagar', 15, 2);

            $table->unsignedDecimal('porcentaje_descuento', 15, 2)->nullable();
            $table->unsignedDecimal('monto_descuento', 15, 2)->nullable();


            $table->unsignedInteger('tipo_pago_id')->nullable();
            $table->foreign('tipo_pago_id')->references('id')->on('tipos_pago')->onDelete('cascade');
            $table->unsignedDecimal('efectivo', 15, 2)->nullable()->default(0.00);
            $table->unsignedDecimal('importe', 15, 2)->nullable()->default(0.00);

            $table->foreignId('condicion_id')->nullable()->constrained()->onDelete('SET NULL');
            $table->longText('ruta_xml')->nullable();
            $table->longText('ruta_qr')->nullable();
            $table->longText('hash')->nullable();

            $table->longText('ruta_pago')->nullable();
            $table->longText('ruta_pago_2')->nullable();

            // $table->unsignedInteger('banco_empresa_id')->unsigned()->nullable();
            // $table->foreign('banco_empresa_id')
            //       ->references('id')->on('banco_empresas')
            //       ->onDelete('SET NULL');

            $table->string('igv_check',2)->nullable();
            $table->unsignedDecimal('igv',15,4)->nullable();
            $table->string('moneda');

            $table->string('numero_doc')->nullable();

            $table->BigInteger('cotizacion_venta')->nullable();

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->mediumText('observacion')->nullable();
            $table->enum('estado',['ACTIVO','ANULADO'])->default('ACTIVO');
            $table->enum('estado_pago',['PAGADA','PENDIENTE','ADELANTO','CONCRETADA','VIGENTE','DEVUELTO'])->default('PENDIENTE');

            $table->longText('legenda');

            $table->enum('sunat',['0','1','2'])->default('0');

            $table->enum('regularize',['0','1'])->default('0');

            $table->enum('modo',['CONSUMO','VENTA','ATENCION','RESERVA'])->default('VENTA');
            $table->boolean('es_anticipo')->default(false);
            $table->decimal('saldo_anticipo',16,2)->default(0);

            $table->unsignedInteger('anticipo_consumido_id')->nullable();
            $table->decimal('anticipo_monto_consumido',16,2)->default(0);
            $table->decimal('anticipo_monto_consumido_sin_igv',16,2)->default(0);
            $table->string('anticipo_consumido_serie',20)->nullable();
            $table->unsignedBigInteger('anticipo_consumido_correlativo')->nullable();
            $table->string('anticipo_tipo_venta_id',20)->nullable();

            $table->decimal('mto_oper_gravadas_sunat',16,4);
            $table->decimal('mto_igv_sunat',16,4);
            $table->decimal('total_impuestos_sunat',16,4);
            $table->decimal('valor_venta_sunat',16,4);
            $table->decimal('sub_total_sunat',16,4);
            $table->decimal('mto_imp_venta_sunat',16,4);

            $table->unsignedBigInteger('correlativo');
            $table->string('serie',20);

            $table->string('ruta_comprobante_archivo')->nullable();
            $table->string('nombre_comprobante_archivo')->nullable();

            $table->BigInteger('convertir')->nullable();

            $table->enum('contingencia', ['0', '1'])->default('0');
            $table->BigInteger('correlativo_contingencia')->nullable();
            $table->string('serie_contingencia')->nullable();
            $table->enum('sunat_contingencia', ['0', '1', '2'])->default('0');

            $table->string('cambio_talla',1)->nullable();


            $table->string('cdr_response_description')->nullable();
            $table->string('cdr_response_code')->nullable();
            $table->string('cdr_response_id')->nullable();
            $table->string('response_error_message')->nullable();
            $table->string('response_error_code')->nullable();
            $table->longText('ruta_cdr')->nullable();
            $table->longText('cdr_response_notes')->nullable();
            $table->longText('cdr_response_reference')->nullable();

            $table->string('telefono',20)->nullable();

            $table->unsignedBigInteger('pago_1_cuenta_id');
            $table->string('pago_1_banco_nombre',160);
            $table->string('pago_1_nro_cuenta',100);
            $table->string('pago_1_cci',100);
            $table->string('pago_1_celular',20);
            $table->string('pago_1_titular',200);
            $table->string('pago_1_moneda',160);
            $table->date('pago_1_fecha_operacion');
            $table->string('pago_1_tipo_pago_nombre',160);
            $table->string('pago_1_nro_operacion',30);
            $table->unsignedDecimal('pago_1_monto',16,2);
            $table->unsignedInteger('pago_1_tipo_pago_id');

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
        Schema::dropIfExists('cotizacion_documento');
    }
}
