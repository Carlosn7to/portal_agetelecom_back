<?php

namespace App\Http\Controllers\AgeCommunicate\Base\BillingRule\WhatsApp;

use App\Models\AgeCommunicate\Base\BillingRule\Sending;
use App\Models\AgeCommunicate\Base\BillingRule\Template;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SendingMessage2
{



    private string $idMessage;
    private array $data;

    public function __construct($infoClient, $templates)
    {
        $this->infoClient = $infoClient;
        $this->templates = $templates;
        $this->idMessage = 'BillingRule: '.Str::random(5).'-'.Str::random(5).'-'.Str::random(5).'-'.Str::random(5);
        $this->error = null;
    }

    public function builder()
    {

    }


    public function response()
    {

        $this->saveData();


        return [
            $this->infoClient,
            $this->templates,
            $this->idMessage
        ];
    }


    private function saveData()
    {


        $ruleSending = new Sending();



        $ruleSending->create([
            'contrato' => $this->infoClient['contractClient'],
            'nome' => $this->infoClient['name'],
            'celular' => $this->infoClient['cellphone'],
            'email' => $this->infoClient['email'],
            'faturas' => json_encode($this->infoClient['debits']),
            'canal' => 'whatsapp',
            'template' => 'pos_vencimento',
            'dia_regra' => $this->infoClient['dayRule'],
            'status' => 'disparado',
            'erros' => '[número incorreto, email incorreto.]',
            'id_mensagem' => $this->idMessage
        ]);

    }


    private function getForm()
    {

        $form = [
            'id' => "BillingRule:".$this->data['idMessage'],
            "to" => "55".$this->infoClient['cellphone']."@wa.gw.msging.net",
            "type" => "application/json",
            "content" => [
                "type" => "template",
                "template" => [
                    "namespace" => "0c731938_5304_4f41_9ccf_f0942721dd48",
                    "name" => $this->data['template'],
                    "language" => [
                        "code" => "PT_BR",
                        "policy" => "deterministic"
                    ],
                    "components" => []
                ]
            ]
        ];

        if($this->data['variables']) {
            $form['content']['template']['components'][] = [
                "type" => "body",
                "parameters" => [
                    [
                        "type" => "text",
                        "text" => abs($this->data['dayRule'])
                    ]
                ]
            ];
        }

    }
}
