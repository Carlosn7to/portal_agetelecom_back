<?php

use App\Models\AgeRv\AccessPermission as AccessPermissionAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




Route::middleware('LogAccess', \App\Http\Middleware\LogAccess::class)->group(function () {

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });


//    Route::get('billing-equip-divide', [\App\Http\Controllers\Mail\Billing\EquipDivideController::class, 'index']);
//    Route::get('billing-equip-divide/download', [\App\Http\Controllers\Mail\Billing\EquipDivideController::class, 'createPDF']);

    Route::group([
        'middleware' => 'api',
        'prefix' => 'auth'
    ], function ($router) {

        Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
        Route::post('login_ad', [\App\Http\Controllers\AuthController::class, 'login_ad']);
        Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
        Route::post('refresh', [\App\Http\Controllers\AuthController::class, 'refresh']);
        Route::post('me', [\App\Http\Controllers\AuthController::class, 'me']);
    });

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('teste', [\App\Http\Controllers\TestController::class, 'index']);

        Route::get('aniel/order', [\App\Http\Controllers\Aniel\Services\OrderServiceController::class,'importData']);


        Route::get('/validatedToken', function () {

            $access = null;
            $levelAccess = \App\Models\LevelAccess::
            where('id', auth()->user()->nivel_acesso_id)
                ->first();

            return [
                'levelAccess' => $levelAccess->nivel,
                'status' => true
            ];
        });

        Route::middleware('AccessAdmin')->prefix('admin')->group(function () {

            Route::resource('users', \App\Http\Controllers\UsersController::class);
            Route::get('access-systems', [\App\Http\Controllers\AccessSystemsController::class, 'getUsers']);
            Route::put('access-systems/alternate/{id}', [\App\Http\Controllers\AccessSystemsController::class, 'alternateAccess']);
            Route::get('reports-permitteds/{id}', [\App\Http\Controllers\AgeReport\ReportsPermittedsController::class, 'getReportsPermitteds']);
            Route::put('reports-permitteds/{id}', [\App\Http\Controllers\AgeReport\ReportsPermittedsController::class, 'edit']);
            Route::put('reports-permitteds/alternate/{iduser}/{idreport}', [\App\Http\Controllers\AgeReport\ReportsPermittedsController::class, 'alternateReportsPermitteds']);





            Route::prefix('datawarehouse')->group(function () {
                Route::prefix('voalle')->group(function() {
                    Route::resource('contracts', \App\Http\Controllers\DataWarehouse\Voalle\ContractsController::class);
                    Route::resource('contracts-type', \App\Http\Controllers\DataWarehouse\Voalle\ContractsTypeController::class);
                    Route::resource('peoples', \App\Http\Controllers\DataWarehouse\Voalle\PeoplesController::class);
                    Route::resource('peoples-address', \App\Http\Controllers\DataWarehouse\Voalle\PeopleAddressController::class);
                    Route::resource('authentication-contracts', \App\Http\Controllers\DataWarehouse\Voalle\AuthenticationContractsController::class);
                    Route::resource('service-products', \App\Http\Controllers\DataWarehouse\Voalle\ServiceProductsController::class);
                    Route::resource('contract-assignment-activations', \App\Http\Controllers\DataWarehouse\Voalle\ContractAssignmentActivationsController::class);
                    Route::resource('contract-service-tags', \App\Http\Controllers\DataWarehouse\Voalle\ContractsServiceTagsController::class);
                    Route::resource('requests-breaks', \App\Http\Controllers\DataWarehouse\Voalle\RequestAndBreaksController::class);
                });
            });
        });

        Route::resource('city', \App\Http\Controllers\CitysController::class);
        Route::resource('collaborators/group', \App\Http\Controllers\CollaboratorGroupsController::class);


        Route::middleware('AccessAgeReport')->prefix('agereport')->group(function () {

            Route::get('/Access', function () {

                $level = auth()->user()->nivel_acesso_id;

                if($level === 2 || $level === 3) {

                    return [
                        'levelAccess' => 'Admin',
                        'function' => 'Admin'
                    ];

                } else {
                    $accesPermissions = \Illuminate\Support\Facades\DB::table('agereport_usuarios_permitidos as up')
                        ->leftJoin('portal_colaboradores_funcoes as cf', 'up.funcao_id', '=', 'cf.id')
                        ->leftJoin('portal_nivel_acesso as na', 'up.nivel_acesso_id', '=', 'na.id')
                        ->where('user_id', auth()->user()->id)
                        ->select('cf.funcao', 'na.nivel')
                        ->first();
                    $access = null;
                    return [
                        'levelAccess' => $accesPermissions->nivel,
                        'function' => $accesPermissions->funcao
                    ];
                }
            });


            Route::resource('users-permiteds', \App\Http\Controllers\ReportApp\AccessUsersController::class);

            Route::resource('reports', \App\Http\Controllers\AgeReport\ReportController::class);
            Route::get('report-download/{id}', [\App\Http\Controllers\AgeReport\ReportController::class, 'download']);
            Route::get('report/reports', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'getAll']);
            Route::get('report/list-connections', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'list_connections']);
            Route::get('report/condominiums', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'condominiums']);
            Route::get('report/technical-control', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'technical_control']);
            Route::get('report/dici', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'dici']);
            Route::get('report/take-blip', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'takeBlip']);
            Route::get('report/base-clients', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'base_clients']);
            Route::get('report/sales', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'sales']);
            Route::get('report/contracts-assigments', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'contracts_assigments']);
            Route::get('report/totals-calls', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'totals_calls']);
            Route::get('report/contracts-so-opens', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'contratcs_so_open']);
            Route::get('report/teams-voalle', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'teams_voalle']);
            Route::get('report/contracts-address', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'contracts_address']);
            Route::get('report/human-care', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'human_care']);
            Route::get('report/new-assigments', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'new_assigments']);
            Route::get('report/base-clients-active', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'base_clients_active']);
            Route::get('report/productive-retenction', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'productive_retenction']);
            Route::get('report/contracts-seller', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'contracts_seller']);
            Route::get('report/against-evidence', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'against_evidence']);
            Route::get('report/leads-black', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'leads_black']);
            Route::get('report/monest', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'monest']);
            Route::get('report/renove', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'renove']);
            Route::get('report/receivables-clients', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'receivables_clients']);
            Route::get('report/good_payment', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'good_payment']);
            Route::get('report/local-fat', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'local_fat']);
            Route::get('report/financial-blockade', [\App\Http\Controllers\ReportApp\ReportAllController::class, 'financial_blockade']);
        });




        Route::middleware('AccessAgeBoard')->prefix('ageboard')->group(function () {

            Route::get('/Access', function () {
                $level = auth()->user()->nivel_acesso_id;

                if($level === 2 || $level === 3) {

                    return [
                        'levelAccess' => 'Admin',
                        'function' => 'Admin'
                    ];

                } else {
                    $accesPermissions = \Illuminate\Support\Facades\DB::table('ageboard_usuarios_permitidos as up')
                        ->leftJoin('portal_colaboradores_funcoes as cf', 'up.funcao_id', '=', 'cf.id')
                        ->leftJoin('portal_nivel_acesso as na', 'up.nivel_acesso_id', '=', 'na.id')
                        ->where('user_id', auth()->user()->id)
                        ->select('cf.funcao', 'na.nivel')
                        ->first();

                    $access = null;

                    return [
                        'levelAccess' => $accesPermissions->nivel,
                        'function' => $accesPermissions->funcao
                    ];
                }
            });

            Route::resource('dashboards', \App\Http\Controllers\AgeBoard\PermmittedsDashboardController::class);
            Route::resource('dashboard', \App\Http\Controllers\AgeBoard\DashboardController::class);
            Route::resource('dashboard-items', \App\Http\Controllers\AgeBoard\DashboardItemsController::class);
            Route::get('dashboard-items-management', [\App\Http\Controllers\AgeBoard\PermmittedsDashboardController::class, 'itemsPermittedsAndNot']);
            Route::put('dashboard-items-alternate', [\App\Http\Controllers\AgeBoard\PermmittedsDashboardController::class, 'itemsAlternateAccess']);

        });

        Route::middleware('AccessAgeControl')->prefix('agecontrol')->group(function () {

            Route::get('/Access', function () {
                $level = auth()->user()->nivel_acesso_id;

                if($level === 2 || $level === 3) {

                    return [
                        'levelAccess' => 'Admin',
                        'function' => 'Admin'
                    ];

                } else {
                    $accesPermissions = \Illuminate\Support\Facades\DB::table('agecontrol_usuarios_permitidos as up')
                        ->leftJoin('portal_colaboradores_funcoes as cf', 'up.funcao_id', '=', 'cf.id')
                        ->leftJoin('portal_nivel_acesso as na', 'up.nivel_acesso_id', '=', 'na.id')
                        ->where('user_id', auth()->user()->id)
                        ->select('cf.funcao', 'na.nivel')
                        ->first();

                    $access = null;

                    return [
                        'levelAccess' => $accesPermissions->nivel,
                        'function' => $accesPermissions->funcao
                    ];
                }
            });




        });


    });


});


Route::prefix('assine')->group(function() {
    Route::resource('/leads', \App\Http\Controllers\LeadsController::class);
    Route::resource('/cliques', \App\Http\Controllers\CliquesAssineController::class);
});

Route::prefix('indique')->group(function() {
    Route::post('/leads', [\App\Http\Controllers\AgeIndicate\LeadsController::class, 'store']);
});

//Route::get('teste-email', [\App\Http\Controllers\Mail\TestController::class, 'index']);
Route::get('validate-status-contract/{token}/{contractId}', [\App\Http\Controllers\Voalle\ContractFineController::class, 'getStatus']);



Route::post('open-door', [\App\Http\Controllers\AccessPort::class, 'index']);


Route::prefix('appClient/token/')->controller(\App\Http\Controllers\AgeCommunicate\AppClient\TokenController::class)->group(function() {
   Route::post('/', 'sendToken');
});
