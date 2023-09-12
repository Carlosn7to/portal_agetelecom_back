<?php

namespace App\Http\Controllers\AgeCommunicate\BillingRule;

use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\Response;
use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\SendingWhatsapp;
use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\Base\SendBillingRule;
use App\Mail\AgeCommunicate\Base\SendMailBillingRule;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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



        $whatsapp = $this->sendMessage($data);
        $email = $this->sendEmail($data);
        $sms = $this->sendSMS($data);


        return $this->response->constructResponse(200, 'sucesso', [
            'whatsapp' => $whatsapp,
            'email' => $email,
            'sms' => $sms
        ], []);
    }

    private function sendMessage($dataWhatsapp)
    {


        $templates = [
            // Pré-vencimento
            0 => [
                'd' => -5,
                'template' => 'pre_vencimento__1_',
                'variable' => true,
                'sendings' => 0
            ],
            1 => [
                'd' => -4,
                'template' => 'pre_vencimento__1_',
                'variable' => true,
                'sendings' => 0

            ],
            2 => [
                'd' => -1,
                'template' => 'pre_vencimento__2',
                'variable' => false,
                'sendings' => 0

            ],
            3 => [
                'd' => -0,
                'template' => 'pre_vencimento__3',
                'variable' => false,
                'sendings' => 0

            ],
            // Pós-vencimento
            4 => [
                'd' => 3,
                'template' => 'pos_vencimento__1',
                'variable' => true,
                'sendings' => 0

            ],
            5 => [
                'd' => 4,
                'template' => 'pos_vencimento__1',
                'variable' => true,
                'sendings' => 0

            ],
            6 => [
                'd' => 14,
                'template' => 'pos_vencimento__2',
                'variable' => false,
                'sendings' => 0

            ],
            7 => [
                'd' => 15,
                'template' => 'pos_vencimento__3_',
                'variable' => false,
                'sendings' => 0

            ],
            8 => [
                'd' => 20,
                'template' => 'pos_vencimento__4',
                'variable' => false,
                'sendings' => 0

            ],
            9 => [
                'd' => 21,
                'template' => 'pos_vencimento__4',
                'variable' => false,
                'sendings' => 0

            ],
            10 => [
                'd' => 30,
                'template' => 'pos_vencimento__5',
                'variable' => true,
                'sendings' => 0

            ],
            11 => [
                'd' => 31,
                'template' => 'pos_vencimento__5',
                'variable' => true,
                'sendings' => 0

            ],
            12 => [
                'd' => 45,
                'template' => 'pos_vencimento__5',
                'variable' => true,
                'sendings' => 0

            ],
            13 => [
                'd' => 46,
                'template' => 'pos_vencimento__5',
                'variable' => true,
                'sendings' => 0

            ],
            14 => [
                'd' => 75,
                'template' => 'pos_vencimento__6',
                'variable' => false,
                'sendings' => 0

            ],
            15 => [
                'd' => 76,
                'template' => 'pos_vencimento__6',
                'variable' => false,
                'sendings' => 0

            ],
            16 => [
                'd' =>   85,
                'template' => 'pos_vencimento__6',
                'variable' => false,
                'sendings' => 0

            ],
            17 => [
                'd' => 86,
                'template' => 'pos_vencimento__6',
                'variable' => false,
                'sendings' => 0

            ],

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
                'rule' => 14,
                'sendings' => 0
            ],
            4 => [
                'template' => 'delay_2d',
                'subject' => 'Aviso importante sobre sua internet!',
                'rule' => 2,
                'sendings' => 0
            ],
            5 => [
                'template' => 'delay_6d',
                'subject' => 'ALERTA! Evite suspensões e bloqueios na sua internet Age Telecom',
                'rule' => 6,
                'sendings' => 0
            ],
            6 => [
                'template' => 'missing_3d',
                'subject' => 'Fique atento! Faltam apenas 3 dias',
                'rule' => -3,
                'sendings' => 0
            ],
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
                'rule' => [20, 25, 30, 35, 45, 60],
                'sendings' => 0
            ],
            10 => [
                'template' => 'suspended_sign',
                'subject' => '[ALERTA] Aviso de suspensão de sinal',
                'rule' => 15,
                'sendings' => 0
            ],
            11 => [
                'template' => 'today',
                'subject' => 'Último dia! Pague seu boleto hoje.',
                'rule' => 0,
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



        $limit = 0;

        try {
            // Defina o número máximo de iterações por segundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

            // Tempo inicial do loop
            $startTime = microtime(true);

            foreach ($data as $key => $value) {
                try {


                    if (filter_var($value->email, FILTER_VALIDATE_EMAIL) && $limit < 8000) {
                        foreach ($templates as $k => $v) {
                            if ($value->days_until_expiration == $v['rule']) {

                                $limit++;
                                $templates[$k]['sendings']++;

                                Mail::mailer('fat')->to($value->email)
                                    ->send(new SendMailBillingRule($v['template'], $v['subject'], $value->name, $value->barcode));





                                $sendings['success'][] = [
                                    'template' => $v['template'],
                                    'client' => $value
                                ];
                                $sendings['count']++;
                            }


                            if(is_array($v['rule'])){

                                if(in_array($value->days_until_expiration, $v['rule'])) {
                                    $limit++;
                                    $templates[$k]['sendings']++;


                                    Mail::mailer('fat')->to($value->email)
                                        ->send(new SendMailBillingRule($v['template'], $v['subject'], $value->name, $value->barcode));


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



        return $templates;

    }

    private function sendSMS($data)
    {
        $templates = [
            0 => [
                'day' => [-4, -5],
                'sendings' => 0,
                'template' => "AGE Telecom:\nFaltam {day} dias p/ o vencimento da sua fatura. Codigo de barras: {barcode}\n\nJa pagou? Desconsidere"
            ],
            1 => [
                'day' => [-1],
                'sendings' => 0,
                'template' => "AGE Telecom:\nAmanha é o ultimo dia p/ pagar sua fatura e evitar juros e multas. Codigo de barras: {barcode}\n\nJa pagou? Desconsidere."
            ],
            2 => [
                'day' => [0],
                'sendings' => 0,
                'template' => "AGE Telecom:\nHoje é o ultimo dia p/ pagar sua fatura e evitar juros e multas. Codigo de barras:\n{barcode}.\n\nJa pagou? Desconsidere."
            ],
            3 => [
                'day' => [3, 7],
                'sendings' => 0,
                'template' => "Age Telecom:\nFatura AGE com {day} dias de atraso. Evite a suspensao do sinal. Codigo de barras:\n{barcode}.\n\nJa pagou? Desconsidere."
            ],
            4 => [
                'day' => [14],
                'sendings' => 0,
                'template' => "Age Telecom:\nSua conexao sera suspensa amanha. Evite esse transtorno e regularize sua situação. Codigo de barras {barcode}.\n\nJa pagou? Desconsidere."
            ],
            5 => [
                'day' => [15],
                'sendings' => 0,
                'template' => "Age Telecom:\nSua conexao foi bloqueada por conta do débito. Regularize sua situação. Código de barras: {barcode}\n\nJa pagou? Desconsidere."
            ],
            6 => [
                'day' => [20, 30, 40, 50],
                'sendings' => 0,
                'template' => "AGE Telecom:\nSua fatura está vencida ha {day} dias. Evite a negativação do seu CPF, regularize o seu débito. Codigo de barras:\n{barcode}.\n\nJa pagou? Desconsidere."
            ],
            7 => [
                'day' => [75, 85],
                'sendings' => 0,
                'template' => "AGE Telecom:\nEvite o cancelamento do seu contrato e negativação do seu CPF. Regularize o seu debito. Código de barras:\n{barcode}.\n\nJa pagou? Desconsidere."
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
            ';

        return $query;

    }
}
