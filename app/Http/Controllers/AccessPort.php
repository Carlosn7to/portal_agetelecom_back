<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class AccessPort
{

    private  $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTUsInVzZXJuYW1lIjoiYWRtaW4iLCJleHAiOjE1NjY0N';

    public function index(Request $request)
    {


        return $request->getBody();




        $client = new Client();

        $url = 'https://10.25.3.170:4441/v1/auth/';
        $data = ['username' => 'admin', 'password' => 'frsetvTz'];

        $client = new Client();

        try {
            $response = $client->post($url, [
                'verify' => false, // Ignore SSL verification (not recommended for production)
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $data,
            ]);

            $result = json_decode($response->getBody(), true);
            $httpCode = $response->getStatusCode();
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $result = json_decode($response->getBody(), true);
            $httpCode = $response->getStatusCode();
        }

// Assuming $token is obtained from the previous request
        if (isset($result['token'])) {
            $token = $result['token'];

            $url2 = 'https://10.25.3.170:4441/v1/dispositivo/15/abrir_porta_acesso';
            $data2 = ['id' => 15, 'saida' => 0, 'porta' => 1];

            $client = new Client();

            try {
                $response2 = $client->put($url2, [
                    'headers' => [
                        'Authorization' => 'JWT ' . $token,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $data2,
                    'verify' => false, // Ignore SSL verification (not recommended for production)
                ]);

                $result2 = json_decode($response2->getBody(), true);
                $result4 = true;
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $response2 = $e->getResponse();
                $result2 = json_decode($response2->getBody(), true);
                // Handle exception if needed
            }
        } else {
        }

    }

}
