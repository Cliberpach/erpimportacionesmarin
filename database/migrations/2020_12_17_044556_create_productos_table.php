<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->Increments('id');

            $table->unsignedInteger('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');

            $table->unsignedInteger('marca_id');
            $table->foreign('marca_id')->references('id')->on('marcas')->onDelete('cascade');

            $table->unsignedInteger('modelo_id');
            $table->foreign('modelo_id')->references('id')->on('modelos')->onDelete('cascade');


            $table->string('codigo', 50)->nullable();
            $table->string('nombre');
            $table->mediumText('descripcion')->nullable();
            $table->string('medida');
            $table->string('codigo_barra')->nullable();

            $table->unsignedDecimal('stock_minimo', 15, 2)->default(1);
            $table->unsignedDecimal('precio_compra', 15, 2)->nullable();

            $table->unsignedDecimal('precio_venta_1', 15, 2)->nullable();
            $table->unsignedDecimal('precio_venta_2', 15, 2)->nullable();
            $table->unsignedDecimal('precio_venta_3', 15, 2)->nullable();

            $table->boolean('igv')->default(TRUE);
            $table->string('facturacion')->default('SI');

            $table->unsignedDecimal('costo', 15, 2)->default(0);

            $table->longText('img1_ruta')->nullable()->after('columna_anterior');
            $table->longText('img1_nombre')->nullable()->after('img1_ruta');
            $table->longText('img2_ruta')->nullable()->after('img1_nombre');
            $table->longText('img2_nombre')->nullable()->after('img2_ruta');
            $table->longText('img3_ruta')->nullable()->after('img2_nombre');
            $table->longText('img3_nombre')->nullable()->after('img3_ruta');
            $table->longText('img4_ruta')->nullable()->after('img3_nombre');
            $table->longText('img4_nombre')->nullable()->after('img4_ruta');
            $table->longText('img5_ruta')->nullable()->after('img4_nombre');
            $table->longText('img5_nombre')->nullable()->after('img5_ruta');
            $table->boolean('mostrar_en_web')->default(0)->after('img5_nombre');

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
        Schema::dropIfExists('productos');
    }
}
