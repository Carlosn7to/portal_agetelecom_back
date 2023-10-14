<?php

namespace App\Http\Controllers\AgeRv\Builder\Result\b2c;

use App\Models\AgeRv\Commission;
use Carbon\Carbon;

class Sales
{
    private $response = [
        'valid' => [
            'total' => 0,
            'extract' => []
        ],
        'cancelled' => [
            'total' => 0,
            'extract' => []
        ]
    ];

    private $sales;

    private $sellerInfo;
    public function __construct($sellerInfo)
    {
        $this->sellerInfo = $sellerInfo;

        $this->sales = $this->getSales();

        $this->response['cancelled']['extract'] = $this->getCancelledSales();

        $this->response['valid']['extract'] = $this->sales;
        $this->response['valid']['total'] = count($this->sales);


    }

    public function response()
    {
        return $this->response;
    }

    private function getSales()
    {
        return Commission::whereVendedor($this->sellerInfo['userName'])
                        ->whereMesCompetencia(Carbon::parse($this->sellerInfo['date_request'])->format('m'))
                        ->whereAnoCompetencia(Carbon::parse($this->sellerInfo['date_request'])->format('Y'))
                        ->get(['
                        ']);

    }

    private function getValidSales()
    {


    }

    private function getCancelledSales()
    {

        $cancelled = $this->sales->where('situacao', 'Cancelado')->pluck('id_contrato')->toArray();



        return $cancelled;

    }


}
