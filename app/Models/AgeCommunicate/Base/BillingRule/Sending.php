<?php

namespace App\Models\AgeCommunicate\Base\BillingRule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sending extends Model
{
    use HasFactory;



    protected $table = 'agecomunica_regua_envios';
    protected $fillable = [
        'contrato',
        'nome',
        'celular',
        'email',
        'faturas',
        'canal',
        'template',
        'dia_regra',
        'status',
        'erros',
        'id_mensagem'
    ];
}
