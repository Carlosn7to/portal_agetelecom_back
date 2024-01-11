<?php

namespace App\Http\Controllers\AgeCommunicate\Base\BillingRule;

use App\Http\Controllers\AgeCommunicate\Base\BillingRule\WhatsApp\SendingMessage;
use App\Http\Controllers\AgeCommunicate\Base\BillingRule\WhatsApp\SendingMessage2;
use App\Http\Controllers\Controller;
use App\Models\AgeCommunicate\Base\BillingRule\Template;
use Illuminate\Http\Request;

class BuilderController extends Controller
{



    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function builder()
    {

        $test = [
            0 => [
                'cellphone' => '61984700440',
                'name' => 'Geovane Fernandes Ferreira',
                'tx_id' => '026.814.681-08',
                'dayRule' => 14,
                'email' => 'carlos.neto@agetelecom.com.br',
                'contractClient' => 68510,
                'debits' => [
                    0 => [
                        'value' => 99.50,
                        'date' => '2023-11-20',
                        'frt_id' => 4272316
                    ]
                ]
            ],
        ];

        $templates = (new Template())->where('status', 1)->get();


        foreach($test as &$value) {

        return $whatsapp = (new SendingMessage2($value, $templates->where('canal', 'whatsapp')->all()))->response();

//           return $email = (new \App\Http\Controllers\AgeCommunicate\Base\BillingRule\Email\SendingMessage($value))->builder();
        }



    }


}
