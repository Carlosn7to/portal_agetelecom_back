<?php

namespace App\Http\Controllers\Voalle;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
        session_start();

        $_SESSION['contador_sessao'] = now();
    }

    public function verifyTime()
    {

        if (session_status() == PHP_SESSION_NONE) {
            // A sessão não foi iniciada ainda
            session_start();
        }

        $session = $_SESSION['contador_sessao'];


        // Verifica se a sessão existe
        if (isset($_SESSION['contador_sessao'])){

            // Obtém a data e hora armazenadas na sessão
            $lastReq = $_SESSION['contador_sessao'];


            // Calcula a diferença em minutos entre a data e hora atual e a última requisição
            $diffMinutes = now()->diffInMinutes($lastReq);



            // Verifica se o tempo expirou
            if ($diffMinutes >= 1) {
                // Tempo expirou, retorna false ou executa alguma ação desejada

                return $this->warningSms();
            }

        }
    }

    private function warningSms()
    {

        if (session_status() == PHP_SESSION_NONE) {
            // A sessão não foi iniciada ainda
            session_start();
        }



        $numbers = ['61984700440', '61993419869', '61991210156'];



        if(isset($_SESSION['warning_sms'])) {


            // Obtém a data e hora armazenadas na sessão
            $lastReq = $_SESSION['warning_sms'];

            // Calcula a diferença em minutos entre a data e hora atual e a última requisição
            $diffMinutes = now()->diffInMinutes($lastReq);


            // Verifica se  o tempo expirou
            if ($diffMinutes >= 30) {
                // Tempo expirou, retorna false ou executa alguma ação desejada

                $client = new Client();



                foreach($numbers as $key => $value) {


                    $data = [
                        "id" => uniqid(),
                        "to" => "55$value@wa.gw.msging.net",
                        "type" => "application/json",
                        "content" => [
                            "type" => "template",
                            "template" => [
                                "namespace" => "0c731938_5304_4f41_9ccf_f0942721dd48",
                                "name" => "avisos_sistemicos",
                                "language" => [
                                    "code" => "PT_BR",
                                    "policy" => "deterministic"
                                ],
                                "components" => [
                                    [
                                        "type" => "body",
                                        "parameters" => [
                                            [
                                                "type" => "text",
                                                "text" => "Cancelamento de contratos"
                                            ],
                                            [
                                                "type" => "text",
                                                "text" => "Nenhuma requisição recebida - última em " . Carbon::parse($_SESSION['contador_sessao'])->format('d/m/Y H:i:s')
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];

                    // Faz a requisição POST usando o cliente Guzzle HTTP
                    $response = $client->post('https://agetelecom.http.msging.net/messages', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
                        ],
                        'json' => $data
                    ]);

                }

                unset($_SESSION['warning_sms']);
            }

        } else {

            $_SESSION['warning_sms'] = now();

            $client = new Client();

            foreach($numbers as $key => $value) {


                $data = [
                    "id" => uniqid(),
                    "to" => "55$value@wa.gw.msging.net",
                    "type" => "application/json",
                    "content" => [
                        "type" => "template",
                        "template" => [
                            "namespace" => "0c731938_5304_4f41_9ccf_f0942721dd48",
                            "name" => "avisos_sistemicos",
                            "language" => [
                                "code" => "PT_BR",
                                "policy" => "deterministic"
                            ],
                            "components" => [
                                [
                                    "type" => "body",
                                    "parameters" => [
                                        [
                                            "type" => "text",
                                            "text" => "Cancelamento de contratos"
                                        ],
                                        [
                                            "type" => "text",
                                            "text" => "Nenhuma requisição recebida - última em " . Carbon::parse($_SESSION['contador_sessao'])->format('d/m/Y H:i:s')
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                // Faz a requisição POST usando o cliente Guzzle HTTP
                $response = $client->post('https://agetelecom.http.msging.net/messages', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
                    ],
                    'json' => $data
                ]);

            }


        }


    }
}
