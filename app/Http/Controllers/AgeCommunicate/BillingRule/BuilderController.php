<?php

namespace App\Http\Controllers\AgeCommunicate\BillingRule;

use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\Response;
use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\SendingWhatsapp;
use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\Base\SendBillingRule;
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


//        return $this->response->constructResponse(200, 'sucesso', [], []);
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
//                                     $sendingWhatsapp = new SendingWhatsapp($v['template'], $value->phone, ['d' => $v['d']]);
//                                     $sendingWhatsapp->builder();

                                    $sendings['success'][] = [
                                        'template' => $v['template'],
                                        'client' => $value
                                    ];
                                } else {
//                                     $sendingWhatsapp = new SendingWhatsapp($v['template'], $value->phone);
//                                     $sendingWhatsapp->builder();

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

    private function sendEmail()
    {
//        $templates = [
//            1 => ['d' => -5, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPreCobranca/Falta+5+dias.png'],
//            2 => ['d' => -4, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPreCobranca/Falta+4+dias.png'],
//            3 => ['d' => -3, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPreCobranca/Falta+3+dias.png'],
//            4 => ['d' => -1, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPreCobranca/Amanh%C3%A3.png'],
//            5 => ['d' => 0, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPreCobranca/Hoje.png'],
//            6 => ['d' => 2, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Atraso+2+dias.png'],
//            7 => ['d' => 3, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Atraso+copiar.png'],
//            8 => ['d' => 6, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Atraso+6+dias.png'],
//            9 => ['d' => 14, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Suspens%C3%A3o+Amanh%C3%A3.png'],
//            10 => ['d' => 15, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Sinal+Suspenso.png'],
//            11 => ['d' => 20, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Evite+a+negativa%C3%A7%C3%A3o.png'],
//            12 => ['d' => 25, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Evite+a+negativa%C3%A7%C3%A3o.png'],
//            13 => ['d' => 30, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Evite+a+negativa%C3%A7%C3%A3o.png'],
//            14 => ['d' => 35, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Evite+a+negativa%C3%A7%C3%A3o.png'],
//            15 => ['d' => 45, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Evite+a+negativa%C3%A7%C3%A3o.png'],
//            16 => ['d' => 60, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/Evite+a+negativa%C3%A7%C3%A3o.png'],
//            17 => ['d' => 75, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/75+dias+de+atraso.png'],
//            18 => ['d' => 80, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/80+dias+de+atraso.png'],
//            19 => ['d' => 85, 'template' => 'https://agenotifica.s3.sa-east-1.amazonaws.com/age/ReguaDeCobranca/ReguaPosCobranca/85+dias+de+atraso.png'],
//        ];
//
//
//        foreach($templates as $key => $value) {
//
//
////            Mail::mailer('notification')->to('veronice.silva@agetelecom.com.br')
////                    ->send(new SendBillingRule($value['template']));
//
//        }

    }

    private function getQuery()
    {


        $query = '
            SELECT
                c.id AS "contract_id",
                p.email AS "email",
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
                AND frt.competence >= \'2023-05-01\'
                AND frt.deleted IS FALSE
                AND frt.finished IS FALSE
                AND frt.title LIKE \'%FAT%\'
                and frt.p_is_receivable is true';

        return $query;

    }
}
