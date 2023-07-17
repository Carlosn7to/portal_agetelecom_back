<?php

namespace App\Http\Controllers\AgeCommunicate\BillingRule;

use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\Response;
use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\SendingWhatsapp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuilderController extends Controller
{
    private $response;


    public function __construct()
    {
        $this->response = new Response();
    }


    public function build()
    {
        set_time_limit(2000000000);

        $query = $this->getQuery();

        $data = DB::connection('pgsql')->select($query);


        return $sendings;


        return $this->response->constructResponse(200, 'sucesso', [], []);
    }

    private function sendMessage($dataWhatsapp)
    {


        $templates = [

        ];

        $sendings = [
            'success' => [],
            'count' => 0,
            'error' => []
        ];

        $data = collect($dataWhatsapp);

        $data = $data->unique('phone');

        foreach($data as $key => $value) {

            if(isset($value->phone)) {
                $sendingWhatsapp = new SendingWhatsapp($template, $value->phone);
                $sendings['success'][] = $sendingWhatsapp->builder();
                $sendings['count']++;
            } else {
                $sendings['error'][] = $value->contract_id;
            }

        }



        return $dataWhatsapp;
    }

    private function getQuery()
    {
        $query = '
            select
                c.id as "contract_id",
                c.collection_day as "collection_day",
                p.email as "email",
                CASE
                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
                    ELSE p.cell_phone_2
                END AS "phone",
                frt.barcode as "barcode",
                frt.expiration_date as "expiration_date",
                frt.competence as "competence"
            from erp.contracts c
            left join erp.people p on p.id = c.client_id
            left join erp.financial_receivable_titles frt on frt.contract_id = c.id
            where
            c.v_stage = \'Aprovado\'
            and frt.competence >= \'2023-05-01\'
            and frt.deleted is false
            and frt.finished is false
            and frt.title like \'%FAT%\' and frt.expiration_date = \'2023-07-15\'';

        return $query;

    }
}
