<?php

namespace App\Http\Controllers\Voalle;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ContractFineController extends Controller
{


    public function getStatus($token, $contractId)
    {

        if($token !== 'FiU4HdzsLJ5yRADjB5uLVV8nJhAor') {
            return response()->json('Token inválido', 401);
        }

        $this->startSession();

        set_time_limit(200000000);

        $contract = $contractId;

        $query = 'select
            f.contract_id,
            p.tx_id as "CPF_CNPJ",
            ROUND((((c.billing_final_date - CURRENT_DATE) / 30.0) * 950/12), 2) as "MULTA",
            current_date - f.expiration_date as "Dias_Vencimento",
            c.cancellation_date,
            c.v_stage,
            c.v_status
        from erp.financial_receivable_titles f
        inner join erp.people p on p.id = f.client_id
        inner join erp.contracts c on c.id = f.contract_id
        where f.title like \'%FAT%\'
        and c.billing_final_date not between to_date(\'01/01/2050\', \'DD/MM/YYYY\') and to_date(\'31/12/2050\', \'DD/MM/YYYY\')
        and f.deleted != \'TRUE\'
        and not exists (select * from erp.financial_receipt_titles t
                        where f.id = t.financial_receivable_title_id)
        and current_date - f.expiration_date >= 90
        and c.v_status != \'Cancelado\'
        and c.id = '.$contract.'
        and ROUND((((c.billing_final_date - CURRENT_DATE) / 30.0) * 950/12), 2) > 0
        order by 4 desc';


        $result = DB::connection('pgsql')->select($query);

        if(! empty($result)) {
            return response()->json(true, 200);
        } else {
            return response()->json(false, 200);
        }
    }

    private function startSession()
    {
        $keySession = 'contador_sessao';

        // Armazena a data e hora atual na sessão
        Session::put($keySession, now());
    }

    public function verifyTime()
    {
        // Define a chave da sessão
        $keySession = 'contador_sessao';

        // Verifica se a sessão existe
        if (Session::has($keySession)) {
            // Obtém a data e hora armazenadas na sessão
            $lastReq = Session::get($keySession);

            // Calcula a diferença em minutos entre a data e hora atual e a última requisição
            $diffMinutes = now()->diffInMinutes($lastReq);

            // Verifica se o tempo expirou
            if ($diffMinutes >= 1) {
                // Tempo expirou, retorna false ou executa alguma ação desejada

                return $this->warningSms();
            }

        }
    }

    private function warningSms() : void
    {

        if(Session::has('warning_sms')) {

            // Obtém a data e hora armazenadas na sessão
            $lastReq = Session::get($keySession);

            // Calcula a diferença em minutos entre a data e hora atual e a última requisição
            $diffMinutes = now()->diffInMinutes($lastReq);

            // Verifica se o tempo expirou
            if ($diffMinutes >= 5) {
                // Tempo expirou, retorna false ou executa alguma ação desejada


                $client = new Client();

                $numbers = ['61984700440', '61993419869', '61991210156'];


                foreach ($numbers as $key => $value) {

                    $data = [
                        "id" => uniqid(),
                        "to" => "+55$value@sms.gw.msging.net",
                        "type" => "text/plain",
                        "content" => "Aviso: Automação de cancelamento de contratos falhou. Favor verificar."
                    ];

                    // Faz a requisição POST usando o cliente Guzzle HTTP
                    $response = $client->post('https://agetelecom.http.msging.net/messages', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Key b3BlcmFjYW9ub2NiMmI6QTZzQ3Z4WUlxbjZqQ2NvSU1JR1o='
                        ],
                        'json' => $data
                    ]);

                }
            }

        } else {
            $keySessionWarning = 'warning_sms';
            Session::put($keySessionWarning, now());


            $client = new Client();

            $numbers = ['61984700440', '61993419869', '61991210156'];


            foreach ($numbers as $key => $value) {

                $data = [
                    "id" => uniqid(),
                    "to" => "+55$value@sms.gw.msging.net",
                    "type" => "text/plain",
                    "content" => "Aviso: Automação de cancelamento de contratos falhou. Favor verificar."
                ];

                // Faz a requisição POST usando o cliente Guzzle HTTP
                $response = $client->post('https://agetelecom.http.msging.net/messages', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Key b3BlcmFjYW9ub2NiMmI6QTZzQ3Z4WUlxbjZqQ2NvSU1JR1o='
                    ],
                    'json' => $data
                ]);

            }
        }


    }
}
