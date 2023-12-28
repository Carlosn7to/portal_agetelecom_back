<?php

namespace App\Http\Controllers\AgeCommunicate\Base\BillingRule\WhatsApp;

use App\Models\AgeCommunicate\Base\BillingRule\Template;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SendingMessage
{

        private array $data = [
            'template' => '',
            'variables' => false,
            'idMessage' => '',
        ];

        private $idMessage;

        private $infoClient;
        private array $exceptions = [];


        public function __construct($data)
        {

            $this->infoClient = $data;

        }


        public function builder()
        {

            $this->getTemplate();

            if(isset($this->exceptions['notFoundTemplate'])) {
                return $this->response();
            }

            return $this->send();

        }

        private function getForm()
        {

            $this->data['idMessage'] = mb_convert_case(Str::random(5).'-' . Str::random(5) . '-' . Str::random(5) . '-' . Str::random(5), MB_CASE_LOWER, "UTF-8");


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

            return $form;

        }


        private function send()
        {
            $client = new Client();


            $sending = $client->post('https://agetelecom.http.msging.net/messages', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
                ],
                'json' => $this->getForm()
            ]);

            if($sending->getStatusCode() != 202) {
                $this->exceptions[] = 'key invalid';
            }




            if(!empty($this->exceptions)) {
                $sendingStatus = false;
                return $this->response($sendingStatus);
            } else {
                $sendingStatus = true;
                $intermediaryStatus = (new Intermediary($this->infoClient['cellphone']))->send();
                $moveBlockStatus = (new BlockMove($this->infoClient['cellphone']))->send();
                return $this->response($sendingStatus, $intermediaryStatus, $moveBlockStatus);
            }


        }

        private function response($sendingStatus, $intermediaryStatus = false, $moveBlockStatus = false)
        {

            return [
                'sendMessage' => [
                    'status' => $sendingStatus,
                    'data' => $this->data,
                    'exceptions' => $this->exceptions
                ],
                'intermediary' => $intermediaryStatus,
                'moveBlock' => $moveBlockStatus
            ];

        }

        private function getTemplate() :  void
        {
            $template = (new Template())->whereStatus(1)->whereStatus(1)->whereCanal('whatsapp')->get(['template', 'regra', 'variavel']);

            foreach ($template as $key => $value) {

                if(in_array($this->infoClient['dayRule'], (json_decode($value->regra, true))['dias'] )) {
                    $this->data['template'] = $value->template;

                    if($value->variavel) {
                        $this->data['variables'] = true;
                    }

                }

            }

            if($this->data['template'] == '') {
                $this->exceptions[] = 'notFoundTemplate';
            }

        }
}
