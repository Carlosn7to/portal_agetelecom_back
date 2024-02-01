<?php

namespace App\Http\Controllers\AgeCommunicate\Base\Welcome;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\Base\BlackNovember\SendBlackNovember;
use App\Mail\AgeCommunicate\Base\SendClientDay;
use App\Mail\AgeCommunicate\Base\Welcome\SendWelcomeRule;
use App\Mail\Portal\Alert\SendAlert;
use App\Models\AgeCommunicate\Base\Welcome\Welcome;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class WelcomeController extends Controller
{

    private $sendings = [];
    private $notSendings = [];


    public function __invoke()
    {


        return $this->builder();
    }

    public function builder()
    {

        set_time_limit(2000000000);

        $result = DB::connection('pgsql')->select($this->getQuery());
        $sendingsData = Welcome::where('created_at', '>=', Carbon::now()->subDays(17))->get(['contrato_id', 'regra']);



        foreach($result as $key => $value) {
            $response = null;

            // Verifica se existe um registro com os mesmos valores em contrato_id e regra
            if($sendingsData->where('contrato_id', $value->contract_id)->where('regra', $value->date)->count() > 0) {
                // Se existir, não faz o envio

                continue;
            }

            // Se não existir, faz o envio e registra nos arrays correspondentes
            $response = $this->sending($value);

            if($response !== null) {
                $this->sendings[] = [
                    'email' => $value->email,
                    'contract_id' => $value->contract_id,
                    'date' => $value->date
                ];
            } else {
                $this->notSendings[] = [
                    'email' => $value->email,
                    'contract_id' => $value->contract_id,
                    'date' => $value->date
                ];
            }
        }


        return [
          'sendings' => $this->sendings,
            'notSendings' => $this->notSendings
        ];


    }

    private function sending($data)
    {

        $template = $this->getTemplate($data->date);


        if($template === null) {
            return null;
        }


        if (filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
            Mail::mailer('contact')->to($data->email)->send(new SendWelcomeRule($data, $template['subject'], $template['template']));
        }
//
        $saveData = new Welcome();

        $saveData->create([
           'contrato_id' => $data->contract_id,
           'email' => $data->email,
           'regra' => $data->date,
        ]);

        return true;



    }

    private function getTemplate($templateDay)
    {

        $templates = [
            0 => [
                'description' => 'Envio de e-mail para o cliente com as boas-vindas após a aprovação do contrato.',
                'day' => 0,
                'template' => 'approved',
                'subject' => 'Seja Bem-Vindo à Age Telecom! 🌐'
            ],
            10 => [
                'description' => 'Envio de e-mail para o cliente com as formas de pagamento.',
                'day' => 10,
                'template' => 'forms_payment',
                'subject' => 'Facilidade e Comodidade: Escolha a Melhor Forma para Seu Pagamento com a Age Telecom! 💳'
            ],
            15 => [
                'description' => 'Envio de e-mail para o cliente com as informações de desconto.',
                'day' => 15,
                'template' => 'discount',
                'subject' => 'Descubra Como Ganhar R$ 10,00 de Desconto com a Age Telecom! 💰'
            ],
        ];

        foreach($templates as $key => $value) {

            if($key == $templateDay) {
                return $value;
            }

        }

    }

    private function getQuery()
    {
            $query = "select c.id as contract_id,
            c.v_stage, c.v_status, (DATE(now()) - DATE(c.approval_date)) as date,
            c.approval_date, p.email,  p.name as nameClient from erp.contracts c
             left join erp.people p on p.id = c.client_id
             where c.v_stage = 'Aprovado' and (DATE(now()) - DATE(c.approval_date)) <= 15 and c.v_status != 'Cancelado'
        ";
        return $query;

    }

    public function sendReport()
    {

        $data = new Welcome();

        $result = $data->where('created_at', '>=', Carbon::now()->startOfDay())->get(['contrato_id', 'email', 'regra', 'created_at']);


        $data = [
          'header' => [
              'title' => 'informativo',
              'subTitle' => 'Régua de boas-vindas.'
          ],
            'messageMail' => 'Os seguintes disparos foram realizados hoje',
            'table' => [
                'titles' => ['Título da Régua', 'Regra', 'Quantidade'],
                'data' => [
                     [
                        'title' => 'Aprovado',
                        'rule' => '0',
                        'quantity' => $result->where('regra', 0)->count()
                    ],
                     [
                        'title' => 'Formas de Pagamento',
                        'rule' => '10',
                        'quantity' => $result->where('regra', 10)->count()
                    ],
                     [
                        'title' => 'Desconto',
                        'rule' => '15',
                        'quantity' => $result->where('regra', 15)->count()
                    ],
                ],
            ],
            'tableVisible' => true
        ];

        $this->headers = [
            'contrato_id',
            'email',
            'regra',
            'data_hora_envio'
        ];


        $storagePath = storage_path('app/pdf/');

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }


        $excel = \Maatwebsite\Excel\Facades\Excel::download(new ReportExport($result->toArray(), !empty($this->headers) ? $this->headers : $arrHeaders), 'relatorio.xlsx');

        $filePath = $storagePath . 'relatorio_boas_vindas.xlsx';
        $excel->getFile()->move($storagePath, 'relatorio_boas_vindas.xlsx');


        Mail::mailer('portal')->to('boasvindas@agetelecom.com.br')
            ->send(new SendAlert('Relatório de Envio de E-mails de Boas-Vindas', $data, $filePath))
        ;

        unlink($filePath);

        return false;
    }

    public function sendApp()
    {
        $query = 'select c.id, p.email from erp.contracts c
                    left join erp.people p on p.id = c.client_id
                    where c.v_stage = \'Aprovado\' and c.v_status != \'Cancelado\' and c.id <= 77293
                    order by c.id asc
                    ';
        $result = DB::connection('pgsql')->select($query);

        $result = collect($result);

        $result = $result->unique('email');


        try {
            // Defina o número máximo de iterações por segcdundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

            // Tempo inicial do loop
            $starTime = microtime(true);

            foreach($result as $key => $value) {

                try {
                    if (filter_var($value->email, FILTER_VALIDATE_EMAIL)) {


                        $mail = Mail::mailer('contact')->to($value->email)
                            ->send(new SendClientDay());

                    }
                } catch (\Exception $e) {
                    $e;
                }

            }


//                Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
            $elapsedTime = microtime(true) - $starTime;
            $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
            if ($remainingMicroseconds > 0) {
                usleep($remainingMicroseconds);
            }

            // Atualiza o tempo inicial para a próxima iteração
            $starTime = microtime(true);
        }
        catch (\Exception $e) {
            $e;
        }
    }
}
