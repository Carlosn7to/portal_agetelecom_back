<?php

namespace App\Models\AWS\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'age_folha_pagamento';
    protected $fillable = ['codigo', 'nome', 'cargo', 'salario', 'adicional_30', 'adicional_40', 'data_admissao', 'st', 'data_st', 'observacao', 'dias_atm', 'descricao_dias_atm', 'cid_atm', 'dias_faltas', 'descricao_dias_faltas', 'dias_bh', 'descricao_dias_bh', 'observacao_2', 'sabados_trabalhados', 'descricao_sabados_trabalhados', 'dias_va_extra', 'descricao_horas_mais', 'quantidade_va', 'horas_sobreaviso', 'horas_adn', 'horas_extras_50', 'horas_extras_100', 'anuenio', 'adc_condutor_autorizado', 'placa_carro', 'plano_saude_titular', 'plano_saude_dependente', 'plano_saude_desconto_total', 'valor_va_mes_anterior', 'calculo_desconto_va', 'mensalidade_sindical', 'desconto_avaria_veiculo', 'banco_horas'];
    protected $connection = 'aws_admin';

}
