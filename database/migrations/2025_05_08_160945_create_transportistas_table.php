<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportistasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transportistas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tipo_documento_id');

            $table->string('tipo_documento_nombre',200);
            $table->string('tipo_documento_codigo',10);

            $table->string('nombre',160);
            $table->string('nro_documento',20);
            $table->string('direccion',160)->nullable();
            $table->string('mtc',20)->nullable();

            $table->enum('estado',['ACTIVO','ANULADO'])->default('ACTIVO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportistas');
    }
};
