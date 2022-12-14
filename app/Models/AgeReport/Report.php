<?php

namespace App\Models\AgeReport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'agereport_relatorios';
    protected $fillable = ['nome', 'url', 'nome_arquivo', 'query', 'cabecalhos', 'banco_solicitado', 'isPeriodo', 'isPeriodoHora'];
}
