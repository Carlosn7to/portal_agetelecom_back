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
        Schema::create('agecomunica_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('template')->nullable();
            $table->text('texto')->nullable();
            $table->string('titulo_email')->nullable();
            $table->string('canal');
            $table->json('regra');
            $table->tinyInteger('status');
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
        Schema::dropIfExists('agecomunica_templates');
    }
};
