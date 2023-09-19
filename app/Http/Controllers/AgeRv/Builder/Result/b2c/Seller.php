<?php

namespace App\Http\Controllers\AgeRv\Builder\Result\b2c;

use App\Http\Controllers\Controller;

class Seller extends Controller
{
    private $response = [
      'rules' => [],
      'data' => []
    ];
    public function __construct()
    {
        //
    }

    static function response()
    {
        return 'resultado b2c seller';
    }
}
