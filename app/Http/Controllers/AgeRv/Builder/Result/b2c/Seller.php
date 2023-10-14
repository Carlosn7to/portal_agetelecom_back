<?php

namespace App\Http\Controllers\AgeRv\Builder\Result\b2c;

use App\Http\Controllers\AgeRv\Builder\Rules\b2c\seller\Stars;
use App\Http\Controllers\AgeRv\Builder\Rules\b2c\seller\ValuePayment;
use App\Http\Controllers\Controller;

class Seller extends Controller
{
    private $response = [
      'rules' => [
          'stars' => [],
          'valuePayment' => []
      ],
      'sales' => [],
    ];

    private $date;
    public function __construct($sellerInfo)
    {
        $this->date = $sellerInfo['date_request'];
        $valuePayment = new ValuePayment($this->date, $sellerInfo['userCommission']['typeCommission']['id']);
        $stars = new Stars($this->date);
        $sales = new Sales($sellerInfo);
        $this->response['rules']['valuePayment'] = $valuePayment->response();
        $this->response['rules']['stars'] = $stars->response();
        $this->response['sales'] = $sales->response();

    }

    public function response()
    {
        return $this->response;
    }
}
