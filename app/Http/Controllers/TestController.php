<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Exports\UsersExport;
use App\Http\Controllers\AgeRv\_aux\sales\Stars;
use App\Http\Controllers\AgeRv\VoalleSalesController;
use App\Http\Controllers\DataWarehouse\Voalle\PeoplesController;
use App\Http\Controllers\Ixc\Api\WebserviceClient;
use App\Http\Controllers\Mail\Billing\EquipDivideController;
use App\Http\Requests\AgeControl\ConductorStoreRequest;
use App\Ldap\UserLdap;
use App\Mail\AgeCommunicate\Base\SendMailBillingRule;
use App\Mail\BaseManagement\SendPromotion;
use App\Mail\Portal\SendNewUser;
use App\Mail\SendBlackFiber;
use App\Mail\SendInvoice;
use App\Mail\SendMainUser;
use App\Mail\SendOutstandingDebts;
use App\Models\AgeBoard\AccessPermission;
use App\Models\AgeBoard\DashboardPermitted;
use App\Models\AgeBoard\ItemPermitted;
use App\Models\AgeReport\Report;
use App\Models\AgeRv\Channel;
use App\Models\AgeRv\Collaborator;
use App\Models\AgeRv\CollaboratorMeta;
use App\Models\AgeRv\Commission;
use App\Models\AgeRv\Plan;
use App\Models\AgeRv\VoalleSales;
use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;
use Maatwebsite\Excel\Excel;
use Nette\Utils\Random;
use Barryvdh\DomPDF\PDF;

class TestController extends Controller
{
    protected $year;
    protected $monthCompetence;
    protected $dateCompetence;
    private $salesTotals;
    private $dateAdmission;
    private $meta;




    public function __invoke()
    {
        $test = new Test();

        $test->truncate();

    }

    public function index(Request $request)
    {
        set_time_limit(200000);

        $templates = [
            0 => [
                'template' => 'after_expiration_75d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 0
            ],
            1 => [
                'template' => 'after_expiration_80d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 0
            ],
            2 => [
                'template' => 'after_expiration_85d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 0
            ],
            3 => [
                'template' => 'alert_suspencion',
                'subject' => 'Esse é o nosso último aviso! Não fique sem internet!',
                'rule' => 0
            ],
            4 => [
                'template' => 'delay_2d',
                'subject' => 'Aviso importante sobre sua internet!',
                'rule' => 0
            ],
            5 => [
                'template' => 'delay_6d',
                'subject' => 'ALERTA! Evite suspensões e bloqueios na sua internet Age Telecom',
                'rule' => 0
            ],
            6 => [
                'template' => 'missing_3d',
                'subject' => 'Fique atento! Faltam apenas 3 dias',
                'rule' => 0
            ],
            7 => [
                'template' => 'missing_4d',
                'subject' => 'Lembrete Importante: vencimento da sua fatura em 4 dias',
                'rule' => 0
            ],
            8 => [
                'template' => 'missing_5d',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 5 dias',
                'rule' => 0
            ],
            9 => [
                'template' => 'negative',
                'subject' => 'Essa é a sua chance de evitar a negativação do seu CPF',
                'rule' => 0
            ],
            10 => [
                'template' => 'suspended_sign',
                'subject' => '[ALERTA] Aviso de suspensão de sinal',
                'rule' => 0
            ],
            11 => [
                'template' => 'today',
                'subject' => 'Último dia! Pague seu boleto hoje.',
                'rule' => 0
            ],
            12 => [
                'template' => 'tomorrow',
                'subject' => 'É Amanhã! Evite juros e multas desnecessárias!',
                'rule' => 0
            ],
        ];

        foreach($templates as $key => $value) {

            Mail::mailer('fat')->to('carlos.neto@agetelecom.com.br')
                ->send(new SendMailBillingRule($value['template'], $value['subject']));

            return $value;
        }



        return true;

        $query = '
        SELECT DISTINCT
            c.id as contract_id,
            p.name AS name_client,
            c.v_stage as status,
            c.v_status as situacao,
            c.date as data_contrato,
            c.beginning_date as data_ativacao,
            c.beginning_date as data_vigencia,
            c.amount AS valor,
            p_seller.name AS vendedor,
            p_supervisor.name AS supervisor,
            c.cancellation_date AS data_cancelamento,
            CASE
             WHEN sp.title <> \'\' THEN sp.title
             WHEN c.v_status = \'Cancelado\' THEN COALESCE(cst.title, cst2.title)
            END AS plano
        FROM
            erp.contracts c
        LEFT JOIN
            erp.contract_assignment_activations caa ON caa.contract_id = c.id
        LEFT JOIN
            erp.authentication_contracts ac ON ac.contract_id = c.id
        LEFT JOIN
            erp.people p ON p.id = c.client_id
        LEFT JOIN
            erp.service_products sp ON ac.service_product_id = sp.id
        LEFT JOIN
            erp.people p_seller ON c.seller_1_id = p_seller.id
        LEFT JOIN
            erp.people p_supervisor ON c.seller_2_id = p_supervisor.id
        LEFT JOIN
            erp.contract_service_tags cst ON cst.contract_id = c.id AND cst.title LIKE \'PLANO COMBO%\'
        LEFT JOIN
            erp.contract_service_tags cst2 ON cst2.contract_id = c.id AND cst2.title LIKE \'PLANO%\' AND cst2.title NOT LIKE \'%COMBO%\'
        WHERE (caa.contract_id = c.id OR ac.user LIKE \'ALCL%\') and p_supervisor.name = \'B2B\' and c.v_stage = \'Aprovado\'';


        $data = DB::connection('pgsql')->select($query);

        $data = collect($data);


        $result = [];


//        foreach($data as $key => $value) {
//            $monthCompetence = Carbon::parse($value->data_ativacao)->format('m/Y');
//
//            $result[] = [
//                'id_contrato' => $value->contract_id,
//                'activation_day' => $value->data_ativacao,
//                'seller' => $value->vendedor,
//                'plan' => $value->plano,
//                'name' => $value->name_client,
//                'comissionable' => true,
//                'competence' => $monthCompetence,
//                'values_payment' => $this->calcFirstAndSecondMonth($value->data_ativacao, $value->valor, $value->contract_id),
//            ];
//        }

//        return $result;






        foreach($data as $key => $value) {
            $query = 'select
                        frt2.title,
                        frt.amount,
                        frt2.competence
                    from erp.financial_receipt_titles frt
                    left join erp.financial_receivable_titles frt2 on frt.financial_receivable_title_id = frt2.id
                    where frt.financer_nature_id = 59
                      and frt2.title like \'FAT%\'
                      and frt2.contract_id = '.$value->contract_id.'
                      and frt2.deleted is false
                      and frt2.finished is false
                    order by frt.id asc limit 2';


            $data2 = DB::connection('pgsql')->select($query);

            if(!empty($data2)) {
                $result[] = [
                    'id_contrato' => $value->contract_id,
                    'data_ativacao' => $value->data_ativacao,
                    'vendedor' => $value->vendedor,
                    'plano' => $value->plano,
                    'name' => $value->name_client,
                    'fat' => $data2,
                    'comissionable' => true
                ];
            } else {
                $result[] = [
                    'id_contrato' => $value->contract_id,
                    'data_ativacao' => $value->data_ativacao,
                    'vendedor' => $value->vendedor,
                    'plano' => $value->plano,
                    'name' => $value->name_client,
                    'fat' => $data2,
                    'comissionable' => false
                ];
            }
        }

        return response()->json($result, 202);




//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//        $error = [];
//
//        foreach ($array[0] as $key => $value) {
//            if(filter_var($value[1], FILTER_VALIDATE_EMAIL)) {
//
//                Mail::mailer('notification')->to($value[1])
//                    ->send(new SendMainUser($value[0]));
//
//            } else {
//                $error[] = [
//                    'nome' => $value[0],
//                    'email' => $value[1]
//                ];
//            }
//        }


      //  return $error;

//
//
//        return 'Break';

//
//        $users = UserLdap::all();
//
//
//
//        return $users;

//        $array = [
//            'Dirley Teixeira',
//            'Jhonata Junio',
//            'Samuel dos Santos',
//            'Valeria de Carvalho',
//            'Vivian Machado'
//        ];


//        $array = [
//            'Amanda Mariana De Morais',
//            'Barbara Kaliny',
//            'Jessica dos Santos Rocha',
//            'Maria Julia Macedo',
//            'Rayane Neves',
//            'Sthefany Rodrigues'
//        ];
//
//        $collabs = [];
//        $fails = [];
//
//
//        foreach($array as $k => $v) {
//            $collab = Collaborator::where('nome', 'like', '%'.$v.'%')->first(['id', 'nome']);
//
//            if(isset($collab->id)) {
//                $collabs[] = $collab;
//            } else {
//                $fails[] = $v;
//            }
//        }
//
//        return $collabs;
//
//
//        if(count($fails) !== 0) {
//            foreach($collabs as $k => $v) {
//                if(isset($v->id)) {
//
//                    $collab = CollaboratorMeta::whereColaboradorId($v->id)->where('mes_competencia', 01)
//                        ->where('ano_competencia', 2023)
//                        ->first();
//
//                    if(isset($collab->id)) {
//                        $collab->update([
//                            'meta' => 12
//                        ]);
//                    } else {
//
//                        CollaboratorMeta::create([
//                            'colaborador_id' => $v->id,
//                            'mes_competencia' => 01,
//                            'ano_competencia' => 2023,
//                            'meta' => 12,
//                            'modified_by' => 1
//                        ]);
//
//                    }
//                }
//            }
//
//        }
//
//            return "BREAK";

//        $id = $request->input('id');
//        $idCollab = $request->input('idCollab');
//
//        $user = User::find($id);
//
//        $collab = User::find($idCollab);

//
//
//        $plan = new Plan();


//        $json = $request->;
//
//        foreach($request->json('rows') as $k => $v) {
//            $plan->create([
//               'plano' => $v['plano'],
//                'valor_estrela' => $v['estrela'],
//                'mes_competencia' => 1,
//                'ano_competencia' => 2023
//            ]);
//        }
//
//        return "ok";



//
//        $user = new User();
//
//        $user = $user->find(153);
//
//        $password = 'Age@Telecom2023';
//
//        $user = $user->update([
//            'password' => Hash::make($password)
//        ]);
//
//
//        return $user;

//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//
//        $newArray = [];
//
//
//        foreach ($array as $key => $value) {
//            foreach($value as $k => $v) {
//                $newArray[] = [
//                    'nome' => mb_convert_case($v[1], MB_CASE_TITLE, 'UTF-8'),
//                    'email' => $v[2],
//                    'group_id' => $v[0]
//                ];
//            }
//        }
//
//        $host = 'https://ixc.agetelecom.com.br/webservice/v1';
//        $token = '10:8db6eebcbf1b5f8ddb6800f2d79e62690f4e7eec161ef80ff39bba2ad5e5f5a3';//token gerado no cadastro do usuario (verificar permissões)
//        $selfSigned = true; //true para certificado auto assinado
//        $api = new Ixc\Api\WebserviceClient($host, $token, $selfSigned);
//
//        $params = array(
//            'qtype' => 'usuarios.id',//campo de filtro
//            'oper' => '=',//operador da consulta
//            'page' => '1',//página a ser mostrada
//            'rp' => '500',//quantidade de registros por página
//            'sortname' => 'usuarios.id',//campo para ordenar a consulta
//            'sortorder' => 'desc'//ordenação (asc= crescente | desc=decrescente)
//        );
//        $api->get('usuarios', $params);
//
//        $retorno = $api->getRespostaConteudo(true);// false para json | true para array
//
//        $users = [];
//
//        foreach($retorno['registros'] as $k => $value) {
//            $users[] = [
//                'id' => $value['id'],
//                'email' => $value['email'],
//                'group_id' => 0
//            ];
//        }
//
//        $usersLinked = [];
//
//
//        foreach($newArray as $key => $value) {
//
//            foreach($users as $k => $v) {
//
//                if($value['email'] === $v['email']) {
//                    $usersLinked[] = [
//                        'nome' => $value['nome'],
//                        'email' => $v['email'],
//                        'id' => $v['id'],
//                        'group_id' => $value['group_id']
//                    ];
//                }
//
//            }
//
//        }
//
//        foreach($usersLinked as $key => $value) {
//            $dados = array(
//                'id_grupo' => $value['group_id'],
//                'nome' => $value['nome'],
//                'email' => $value['email'],
//                'senha' => 'Age@telecom2023',
//                'status' => 'A',
//                'permite_acesso_ixc_mobile' => 'S',
//                'imagem' => '',
//                'dica_imagem' => '',
//                'acesso_webservice' => 'N',
//                'acesso_token' => '',
//                'user_callcenter' => 'N',
//                'callcenter' => '',
//                'alter_passwd_date' => 'NULL',
//                'language' => 'Pt-Br',
//                'caixa_fn_receber' => '',
//                'id_caixa' => '',
//                'vendedor_padrao' => '',
//                'recebimentos_dia_atual' => 'N',
//                'pagamentos_dia_atual' => 'N',
//                'lancamentos_dia_atual' => 'N',
//                'desc_max_recebimento' => '0.00',
//                'desc_max_venda' => '0.00',
//                'desc_max_renegociacao' => '0.00',
//                'funcionario' => '',
//                'filtra_setor' => 'N',
//                'filtra_funcionario' => 'N',
//                'mostrar_os_sem_funcionario' => 'N',
//                'crm_filtra_vendedor' => 'N',
//                'inmap_filtra_vendedor' => 'N',
//                'enviar_monitoramento_host' => 'N',
//                'enviar_notificacao_backup' => 'N',
//                'permite_inutilizar_patrimonio' => 'N',
//                'permite_ver_diferenca' => 'N'
//            );
//            $registro = $value['id'];//registro a ser editado
//            $api->put('usuarios', $dados, $registro);
//        }
//
//        return "ok";







//
//        foreach($array as $key => $value) {
//            foreach($value as $k => $v) {
//            $dados = array(
//                'id_grupo' => "$v[0]",
//                'nome' => mb_convert_case($v[1], MB_CASE_TITLE, 'UTF-8'),
//                'email' => "$v[2]",
//                'senha' => 'Age@telecom2023',
//                'status' => 'A',
//                'permite_acesso_ixc_mobile' => 'S',
//                'imagem' => '',
//                'dica_imagem' => '',
//                'acesso_webservice' => 'N',
//                'acesso_token' => '',
//                'user_callcenter' => 'N',
//                'callcenter' => '',
//                'alter_passwd_date' => 'NULL',
//                'language' => 'Pt-Br',
//                'caixa_fn_receber' => '',
//                'id_caixa' => '',
//                'vendedor_padrao' => '',
//                'recebimentos_dia_atual' => 'N',
//                'pagamentos_dia_atual' => 'N',
//                'lancamentos_dia_atual' => 'N',
//                'desc_max_recebimento' => '0.00',
//                'desc_max_venda' => '0.00',
//                'desc_max_renegociacao' => '0.00',
//                'funcionario' => '',
//                'filtra_setor' => 'N',
//                'filtra_funcionario' => 'N',
//                'mostrar_os_sem_funcionario' => 'N',
//                'crm_filtra_vendedor' => 'N',
//                'inmap_filtra_vendedor' => 'N',
//                'enviar_monitoramento_host' => 'N',
//                'enviar_notificacao_backup' => 'N',
//                'permite_inutilizar_patrimonio' => 'N',
//                'permite_ver_diferenca' => 'N'
//            );
//        $api->post('usuarios', $dados);
//        $retorno = $api->getRespostaConteudo(false);// false para json | true para array
//            }
//        }
//
//        return "Ok!";

//        $dados = array(
//            'id_grupo' => '',
//            'tipo_alcada' => 'ADM',
//            'nome' => '',
//            'email' => '',
//            'senha' => '',
//            'status' => 'A',
//            'permite_acesso_ixc_mobile' => 'S',
//            'imagem' => '',
//            'dica_imagem' => '',
//            'acesso_webservice' => 'S',
//            'acesso_token' => '',
//            'user_callcenter' => 'S',
//            'callcenter' => '',
//            'alter_passwd_date' => 'NULL',
//            'language' => 'Pt-Br',
//            'caixa_fn_receber' => '',
//            'id_caixa' => '',
//            'vendedor_padrao' => '',
//            'recebimentos_dia_atual' => 'N',
//            'pagamentos_dia_atual' => 'N',
//            'lancamentos_dia_atual' => 'S',
//            'desc_max_recebimento' => '0.00',
//            'desc_max_venda' => '0.00',
//            'desc_max_renegociacao' => '0.00',
//            'funcionario' => '',
//            'filtra_setor' => 'S',
//            'filtra_funcionario' => 'S',
//            'mostrar_os_sem_funcionario' => 'S',
//            'crm_filtra_vendedor' => 'S',
//            'inmap_filtra_vendedor' => 'S',
//            'enviar_monitoramento_host' => 'S',
//            'enviar_notificacao_backup' => 'S',
//            'permite_inutilizar_patrimonio' => 'N',
//            'permite_ver_diferenca' => 'S'
//        );
//        $api->post('usuarios', $dados);
//        $retorno = $api->getRespostaConteudo(false);// false para json | true para array
//        return $retorno;


//        $sellers = VoalleSales::whereMonth('data_contrato', '>=', '5')->whereYear('data_contrato', '2022')
//                                ->whereNotNull('vendedor')
//                                ->distinct('vendedor')->get(['vendedor']);
//
//        $supervisors = VoalleSales::whereMonth('data_contrato', '>=', '5')->whereYear('data_contrato', '2022')
//            ->whereNotNull('supervisor')
//            ->distinct('supervisor')->get(['supervisor']);
//
//        $sellers = DB::select('SELECT DISTINCT vendedor, COUNT(*) as vendas_vendedor FROM agerv_voalle_vendas
//                                            WHERE (MONTH(data_contrato) >= 5 AND YEAR(data_contrato) = 2022)
//                                            AND vendedor != \' \'
//                                            GROUP BY vendedor');
//
//        $supervisors = DB::select('SELECT DISTINCT supervisor, COUNT(*) as vendas_vendedor FROM agerv_voalle_vendas
//                                            WHERE (MONTH(data_contrato) >= 5  AND YEAR(data_contrato) = 2022)
//                                            AND supervisor != \' \'
//                                            GROUP BY supervisor');
//
//        $collaborators = DB::select('SELECT DISTINCT vendedor FROM agerv_voalle_vendas
//                                            WHERE (MONTH(data_contrato) >= 5  AND YEAR(data_contrato) = 2022)
//                                            AND vendedor != \' \'
//                                            GROUP BY vendedor
//                                            UNION
//                                            SELECT DISTINCT supervisor FROM agerv_voalle_vendas
//                                            WHERE (MONTH(data_contrato) >= 5  AND YEAR(data_contrato) = 2022)
//                                            AND supervisor != \' \'
//                                            GROUP BY supervisor');
//
//        $result = [];
//
//        foreach($collaborators as $k => $v) {
//            $result[] = [
//                'colaborador' => [
//                    'nome' => $v->vendedor,
//                    'vendedor' => $this->sellers($v->vendedor, $sellers),
//                    'supervisor' => $this->supervisors($v->vendedor, $supervisors)
//                ]
//            ];
//        }
//
//        $duplicates = [];
//
//        foreach($result as $k => $v) {
//            if($v['colaborador']['vendedor'] !== null && $v['colaborador']['supervisor'] !== null) {
//                $duplicates[] = $v;
//            }
//        }
//
//        return $duplicates;

//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//
//        $collabs = [];
//
//        foreach($array as $k => $v) {
//            foreach($v as $kk => $vv) {
//                $collabs[] = Collaborator::whereNome($vv)->first(['id']);
//            }
//        }
//
//        $success = [];
//
//
//        foreach($collabs as $k => $v) {
//            if(isset($v->id)) {
//
//                $collab = CollaboratorMeta::whereColaboradorId($v->id)->where('mes_competencia', 11)->first();
//
//                if(isset($collab->id)) {
//                    $collab->update([
//                        'meta' => 16.5
//                    ]);
//                } else {
//
//                    CollaboratorMeta::create([
//                       'colaborador_id' => $v->id,
//                       'mes_competencia' => 11,
//                       'meta' => 16.5,
//                       'modified_by' => 1
//                    ]);
//
//                }
//            }
//        }

//
//        return view('mail.invoice_error');
//
//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));


//        $connection = new Connection([
//            'hosts' => ['10.25.0.1'],
//            'base_dn' => 'dc=tote, dc=local',
//            'username' => 'ldap',
//            'password' => 'iAcWMMqC@',
//
//            // Optional Configuration Options
//            'port' => 389,
//            'use_ssl' => false,
//            'use_tls' => false,
//            'version' => 3,
//            'timeout' => 5,
//            'follow_referrals' => false,
//
//        ]);
//
//
//        try {
//            $connection->connect();
//
//            $username = $request->input('email') . '@tote.local';
//            $password = $request->input('password');
//
//            if ($connection->auth()->attempt($username, $password)) {
//                // Separa o nome e o sobrenome
//
//                return response()->json('Authentic', 201);
//
//            } else {
//
//                return response()->json('Unauthentic', 200);
//
//            }
//        } catch (\Exception $e) {
//
//        }

//        foreach($array as $key => $value) {
//
//            foreach($value as $k => $v) {
//
//                if (! preg_match('/^[a-zA-Z0-9]+/', $v[1])) {
//                    return $v[1];
//                }
//            }
//        }

//
        //
//        Mail::to('carlos.neto@agetelecom.com.br')
//                ->send(new SendMainUser('Carlos Neto'));

//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
////        Mail::mailer('notification')->to('carlos.neto@agetelecom.com.br')
//            ->send(new SendPromotion('Carlos Neto'));
////
////        return "Ok";
//
////        return count($array[0]);
//
//        foreach($array as $key => $value) {
//
//            foreach($value as $k => $v) {
//                Mail::mailer('notification')->to($v[1])
//                    ->send(new SendPromotion($v[0]));
//            }
//        }
//
//        return "ok";
//
//        return "ok";

//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//
////        Mail::to('carlos.neto@agetelecom.com.br')
////                ->send(new SendMainUser('Carlos Neto'));
//
//        foreach($array as $key => $value) {
//
//            foreach($value as $k => $v) {
//                Mail::to($v[1])
//                    ->send(new SendMainUser($v[0]));
//            }
//        }
//
//        return "ok";

////
////
//        Mail::to('carlos.neto@agetelecom.com.br')
//            ->send(new SendInvoice());
//

//
//        $people = new PeoplesController();
//
//        return $people->create();

//        $array = [
//          'channels' => [
//              0 => [
//                  'name' => 'MCV',
//                  'collaborators' => [
//                      0 => [
//                          'name' => 'Carlos Neto',
//                          'commission' => 100
//                      ],
//                      1 => [
//                          'name' => 'Neto Carlos',
//                          'commission' => 300
//                      ],
//                      2 => [
//                          'name' => 'Joao da Silva',
//                          'commission' => 200
//                      ],
//                  ]
//              ],
//              1 => [
//                  'name' => 'PAP',
//                  'collaborators' => [
//                      0 => [
//                          'name' => 'Carlos Neto',
//                          'commission' => 400
//                      ],
//                      1 => [
//                          'name' => 'Neto Carlos',
//                          'commission' => 500
//                      ],
//                      2 => [
//                          'name' => 'Joao da Silva',
//                          'commission' => 300
//                      ],
//                  ]
//              ],
//              2 => [
//                  'name' => 'LIDER',
//                  'collaborators' => [
//                      0 => [
//                          'name' => 'Carlos Neto',
//                          'commission' => 500
//                      ],
//                      1 => [
//                          'name' => 'Neto Carlos',
//                          'commission' => 300
//                      ],
//                      2 => [
//                          'name' => 'Joao da Silva',
//                          'commission' => 400
//                      ],
//                  ]
//              ]
//          ]
//        ];
//
//
//        $array = collect($array);
//
//
//        $array = $array->sortByDesc("channels.collaborators.commission");
//
//        dd($array);


//
//
//        $collab = [
//            0 => [
//                'name' => 'Aegiton',
//                'meta' => 231
//            ],
//            1 => [
//                'name' => 'Cesar',
//                'meta' => 396
//            ],
//            2 => [
//                'name' => 'Clebersom',
//                'meta' => 297
//            ],
//            3 => [
//                'name' => 'DAIANE',
//                'meta' => 264
//            ],
//            4 => [
//                'name' => 'EMANUEL',
//                'meta' => 297
//            ],
//            5 => [
//                'name' => 'HEBERTY',
//                'meta' => 231
//            ],
//            6 => [
//                'name' => 'JESSICA',
//                'meta' => 198
//            ],
//            7 => [
//                'name' => 'KEILA',
//                'meta' => 264
//            ],
//            8 => [
//                'name' => 'LAIANE',
//                'meta' => 264
//            ],
//            9 => [
//                'name' => 'NILMAR',
//                'meta' => 297
//            ],
//            10 => [
//                'name' => 'PEDRO',
//                'meta' => 231
//            ],
//            11 => [
//                'name' => 'TARCISIANE',
//                'meta' => 264
//            ],
//            12 => [
//                'name' => 'ALISSON',
//                'meta' => 1.977
//            ]
//        ];
//
//        foreach($collab as $k => $v) {
//
//            $collaborator = Collaborator::where('nome', 'like', $v['name'].'%')->whereTipoComissaoId(3)->first();
//
//            $meta = new CollaboratorMeta();
//
//            $meta->create([
//               'colaborador_id' => $collaborator->id,
//                'mes_competencia' => '11',
//                'meta' => $v['meta'],
//                'modified_by' => 1
//            ]);
//        }
//
//
//        return "break";
//
//        $this->dateAdmission = $request->input('dateAdmission') ? Carbon::parse($request->input('dateAdmission'))->format('Y-m-d') : null;
//
//        $this->dateCompetence = $this->monthCompetence ? $this->monthCompetence : Carbon::now()->subMonth(2)->format('Y-m-d');
//
//        if(! $this->dateAdmission) {
//            return $this->response($this->meta);
//        }
//
//
//
//        return $this->dateCompetence;
//
//
//        $dateAdmission = Carbon::parse('2023-01-09');
//
//
//
//        $calendar = [];
//
//        return $dateAdmission->format('Y');
//
//
//        for($i = 1; $daysMonth >= $i; $i++) {
//            $calendar[] = [
//                'date' => Carbon::parse("$year-$month-$i")->format('Y-m-d'),
//                'name' => Carbon::parse("$year-$month-$i")->format('l')
//            ];
//        }
//
//        $calendar = collect($calendar);
//
//        $countDaysUtils = 0;
//        $countDaysCollab = 0;
//
//
//
//        foreach($calendar as $k => $v) {
//            if($dateAdmission == $v['date'] || $dateAdmission <= $v['date']) {
//                echo $v['date'];
//                echo '<br>';
//            }
//
//            if($v['name'] !== 'Sunday') {
//                if($v['name'] === 'Saturday') {
//                    $countDaysUtils = $countDaysUtils + 0.5;
//                } else {
//                    $countDaysUtils = $countDaysUtils + 1;
//                }
//            }
//
//        }
//
//        $meta = 90;
//
//
//        return $countDaysCollab;
//
//
//        $dateActual = Carbon::now()->format('d');
//        $daysMonth = Carbon::now()->format('t');
//        $dayName = Carbon::now()->format('l');
//        $year = Carbon::now()->format('Y');
//        $month = Carbon::now()->format('m');
//        $dayUtils = $daysMonth;
//        $dayUtil = 0;
//        $datesUtils = [];
//
//
//        for ($i = 1; ($daysMonth + 1) > $i; $i++) {
//            $date = Carbon::parse("$year-$month-$i")->format('d/m/Y');
//            $dayName = Carbon::parse("$year-$month-$i")->format('l');
//
//            if($date != '07/09/2022') {
//                if ($dayName !== 'Sunday') {
//                    if ($dayName === 'Saturday') {
//                        $dayUtil = $dayUtil + 0.5;
//                    } else {
//                        $dayUtil += 1;
//                    }
//                }
//            }
//
//            $datesUtils[] = [
//                $i => [
//                    $dayUtil
//                ]
//            ];
//        }




//
//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//        foreach($array as $key => $value) {
//
//            foreach($value as $k => $v) {
//                Mail::to($v[1])
//                    ->send(new SendMainUser($v[0]));
//
//            }
//        }
//
//        return "ok";

//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//        foreach($array as $key => $value) {
//
//            foreach($value as $k => $v) {
//                Mail::to($v[1])
//                    ->send(new SendBlackFiber());
//
//            }
//        }
//
//        return "ok";


//
//
//            return "Ok";
//


//        $array = [
//          'Ancelmo De Sales',
//            'Angelica Mires',
//            'Carla Julia',
//            'Erivan de Souza',
//            'Geovana Souza',
//            'Itanael',
//            'Jose Loiola',
//            'Karen de Almeida',
//            'Lucas de Brito',
//            'Marcus Vinicius',
//            'Maria Dilma',
//            'Thalia Isabella'
//        ];
//
//
//        foreach($array as $key => $value) {
//
//            $collab = Collaborator::where('nome', 'like', $value.'%')->first('id');
//
//            $meta = CollaboratorMeta::whereColaboradorId($collab->id)->whereMesCompetencia('10')->first();
//
//            $meta = $meta->update(['meta' => 30]);
//
//
//
//        }

//
//


//
//        for($i = 9502; $i < 99999; $i++) {
//            $data = Http::withHeaders(['Content-Type' => 'Application/json'])
//                ->post('https://plataforma.astenassinatura.com.br/api/downloadPDFEnvelopeDocs/', [
//                    'token' => '3koqaYFIRSx5QypP2huHk4gpIsVT0WZ3bIEbLfbfJWwKzgu0WP+jI13IISftJl+6x5yKrknzeGyvNuqYcVVky4-S8HNSIjlCU90x8GWDthturJN+Nue40K9PPLxRCvo5mqdQ28eqVfA',
//                    'params' => [
//                        'idEnvelope' => $i,
//                        'incluirDocs' => 'S',
//                        'versaoSemCertificado' => null
//                    ]
//                ])
//                ->json();
//
//            if(isset($data['response'])) {
//                if($data['response']['nomeArquivo'] !== '6763 - KLEBSON SANTOS SILVA.zip') {
//
//                    if(! Storage::disk('public2')->exists($data['response']['nomeArquivo'])) {
//                        Storage::disk('public2')->put($data['response']['nomeArquivo'], base64_decode($data['response']['envelopeContent']));
//                    }
//
//                }
//
//
//            }
//        }


//        $array = [
//            'Ana Paula Andrade',
//            'Eduardo Alves de Lima',
//            'Elenilda Pereira',
//            'Filipe de Carvalho',
//            'Geony de Sousa',
//            'Jaqueline Ferreira',
//            'Joao Victor Alves',
//            'Jordelino Rodrigues',
//            'Luiza de Oliveira',
//            'Mateus Lisboa'
//        ];
//
//        $collaborator = Collaborator::whereIn('nome', $array)->whereTipoComissaoId(2)->get(['id']);
//        $metas = new CollaboratorMeta();
//
//        foreach($array as $k => $v) {
//
//
//            $collaborator = Collaborator::where('nome', 'like', '%'.$v.'%')->whereTipoComissaoId(2)->first('id');
//
//            $metas = CollaboratorMeta::where('colaborador_id', $collaborator->id)->whereMesCompetencia('10')->delete();
//            $metas = new CollaboratorMeta();
//
//            $metas->create([
//                'colaborador_id' => $collaborator->id,
//                'mes_competencia' => '10',
//                'meta' => 16.5,
//                'modified_by' => 1
//            ]);
//
//
//        }
//
//
//
//
//        $metas = new CollaboratorMeta();
//
//        foreach($collaborator as $k => $v) {
//
//        }
//        $users = UserLdap::limit(10)->get(['name']);
//
//        return $users;
//
//        $result = [];
//
//        foreach($users as $key => $val) {
//            $result[] = $val->name;
//        }
//
//        return $result;



//        $query = $request->input('query');
//
//        $query = Str::replaceFirst('#', $request->input('first'), $query);
//        $query = Str::replaceLast('#', $request->input('last'), $query);
//
//        return $query;
//
//        $result = DB::connection('mysql')->select($query);
//
//        return $result;

    }


    private function calcFirstAndSecondMonth($date, $value, $contractId)
    {
        $dateFormatted = Carbon::parse($date);
        $date = Carbon::parse($date);
        $countDays = intval($dateFormatted->format('t'));


        $dateCut = null;

        if($countDays > 30) {
            $dateCut = Carbon::parse('30-'.$dateFormatted->format('m').'-'.$dateFormatted->format('Y'))->format('Y-m-d');
        } else if($countDays <= 30) {
            $dateCut = Carbon::parse("$countDays-".$dateFormatted->format('m').'-'.$dateFormatted->format('Y'))->format('Y-m-d');
        }

        $diffDays = Carbon::parse($dateCut)->diffInDays($dateFormatted->subDay()->format('Y-m-d'));


        return [
          'first_month' => $this->getDiscount($contractId, $date, 1),//number_format((($value / 30) * $diffDays - $this->getDiscount($contractId, $date)), 2, '.', '.'),
            'second_month' => $this->getDiscount($contractId, $date, 2),  //$value - $this->getDiscount($contractId, $date->addMonth()),
            'activation_day' => $date->format('Y-m-d'),
            'cut_day' => $dateCut,
            'proportional_first_month' => $diffDays,
            'value_plan' => $value,
            'value_first_month' => [
                'value_total' => $value,
                'value_discount' => $this->getDiscount($contractId, $date, 1),
                'value_proportional' => number_format((($value / 30) * $diffDays - $this->getDiscount($contractId, $date, 1)), 2, '.', '.')
            ],
            'value_second_month' => (intval($value) + intval($this->getDiscount($contractId, $date, 2))),
        ];




        return [$date];
    }

    private function getDiscount($contractId, $date, $month = 0)
    {

        $dateFormatted = Carbon::parse($date);

        if($month === 1) {
            $dateFormatted = Carbon::parse($dateFormatted->format('y').'-'.$dateFormatted->format('m').'-01')->format('Y-m-d');

        } elseif ($month === 2) {
            $dateFormatted = Carbon::parse($dateFormatted->format('y').'-'.$dateFormatted->addMonth()->format('m').'-01')->format('Y-m-d');

        }


        $query = 'select sum(v_total_amount) from erp.contract_eventual_values cev where cev.contract_id = '.$contractId.' and month_year = \''.$dateFormatted.'\' and cev.deleted is false';

        $result = DB::connection('pgsql')->select($query);


        return $result[0]->sum;

    }

    public function response()
    {
        return true;
    }

    public function sellers($name, $sellers)
    {
        $sellers = collect($sellers);

        $sellers = $sellers->filter(function ($item) use($name) {
           if($name === $item->vendedor) {
               return $item;
           }
        });


        foreach($sellers as $k => $v) {
            return $v->vendas_vendedor;
        }

    }

    public function supervisors($name, $supervisors)
    {
        $supervisors = collect($supervisors);

        $supervisors = $supervisors->filter(function ($item) use($name) {
            if($name === $item->supervisor) {
                return $item;
            }
        });


        foreach($supervisors as $k => $v) {
            return $v->vendas_vendedor;
        }
    }

}
