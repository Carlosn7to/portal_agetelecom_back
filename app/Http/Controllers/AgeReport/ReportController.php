<?php

namespace App\Http\Controllers\AgeReport;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\AgeReport\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Psy\Util\Str;
use Symfony\Component\Console\Input\Input;

class ReportController extends Controller
{

    private $report;

    public function index()
    {
        return "index";
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {

        $report = new Report();

        $report = $report->create([
           'nome' => $request->input('name'),
           'nome_arquivo' => $request->input('namearchive'),
           'query' => $request->input('query'),
           'cabecalhos' => $request->input('headers'),
           'banco_solicitado' => $request->input('database'),
           'isPeriodo' => $request->input('isPeriod'),
           'isPeriodoHora' => $request->input('isPeriodHour'),
        ]);

        if(isset($report->id)) {
            return response()->json(['status' => true, 'msg' => 'Relatório criado com sucesso!'], 201);
        }
    }


    public function show($id)
    {
        $report = Report::find($id);

        if(isset($report->id)) {
            return response()->json(['status' => true, 'data' => $report], 200);
        } else {return response()->json(['status' => false, 'msg' => 'Nenhum relatório encontrado id:'.$id], 404);}
    }


    public function edit($id, Request $request)
    {

        $report = Report::find($id);


        $report->update([
           'nome' => $request->input('name'),
           'nome_arquivo' => $request->input('namearchive'),
           'query' => $request->input('query'),
           'cabecalhos' => $request->input('headers'),
           'banco_solicitado' => $request->input('database'),
           'isPeriodo' => $request->input('type') == 1 ? 1 : 0,
           'isPeriodoHora' => $request->input('type') == 2 ? 1 : 0,
        ]);

        $report = Report::find($id);

        return response()->json(['data' => $report, 'msg' => 'Relatório atualizado com sucesso!'], 201);

    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }

    public function download(Request $request, $id)
    {


        $this->report = Report::find($id);


        set_time_limit(2000);
        ini_set('memory_limit', '2048M');

        $query = $this->report->query;
        $this->paramns = json_decode($this->report->parametros);

        $this->headers = [];

        if($request->has('paramnsId')) {

            $paramnsId = $request->paramnsId;
            $paramnsMounted = '';


            foreach($paramnsId as $key => $value) {

                foreach($this->paramns as $k => $v) {

                    if($value == $v->id) {
                        $paramnsMounted .= $v->column . ' as ' . "\"$v->name\"";

                        // Verifica se não é o último item antes de adicionar a vírgula
                        if ($key < count($paramnsId) - 1) {
                            $paramnsMounted .= ', ';
                        }

                        $this->headers[] = $v->name;
                    }

                }
            }


        } else {
            $paramnsMounted = '';



               if($this->paramns !== null) {
                   foreach($this->paramns as $k => $v) {
                       $paramnsMounted .= $v->column . ' as ' . "\"$v->name\"";

                       // Verifica se não é o último item antes de adicionar a vírgula
                       if ($k < count($this->paramns) - 1) {
                           $paramnsMounted .= ', ';
                       }

                       $this->headers[] = $v->name;

                   }
               }
        }


        $this->report->query = str_replace('{{paramnsColumn}}', $this->paramns !== null ? $paramnsMounted : '*', $this->report->query);


        if($request->has('date')) {

            return $this->reportPeriod($request, 3);

        } elseif ($request->has('month') && $request->has('year')) {

            return $this->reportPeriod($request, 4);

        } elseif ($request->has('firstPeriod') && $request->has('lastPeriod')) {

            return $this->reportPeriod($request, 1);


        } else {
            return $this->report($this->report->query);
        }



    }

    private function reportPeriod($request, $type) {




        if($type === 1) {
            $firstPeriod = $request->has('firstPeriod') ? Carbon::parse($request->input('firstPeriod'))->format('Y-m-d') : null;
            $lastPeriod = $request->has('lastPeriod') ? Carbon::parse($request->input('lastPeriod'))->format('Y-m-d') : null;

            $query = \Illuminate\Support\Str::replaceFirst('paramTypeDate', 'DATE', $this->report->query);
            $query = \Illuminate\Support\Str::replaceFirst('paramTypeComparative', ' between ', $query);
            $query .= '\''.$request->input('firstPeriod').'\' and \''.$request->input('lastPeriod').'\'';


        } elseif ($type === 3) {

            $query = \Illuminate\Support\Str::replaceFirst('paramTypeDate', 'DATE', $this->report->query);
            $query = \Illuminate\Support\Str::replaceFirst('paramTypeComparative', ' = ', $query);
            $query .= '\''.$request->input('date').'\'';

        } elseif ($type === 4) {

            $query = \Illuminate\Support\Str::replaceFirst('paramTypeDate', 'DATE', $this->report->query);
            $query = \Illuminate\Support\Str::replaceFirst('paramTypeComparative', ' = ', $query);
            $query .= '\''.$request->input('date').'\'';

        }


        $result = DB::connection($this->report->banco_solicitado)->select($query);

        return $this->report($query);

    }

    private function report($query) {

        set_time_limit(3000);
        ini_set('memory_limit', '6144M');

        $i = substr_count($this->report->cabecalhos, ';');
        $headers = explode(';', $this->report->cabecalhos);
        $arrHeaders = [];

        for($x = 0; $i > $x; $x++) {
            $arrHeaders[] = $headers[$x];
        }

        $result = DB::connection($this->report->banco_solicitado)->select($query);


        return \Maatwebsite\Excel\Facades\Excel::download(new ReportExport($result, !empty($this->headers) ? $this->headers : $arrHeaders), $this->report->nome_arquivo);

    }
}
