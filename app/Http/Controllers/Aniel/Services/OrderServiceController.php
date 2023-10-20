<?php

namespace App\Http\Controllers\Aniel\Services;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Mail\Aniel\Services\SendOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderServiceController extends Controller
{
    public function __invoke()
    {
        return $this->importData();
    }


    private function importData()
    {

        $query = $this->getQuery();

        $result = DB::connection('pgsql')->select($query);

        $excel = \Maatwebsite\Excel\Facades\Excel::download(new ReportExport($result,['id']), 'importacao_ordens.xlsx');

        $mail = Mail::mailer('portal')
            ->to('carlos.neto@agetelecom.com.br')
            ->send(new SendOrders());

        return $mail;

    }

    private function getQuery () {


        $query = 'select id from erp.contracts limit 10';

        return $query;

    }
}
