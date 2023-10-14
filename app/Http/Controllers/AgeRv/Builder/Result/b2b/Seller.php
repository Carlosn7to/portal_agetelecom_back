<?php

namespace App\Http\Controllers\AgeRv\Builder\Result\b2b;

use App\Models\AgeRv\Commission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Seller
{

    private $data;

    private $sellers;

    public function response()
    {
        $this->data = Commission::whereSupervisor('b2b');


        $this->sellers = $this->data->get('vendedor')->unique('vendedor');

        $months = $this->data->get(['mes_competencia', 'ano_competencia'])->unique('mes_competencia');

        foreach($months as $key => $value) {
            $this->month[] = [
                'date' => Carbon::parse($value->ano_competencia.'-'.$value->mes_competencia)->format('m/Y'),
            ];
        }


        $result = [];

        foreach($this->sellers as $key => $value) {
            $result[] = [
                'seller' => $value->vendedor,
                'sales' => $this->getSales($value->vendedor)
            ];
        }

        $result = $this->builderMatriz($result);


        return $result;


    }


    private function getSales($seller)
    {
        $result = [];


        foreach($this->data->get(['id_contrato', 'data_contrato', 'nome_cliente', 'vendedor'])->unique('id_contrato') as $key => $value) {

            if(mb_convert_case($value->vendedor, MB_CASE_LOWER) == mb_convert_case($seller, MB_CASE_LOWER)) {
                $result[] = [
                    'client' => $value->nome_cliente,
                    'contract' => $value->id_contrato,
                    'date' => $value->data_contrato,
                    'commission' => [
                        'fat' => $this->getFat($value->id_contrato, $value->data_contrato),
                        'value' => $this->getCommmission($this->getFat($value->id_contrato, $value->data_contrato))
                    ],
                ];
            }
        }


        return $result;

    }

    private function getFat($contract, $dateActivation)
    {
        $query = 'select frt2.title, frt.amount, frt2.competence from erp.financial_receipt_titles frt
                    left join erp.financial_receivable_titles frt2 on frt2.id = frt.financial_receivable_title_id
                    where frt2.contract_id = '.$contract.' and frt2.title like \'%FAT%\'
                    and EXTRACT(MONTH FROM frt.receipt_date) >= \''.Carbon::parse($dateActivation)->format('m').'\'
                    and frt2.deleted is false and frt2.finished is false
                    order by frt2.id limit 2';

        $result = DB::connection('pgsql')->select($query);

        return $result;

    }

    private function getCommmission($fat)
    {
        $result = 0;

        if(count($fat) === 2) {

            return [
                'commission' => $fat[1]->amount * .2,
                'reference' => $fat[1]->title
            ];



        } elseif (count($fat) === 1) {
            return [
                'commission' => $fat[0]->amount * .2,
                'reference' => $fat[0]->title
            ];
        } else {
            return [
                'commission' => 0,
                'reference' => 'Fatura não vinculada'
            ];
        }
    }

    private function builderMatriz($result)
    {
        $sellers = [];
        $base = [];
        $baseCommission = [];
        $result = $result;

        foreach($this->month as $key => $value) {
            $sellers[] = [
                'date' => $value['date']
            ];

            foreach($this->sellers as $k => $v) {

                $sellers[$key]['sellers'][] = [
                    'seller' => $v->vendedor,
                    'sales' => 0,
                    'commission' => 0
                ];

            }

        }



        foreach($sellers as $k => $value) {

            foreach($value['sellers'] as $key => $seller) {
                $countSales = 1;
                $commission = 0;
                foreach($result as $kk => $v) {

                    if($seller['seller'] == $v['seller']) {


                        foreach($v['sales'] as $kkk => $sale) {

                            if(Carbon::parse($sale['date'])->format('m/Y') == $value['date']) {
                                $countSales++;
                                $commission += $sale['commission']['value']['commission'];
                            }

                        }


                    }

                }


                $sellers[$k]['sellers'][$key]['sales'] = $countSales;
                $sellers[$k]['sellers'][$key]['commission'] = $commission;

            }

        }

//        return $sellers;

        return $result;


            foreach($result as $key => $value) {
                $commission = 0;


                foreach($value['sales'] as $sales => $sale) {

                    $commission += $sale['commission']['value']['commission'];

                }

                $sellers[] = [
                    'seller' => $value['seller'],
                    'sales' => count($value['sales']),
                    'commission' => $commission
                ];


            }


        return [
            'plan1' => $sellers,
            'plan2' => $base,
            'plan3' => $baseCommission,
        ];



        return $result;

    }

}
