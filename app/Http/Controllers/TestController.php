<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Exports\UsersExport;
use App\Http\Controllers\AgeRv\_aux\sales\Stars;
use App\Models\AgeReport\Report;
use App\Models\AgeRv\Channel;
use App\Models\AgeRv\Collaborator;
use App\Models\AgeRv\CollaboratorMeta;
use App\Models\AgeRv\VoalleSales;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LdapRecord\Auth\BindException;
use LdapRecord\Connection;
use Maatwebsite\Excel\Excel;

class TestController extends Controller
{
    protected $year;
    protected $month;
    private $salesTotals;


    public function index(Request $request)
    {

//        $report = Report::find(14);
//
//        $query = str_replace('\\', '', $report->query);
//
//        $array = [];
//


        //$result = DB::connection($report->banco_solicitado)->select($query);

        $query2 = 'SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_NAME = \'Eventos\'';

        $result = DB::connection('mysql_take')->select($query2);

        return $result;


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


}
