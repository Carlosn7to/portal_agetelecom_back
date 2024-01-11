<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agecomunica_regua_envios', function (Blueprint $table) {
            $table->id();
            $table->string('contrato');
            $table->string('nome');
            $table->string('celular');
            $table->string('email');
            $table->json('faturas');
            $table->string('canal');
            $table->string('template');
            $table->string('dia_regra');
            $table->string('status');
            $table->text('erros')->nullable();
            $table->string('id_mensagem')->nullable();
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
        Schema::dropIfExists('age_comunica_regua_envios');
    }
};
