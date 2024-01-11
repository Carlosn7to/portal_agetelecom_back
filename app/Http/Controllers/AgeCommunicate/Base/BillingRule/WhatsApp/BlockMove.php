<?php

namespace App\Http\Controllers\AgeCommunicate\Base\BillingRule\WhatsApp;

use GuzzleHttp\Client;

class BlockMove
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
            "uri" => "/contexts/55$this->cellphone@wa.gw.msging.net/stateid@684abf3b-a37b-4c29-bb28-4600739efde0",
            "type" => "text/plain",
            "resource" => "b1814ddb-4d3b-4857-904f-cd5a0a6a9c5e"
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
