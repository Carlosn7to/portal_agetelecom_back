<?php

namespace App\Http\Controllers\AgeCommunicate\Suspension;

use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\Suspension\SendSuspencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SuspensionController extends Controller
{
    public function __invoke()
    {
        return $this->response();
    }

    public function response() : void
    {

        $response = $this->sendEmail();


    }

    private function getData()
    {
        $result = DB::connection('pgsql')->select($this->getQuery());

        $data = [];

        foreach($result as $key => $value) {
            $data[] = [
                'client' => mb_convert_case($value->cliente, MB_CASE_TITLE, "UTF-8"),
                'contract' => $value->contrato,
                'status' => $value->v_status,
                'event' => $value->evento,
                'date' => $value->data_suspencao,
                'days' => $value->dias_suspenso,
                'attendant' => mb_convert_case($value->atendente, MB_CASE_TITLE, "UTF-8")
            ];
        }

        return $data;

    }

    private function sendEmail()
    {
        $to = 'suspensao@agetelecom.com.br';

        $mail = Mail::mailer('portal')->to($to)->send(new SendSuspencion($this->getData()));

        return $mail->getMessage();
    }


    private function getQuery()
    {

        $query = '
            select p."name" as cliente, c.id as contrato, v_status, cet.title as evento, DATE(ce."date") as data_suspencao, (current_date - DATE(ce."date")) as dias_suspenso, vu."name" as atendente
            from erp.contracts c
            left join erp.contract_events ce on ce.id = c.contract_event_id
            left join erp.contract_event_types cet on cet.id = ce.contract_event_type_id
            left join erp.people p ON p.id = c.client_id
            left join erp.v_users vu on vu.id = ce.created_by
            where v_status = \'Suspenso\' and v_stage = \'Aprovado\' and (current_date - DATE(ce."date")) >= 120
            order by (current_date - DATE(ce."date")) desc
        ';

        return $query;

    }
}
