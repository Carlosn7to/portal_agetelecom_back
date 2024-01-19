<?php

namespace App\Models\AgeCommunicate\Base\Welcome;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Welcome extends Model
{
    use HasFactory;

    protected $table = 'agecomunica_boas_vindas';

    protected $fillable = [
        'email',
        'contrato_id',
        'regra',
        'created_at',
        'updated_at'
    ];


}
