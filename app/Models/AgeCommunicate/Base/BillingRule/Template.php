<?php

namespace App\Models\AgeCommunicate\Base\BillingRule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory;


    protected $fillable = ['nome', 'template', 'texto', 'titulo_email', 'canal', 'regra', 'status', 'variavel'];
    protected $connection = 'mysql';
    protected $table = 'agecomunica_templates';
}
