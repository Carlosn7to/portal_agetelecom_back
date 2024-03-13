<?php

namespace App\Http\Controllers\AgeCommunicate\AppClient;

use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\AppClient\SendToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TokenController extends Controller
{

    private $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDEvYXBpL2F1dGgvbG9naW5fYWQiLCJpYXQiOjE3MTAzMzk5MjEsImV4cCI6MTcxMDQyNjMyMSwibmJmIjoxNzEwMzM5OTIxLCJqdGkiOiJvaTlSMkc0M3pBRDV5WTNHIiwic3ViIjoiMSIsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.3LA39exc1PGy-DiFshltxX5gtSZBAftGdbCkyP-c864';
    private $nameClient;
    private $tokenClient;

    public function __construct(Request $request)
    {
        $this->nameClient = $request->nameClient;
        $this->tokenClient = $request->tokenClient;
        $this->to = $request->to;

    }

    public function sendToken(Request $request)
    {
        if(! $request->has('token')) {
            return response()->json(['error' => 'Token não informado'], 401);
        }

        if($request->token != $this->token) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        if(! $request->has('nameClient') || ! $request->has('tokenClient') || ! $request->has('to')){

            return response()->json(['error' => 'Dados inválidos', 'info' => [
                'nameClient' => $request->nameClient,
                'tokenClient' => $request->tokenClient,
                'to' => $request->to,
            ]], 400);
        }


        $mail = Mail::mailer('portal')->to($this->to)
            ->send(new SendToken($this->tokenClient, $this->nameClient));

        return response()->json(['success' => 'Token enviado com sucesso'], 200);

    }
}
