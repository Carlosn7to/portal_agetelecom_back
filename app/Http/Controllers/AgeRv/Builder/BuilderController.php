<?php

namespace App\Http\Controllers\AgeRv\Builder;

use App\Http\Controllers\AgeRv\Builder\Result\b2c\Seller;
use App\Http\Controllers\Controller;
use App\Models\AgeRv\AccessPermission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BuilderController extends Controller
{

    private $response = [
        'userId' => 0,
        'userSector' => [
            'id' => 0,
            'title' => ''
        ],
        'userLevel' => [
            'id' => 0,
            'title' => ''
        ],
        'userFunction' => [
            'id' => 0,
            'title' => ''
        ],
        'date_request' => '',
        'result' => []
    ];

    public function __construct(Request $request)
    {
        $user = AccessPermission::from('agerv_usuarios_permitidos as up')->where('up.user_id', auth()->user()->id)
                    ->leftJoin('portal_nivel_acesso as na', 'up.nivel_acesso_id', '=', 'na.id')
                    ->leftJoin('agerv_colaboradores as c', 'c.user_id', '=', 'up.user_id')
                    ->leftJoin('portal_colaboradores_funcoes as cf', 'up.funcao_id', '=', 'cf.id')
                    ->leftJoin('portal_colaboradores_setores as cs', 'up.setor_id', '=', 'cs.id')
                    ->select('na.nivel','na.id as nivel_id', 'cf.id as funcao_id', 'cf.funcao',
                            'cs.id as setor_id', 'cs.setor')
                    ->first();

        $this->response['userId'] = auth()->user()->id;
        $this->response['userLevel']['id'] = $user->nivel_id;
        $this->response['userLevel']['title'] = $user->nivel;
        $this->response['userFunction']['id'] = $user->funcao_id;
        $this->response['userFunction']['title'] = $user->funcao;
        $this->response['userSector']['id'] = $user->setor_id;
        $this->response['userSector']['title'] = $user->setor;
        $this->response['date_request'] = $request->has('date') ? Carbon::parse($request->input('date'))->format('Y-m') : Carbon::now()->format('Y-m');


    }

    public function response()
    {
        $this->result();

        return response()->json($this->response, 201);
    }

    private function result()
    {
        if($this->response['userSector']['id'] === 1) {
            switch ($this->response['userFunction']['id']) {
                case 1:
                    $seller = new Seller();
                    return $this->response['result'] = $seller->response();
                break;
            }
        }

    }

    public function commission()
    {
        return $this->response;
    }
}
