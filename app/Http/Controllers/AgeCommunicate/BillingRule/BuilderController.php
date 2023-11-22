<?php

namespace App\Http\Controllers\AgeCommunicate\BillingRule;

use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\Response;
use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\SendingWhatsapp;
use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\Base\SCPC\SendSCPC;
use App\Mail\AgeCommunicate\Base\SendBillingRule;
use App\Mail\AgeCommunicate\Base\SendMailBillingRule;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BuilderController extends Controller
{
    private $response;


    public function __invoke()
    {
        return $this->build();
    }

    public function __construct()
    {
        $this->response = new Response();
    }


    public function build()
    {
        set_time_limit(2000000000);


        $query = $this->getQuery();

        $data = DB::connection('pgsql')->select($query);



//        $whatsapp = $this->sendMessage($data);
          return $this->sendEmail($data);
//        $sms = $this->sendSMS($data);


        return $this->response->constructResponse(200, 'sucesso', [
//            'whatsapp' => $whatsapp,
            'email' => $email,
//            'sms' => $sms
        ], []);
    }

    private function sendMessage($dataWhatsapp)
    {


        $templates = [
            // Pré-vencimento
            0 => [
                'd' => -5,
                'template' => 'pre_vencimento__1',
                'variable' => true,
                'sendings' => 0
            ],
            1 => [
                'd' => -4,
                'template' => 'pre_vencimento__1',
                'variable' => true,
                'sendings' => 0

            ],
            2 => [
                'd' => -1,
                'template' => 'pre_vencimento_2',
                'variable' => false,
                'sendings' => 0

            ],
            3 => [
                'd' => -0,
                'template' => 'pre_vencimento_2',
                'variable' => false,
                'sendings' => 0

            ],
            // Pós-vencimento
            4 => [
                'd' => 3,
                'template' => 'pos_vencimento_1',
                'variable' => true,
                'sendings' => 0

            ],
//            5 => [
//                'd' => 4,
//                'template' => 'pos_vencimento__1',
//                'variable' => true,
//                'sendings' => 0
//
//            ],
//            6 => [
//                'd' => 5,
//                'template' => 'pos_vencimento__1',
//                'variable' => true,
//                'sendings' => 0
//
//            ],
            6 => [
                'd' => 7,
                'template' => 'pos_vencimento_1',
                'variable' => true,
                'sendings' => 0

            ],
            7 => [
                'd' => 12,
                'template' => 'pos_vencimento_2_',
                'variable' => false,
                'sendings' => 0

            ],
            8 => [
                'd' => 13,
                'template' => 'pos_vencimentos_3',
                'variable' => false,
                'sendings' => 0

            ],
//            8 => [
//                'd' => 20,
//                'template' => 'pos_vencimento__4',
//                'variable' => false,
//                'sendings' => 0
//
//            ],
//            9 => [
//                'd' => 21,
//                'template' => 'pos_vencimento__4',
//                'variable' => false,
//                'sendings' => 0
//
//            ],
//            10 => [
//                'd' => 30,
//                'template' => 'pos_vencimento__5',
//                'variable' => true,
//                'sendings' => 0
//
//            ],
//            11 => [
//                'd' => 31,
//                'template' => 'pos_vencimento__5',
//                'variable' => true,
//                'sendings' => 0
//
//            ],
//            12 => [
//                'd' => 45,
//                'template' => 'pos_vencimento__5',
//                'variable' => true,
//                'sendings' => 0
//
//            ],
//            13 => [
//                'd' => 46,
//                'template' => 'pos_vencimento__5',
//                'variable' => true,
//                'sendings' => 0
//
//            ],
//            14 => [
//                'd' => 75,
//                'template' => 'pos_vencimento__6',
//                'variable' => false,
//                'sendings' => 0
//
//            ],
//            15 => [
//                'd' => 76,
//                'template' => 'pos_vencimento__6',
//                'variable' => false,
//                'sendings' => 0
//
//            ],
//            16 => [
//                'd' =>   85,
//                'template' => 'pos_vencimento__6',
//                'variable' => false,
//                'sendings' => 0
//
//            ],
//            17 => [
//                'd' => 86,
//                'template' => 'pos_vencimento__6',
//                'variable' => false,
//                'sendings' => 0
//
//            ],

        ];

        $sendings = [
            'success' => [],
            'count' => 0,
            'error' => []
        ];



        $data = collect($dataWhatsapp);

        $data = $data->unique('phone');



        try {
            // Defina o número máximo de iterações por segundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

//                 Tempo inicial do loop
            $startTime = microtime(true);

            foreach ($data as $key => $value) {
                try {
                    if (isset($value->phone)) {
                        foreach ($templates as $k => $v) {
                            if ($value->days_until_expiration == $v['d']) {

                                $templates[$k]['sendings']++;

                                if ($v['variable'] === true) {
                                     $sendingWhatsapp = new SendingWhatsapp($v['template'], $value->phone, ['d' => $v['d']]);
                                     $sendingWhatsapp->builder();

                                    $sendings['success'][] = [
                                        'template' => $v['template'],
                                        'client' => $value
                                    ];
                                } else {
                                     $sendingWhatsapp = new SendingWhatsapp($v['template'], $value->phone);
                                     $sendingWhatsapp->builder();

                                    $sendings['success'][] = [
                                        'template' => $v['template'],
                                        'client' => $value
                                    ];
                                }
                                $sendings['count']++;
                            }
                        }


                    } else {
                        $sendings['error'][] = $value->contract_id;
                    }
                } catch (\Exception $e) {
                    $e;
                }

                // Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
                $elapsedTime = microtime(true) - $startTime;
                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
                if ($remainingMicroseconds > 0) {
                    usleep($remainingMicroseconds);
                }

//                 Atualiza o tempo inicial para a próxima iteração
                $startTime = microtime(true);

            }
        } catch (\Exception $e) {
            $e;
        }



        return $templates;
    }

    private function sendEmail($dataEmail)
    {


        $templates = [
            0 => [
                'template' => 'after_expiration_75d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 75,
                'sendings' => 0
            ],
            1 => [
                'template' => 'after_expiration_80d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 80,
                'sendings' => 0
            ],
            2 => [
                'template' => 'after_expiration_85d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 85,
                'sendings' => 0
            ],
            3 => [
                'template' => 'alert_suspencion',
                'subject' => 'Esse é o nosso último aviso! Não fique sem internet!',
                'rule' => 12,
                'sendings' => 0
            ],
            4 => [
                'template' => 'delay_2d',
                'subject' => 'Aviso importante sobre sua internet!',
                'rule' => 3,
                'sendings' => 0
            ],
            5 => [
                'template' => 'delay_6d',
                'subject' => 'ALERTA! Evite suspensões e bloqueios na sua internet Age Telecom',
                'rule' => 5,
                'sendings' => 0
            ],
//            6 => [
//                'template' => 'scpc',
//                'subject' => '[Age Telecom] - Comunicado Importante',
//                'rule' => 30,
//                'sendings' => 0
//            ],
            7 => [
                'template' => 'missing_4d',
                'subject' => 'Lembrete Importante: vencimento da sua fatura em 4 dias',
                'rule' => -4,
                'sendings' => 0
            ],
            8 => [
                'template' => 'missing_5d',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 5 dias',
                'rule' => -5,
                'sendings' => 0
            ],
            9 => [
                'template' => 'negative',
                'subject' => 'Essa é a sua chance de evitar a negativação do seu CPF',
                'rule' => [20, 25, 35, 45, 60],
                'sendings' => 0
            ],
            10 => [
                'template' => 'suspended_sign',
                'subject' => '[ALERTA] Aviso de suspensão de sinal',
                'rule' => 13,
                'sendings' => 0
            ],
            11 => [
                'template' => 'today',
                'subject' => 'Último dia! Pague seu boleto hoje.',
                'rule' => 78,
                'sendings' => 0
            ],
            12 => [
                'template' => 'tomorrow',
                'subject' => 'É Amanhã! Evite juros e multas desnecessárias!',
                'rule' => -1,
                'sendings' => 0
            ],
        ];

        $sendings = [
            'success' => [],
            'count' => 0,
            'error' => []
        ];



        $data = collect($dataEmail);


        $data = $data->unique('email');

        $date = Carbon::now();
        $carbonDate = Carbon::parse($date);

        $dia = $carbonDate->day;
        $mes = $carbonDate->format('m');
        $ano = $carbonDate->year;


        switch ($mes){
            case 1:
                $mes = 'Janeiro';
                break;
            case 2:
                $mes = 'Fevereiro';
                break;
            case 3:
                $mes = 'Março';
                break;
            case 4:
                $mes = 'Abril';
                break;
            case 5:
                $mes = 'Maio';
                break;
            case 6:
                $mes = 'Junho';
                break;
            case 7:
                $mes = 'Julho';
                break;
            case 8:
                $mes = 'Agosto';
                break;
            case 9:
                $mes = 'Setembro';
                break;
            case 10:
                $mes = 'Outubro';
                break;
            case 11:
                $mes = 'Novembro';
                break;
            case 12:
                $mes = 'Dezembro';
                break;
        }

        $mes = ucfirst(mb_strtolower($mes, 'UTF-8')); // Converte a primeira letra do mês para maiúscula

        $dateFormatted = "$dia de $mes de $ano";


        try  {
//             Defina o número máximo de iterações por segundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

            // Tempo inicial do loop
            $startTime = microtime(true);

            $client = new Client();

            $data = [
                "grant_type" => "client_credentials",
                "scope" => "syngw",
                "client_id" => env('VOALLE_API_CLIENT_ID'),
                "client_secret" => env('VOALLE_API_CLIENT_SECRET'),
                "syndata" => env('VOALLE_API_SYNDATA')
            ];

            $response = $client->post('https://erp.agetelecom.com.br:45700/connect/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => $data
            ]);

            $access = json_decode($response->getBody()->getContents());


            foreach ($data as $key => $value) {
                try {

//                    $responseBillet = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/3957492',[
//                        'headers' => [
//                            'Authorization' => 'Bearer '.$access->access_token
//                        ]
//                    ]);
//
//
//                    // Verifique se a requisição foi bem-sucedida (código de status 200)
//                    if ($responseBillet->getStatusCode() == 200) {
//                        // Obtenha o conteúdo do PDF
//                        $pdfContent = $responseBillet->getBody()->getContents();
//
//                        // Especifique o caminho onde você deseja salvar o arquivo no seu computador
//                        $billetPath = storage_path('app/pdf/boleto.pdf');
//
//                        // Salve o arquivo no caminho especificado
//                        file_put_contents($billetPath, $pdfContent);
//
//
//                    }


                    if($value->days_until_expiration == 30) {

                        $debits = [];

                        $debits[] = [
                            'contractClient' => $value->contract_id,
                            'value' => $value->document_amount,
                            'date' => $value->expiration_date
                        ];


                        $mail = Mail::mailer('fat')->to('carlos.neto@agetelecom.com.br')
                            ->send(new SendSCPC($value->name, $value->tx_id, $debits, $dateFormatted));

//                        unlink($billetPath);


                    }

                    if (filter_var('carlos.neto@agetelecom.com.br', FILTER_VALIDATE_EMAIL)) {
                        foreach ($templates as $k => $v) {

                            if ($value->days_until_expiration == $v['rule']) {

                                $templates[$k]['sendings']++;


                                Mail::mailer('fat')->to('carlos.neto@agetelecom.com.br')
                                    ->send(new SendMailBillingRule($v['template'], $v['subject'], $value->name, $value->barcode, $billetPath));


//                                unlink($billetPath);

                                $sendings['success'][] = [
                                    'template' => $v['template'],
                                    'client' => $value
                                ];
                                $sendings['count']++;
                            }


                            if(is_array($v['rule'])){

                                if(in_array($value->days_until_expiration, $v['rule'])) {
                                    $templates[$k]['sendings']++;


                                    Mail::mailer('fat')->to('carlos.neto@agetelecom.com.br')
                                        ->send(new SendMailBillingRule($v['template'], $v['subject'], $value->name, $value->barcode));

//                                    unlink($billetPath);

                                    $sendings['success'][] = [
                                        'template' => $v['template'],
                                        'client' => $value
                                    ];
                                    $sendings['count']++;
                                }

                            }


                        }


                    } else {
//                        $sendings['error'][] = $value->email;
                    }
                } catch (\Exception $e) {
                    $e;
                }

//                 Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
                $elapsedTime = microtime(true) - $startTime;
                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
                if ($remainingMicroseconds > 0) {
                    usleep($remainingMicroseconds);
                }

                // Atualiza o tempo inicial para a próxima iteração
                $startTime = microtime(true);

            }

        } catch (\Exception $e) {
            $e;
        }



//        return $templates;

    }

    private function sendSMS($data)
    {
        $templates = [
            0 => [
                'day' => [-3, -5],
                'sendings' => 0,
                'template' => "AGE Telecom:\nSua fatura já está disponível. Acesse através do portal da AGE: https://encr.pw/qv4Ed\n\nSe já pagou, desconsidere."
            ],
            1 => [
                'day' => [-1],
                'sendings' => 0,
                'template' => "AGE Telecom:\nAmanhã é o dia do vencimento da sua fatura. Acesse através do portal da AGE:\nhttps://encr.pw/qv4Ed\n\nJSe já pagou, desconsidere."
            ],
            2 => [
                'day' => [0],
                'sendings' => 0,
                'template' => "AGE Telecom:\nHoje é o ÚLTIMO DIA p/ pagar sua fatura e evitar juros e multas. Acesse através do portal da AGE: https://encr.pw/qv4Ed\n\nSe já pagou, desconsidere."
            ],
            3 => [
                'day' => [5,8],
                'sendings' => 0,
                'template' => "Age Telecom:\nAtenção! Fatura AGE Telecom com {day} dias de atraso 😥 Evite a suspensão do sinal. https://encr.pw/qv4Ed\n\nSe já pagou, desconsidere."
            ],
            4 => [
                'day' => [12],
                'sendings' => 0,
                'template' => "Age Telecom:\nEvite a suspensão da sua internet. Acesse através do portal da AGE: https://encr.pw/qv4Ed\n\nSe já pagou, desconsidere."
            ],
            5 => [
                'day' => [13],
                'sendings' => 0,
                'template' => "Age Telecom:\nEvite a suspensão da sua internet. Acesse através do portal da AGE: https://encr.pw/qv4Ed\n\nSe já pagou, desconsidere."
            ],
            6 => [
                'day' => [20, 30],
                'sendings' => 0,
                'template' => "AGE Telecom:\nSua fatura está vencida há {day} dias. Evite a negativação do seu CPF, regularize o seu débito. https://encr.pw/qv4Ed\n\nSe já pagou, desconsidere."
            ],
            7 => [
                'day' => [45],
                'sendings' => 0,
                'template' => "AGE Telecom:\nEvite o cancelamento do seu contrato e negativação do seu CPF. Regularize o seu débito. https://encr.pw/qv4Ed\n\nSe já pagou, desconsidere."
            ]
        ];


        $data = collect($data);

        $data = $data->unique('phone');


        $sendings = [
            'success' => [],
            'count' => 0,
            'error' => []
        ];

        $client = new Client();

        try {
//             Defina o número máximo de iterações por segundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

//                 Tempo inicial do loop
            $startTime = microtime(true);

            foreach ($data as $key => $value) {


                try {
                    if (isset($value->phone)) {


                        foreach ($templates as $k => $v) {


                            if(is_array($v['day'])){

                                if(in_array($value->days_until_expiration, $v['day'])) {

                                    $templates[$k]['sendings']++;


                                    $template = str_replace('{day}', abs($value->days_until_expiration), $v['template']);
                                    $template = str_replace('{barcode}', $value->barcode, $template);


                                    // Cria o array com os dados a serem enviados

                                    $data = [
                                        "id" => uniqid(),
                                        "to" => "+55$value->phone@sms.gw.msging.net",
                                        "type" => "text/plain",
                                        "content" => "$template"
                                    ];

                                    // Faz a requisição POST usando o cliente Guzzle HTTP
                                    $response = $client->post('https://agetelecom.http.msging.net/messages', [
                                        'headers' => [
                                            'Content-Type' => 'application/json',
                                            'Authorization' => 'Key b3BlcmFjYW9ub2NiMmI6QTZzQ3Z4WUlxbjZqQ2NvSU1JR1o='
                                        ],
                                        'json' => $data
                                    ]);

                                    // Obtém o corpo da resposta
                                    $body = $response->getBody();


                                    $sendings['success'][] = [
                                        'template' => $template,
                                        'client' => $value->name
                                    ];
                                    $sendings['count']++;
                                }

                            }
                        }


                    } else {
                        $sendings['error'][] = $value->contract_id;
                    }
                } catch (\Exception $e) {
                    $e;
                }

                // Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
                $elapsedTime = microtime(true) - $startTime;
                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
                if ($remainingMicroseconds > 0) {
                    usleep($remainingMicroseconds);
                }

                // Atualiza o tempo inicial para a próxima iteração
                $startTime = microtime(true);

            }
        } catch (\Exception $e) {
            $e;
        }

        return $templates;


    }

    private function getQuery()
    {


        $query = '
            SELECT
                c.id AS "contract_id",
                p.email AS "email",
                p.v_name AS "name",
                frt.document_amount,
                p.tx_id,
                CASE
                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
                    ELSE p.cell_phone_2
                END AS "phone",
                frt.typeful_line AS "barcode",
                frt.expiration_date AS "expiration_date",
                frt.competence AS "competence",
                case
                    when frt.expiration_date > current_date then -(frt.expiration_date - current_date)
                    else (current_date - frt.expiration_date)
                end as "days_until_expiration"
            FROM erp.contracts c
            LEFT JOIN erp.people p ON p.id = c.client_id
            LEFT JOIN erp.financial_receivable_titles frt ON frt.contract_id = c.id
            WHERE
                c.v_stage = \'Aprovado\'
                and c.v_status != \'Cancelado\'
                AND frt.competence >= \'2023-05-01\'
                AND frt.deleted IS FALSE
                AND frt.finished IS FALSE
                AND frt.title LIKE \'%FAT%\'
                and frt.p_is_receivable is true
                and frt.typeful_line is not null
            limit 1
            ';
//
//        $query = '
//            SELECT
//                c.contract_id  AS "contract_id",
//                p.email AS "email",
//                p.v_name AS "name",
//                CASE
//                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
//                    ELSE p.cell_phone_2
//                END AS "phone",
//                frt.typeful_line AS "barcode",
//                frt.expiration_date AS "expiration_date",
//                frt.competence AS "competence",
//                case
//                    when frt.expiration_date > current_date then -(frt.expiration_date - current_date)
//                    else (current_date - frt.expiration_date)
//                end as "days_until_expiration"
//            FROM datawarehouse.dwd_contracts c
//            LEFT JOIN datawarehouse.dwd_people p ON p.people_id = c.client_id
//            LEFT JOIN datawarehouse.dwf_financial_receivable_titles frt ON frt.contract_id = c.contract_id
//            left join datawarehouse.dwf_financial_receipt_titles dfrt on dfrt.financial_receivable_title_id = frt.financial_receivable_title_id
//            WHERE
//                c.v_stage = \'Aprovado\'
//                and c.v_status != \'Cancelado\'
//                AND frt.competence >= \'2023-05-01\'
//                AND frt.deleted IS FALSE
//                AND frt.finished IS FALSE
//                AND frt.title LIKE \'%FAT%\'
//                and frt.p_is_receivable is true
//                and dfrt.receipt_date is null limit 100
//        ';

        return $query;

    }
}
