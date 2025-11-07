<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColaboradoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->Increments('id');

            $table->unsignedBigInteger('sede_id');
            $table->foreign('sede_id')->references('id')->on('empresa_sedes');

            $table->unsignedInteger('tipo_documento_id');
            $table->foreign('tipo_documento_id')->references('id')->on('tabladetalles');

            $table->unsignedInteger('cargo_id');
            $table->foreign('cargo_id')->references('id')->on('tabladetalles');

            $table->string('tipo_documento_nombre',160)->nullable();
            $table->string('nro_documento',20);
            $table->string('nombre',260);
            $table->string('direccion',200)->nullable();
            $table->string('telefono',20)->nullable();
            $table->decimal('dias_trabajo',20,2);
            $table->decimal('dias_descanso',20,2);
            $table->decimal('pago_mensual',10,2)->nullable();
            $table->decimal('pago_dia',10,6)->nullable();

            $table->enum('estado',['ACTIVO','ANULADO'])->default('ACTIVO');
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
        Schema::dropIfExists('colaboradores');
    }
}
