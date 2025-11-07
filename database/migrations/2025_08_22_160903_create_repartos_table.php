<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepartosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('repartos', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('registrador_id');
            $table->foreign('registrador_id')->references('id')->on('users');
            $table->string('registrador_nombre', 200);

            $table->string('qr_codigo',20)->unique();
            $table->string('observacion',200)->nullable();

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
        Schema::dropIfExists('repartos');
    }
}
