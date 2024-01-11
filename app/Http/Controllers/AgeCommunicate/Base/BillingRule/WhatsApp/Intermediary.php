<?php

namespace App\Http\Controllers\AgeCommunicate\Base\BillingRule\WhatsApp;

use GuzzleHttp\Client;

class Intermediary
{

    private $cellphone;

    public function __construct($cellphone)
    {
        $this->cellphone = $cellphone;
    }

    public function send()
    {
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
                'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
            ]
        ]);

        if($response->getStatusCode() != 200) {
            return $this->response(false);
        }



        return $this->response(true);
    }

    private function response($status) : bool
    {
        return $status;
    }

}
