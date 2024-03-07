<?php

namespace App\Http\Controllers\AgeReport\NetworkManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NetworkManagementController extends Controller
{

    protected string $token;
    private bool $pass;
    private string $command;

    public function __construct(Request $request)
    {
        $this->token = env('AGE_NETWORK_MANAGEMENT_TOKEN');
        $this->pass = $this->token === $request->token;
        $this->command = $request->input('command');

    }

    public function __call($method, $parameters)
    {
        if (!$this->pass) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        // Verifica se o método existe e se é uma função válida
        if (method_exists($this, $method) && is_callable([$this, $method])) {
            return call_user_func_array([$this, $method], $parameters);
        } else {
            // Se o método não existir ou não for uma função válida, lança uma exceção
            throw new \BadMethodCallException("Método {$method} não encontrado.");
        }

    }

}
