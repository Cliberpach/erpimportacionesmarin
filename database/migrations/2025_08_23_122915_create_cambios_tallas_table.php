<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCambiosTallasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cambios_tallas', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->unsignedBigInteger('documento_id');
            $table->foreign('documento_id')->references('id')->on('cotizacion_documento');;
            
            $table->bigInteger('detalle_id');

            // Producto reemplazado
            $table->integer('producto_reemplazado_id');
            $table->integer('color_reemplazado_id');
            $table->integer('talla_reemplazado_id');
            $table->string('producto_reemplazado_nombre', 255);
            $table->string('color_reemplazado_nombre', 255);
            $table->string('talla_reemplazado_nombre', 255);
            $table->unsignedInteger('cantidad_detalle');

            // Producto reemplazante
            $table->integer('producto_reemplazante_id');
            $table->integer('color_reemplazante_id');
            $table->integer('talla_reemplazante_id');
            $table->string('producto_reemplazante_nombre', 255);
            $table->string('color_reemplazante_nombre', 255);
            $table->string('talla_reemplazante_nombre', 255);

            // Cantidades
            $table->unsignedInteger('cantidad_cambiada');
            $table->unsignedInteger('cantidad_sin_cambio');

            // Usuario
            $table->integer('user_id');
            $table->string('user_nombre', 255);

            // Estado
            $table->string('estado', 50)->default('ACTIVO');

            // Sede y almacén
            $table->unsignedBigInteger('sede_id');
            $table->unsignedInteger('almacen_id');
            $table->string('almacen_nombre', 160)->nullable();

            // Timestamps
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
        Schema::dropIfExists('cambios_tallas');
    }
}
