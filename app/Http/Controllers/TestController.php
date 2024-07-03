<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Exports\UsersExport;
use App\Http\Controllers\AgeCommunicate\Base\Welcome\WelcomeController;
use App\Http\Controllers\AgeCommunicate\BillingRule\_aux\SendingWhatsapp;
use App\Http\Controllers\AgeCommunicate\BillingRule\BuilderController;
use App\Http\Controllers\AgeCommunicate\BlockedClients\BlockedClientsController;
use App\Http\Controllers\AgeCommunicate\Suspension\SuspensionController;
use App\Http\Controllers\AgeReport\NetworkManagement\NetworkManagementController;
use App\Http\Controllers\AgeRv\_aux\sales\analytics\Master;
use App\Http\Controllers\AgeRv\_aux\sales\Calendar;
use App\Http\Controllers\AgeRv\_aux\sales\Cancel;
use App\Http\Controllers\AgeRv\_aux\sales\Meta;
use App\Http\Controllers\AgeRv\_aux\sales\MetaPercent;
use App\Http\Controllers\AgeRv\_aux\sales\Sales;
use App\Http\Controllers\AgeRv\_aux\sales\Stars;
use App\Http\Controllers\AgeRv\_aux\sales\ValueStar;
use App\Http\Controllers\AgeRv\Builder\Result\b2b\Seller;
use App\Http\Controllers\AgeRv\VoalleSalesController;
use App\Http\Controllers\Aniel\Services\OrderServiceController;
use App\Http\Controllers\Aniel\Services\OrderServiceV2Controller;
use App\Http\Controllers\DataWarehouse\Voalle\PeoplesController;
use App\Http\Controllers\Ixc\Api\WebserviceClient;
use App\Http\Controllers\Mail\Billing\EquipDivideController;
use App\Http\Requests\AgeControl\ConductorStoreRequest;
use App\Ldap\UserLdap;
use App\Mail\AgeCommunicate\AppClient\SendToken;
use App\Mail\AgeCommunicate\Base\BlackNovember\SendBlackNovember;
use App\Mail\AgeCommunicate\Base\RA\SendRa;
use App\Mail\AgeCommunicate\Base\SCPC\SendSCPC;
use App\Mail\AgeCommunicate\Base\SendClientDay;
use App\Mail\AgeCommunicate\Base\SendMailBillingRule;
use App\Mail\AgeCommunicate\Base\Welcome\SendWelcomeRule;
use App\Mail\BaseManagement\SendPromotion;
use App\Mail\Portal\SendNewUser;
use App\Mail\SendBlackFiber;
use App\Mail\SendInvoice;
use App\Mail\SendMainUser;
use App\Mail\SendOutstandingDebts;
use App\Models\AgeBoard\AccessPermission;
use App\Models\AgeBoard\DashboardPermitted;
use App\Models\AgeBoard\ItemPermitted;
use App\Models\AgeCommunicate\Base\BillingRule\Sending;
use App\Models\AgeCommunicate\Base\BillingRule\Template;
use App\Models\AgeReport\Report;
use App\Models\AgeRv\Channel;
use App\Models\AgeRv\Collaborator;
use App\Models\AgeRv\CollaboratorMeta;
use App\Models\AgeRv\Commission;
use App\Models\AgeRv\Plan;
use App\Models\AgeRv\VoalleSales;
use App\Models\AgeTools\Tools\Mailer\Mailer;
use App\Models\AWS\Admin\Payroll;
use App\Models\Test;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
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

    private function getRange($metaPercent)
    {
        $range = 0;

        if ($metaPercent >= 70 && $metaPercent < 100) {
            $range = 1;
        } elseif ($metaPercent >= 100 && $metaPercent < 120) {
            $range = 2;
        } elseif ($metaPercent >= 120 && $metaPercent < 141) {
            $range = 3;
        } elseif ($metaPercent >= 141) {
            $range = 4;
        }

        return $range;

    }

    public function index(Request $request)
    {
        set_time_limit(2000000);

        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
        $dbPhoto = new Commission();
        $dbVoalle = new VoalleSales();

        $data = $dbPhoto->where('mes_competencia', '04')
            ->where('ano_competencia', '2024')
            ->get();

        $result = $data->pluck('id_contrato');
        $result = $result->unique();


        return response()->json($result);


        foreach($array[0] as $key => $value) {
            $result = $dbPhoto
                ->where('mes_competencia', '04')
                ->where('ano_competencia', '2024')
                ->where('id_contrato', $value[0])
                ->first();

            if(isset($result->id)) {
//                $update = $dbPhoto->find($result->id);
//
//                $update->update([
//                    'vendedor' => $value[1],
//                    'supervisor' => $value[2],
//                ]);
            } else {
                $voalleData = $dbVoalle->where('id_contrato', $value[0])->first();

                $dbPhoto->create([
                    'mes_competencia' => '04',
                    'ano_competencia' => '2024',
                    'id_contrato' => $voalleData->id_contrato,
                    'nome_cliente' => $voalleData->nome_cliente,
                    'supervisor' => $value[2],
                    'vendedor' => $value[1],
                    'status' => $voalleData->status,
                    'situacao' => $voalleData->situacao,
                    'data_contrato' => $voalleData->data_contrato,
                    'data_ativacao' => $voalleData->data_ativacao,
                    'data_vigencia' => $voalleData->data_vigencia,
                    'data_cancelamento' => $voalleData->data_cancelamento,
                    'plano' => $voalleData->plano
                ]);

            }


        }

        return true;

//        foreach ($array[0] as $key => $value) {
//
//            if(filter_var($value[0], FILTER_VALIDATE_EMAIL)) {
//                try {
//                        Mail::mailer('contact')->to($value[0])
//                            ->send(new SendClientDay());
//
//                } catch (\Exception $e) {
//                    // Armazena o e-mail e a mensagem de erro no array
//                    $error[] = $e->getMessage();
//                }
//
//            } else {
//                $erros[] = $value[0];
//            }
//
//
//        }
//
//        return $erros;

        $client = new Client();

        $data = [
            "CodigoIntegracao" => uniqid('TALK'),
            "NB" => "5561981069695",
            "DataInicio" => "2024-04-18 11:08:00",
            "Mensagem" => "Olá, estamos testando.",
            "ApiKey" => env('SMSTALK_API_KEY')
        ];


        // Faz a requisição POST usando o cliente Guzzle HTTP
        $response = $client->post('https://secure.talktelecom.com.br/api/EnvioSimples/EnviarJson', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $data
        ]);

        // Obtém o corpo da resposta
        $body = $response->getBody();

        return $body;


//        $client = new Client();
//
//        $data = [
//            "CodigoIntegracao" => uniqid('TALK'),
//            "NB" => "5561985034988",
//            "DataInicio" => "2023-01-30 16:13:00",
//            "Mensagem" => "Verificar spam - Teste Age",
//            "ApiKey" => env('SMSTALK_API_KEY')
//        ];
//
//
//        // Faz a requisição POST usando o cliente Guzzle HTTP
//        $response = $client->post('https://secure.talktelecom.com.br/api/EnvioSimples/EnviarJson', [
//            'headers' => [
//                'Content-Type' => 'application/json',
//            ],
//            'json' => $data
//        ]);
//
//        // Obtém o corpo da resposta
//        $body = $response->getBody();
//
//        return $body;


        return true;

//        $client = new Client();
//
//        $response = $client->post('https://agetelecom.http.msging.net/messages', [
//            'headers' => [
//                'Authorization' => 'Key b3BlcmFjYW9ub2NiMmI6QTZzQ3Z4WUlxbjZqQ2NvSU1JR1o=',
//                'Content-Type' => 'application/json',],
//            'json' => [
//                'id' => uniqid(),
//                'to' => '+5561984700440@sms.gw.msging.net',
//                'type' => 'text/plain',
//                'content' => 'AGE Telecom: Sua fatura já está disponível. Acesse através do portal da AGE: https://encr.pw/qv4Ed. Se já pagou, desconsidere.'
//            ]
//        ]);
//
//        $body = $response->getBody();
//
//        return $body;

//        $query = 'select c.id, p.email from erp.contracts c
//                    left join erp.people p on p.id = c.client_id
//                    where c.v_stage = \'Aprovado\' and c.v_status != \'Cancelado\' and c.id <= 77293
//                    order by c.id asc
//                    limit 1 ';
//        $result = DB::connection('pgsql')->select($query);
//
//        $result = collect($result);
//
//        $result = $result->unique('email');
//
//
//
//        try {
//            // Defina o número máximo de iterações por segcdundo
//            $maxIterationsPerSecond = 150;
//            $microsecondsPerSecond = 1000000;
//            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;
//
//            // Tempo inicial do loop
//            $starTime = microtime(true);
//
//            foreach($result as $key => $value) {
//
//                try {
//                    if (filter_var($value->email, FILTER_VALIDATE_EMAIL)) {
//
//
//                        $mail = Mail::mailer('contact')->to('carlos.neto@agetelecom.com.br')
//                            ->send(new SendClientDay());
//
//                    }
//                } catch (\Exception $e) {
//                    $e;
//                }
//
//            }
//
//
////                Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
//            $elapsedTime = microtime(true) - $starTime;
//            $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
//            if ($remainingMicroseconds > 0) {
//                usleep($remainingMicroseconds);
//            }
//
//            // Atualiza o tempo inicial para a próxima iteração
//            $starTime = microtime(true);
//        }
//        catch (\Exception $e) {
//            $e;
//        }
//
//        return count($result);






//

//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//        $client = new SendClientDay();


//        $welcome = new WelcomeController();
//
//        return $welcome->sendReport();
//
//        return true;


//        $client = new Client();
//
//
//        $os = [
//            916644, 916635, 916634, 916628, 916626, 916619, 916608, 916606, 916605, 916591,
//            916590, 916589, 916581, 916579, 916578, 916576, 916572, 916567, 916563, 916562,
//            916558, 916544, 916543, 916542, 916258, 916240, 916235, 916230, 916220, 916187,
//            916175, 916172, 916164, 916159, 916151, 916137, 916128, 916124, 916108, 916082,
//            916077, 916075, 916057, 916043, 916037, 916012, 916005, 916004, 916001, 915987,
//            915968, 915967, 915948, 915944, 915931, 915831, 915793, 915765, 915728, 915688,
//            915683, 915679, 914301, 915351, 915216, 915136, 915098, 914990, 914943, 914742,
//            914679, 914632, 914612, 914460, 914213, 914158, 914115, 914074, 914031, 912471,
//            895190, 905314, 909473, 911909
//        ];
//
//        foreach($os as $key) {
//            $client = new Client();
//
//            $data = [
//                "num_Obra_Original" => $key,
//                "settings" => [
//                    "user" => env('ANIEL_USER'),
//                    "password" => env('ANIEL_PASSWORD'),
//                    "token" => env('ANIEL_TOKEN'),
//                ]
//            ];
//
//
//            $client =  $client->post('https://cliente01.sinapseinformatica.com.br:4383/AGE/Servicos/API_Aniel/api/OsApiController/ConsultarOS', [
//                'json' => $data
//            ]);
//
//            $response = json_decode($client->getBody()->getContents());
//
//
//
//
//            $client = new Client();
//
//            $form = [
//                "os" => "916644",
//                "contrato" => "OP01",
//                "projeto" => "CASA CLIENTE",
//                "codigoCliente" => "48724",
//                "settings" => [
//                    "user" => env('ANIEL_USER'),
//                    "password" => env('ANIEL_PASSWORD'),
//                    "token" => env('ANIEL_TOKEN'),
//                ]
//            ];
//
//            $client =  $client->post('https://cliente01.sinapseinformatica.com.br:4383/AGE/Servicos/API_Aniel/api/OsApiController/ExcluirServico', [
//                'json' => $data
//            ]);
//
//            return json_decode($client->getBody()->getContents());
//
//
//        }
//
//
//        return true;

//        $os = new OrderServiceV2Controller();
//
//        return $os->store();

        $billing = new \App\Http\Controllers\AgeCommunicate\Base\BillingRule\BuilderController();

        return $billing->builder();

        $token = 'YWdldGVsZWNvbXJvdXRlcjpYdlBjZWNRaUs0VTdKT2RNT2VmdQ==';
        $userIdentity = '5561984700440@wa.gw.msging.net';


        $response = $client->request('POST', "https://agetelecom.http.msging.net/commands", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Key ' . $token,
            ],
            'json' => [
                'id' => uniqid(),
                'method' => 'get',
                'to' => 'postmaster@msging.net',
                'uri' => "/threads/{$userIdentity}?refreshExpiredMedia=true",
            ],
        ]);



        $response1 = json_decode($response->getBody(), true);


        $idReq = $response1['resource']['items'][0]['id'];

        return $response1;

        $client = new Client();

        $response = $client->request('POST', "https://agetelecom.http.msging.net/commands", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Key ' . $token,
            ],
            'json' => [
                'id' => uniqid(),
                'to' => 'postmaster@msging.net',
                'method' => 'get',
                'uri' => '/notifications?id='.$idReq.'',
            ],
        ]);


        return $response->getBody()->getContents();


        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json'
        ];

        $body = '{
            "codigo": "02703072228",
            "callerid": "xxxxxxxxxx",
            "token": "4d0e415e-0bd0-11ea-a050-90b11c2d743a"
        }';


        $response = $client->post('https://erp.agetelecom.com.br//pbx/pbx/events/default/CLIENT_VALIDATE', [
            'headers' => $headers,
            'body' => $body
        ]);

        $result = json_decode($response->getBody()->getContents(), true);


        $idClient = $result['clients'][0]['client_id'];

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization-Token' => '4d0e415e-0bd0-11ea-a050-90b11c2d743a'
        ];

        $body = '{
            "data_billets_from_contract": "110482",
            "data_billets_filter": false,
            "get_all_billets": false
        }';

        $response = $client->post('https://erp.agetelecom.com.br:443/api/api/events/get_billets_from_contract', [
            'headers' => $headers,
            'body' => $body
        ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return $result;

        $client = new Client();

        // Cria o array com os dados a serem enviados
        $data = [
            "id" => uniqid(),
            "to" => "+5561984700440@sms.gw.msging.net",
            "type" => "text/plain",
            "content" => "Ola \nteste"
        ];

        // Faz a requisição POST usando o cliente Guzzle HTTP
        $response = $client->post('https://agetelecom.http.msging.net/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Key b3BlcmFjYW9ub2NiMmI6QTZzQ3Z4WUlxbjZqQ2NvSU1JR1o='
            ],
            'json' => $data
        ]);

        // Obtém o corpo da resposta
        $body = $response;

        dd($body);


        return $body;

//        $b2b = new Seller();
//
//        $result = $b2b->response();
//
//        return $result;

        $order = new OrderServiceV2Controller();

        $result = $order->store();

        return $result;

        $client = new Client();
        $data = [
                "id" => uniqid(),
                "to" => "556199889451@wa.gw.msging.net",
                "type" => "application/json",
                "content" => [
                    "type" => "template",
                    "template" => [
                        "namespace" => "0c731938_5304_4f41_9ccf_f0942721dd48",
                        "name" => "black_november",
                        "language" => [
                            "code" => "PT_BR",
                            "policy" => "deterministic"
                        ],
                        "components" => [
                            [
                                "type" => "header",
                                "parameters" => [
                                    [
                                        "type" => "image",
                                        "image" => [
                                            "link" => "https://docs.google.com/uc?export=download&id=1JfTFYexsKL_2wdwDAKDvzDll4VNgqLHn"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

        $response = $client->post('https://agetelecom.http.msging.net/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
            ],
            'json' => $data
        ]);




        // Cria o array com os dados a serem enviados
        $data = [
            "id" => uniqid(),
            "to" => "postmaster@msging.net",
            "method" => "set",
            "uri" => "/contexts/556199889451@wa.gw.msging.net/Master-State",
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

        $data = [
            "id" => uniqid(),
            "to" => "postmaster@msging.net",
            "method" => "set",
            "uri" => "/contexts/556199889451@wa.gw.msging.net/stateid@684abf3b-a37b-4c29-bb28-4600739efde0",
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

        // Obtém o corpo da resposta
        $body = $response->getBody();

        return $body;

//        $import = new OrderServiceController();
//
//
//
//
//
//        $result = $import->__invoke();
//
//
//
//        return $result;
//
//        return false;


        $query = '
            SELECT
                c.id AS "contract_id",
                p.email AS "email",
                p.v_name AS "name",
                frt.document_amount,
                p.tx_id,
                CASE
                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
                    WHEN p.cell_phone_2 IS NOT NULL THEN p.cell_phone_2
                    ELSE p.phone
                END AS "phone",
                frt.typeful_line AS "barcode",
                frt.expiration_date AS "expiration_date",
                frt.competence AS "competence",
                case
                    when frt.expiration_date > current_date then -(frt.expiration_date - current_date)
                    else (current_date - frt.expiration_date)
                end as "days_until_expiration"
            FROM erp.contracts c
            LEFT JOIN erp.people p ON p.id = c.client_id
            LEFT JOIN erp.financial_receivable_titles frt ON frt.contract_id = c.id
            WHERE frt.deleted IS FALSE
                AND frt.finished IS FALSE
                AND frt.title LIKE \'%FAT%\'
                and frt.p_is_receivable is true
                and (current_date - frt.expiration_date) >= 324
            limit 20000
            ';

        // ultimo 44302

        $result = DB::connection('pgsql')->select($query);

        $result = collect($result);

        $result = $result->unique('contract_id');


//        $result = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));


        try {
            // Defina o número máximo de iterações por segcdundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

            // Tempo inicial do loop
            $starTime = microtime(true);

            foreach($result as $key => $value) {

                try {
                    if (filter_var($value->email, FILTER_VALIDATE_EMAIL)) {


                        $mail = Mail::mailer('sac')->to($value->email)
                            ->send(new SendBlackNovember());

                    }
                } catch (\Exception $e) {
                    $e;
                }

            }


//                Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
                $elapsedTime = microtime(true) - $starTime;
                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
                if ($remainingMicroseconds > 0) {
                    usleep($remainingMicroseconds);
                }

                // Atualiza o tempo inicial para a próxima iteração
                $starTime = microtime(true);
            }
            catch (\Exception $e) {
            $e;
        }

        return count($result);
//
//        $query = 'SELECT
//    a.title as Protocolo,
//    ai.protocol as Nº_protocolo,
//    vu.name as Atendente_Origem,
//    cs.title as Catalago_de_Servico,
//    csi.title as itens_de_serviço,
//    csc.title as Sub_item,
//    sp.title as Problema,
//    sc.title as Contexto,
//    a.beginning_date as data_abertura,
//    CASE EXTRACT(MONTH FROM a.beginning_date)
//    	WHEN 1 THEN \'Janeiro\'
//    	WHEN 2 THEN \'Fevereiro\'
//    	WHEN 3 THEN \'Março\'
//    	WHEN 4 THEN \'Abril\'
//    	WHEN 5 THEN \'Maio\'
//    	WHEN 6 THEN \'Junho\'
//    	WHEN 7 THEN \'Julho\'
//    	WHEN 8 THEN \'Agosto\'
//    	WHEN 9 THEN \'Setembro\'
//    	WHEN 10 THEN \'Outubro\'
//    	WHEN 11 THEN \'Novembro\'
//    	WHEN 12 THEN \'Dezembro\'
//	END as mes_abertura,
//	EXTRACT(YEAR FROM a.beginning_date) as ano_abertura,
//	p2.name as Cliente,
//	p2.id as ID_cliente,
//	c2.id as Nº_contrato,
//	c2.v_status as Status,
//	c2.v_stage as Situacao,
//	p2.neighborhood as Endereco,
//	p2.street as Rua, p2."number" as Nº
//from erp.assignments a
//   left join erp.assignment_incidents ai on ai.assignment_id = a.id
//   left join erp.incident_types it on it.id = ai.incident_type_id
//   left join erp.catalog_services cs on cs.id = ai.catalog_service_id
//   left join erp.catalog_services_items csi on csi.id = ai.catalog_service_item_id
//   left join erp.catalog_service_item_classes csc on csc.id = ai.catalog_service_item_class_id
//   left join erp.solicitation_problems sp on sp.id = ai.solicitation_problem_id
//   left join erp.solicitation_classifications sc on sc.id = ai.solicitation_classification_id
//   left join erp.people p on p.id = a.responsible_id
//   left join erp.people p2 on p2.id = a.requestor_id
//   left join erp.v_users vu on vu.id = a.created_by
//  JOIN erp.contract_service_tags AS cst ON ai.contract_service_tag_id = cst.id
//   JOIN erp.contracts AS c2 ON cst.contract_id = c2.id
//where it.id = 1068 limit 1000';
//
//        $result = DB::connection('pgsql')->select($query);
//
//        return $result;






        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));

        $headers = [];
        $result = [];


        foreach($array[0] as $key => $value) {

                if($key === 0) {
                    $headers = $value;
                } else {
                    $array[0][$key] = array_combine($headers, $value);


                    $result[] = [
                        "Contrato" => $value[0],
                        "Data Agendamento" => $value[1],
                        "Numero do Cliente" => $value[2],
                        "Protocolo" => $value[3],
                        "Bairro" => $value[4],
                        "CEP" => $value[5],
                        "CPF\/CNPJ" => $value[6],
                        "Cidade" => $value[7],
                        "Cliente" => $value[8],
                        "Complemento" => $value[9],
                        "Abertura" => $value[10],
                        "E-mail" => $value[11],
                        "Latitude" => $value[12],
                        "Endereço" => $value[13],
                        "Longitude" => $value[14],
                        "Numero" => $value[15],
                        "Período" => $value[16],
                        "Tel Celular" => $value[17],
                        "Tel Residencial" => $value[18],
                        "Tipo de Imovel" => $value[19],
                        "id" => $value[20],
                        "Tipo de Serviço" => $value[21],
                        "Node" => $value[22],
                        "Área de Despacho" =>$value[23],
                        "Observação" => strip_tags($value[24]),
                        "assigment_id" => $value[25]
                    ];
                }


        }

        return $result;


        $data = [];

        $client = new Client();


        foreach($result as $key => $value) {


        $addressFormatted = "{$value['Endereço']} {$value['Numero']} {$value['Bairro']} {$value['Cidade']}";

        $addressFormatted = str_replace(' ', '+', $addressFormatted);

        // Faz a requisição POST usando o cliente Guzzle HTTP
            $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address='.$addressFormatted.'&key=AIzaSyAU22qEwlrC4cLLyTAFviFZGBG3ZIrpCKM', [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);
        $body = $response->getBody();

        $response = json_decode($body);
        $result[$key]['Latitude'] =  $response->results[0]->geometry->location->lat;
        $result[$key]['Longitude'] = $response->results[0]->geometry->location->lng;

        }

        return $result;


        return $array;
//        $client = new Client();
//
//
//        $data = [
//            "id" => uniqid(),
//            "to" => "+5561981772148@sms.gw.msging.net",
//            "type" => "text/plain",
//            "content" => "http://tim-brasil.com/9y9egN-jAi8"
//        ];
//
//        // Faz a requisição POST usando o cliente Guzzle HTTP
//        $response = $client->post('https://agetelecom.http.msging.net/messages', [
//            'headers' => [
//                'Content-Type' => 'application/json',
//                'Authorization' => 'Key b3BlcmFjYW9ub2NiMmI6QTZzQ3Z4WUlxbjZqQ2NvSU1JR1o='
//            ],
//            'json' => $data
//        ]);
//
//        // Obtém o corpo da resposta
//        $body = $response->getBody();
//
//
//        return $body;
//
        $client = new Client();
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $options = [
                'grant_type' => 'client_credentials',
                'scope' => 'syngw',
                'client_id' => '7d787633-b8d5-45d3-84a1-714d6185399d',
                'client_secret' => '4e1ac5c5-14ef-49c0-ae14-c0e16e798489',
                'syndata' => 'TWpNMU9EYzVaakk1T0dSaU1USmxaalprWldFd00ySTFZV1JsTTJRMFptUT06WlhsS1ZHVlhOVWxpTTA0d1NXcHZhVTFxUVRKTWFrbDNUa00wZVU1RVozVlBSRmxwVEVOS1ZHVlhOVVZaYVVrMlNXMVNhVnBYTVhkTlJFRXdUMFJyYVdaUlBUMD06WlRoa01qTTFZamswWXpsaU5ETm1aRGczTURsa01qWTJZekF4TUdNM01HVT0='
            ];

        $response = $client->post('https://erp.agetelecom.com.br:45700/connect/token', [
            'headers' => $headers,
            'form_params' => $options
        ]);


        return $response->getBody();


        $json = $request->json('result');

        $base = [];
        $resume =  [];


        foreach($json as $key => $month) {


            foreach($month as $key2 => $month2) {

                $resume[] = ['reference' => $key2];
                $base[] = ['reference' => $key2, 'base' => []];

                foreach($month2['channels'] as $key3 => $collaborator) {


                    foreach($collaborator['collaborators'] as $key4 => $collab) {



                        if($collab['channel'] === 'Multi Canal de Vendas') {
                            $resume[$key]['name'] = $collab['name'];
                            $resume[$key]['sales'] = $collab['sales']['count'];
                            $resume[$key]['cancel'] = $collab['cancel']['count'];
                            $resume[$key]['meta'] = $collab['meta'];
                            $resume[$key]['metaPercent'] = $collab['metaPercent'];
                            $resume[$key]['valueStar'] = $collab['valueStar'];
                            $resume[$key]['stars'] = $collab['stars'];
                            $resume[$key]['mediator'] = $collab['mediator'];
                            $resume[$key]['commission'] = $collab['commission'];


                            foreach($collab['sales']['extract'] as $key5 => $sale) {


                                $base[$key]['base'][] = $sale;


                            }
                        }



                    }

                }

            }

        }




        foreach($base as $key => $sales) {

            foreach($sales['base'] as $k => $sale) {

                $base[$key]['base'][$k]['star'] = $this->stars($sale);
            }
        }

        return $base;



        return false;


        $seller = Commission::whereVendedor('khadija sousa santos')->get();

        $months = $seller->groupBy('mes_competencia')->map(function ($group) {

            $mes_competencia = $group->first()->mes_competencia;
            $ano_competencia = $group->first()->ano_competencia;


            $meta = CollaboratorMeta::where('colaborador_id', 2039)
                ->whereMesCompetencia($mes_competencia)
                ->whereAnoCompetencia($ano_competencia)
                ->first();

            $valueStar = new ValueStar($metaPercent->getMetaPercent(), 1, $mes_competencia, $ano_competencia);


            return [
                'month' => $group->first()->mes_competencia,
                'year' => $group->first()->ano_competencia,
//                'plans' => [
//                    'extract' => $group->map(function ($item) {
//                        return [
//                            'id_contrato' => $item->id_contrato,
//                            'status' => $item->status,
//                            'situacao' => $item->situacao,
//                            'data_contrato' => $item['data_contrato,
//                            'data_cancelamento' => $item->data_cancelamento,
//                            'plano' => $item['plano,
//                            'estrela' => $this->stars($item),
//                        ];
//                    })->values()->all()
//                ],
                'meta' => $meta ? $meta->meta : 0,
                'valor_estrelas' => $valueStar->getValueStar()
            ];
        })->values()->all();


        return $months;



        $calendar = new Calendar(false, $this->month, $this->year);
        $sales = new Sales('khadija sousa santos', 1, $seller, $calendar);
        $cancel = new Cancel($sales->getExtractData());
        $meta = new Meta(2039, $this->month, $this->year, null);
        $metaPercent = new MetaPercent($sales->getCountValids(), $meta->getMeta());
        $valueStar = new ValueStar($metaPercent->getMetaPercent(), 1, $this->month, $this->year);
        $stars = new Stars($sales->getExtractValids(), $calendar);
        $commission = new \App\Http\Controllers\AgeRv\_aux\sales\Commission(1, $valueStar->getValueStar(), $stars->getStars(),
            $cancel->getCountCancel(), $this->month, $this->year, $metaPercent->getMetaPercent());

        $data = [
            'name' => 'khadija sousa santos',
            'sales' => [
                'count' => $sales->getCountValids(),
                'extract' => $sales->getExtractSalesArray()
            ],
            'cancel' => [
                'count' => $cancel->getCountCancel(),
                'extract' => $cancel->getExtractCancel()
            ],
            'meta' => $meta->getMeta(),
            'metaPercent' => number_format($metaPercent->getMetaPercent(), 2, '.', '.'),
            'valueStar' => $valueStar->getValueStar(),
            'stars' => $stars->getStars(),
            'mediator' => $channelId !== 3 ? $cancel->getCountCancel() > 0 ? -10 : 10 : 0,
            'commission' => number_format($commission->getCommission(), 2, '.', '.')
        ];



        return false;

        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));

        return [
          count($array[0]),
          $array[0]
        ];

//        $client = new Client();
//
//        $verify_token = 'TWpNMU9EYzVaakk1T0dSaU1USmxaalprWldFd00ySTFZV1JsTTJRMFptUT06V2tkS2JHSllRWGROUkZFMFQxRTlQUT09OlpUaGtNak0xWWprMFl6bGlORE5tWkRnM01EbGtNalkyWXpBeE1HTTNNR1U9==';
//        $client_id = '8_x6hjpc9gb80c4co8k0sooookso4ko08ogs0oo8occss804kow';
//        $client_secret = '3r60l9qlg12cws04s088ook4coc8wogsg0wkkoskck4ks044ss';
//        $username = '07085594179';
//        $password = '07085594179';
//
//
//        $response = $client->get(
//            "http://erpapi.agetelecom.com.br/portal_authentication?" .
//            "verify_token=${verify_token}&" .
//            "client_id=${client_id}&" .
//            "client_secret=${client_secret}&" .
//            "grant_type=client_credentials&" .
//            "username=${username}&" .
//            "password=${password}"
//            ,[
//                'headers' => [
//                    'Content-Type' => 'application/json'
//                ]
//            ]);
//
//        return $response->getBody();


//
//        $result = [];
//
//        foreach($array[0] as $key => $value) {
//
//            $result[] = [
//                'OS' => $value[0],
//                'Endereço' => $value[1],
//                'Bairro' => $value[2],
//                'Cidade' => $value[3],
//                'CEP' => $value[4],
//                'Numero' => $value[5],
//                'T.Serviço' => $value[6],
//                'Periodo' => $value[7],
//                'Status' => $value[8],
//                'Localização' => $value[9],
//                'Latitude' => null,
//                'Longitude' => null
//            ];
//
//        }
//
//        $client = new Client();
//
//
//        foreach($result as $key => $value) {
//
//
//            $addressFormatted = "{$value['Endereço']} {$value['Numero']} {$value['Bairro']} {$value['Cidade']}";
//
//
//            $addressFormatted = str_replace(' ', '+', $addressFormatted);
//
//            // Faz a requisição POST usando o cliente Guzzle HTTP
//            $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address='.$addressFormatted.'&key=AIzaSyAU22qEwlrC4cLLyTAFviFZGBG3ZIrpCKM', [
//                'headers' => [
//                    'Content-Type' => 'application/json'
//                ]
//            ]);
//            $body = $response->getBody();
//
//            $response = json_decode($body);
//
//
//            $result[$key]['Latitude'] =  $response->results[0]->geometry->location->lat;
//            $result[$key]['Longitude'] = $response->results[0]->geometry->location->lng;
//
//        }
//
//        return $result;


//        $client = new Client();
//
//        $options = [
//            'form_params' => [
//                'grant_type' => 'client_credentials',
//                'scope' => 'syngw',
//                'client_id' => '8_x6hjpc9gb80c4co8k0sooookso4ko08ogs0oo8occss804kow',
//                'client_secret' => '3r60l9qlg12cws04s088ook4coc8wogsg0wkkoskck4ks044ss',
//                'syndata' => 'TWpNMU9EYzVaakk1T0dSaU1USmxaalprWldFd00ySTFZV1JsTTJRMFptUT06WlhsS1ZHVlhOVWxpTTA0d1NXcHZhVTFxUVRKTWFrbDNUa00wZVU1RVozVlBSRmxwVEVOS1ZHVlhOVVZaYVVrMlNXMVNhVnBYTVhkTlJFRXdUMFJyYVV4RFNrVlpiRkkxWTBkVmFVOXBTbmRpTTA0d1dqTktiR041U2prPTpaVGhrTWpNMVlqazBZemxpTkRObVpEZzNNRGxrTWpZMll6QXhNR00zTUdVPQ'
//            ]];
//
//        $response = $client->post('https://erp.agetelecom.com.br/:45700/connect/token', [
//                'headers' => [
//                    'Content-Type' => 'application/x-www-form-urlencoded'
//                ],
//                'data' => $options
//            ]);
//
//        return $response;


//        $query = 'SELECT
//                c.contract_id  AS "contract_id",
//                p.tx_id as "cpf",
//                p.email AS "email",
//                p.v_name AS "name",
//                CASE
//                    WHEN p.cell_phone_1 IS NOT NULL THEN p.cell_phone_1
//                    ELSE p.cell_phone_2
//                END AS "phone",
//                frt.typeful_line AS "barcode",
//                frt.title_amount as "value",
//                frt.expiration_date AS "expiration_date",
//                frt.competence AS "competence",
//                case
//                    when frt.expiration_date > current_date then -(frt.expiration_date - current_date)
//                    else (current_date - frt.expiration_date)
//                end as "days_until_expiration"
//            FROM datawarehouse.dwd_contracts c
//            LEFT JOIN datawarehouse.dwd_people p ON p.people_id = c.client_id
//            LEFT JOIN datawarehouse.dwf_financial_receivable_titles frt ON frt.contract_id = c.contract_id
//            WHERE
//                c.v_stage = \'Aprovado\'
//                AND frt.deleted IS FALSE
//                AND frt.finished IS FALSE
//                AND frt.title LIKE \'%FAT%\'
//                and frt.p_is_receivable is true
//                and (current_date - frt.expiration_date) >= 30';
//
//
//        $result = DB::connection('voalle_dw')->select($query);
//
//        $data = collect($result);
//
//        $building = $data->groupBy('cpf')->map(function ($group) {
//            return [
//                'cpf' => $group->first()->cpf,
//                'name' => $group->first()->name,
//                'email' => $group->first()->email,
//                'debits' => $group->map(function ($item) {
//                    return [
//                        'contractClient' => $item->contract_id,
//                        'date' => $item->expiration_date,
//                        'value' => number_format($item->value, 2, ',', '.')
//                    ];
//                })->all()
//            ];
//        })->values()->all();
//
//
//        try {
//            // Defina o número máximo de iterações por segundo
//            $maxIterationsPerSecond = 150;
//            $microsecondsPerSecond = 1000000;
//            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;
//
//            // Tempo inicial do loop
//            $starTime = microtime(true);
//
//            foreach($building as $key => $value) {
//
//                try {
//                    if (filter_var($value['email'], FILTER_VALIDATE_EMAIL)) {
//
//
//                        $mail = Mail::mailer('warning')->to($value['email'])
//                            ->send(new SendSCPC($value['name'], $value['cpf'], $value['debits']));
//
//                    }
//                } catch (\Exception $e) {
//                    $e;
//                }
//
//            }
//
//
////                Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
//                $elapsedTime = microtime(true) - $starTime;
//                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
//                if ($remainingMicroseconds > 0) {
//                    usleep($remainingMicroseconds);
//                }
//
//                // Atualiza o tempo inicial para a próxima iteração
//                $starTime = microtime(true);
//            }
//            catch (\Exception $e) {
//            $e;
//        }
//


        return "break";


//
//        $result = new Seller();
//
//
//        return $result->response();
//
//
//
//
//        return "b2b";



//        Mail::mailer('warning')->to('carlos.neto@agetelecom.com.br')
//            ->send(new SendSCPC('carlos net', '5156456', '454584864', 'rua tal', '123456', ['FAT', 'R$ 100,00', '10/10/2021']));
//
//




        foreach($array[0] as $key => $value) {


                try {
                // Defina o número máximo de iterações por segundo
                $maxIterationsPerSecond = 150;
                $microsecondsPerSecond = 1000000;
                $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

                // Tempo inicial do loop
                $starTime = microtime(true);

                    if (filter_var($value[0], FILTER_VALIDATE_EMAIL)) {

                        Mail::mailer('sac')->to($value[0])
                            ->send(new SendRa());

                    }

                    //                Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
                    $elapsedTime = microtime(true) - $starTime;
                    $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
                    if ($remainingMicroseconds > 0) {
                        usleep($remainingMicroseconds);
                    }

                    // Atualiza o tempo inicial para a próxima iteração
                    $starTime = microtime(true);

            } catch (\Exception $e) {
                $e;
            }

        }

        return true;


//        $data = [];
//
//        foreach($array[0] as $key => $value) {
//            $data[] =
//                [
//                    'cpf' => $value[0],
//                    'nameClient' => $value[1],
//                    'contractClient' => $value[2],
//                    'email' => $value[5],
//                    'debits' => [],
//                    'address' => $value[8],
//                    'cnpj' => $value[7]
//                ];
//        }
//
//
//        foreach($data as $key => $value) {
//
//            foreach($array[0] as $k => $v) {
//
//                if($value['cpf'] === $v[0]) {
//                    $data[$key]['debits'][] = [
//                        'date' => $v[3],
//                        'value' => number_format($v[4], 2, ',', '.'),
//                        'financialNature' => $v[6]
//                    ];
//                }
//            }
//
//        }
//
//        try {
//            // Defina o número máximo de iterações por segundo
//            $maxIterationsPerSecond = 150;
//            $microsecondsPerSecond = 1000000;
//            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;
//
//            // Tempo inicial do loop
//            $starTime = microtime(true);
//
//            foreach($data as $key => $value) {
//                try {
//                    if (filter_var($value['email'], FILTER_VALIDATE_EMAIL)) {
//
//
//                        $mail = Mail::mailer('warning')->to($value['email'])
//                            ->send(new SendSCPC($value['nameClient'], $value['cpf'], $value['cnpj'], $value['address'], $value['contractClient'], $value['debits']));
//
//
//                    }
//                } catch (\Exception $e) {
//                    $e;
//                }
//
////                Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
//                $elapsedTime = microtime(true) - $starTime;
//                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
//                if ($remainingMicroseconds > 0) {
//                    usleep($remainingMicroseconds);
//                }
//
//                // Atualiza o tempo inicial para a próxima iteração
//                $starTime = microtime(true);
//            }
//
//        } catch (\Exception $e) {
//            $e;
//        }

        return "enviados";


//        dd($mail);


//        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));
//
//        $client = new Client();
//
//        foreach($array[0] as $key => $value) {
//
//            $data = [
//                "id" => uniqid(),
//                "to" => "55$value[0]@wa.gw.msging.net",
//                "type" => "application/json",
//                "content" => [
//                    "type" => "template",
//                    "template" => [
//                        "name" => "envio_carta_spc_1",
//                        "language" => [
//                            "code" => "pt_BR",
//                            "policy" => "deterministic"
//                        ],
//                        "components" => [
//                            [
//                                "type" => "header",
//                                "parameters" => [
//                                    [
//                                        "type" => "document",
//                                        "document" => [
//                                            "filename" => "Comunicado_SCPC.pdf",
//                                            "link" => "https://comunicascpc.s3.sa-east-1.amazonaws.com/Comunicado+SCPC.pdf"
//                                        ]
//                                    ]
//                                ]
//                            ]
//                        ]
//                    ]
//                ]
//            ];
//
//            // Faz a requisição POST usando o cliente Guzzle HTTP
//            $response = $client->post('https://agetelecom.http.msging.net/messages', [
//                'headers' => [
//                    'Content-Type' => 'application/json',
//                    'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
//                ],
//                'json' => $data
//            ]);
//
//            // Cria o array com os dados a serem enviados
//            $data = [
//                "id" => uniqid(),
//                "to" => "postmaster@msging.net",
//                "method" => "set",
//                "uri" => "/contexts/55$value[0]@wa.gw.msging.net/Master-State",
//                "type" => "text/plain",
//                "resource" => "contatoativoenviodefatura"
//            ];
//
//            // Faz a requisição POST usando o cliente Guzzle HTTP
//            $response = $client->post('https://agetelecom.http.msging.net/commands', [
//                'json' => $data,
//                'headers' => [
//                    'Content-Type' => 'application/json',
//                    'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
//                ]
//            ]);
//
//            $data = [
//                "id" => uniqid(),
//                "to" => "postmaster@msging.net",
//                "method" => "set",
//                "uri" => "/contexts/55$value[0]@wa.gw.msging.net/stateid@684abf3b-a37b-4c29-bb28-4600739efde0",
//                "type" => "text/plain",
//                "resource" => "dd01df6a-c228-40af-91d7-e5ef0c88a3b3"
//            ];
//
//            // Faz a requisição POST usando o cliente Guzzle HTTP
//            $response = $client->post('https://agetelecom.http.msging.net/commands', [
//                'json' => $data,
//                'headers' => [
//                    'Content-Type' => 'application/json',
//                    'Authorization' => env('AUTHORIZATION_WHATSAPP_BLIP')
//                ]
//            ]);
//
//            // Obtém o corpo da resposta
//            $body = $response->getBody();
//        }
//
//        return 'enviado whatsapp';


//        $report = Report::find(16);
//
//        $query = $report->query;
//
//        $paramns = json_decode($report->parametros);
//
//        $ids = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20];
//        $paramnsMounted = '';
//
//
//        foreach($ids as $key => $value) {
//
//            foreach($paramns as $k => $v) {
//
//                if($value === $v->id) {
//                    $paramnsMounted .= $v->column . ' as ' . "\"$v->name\"";
//
//                    // Verifica se não é o último item antes de adicionar a vírgula
//                    if ($key < count($ids) - 1) {
//                        $paramnsMounted .= ', ';
//                    }
//                }
//
//            }
//        }
//
//
//        $query = str_replace('{{paramnsColumn}}', $paramnsMounted, $query);
//
////        return $query;
//
//        $result = DB::connection($report->banco_solicitado)->select($query);


//        $mail = Mail::mailer('warning')->to('diegocliimaa4@gmail.com')
//                            ->send(new SendSCPC('Carlos Neto', '291.293.910-20', '22.931.021/0001-20', 'Rua Arniqueiras', '123456', 'FAT', 'R$ 100,00', '10/10/2021'));
//
//
//
//        return response()->json($mail, 202);


//        $client = new Client();
//
//        // Cria o array com os dados a serem enviados
//        $data = [
//            "id" => uniqid(),
//            "to" => "+55$value[0]@sms.gw.msging.net",
//            "type" => "text/plain",
//            "content" => "Ola \nteste"
//        ];
//
//        // Faz a requisição POST usando o cliente Guzzle HTTP
//        $response = $client->post('https://agetelecom.http.msging.net/messages', [
//            'headers' => [
//                'Content-Type' => 'application/json',
//                'Authorization' => 'Key b3BlcmFjYW9ub2NiMmI6QTZzQ3Z4WUlxbjZqQ2NvSU1JR1o='
//            ],
//            'json' => $data
//        ]);
//
//        // Obtém o corpo da resposta
//        $body = $response->getBody();

        dd($response);

        return $response;


        return true;

        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));

        try {
            // Defina o número máximo de iterações por segundo
            $maxIterationsPerSecond = 150;
            $microsecondsPerSecond = 1000000;
            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;

            // Tempo inicial do loop
            $starTime = microtime(true);

            foreach ($array[1] as $key => $value) {
                try {

                    if (filter_var($value[0], FILTER_VALIDATE_EMAIL)) {
                        $mail = Mail::mailer('notification')->to($value[0])
                            ->send(new SendOutstandingDebts($value[1]));


                    }
                } catch (\Exception $e) {
                    $e;
                }

//                 Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
                $elapsedTime = microtime(true) - $starTime;
                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
                if ($remainingMicroseconds > 0) {
                    usleep($remainingMicroseconds);
                }

                // Atualiza o tempo inicial para a próxima iteração
                $starTime = microtime(true);

            }
        } catch (\Exception $e) {
            $e;
        }

        return "Ok";


//        $query = 'select distinct p.email, c.id, p.v_name  from erp.people p
//                    left join erp.contracts c on c.client_id = p.id
//                    where c.v_stage = \'Aprovado\' and c.v_status != \'Cancelado\' and c.id > 85000';
//
//        $peoples = DB::connection('pgsql')->select($query);
//
//
//        $data = collect($peoples);
//
//        try {
//            // Defina o número máximo de iterações por segundo
//            $maxIterationsPerSecond = 150;
//            $microsecondsPerSecond = 1000000;
//            $microsecondsPerIteration = $microsecondsPerSecond / $maxIterationsPerSecond;
//
//            // Tempo inicial do loop
//            $starTime = microtime(true);
//
//            foreach ($data as $key => $value) {
//                try {
//
//                    if (filter_var($value->email, FILTER_VALIDATE_EMAIL)) {
//                        $mail = Mail::mailer('notification')->to($value->email)
//                            ->send(new SendMailBillingRule('manutence',
//                                '🔧 Aviso Importante: Manutenção Programada na Rede - 16/08/2023 🔧', $value->v_name, '0'));
//
//                    }
//                } catch (\Exception $e) {
//                    $e;
//                }
//
////                 Verifica o tempo decorrido e adiciona um atraso para controlar a velocidade do loop
//                $elapsedTime = microtime(true) - $starTime;
//                $remainingMicroseconds = $microsecondsPerIteration - ($elapsedTime * $microsecondsPerSecond);
//                if ($remainingMicroseconds > 0) {
//                    usleep($remainingMicroseconds);
//                }
//
//                // Atualiza o tempo inicial para a próxima iteração
//                $starTime = microtime(true);
//
//            }
//        } catch (\Exception $e) {
//            $e;
//        }
//


        return "ok";


        $array = \Maatwebsite\Excel\Facades\Excel::toArray(new \stdClass(), $request->file('excel'));

        $payroll = new Payroll();

        foreach($array[0] as $key => $value) {


            $excelBaseDate = Carbon::create(1900, 1, 1); // Data base do Excel

            $realDate = $excelBaseDate->addDays($value[6] - 2); // Subtrai 2 para ajustar para a data base do Excel


            if($value[0] === 93) {
                $excelDateTimeValue = $value[23]; // O valor serial de data e hora do Excel
                $excelBaseDate = Carbon::create(1900, 1, 1); // Data base do Excel

                $days = floor($excelDateTimeValue); // Parte inteira representa os dias
                $timeFraction = $excelDateTimeValue - $days; // Parte decimal representa a fração do dia

                $realDate = $excelBaseDate->addDays($days - 2); // Subtrai 2 para ajustar para a data base do Excel

                $totalSecondsInDay = 24 * 60 * 60;
                $seconds = round($timeFraction * $totalSecondsInDay); // Converte a fração em segundos

                $realDate->addSeconds($seconds);

                return $realDate->format('H:i:s');
            }

            $payroll->create([
                'codigo' => $value[0],
                'nome' => $value[1],
                'cargo' => $value[2],
                'salario' => $value[3],
                'adicional_30' => $value[5],
                'adicional_40' => $value[4],
                'data_admissao' => $realDate->format('Y-m-d'),
                'st' => $value[7],
                'data_st' => $value[8] !== '' ? $value[8] : 0,
                'observacao' => $value[9],
                'dias_atm' => $value[10],
                'descricao_dias_atm' => $value[11],
                'cid_atm' => $value[12],
                'dias_faltas' => $value[13],
                'descricao_dias_faltas' => $value[14],
                'dias_bh' => $value[15],
                'descricao_dias_bh' => $value[16],
                'observacao_2' => $value[17],
                'sabados_trabalhados' => $value[18],
                'descricao_sabados_trabalhados' => $value[19],
                'dias_va_extra' => $value[20],
                'descricao_horas_mais' => $value[21],
                'quantidade_va' => $value[22],
                'horas_sobreaviso' => $value[23],
                'horas_adn' => $value[24],
                'horas_extras_50' => $value[25],
                'horas_extras_100' => $value[26],
                'anuenio' => $value[27],
                'adc_condutor_autorizado' => $value[28],
                'placa_carro' => $value[29],
                'plano_saude_titular' => $value[30],
                'plano_saude_dependente' => $value[31],
                'plano_saude_desconto_total' => $value[32],
                'valor_va_mes_anterior' => $value[33],
                'calculo_desconto_va' => $value[34],
                'mensalidade_sindical' => $value[35],
                'desconto_avaria_veiculo' => $value[36],
                'banco' => $value[37]
            ]);
        }

        return $array[0];



        return true;


        $templates = [
            0 => [
                'template' => 'after_expiration_75d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 75
            ],
            1 => [
                'template' => 'after_expiration_80d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 80
            ],
            2 => [
                'template' => 'after_expiration_85d',
                'subject' => 'Seu CPF será negativado... evite isso!',
                'rule' => 85
            ],
            3 => [
                'template' => 'alert_suspencion',
                'subject' => 'Esse é o nosso último aviso! Não fique sem internet!',
                'rule' => 14
            ],
            4 => [
                'template' => 'delay_2d',
                'subject' => 'Aviso importante sobre sua internet!',
                'rule' => 2
            ],
            5 => [
                'template' => 'delay_6d',
                'subject' => 'ALERTA! Evite suspensões e bloqueios na sua internet Age Telecom',
                'rule' => 6
            ],
            6 => [
                'template' => 'missing_3d',
                'subject' => 'Fique atento! Faltam apenas 3 dias',
                'rule' => -3
            ],
            7 => [
                'template' => 'missing_4d',
                'subject' => 'Lembrete Importante: vencimento da sua fatura em 4 dias',
                'rule' => -4
            ],
            8 => [
                'template' => 'missing_5d',
                'subject' => 'Lembrete - Vencimento da sua fatura Age Telecom em 5 dias',
                'rule' => -5
            ],
            9 => [
                'template' => 'negative',
                'subject' => 'Essa é a sua chance de evitar a negativação do seu CPF',
                'rule' => 0
            ],
            10 => [
                'template' => 'suspended_sign',
                'subject' => '[ALERTA] Aviso de suspensão de sinal',
                'rule' => 15
            ],
            11 => [
                'template' => 'today',
                'subject' => 'Último dia! Pague seu boleto hoje.',
                'rule' => 0
            ],
            12 => [
                'template' => 'tomorrow',
                'subject' => 'É Amanhã! Evite juros e multas desnecessárias!',
                'rule' => -1
            ],
        ];

        foreach($templates as $key => $value) {

            Mail::mailer('notificacao')->to('carlos.neto@agetelecom.com.br')
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


    private function stars($item)
    {
        $star = 0;
        $item = $item;

        // Se o mês do cadastro do contrato for MAIO para trás, executa esse bloco.
        if (Carbon::parse($item['data_contrato']) < Carbon::parse('2022-06-01')) {

            // Verifica qual é o plano e atribui a estrela correspondente.
            if (str_contains($item['plano'], 'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA')) {
                $star = 5;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER PREMIUM')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 960 MEGA ')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 360 MEGA')) {
                $star = 11;
            } elseif (str_contains($item['plano'], 'PLANO 400 MEGA FIDELIZADO')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA - FIDELIZADO')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO 720 MEGA ')) {
                $star = 25;
            } elseif (str_contains($item['plano'], 'PLANO 740 MEGA FIDELIZADO')) {
                $star = 25;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA FIDELIZADO')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO 960 MEGA')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO 960 MEGA TURBINADO + AGE TV + DEEZER PREMIUM')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO + IP FIXO')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 800 MEGA FIDELIZADO')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 800 MEGA FIDELIZADO + IP FIXO')) {
                $star = 20;
            }

            // Se o mês do cadastro do contrato for JUNHO, executa esse bloco.
        } elseif (Carbon::parse($item['data_contrato']) < Carbon::parse('2022-07-01') &&
            Carbon::parse($item['data_contrato']) >= Carbon::parse('2022-06-01')) {

            // Verifica qual é o plano e atribui a estrela correspondente.
            if (str_contains($item['plano'], 'COMBO EMPRESARIAL 600 MEGA + 1 FIXO BRASIL SEM FIDELIDADE')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'COMBO EMPRESARIAL 600 MEGA + 2 FIXOS BRASIL SEM FIDELIDADE')) {
                $star = 13;
            } elseif (str_contains($item['plano'], 'PLANO 120 MEGA')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER PREMIUM')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 960 MEGA ')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA SEM FIDELIDADE')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 400 MEGA FIDELIZADO')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA - FIDELIZADO')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO 720 MEGA ')) {
                $star = 25;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA - COLABORADOR')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA FIDELIZADO')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO 960 MEGA')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO 960 MEGA TURBINADO + AGE TV + DEEZER PREMIUM')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 800 MEGA FIDELIZADO')) {
                $star = 17;
            }

            // Se o mês do cadastro do contrato for JULHO, executa esse bloco.
        } elseif (Carbon::parse($item['data_contrato']) < Carbon::parse('2022-08-01') &&
            Carbon::parse($item['data_contrato']) >= Carbon::parse('2022-07-01')) {

            // Verifica qual é o plano e atribui a estrela correspondente.
            if (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER + HBO MAX + DR. AGE')) {
                $star = 30;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER PREMIUM')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 120 MEGA SEM FIDELIDADE')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA ')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER PREMIUM')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA + DEEZER PREMIUM SEM FIDELIDADE')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 400 MEGA - COLABORADOR')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 400 MEGA FIDELIZADO')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 400 MEGA NÃO FIDELIZADO')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA - FIDELIZADO')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA FIDELIZADO')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 740 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 740 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA - CAMPANHA CONDOMÍNIO FIDELIZADO (AMBOS)')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA - COLABORADOR')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA FIDELIZADO')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO 960 MEGA')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO 960 MEGA (LOJAS)')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO + DEEZER PREMIUM')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO + IP FIXO')) {
                $star = 38;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA SEM FIDELIDADE + IP FIXO')) {
                $star = 36;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO + IP FIXO')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 800 MEGA FIDELIZADO')) {
                $star = 15;
            }

            // Se o mês do cadastro do contrato for AGOSTO, executa esse bloco.
        } elseif (Carbon::parse($item['data_contrato']) >= Carbon::parse('2022-08-01') &&
            Carbon::parse($item['data_contrato']) < Carbon::parse('2023-08-01')) {

            // Verifica qual é o plano e atribui a estrela correspondente.
            if (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER + HBO MAX + DR. AGE')) {
                $star = 30;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER PREMIUM')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA NÃO FIDELIZADO + DEEZER PREMIUM')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER PREMIUM')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA  + DEEZER PREMIUM')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 400 MEGA FIDELIZADO')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA FIDELIZADO')) {
                $star = 7;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA NÃO FIDELIZADO')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 740 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA FIDELIZADO')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO + DEEZER PREMIUM')) {
                $star = 35;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO + IP FIXO')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 800 MEGA FIDELIZADO')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA HOTEL LAKE SIDE')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA FIDELIZADO + DIRECTV GO')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER + HBO MAX + DR. AGE + DIRECTV GO')) {
                $star = 22;
            } elseif (str_contains($item['plano'], 'PLANO 740 MEGA FIDELIZADO + DIRECTV GO')) {
                $star = 18;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA  FIDELIZADO + DEEZER PREMIUM + DIRECTV GO')) {
                $star = 20;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA  FIDELIZADO + DIRECTV GO')) {
                $star = 20;
            } elseif (str_contains($item['plano'], 'PLANO COLABORADOR 1 GIGA + DEEZER + HBO MAX + DR. AGE')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA NÃO FIDELIZADO')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO COLABORADOR 1 GIGA + DEEZER')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA NÃO FIDELIZADO + DEEZER PREMIUM')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA NÃO FIDELIZADO')) {
                $star = 0;
            }
        } elseif (Carbon::parse($item['data_contrato']) >= Carbon::parse('2023-08-01')) {
            // Verifica qual é o plano e atribui a estrela correspondente.
            if (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER PREMIUM + DIRECTV GO')) {
                $star = 20;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DIRECTV GO')) {
                $star = 20;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER + HBO MAX + DR. AGE + DIRECTV GO')) {
                $star = 22;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER + HBO MAX + DR. AGE')) {
                $star = 15;
            } elseif (str_contains($item['plano'], 'PLANO 1 GIGA FIDELIZADO + DEEZER PREMIUM')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO 120 MEGA')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 120 MEGA PROMOCAO LEVE 360 MEGA')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA PROMOCAO LEVE 720 MEGA + DEEZER PREMIUM')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'PLANO 240 MEGA LEVE 960 MEGA')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'PLANO 360 MEGA')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'PLANO 400 MEGA FIDELIZADO')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA FIDELIZADO + DIRECTV GO')) {
                $star = 17;
            } elseif (str_contains($item['plano'], 'PLANO 480 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO 740 MEGA FIDELIZADO + DIRECTV GO')) {
                $star = 18;
            } elseif (str_contains($item['plano'], 'PLANO 740 MEGA FIDELIZADO')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'PLANO 800 MEGA FIDELIZADO')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'PLANO COLABORADOR 1 GIGA + DEEZER')) {
                $star = 10;
            } elseif (str_contains($item['plano'], 'PLANO COLABORADOR 1 GIGA + DEEZER + HBO MAX + DR. AGE')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO')) {
                $star = 16;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA SEM FIDELIDADE')) {
                $star = 20;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO + DEEZER PREMIUM')) {
                $star = 16;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO')) {
                $star = 9;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA FIDELIZADO + IP FIXO')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 600 MEGA NÃO FIDELIZADO')) {
                $star = 0;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 800 MEGA FIDELIZADO')) {
                $star = 12;
            } elseif (str_contains($item['plano'], 'PLANO EMPRESARIAL 1 GIGA FIDELIZADO + IP FIXO')) {
                $star = 20;
            }
        }

        return $star;



    }

}
