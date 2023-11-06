<?php

namespace App\Http\Controllers\Aniel\Services;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Mail\Aniel\Services\SendOrders;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderServiceController extends Controller
{
    public function __invoke()
    {
        return $this->importData();
    }


    public function importData()
    {
        set_time_limit(20000);



        $query = $this->getQuery();



        $result = DB::connection('pgsql')->select($query);
        $addressFormatted = '';
        $client = new Client();



        foreach($result as $key => $value) {


            $query = 'select DATE(s.start_date) from erp.schedules s where s.assignment_id = '.$value->assignment_id.' order by s.id desc limit 1';

            $consult = DB::connection('pgsql')->select($query);


            if(!empty($consult)) {
                $result[$key]->Data_Agendamento = $consult[0]->date;
            } else {
                $result[$key]->Data_Agendamento = null;
            }

        }




        foreach($result as $key => $value) {


            $addressFormatted = "$value->Endereço $value->Numero $value->Bairro $value->Cidade";
            $addressFormatted = str_replace(' ', '+', $addressFormatted);

            $inc = $value->incident_type_id;


           if($inc === 1020 || $inc === 1086 || $inc === 1011) {
               // Faz a requisição POST usando o cliente Guzzle HTTP
               $response = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address='.$addressFormatted.'&key=AIzaSyAU22qEwlrC4cLLyTAFviFZGBG3ZIrpCKM', [
                   'headers' => [
                       'Content-Type' => 'application/json'
                   ]
               ]);
               $body = $response->getBody();

               $response = json_decode($body);

               if(!empty($response->results)) {
                   $value->Latitude =  $response->results[0]->geometry->location->lat;
                   $value->Longitude = $response->results[0]->geometry->location->lng;
               } else {
                   $result[$key]->Latitude = null;
                   $result[$key]->Longitude = null;
               }
           }

        }

        return $result;



        $headers  = [
            'Contrato',
            'Data Agendamento',
            'Numero do Cliente',
            'Protocolo',
            'Bairro',
            'CEP',
            'CPF/CNPJ',
            'Cidade',
            'Cliente',
            'Complemento',
            'Abertura',
            'E-mail',
            'Latitude',
            'Endereço',
            'Longitude',
            'Numero',
            'Período',
            'Tel Celular',
            'Tel Residencial',
            'Tipo de Imovel',
            'Tipo de Serviço',
            'Área de Despacho',
        ];



        return \Maatwebsite\Excel\Facades\Excel::download(new ReportExport($result, $headers), 'import_orders.xlsx');




    }

    private function getQuery () {

        $hour = Carbon::now();



        $query = '
        select distinct
                coalesce(contract_service_tags.contract_id,\'000000\') as "Contrato",
                coalesce(contract_service_tags.contract_id,\'000000\') as "Contrato",
                TO_CHAR(assignment_incidents.responsible_final_date::DATE,\'YYYY-MM-DD\') as "Data_Agendamento",
                assignments.requestor_id as "Numero do Cliente",
                assignment_incidents.protocol as "Protocolo",
                cliente.neighborhood AS "Bairro",
                cliente.postal_code AS "CEP",
                cliente.tx_id as "CPF/CNPJ",
                cliente.city  AS "Cidade",
                cliente.name as "Cliente",
                cliente.address_complement AS "Complemento",
                TO_CHAR(assignments.beginning_date::DATE,\'YYYY-MM-DD\') as "Abertura",
                people.email as "E-mail",
                people.lat as "Latitude",
                cliente.street  AS "Endereço",
                people.lng as "Longitude",
                cliente.number AS "Numero",
                 case
                    when extract (hour from assignment_incidents.responsible_final_date) between 6 and 11 then\'Manhã\'
                    when extract (hour from assignment_incidents.responsible_final_date) between 12 and 19 then \'Tarde\'
                    when assignment_incidents.responsible_final_date is null then \'SEM TURNO MARCADO\'
                    ELSE \'Noite\'
                  END AS "Período",
                cliente.cell_phone_1 as "Tel Celular",
                cliente.phone as  "Tel Residencial",
                \'INDIFERENTE\' as "Tipo de Imovel",
                incident_types.title as "Tipo de Serviço",
                \'DISTRITO FEDERAL\' as "Área de Despacho",
                a.id as "assignment_id",
                incident_types.id as "incident_type_id"
                from erp.assignments
                inner join erp.assignment_incidents on (assignment_incidents.assignment_id = assignments.id )
                inner join erp.incident_types on (incident_types.id = assignment_incidents.incident_type_id)
                inner join erp.incident_status on (assignment_incidents.incident_status_id = incident_status.id)
                left join erp.people cliente ON (cliente.id = assignment_incidents.client_id)
                left join erp.solicitation_classifications on (solicitation_classifications.id = assignment_incidents.solicitation_classification_id)
                left join erp.solicitation_problems on (assignment_incidents.solicitation_problem_id = solicitation_problems.id)
                left join erp.contract_service_tags on (assignment_incidents.contract_service_tag_id = contract_service_tags.id)
                left join erp.authentication_contracts on (authentication_contracts.service_tag_id = contract_service_tags.id)
                inner join erp.people on (assignments.requestor_id = people.id)
                left join erp.contracts on (contracts.client_id = people.id)
                inner join erp.assignments a on a.id = assignments.id
                where incident_types.active = \'1\' and assignments.deleted = \'0\' and incident_types.deleted = \'0\'
                and TO_CHAR( assignments.final_date, \'%Y-%m-%d\' ) <> \'0000-00-00\' and people.deleted = \'0\'
                and TO_CHAR( assignment_incidents.responsible_final_date, \'%Y-%m-%d\' ) <> \'0000-00-00\' and people.deleted = \'0\'
                and incident_status.id <> \'8\'
                and DATE(a.created) >= \'2023-10-20\'
                and incident_types.id in (\'1074\', \'1090\', \'1080\', \'1081\', \'1082\', \'1088\', \'1071\', \'1087\',\'1058\',\'1067\', \'1036\', \'1091\', \'1094\', \'1011\', \'1026\', \'1027\', \'1028\', \'1029\',\'1086\',\'1086\',\'1020\')
                order by 2 desc';


        return $query;



    }
}
