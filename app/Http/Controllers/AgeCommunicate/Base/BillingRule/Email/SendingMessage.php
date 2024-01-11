<?php

namespace App\Http\Controllers\AgeCommunicate\Base\BillingRule\Email;

use App\Mail\AgeCommunicate\Base\SCPC\SendSCPC;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Mail;

class SendingMessage
{

    public function __construct($data)
    {
        $this->data = $data;
    }


    public function builder()
    {

        $data = collect($this->data)->unique('email');
        $date = Carbon::now();

        $dateFormatted = $date->isoFormat('DD [de] MMMM [de] YYYY');


        return $this->send($dateFormatted);

    }

    private function send($dateFormatted)
    {


        try {
            $client = new Client();



            $dataForm = [
                "grant_type" => "client_credentials",
                "scope" => "syngw",
                "client_id" => env('VOALLE_API_CLIENT_ID'),
                "client_secret" => env('VOALLE_API_CLIENT_SECRET'),
                "syndata" => env('VOALLE_API_SYNDATA')
            ];

            $response = $client->post('https://erp.agetelecom.com.br:45700/connect/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => $dataForm
            ]);

            $access = json_decode($response->getBody()->getContents());

            $responseBillet = $client->get('https://erp.agetelecom.com.br:45715/external/integrations/thirdparty/GetBillet/' . $this->data['debits'][0]['frt_id'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $access->access_token
                ]
            ]);



            $billetPath = [];


            // Verifique se a requisição foi bem-sucedida (código de status 200)
            if ($responseBillet->getStatusCode() == 200) {
                // Obtenha o conteúdo do PDF
                $pdfContent = $responseBillet->getBody()->getContents();

                // Especifique o caminho onde você deseja salvar o arquivo no seu computador
                $billetPath = storage_path('app/pdf/boleto.pdf');

                // Salve o arquivo no caminho especificado
                file_put_contents($billetPath, $pdfContent);


            }


            $mail = Mail::mailer('fat')->to($this->data['email'])
                ->send(new SendSCPC($this->data['name'], $this->data['tx_id'], $this->data['debits'], $dateFormatted, $billetPath));

            unlink($billetPath);

            return $mail->getDebug();



        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

}
