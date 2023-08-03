<?php

namespace App\Http\Controllers\AgeCommunicate\BillingRule\_aux;

use GuzzleHttp\Client;

class SendingWhatsapp
{

    private string $template;
    private string $cellphone;
    private array $variables = [];

    private array $exceptions = [];


    public function __construct($template, $cellphone, $variables = [])
    {
        $this->template = $template;
        $this->cellphone = $this->removeCharacterSpecials($cellphone);
        $this->variables = $variables;

    }

    public function builder()
    {

        $variable = null;

        if(! empty($this->variables)) {
            $variable = $this->variables['d'];
        }

        $this->sendingMessage($variable);
        $this->intermediary();
        $this->moveBlock();
        return $this->response();
    }

    private function response()
    {
        return [
            'cellphone' => $this->cellphone,
            'template' => $this->template,
            'variables' => $this->variables,
            'exceptions' => $this->exceptions
        ];
    }

    private function sendingMessage($variable = null)
    {
        $client = new Client();

        // Cria o array com os dados a serem enviados

        if($variable > 0) {
            $data = [
                "id" => uniqid(),
                "to" => "55$this->cellphone@wa.gw.msging.net",
                "type" => "application/json",
                "content" => [
                    "type" => "template",
                    "template" => [
                        "namespace" => "0c731938_5304_4f41_9ccf_f0942721dd48",
                        "name" => "$this->template",
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
                                        "text" => "$variable"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            $data = [
                "id" => uniqid(),
                "to" => "55$this->cellphone@wa.gw.msging.net",
                "type" => "application/json",
                "content" => [
                    "type" => "template",
                    "template" => [
                        "namespace" => "0c731938_5304_4f41_9ccf_f0942721dd48",
                        "name" => "$this->template",
                        "language" => [
                            "code" => "PT_BR",
                            "policy" => "deterministic"
                        ],
                        "components" => []
                    ]
                ]
            ];
        }


        // Faz a requisição POST usando o cliente Guzzle HTTP
        $response = $client->post('https://agetelecom.http.msging.net/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Key YWdldGVsZWNvbXJvdXRlcjpYdlBjZWNRaUs0VTdKT2RNT2VmdQ=='
            ],
            'json' => $data
        ]);

        // Obtém o corpo da resposta
        $body = $response->getBody();


        $this->exceptions[] = $body;


    }

    private function intermediary() {

        // Cria uma instância do cliente Guzzle HTTP
        $client = new Client();

        // Cria o array com os dados a serem enviados
        $data = [
            "id" => uniqid(),
            "to" => "postmaster@msging.net",
            "method" => "set",
            "uri" => "/contexts/55$this->cellphone@wa.gw.msging.net/Master-State",
            "type" => "text/plain",
            "resource" => "contatoativoenviodefatura"
        ];

        // Faz a requisição POST usando o cliente Guzzle HTTP
        $response = $client->post('https://agetelecom.http.msging.net/commands', [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Key YWdldGVsZWNvbXJvdXRlcjpYdlBjZWNRaUs0VTdKT2RNT2VmdQ=='
            ]
        ]);

        // Obtém o corpo da resposta
        $body = $response->getBody();

        $this->exceptions[] = $body;


    }

    private function moveBlock()
    {
        // Cria uma instância do cliente Guzzle HTTP
        $client = new Client();

        // Cria o array com os dados a serem enviados
        $data = [
            "id" => uniqid(),
            "to" => "postmaster@msging.net",
            "method" => "set",
            "uri" => "/contexts/55$this->cellphone@wa.gw.msging.net/stateid@684abf3b-a37b-4c29-bb28-4600739efde0",
            "type" => "text/plain",
            "resource" => "b1814ddb-4d3b-4857-904f-cd5a0a6a9c5e"
        ];

        // Faz a requisição POST usando o cliente Guzzle HTTP
        $response = $client->post('https://agetelecom.http.msging.net/commands', [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Key YWdldGVsZWNvbXJvdXRlcjpYdlBjZWNRaUs0VTdKT2RNT2VmdQ=='
            ]
        ]);

        // Obtém o corpo da resposta
        $body = $response->getBody();

        $this->exceptions[] = $body;


    }

    private function removeCharacterSpecials($cellphone) {
        $cellphone = preg_replace('/[^0-9]/', '', $cellphone);

        return trim($cellphone);
    }

}
