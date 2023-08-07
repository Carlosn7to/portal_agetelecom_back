<?php

namespace App\Http\Controllers\AgeCommunicate\BillingRule;

use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\Response;
use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\SendingWhatsapp;
use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\Base\SendBillingRule;
use App\Mail\AgeCommunicate\Base\SendMailBillingRule;
use Carbon\Carbon;
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



        return $this->sendMessage($data);
//         return $this->sendEmail($data);


        return $this->response->constructResponse(200, 'sucesso', [], []);
    }

    private function sendMessage($dataWhatsapp)
    {


        $templates = [
            // Pré-vencimento
            0 => [
                'd' => -5,
                'template' => 'pre_vencimento__1_',
                'variable' => true
            ],
            1 => [
                'd' => -4,
                'template' => 'pre_vencimento__1_',
                'variable' => true

            ],
            2 => [
                'd' => -1,
                'template' => 'pre_vencimento__2',
                'variable' => false

            ],
            3 => [
                'd' => -0,
                'template' => 'pre_vencimento__3',
                'variable' => false

            ],
            // Pós-vencimento
            4 => [
                'd' => 3,
                'template' => 'pos_vencimento__1',
                'variable' => true

            ],
            5 => [
                'd' => 4,
                'template' => 'pos_vencimento__1',
                'variable' => true

            ],
            6 => [
                'd' => 14,
                'template' => 'pos_vencimento__2',
                'variable' => false

            ],
            7 => [
                'd' => 15,
                'template' => 'pos_vencimento__3_',
                'variable' => false

            ],
            8 => [
                'd' => 20,
                'template' => 'pos_vencimento__4',
                'variable' => false

            ],
            9 => [
                'd' => 21,
                'template' => 'pos_vencimento__4',
                'variable' => false

            ],
            10 => [
                'd' => 30,
                'template' => 'pos_vencimento__5',
                'variable' => true

            ],
            11 => [
                'd' => 31,
                'template' => 'pos_vencimento__5',
                'variable' => true

            ],
            12 => [
                'd' => 45,
                'template' => 'pos_vencimento__5',
                'variable' => true

            ],
            13 => [
                'd' => 46,
                'template' => 'pos_vencimento__5',
                'variable' => true

            ],
            14 => [
                'd' => 75,
                'template' => 'pos_vencimento__6',
                'variable' => false

            ],
            15 => [
                'd' => 76,
                'template' => 'pos_vencimento__6',
                'variable' => false

            ],
            16 => [
                'd' =>   85,
                'template' => 'pos_vencimento__6',
                'variable' => false

            ],
            17 => [
                'd' => 86,
                'template' => 'pos_vencimento__6',
                'variable' => false

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

                // Tempo inicial do loop
            $startTime = microtime(true);

            foreach ($data as $key => $value) {
                try {
                    if (isset($value->phone)) {
                        foreach ($templates as $k => $v) {
                            if ($value->days_until_expiration == $v['d']) {
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

                // Atualiza o tempo inicial para a próxima iteração
                $startTime = microtime(true);

            }
        } catch (\Exception $e) {
            $e;
        }



        return $sendings;
    }

    private function sendEmail($dataEmail)
    {
        $templates = [
            0 => [
                'template' => 'after_expiration_75d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 75
            ],
            1 => [
                'template' => 'after_expiration_80d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 80
            ],
            2 => [
                'template' => 'after_expiration_85d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 85
            ],
            3 => [
                'template' => 'alert_suspencion',
                'subject' => 'Esse é o nosso último aviso! Não fique sem internet!',
                'rule' => 14
            ],
            4 => [
                'template' => 'delay_2d',
                'subject' => 'Aviso importante sobre sua internet!',
                'rule' => 2
            ],
            5 => [
                'template' => 'delay_6d',
                'subject' => 'ALERTA! Evite suspensões e bloqueios na sua internet Age Telecom',
                'rule' => 6
            ],
            6 => [
                'template' => 'missing_3d',
                'subject' => 'Fique atento! Faltam apenas 3 dias',
                'rule' => -3
            ],
            7 => [
                'template' => 'missing_4d',
                'subject' => 'Lembrete Importante: vencimento da sua fatura em 4 dias',
                'rule' => -4
            ],
            8 => [
                'template' => 'missing_5d',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 5 dias',
                'rule' => -5
            ],
            9 => [
                'template' => 'negative',
                'subject' => 'Essa é a sua chance de evitar a negativação do seu CPF',
                'rule' => [20, 25, 30, 35, 45, 60]
            ],
            10 => [
                'template' => 'suspended_sign',
                'subject' => '[ALERTA] Aviso de suspensão de sinal',
                'rule' => 15
            ],
            11 => [
                'template' => 'today',
                'subject' => 'Último dia! Pague seu boleto hoje.',
                'rule' => 0
            ],
            12 => [
                'template' => 'tomorrow',
                'subject' => 'É Amanhã! Evite juros e multas desnecessárias!',
                'rule' => -1
            ],
        ];

        $sendings = [
            'success' => [],
            'count' => 0,
            'error' => []
        ];



        $data = collect($dataEmail);

        $data = $data->unique('email');



        try {
            // Defina o número máximo de iterações por segundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

            // Tempo inicial do loop
            $startTime = microtime(true);

            foreach ($data as $key => $value) {
                try {


                    if (filter_var($value->email, FILTER_VALIDATE_EMAIL)) {
                        foreach ($templates as $k => $v) {
                            if ($value->days_until_expiration == $v['rule']) {

                                Mail::mailer('notificacao')->to('carlos.neto@agetelecom.com.br')
                                        ->send(new SendMailBillingRule($v['template'], $v['subject'], $value->name_client, $value->barcode));



                                $sendings['success'][] = [
                                    'template' => $v['template'],
                                    'client' => $value
                                ];
                                $sendings['count']++;
                            }


                            if(is_array($v['rule'])){

                                if(in_array($value->days_until_expiration, $v['rule'])) {

                                    return [
                                      $value->name_client,
                                        $value->barcode,
                                        $v['template'],
                                        $v['subject']
                                    ];


//                                    Mail::mailer('notificacao')->to('carlos.neto@agetelecom.com.br')
//                                        ->send(new SendMailBillingRule($v['template'], $v['subject'], $value->name_client, $value->barcode));



                                    $sendings['success'][] = [
                                        'template' => $v['template'],
                                        'client' => $value
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



        return $sendings;

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
