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
        Schema::create('age_folha_pagamento', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('codigo')->nullable();
            $table->string('nome', 100)->nullable();
            $table->string('cargo', 100)->nullable();
            $table->integer('salario')->nullable();
            $table->integer('adicional_30')->nullable();
            $table->integer('adicional_40')->nullable();
            $table->date('data_admissao')->nullable();
            $table->integer('st')->nullable();
            $table->integer('data_st')->nullable();
            $table->text('observacao')->nullable();
            $table->integer('dias_atm')->nullable();
            $table->string('descricao_dias_atm')->nullable();
            $table->string('cid_atm')->nullable();
            $table->integer('dias_faltas')->nullable();
            $table->string('descricao_dias_faltas')->nullable();
            $table->integer('dias_bh')->nullable();
            $table->string('descricao_dias_bh')->nullable();
            $table->text('observacao_2')->nullable();
            $table->integer('sabados_trabalhados')->nullable();
            $table->text('descricao_sabados_trabalhados')->nullable();
            $table->integer('dias_va_extra')->nullable();
            $table->string('descricao_horas_mais')->nullable();
            $table->integer('quantidade_va')->nullable();
            $table->time('horas_sobreaviso')->nullable();
            $table->time('horas_extras_50')->nullable();
            $table->time('horas_extras_100')->nullable();
            $table->decimal('anuenio')->nullable();
            $table->decimal('adc_condutor_autorizado')->nullable();
            $table->string('placa_carro')->nullable();
            $table->decimal('plano_saude_titular')->nullable();
            $table->decimal('plano_saude_dependente')->nullable();
            $table->decimal('plano_saude_desconto_total')->nullable();
            $table->decimal('valor_va_mes_anterior')->nullable();
            $table->decimal('calculo_desconto_va')->nullable();
            $table->decimal('mensalidade_sindical')->nullable();
            $table->decimal('desconto_avaria_veiculo')->nullable();
            $table->time('banco_horas')->nullable();
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
        Schema::dropIfExists('age_folha_pagamento');
    }
};
