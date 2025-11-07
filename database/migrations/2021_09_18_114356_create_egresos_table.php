<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEgresosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('egreso', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tipodocumento_id');
            $table->unsignedInteger('cuenta_id')->nullable(); //176
            $table->string('documento')->nullable();
            $table->text('descripcion');
            $table->unsignedDecimal('importe', 15, 2)->default(0);
            $table->unsignedDecimal('efectivo', 15, 2)->default(0);
            $table->unsignedDecimal('monto', 15, 2)->default(0);

            $table->unsignedInteger('tipo_pago_id');
            $table->foreign('tipo_pago_id')->references('id')->on('tipos_pago');

            $table->string("usuario")->nullable();
            $table->integer("user_id")->nullable();

            $table->unsignedBigInteger('sede_id');
            $table->foreign('sede_id')->references('id')->on('empresa_sedes');

            $table->unsignedBigInteger('cuenta_bancaria_id');
            $table->foreign('cuenta_bancaria_id')->references('id')->on('cuentas');

            $table->string('banco_nombre', 160);
            $table->string('banco_nro_cuenta', 100);
            $table->string('banco_cci', 100);
            $table->string('cuenta_celular', 20);
            $table->string('cuenta_titular', 200);
            $table->string('cuenta_moneda', 160);
            $table->date('fecha_operacion');
            $table->string('tipo_pago_nombre', 160);
            $table->string('nro_operacion', 20);

            $table->enum('estado', ['ACTIVO', 'ANULADO'])->default('ACTIVO');

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
        Schema::dropIfExists('egreso');
    }
}
