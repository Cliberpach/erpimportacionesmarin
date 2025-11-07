<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConductoresTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conductores', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tipo_documento_id');
            // $table->foreign('tipo_documento_id')->references('id')->on('tipos_documento');

            $table->string('tipo_documento_nombre',200);
            $table->string('tipo_documento_codigo',10);

            $table->string('nombres',160);
            $table->string('apellidos',160);

            $table->string('nro_documento',20);
            $table->string('licencia',10);

            $table->enum('estado',['ACTIVO','ANULADO'])->default('ACTIVO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conductores');
    }
};
