<?php

namespace App\Http\Controllers\AgeTools\Tools\ServiceDesk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceDeskController extends Controller
{
    public function builder()
    {

    }

    public function getClient($cpf)
    {


        $cpfFiltered = preg_replace('/[^0-9]/', '', $cpf);

        $query = '
            select p.id, p."name", p.tx_id, p.email, p.cell_phone_1, p.cell_phone_2,
            (select pa.street || \' \' || pa."number" || \', \' || pa.city || \' - \' || pa.postal_code AS full_address from erp.people_addresses pa where pa.person_id = p.id order by id desc limit 1)
            from erp.people p where p.tx_id = \'' . $cpfFiltered . '\'
        ';

        $result = DB::connection('pgsql')->select($query);


        return $result;


    }

    public function getContract($clientId)
    {
        $clientId = $clientId;

        $query = '
            select c.id, c.v_status as status, c.v_stage as stage, c."date" as date_incluse, c.approval_date as date_approval,
                (select cst.title from erp.contract_service_tags cst where cst.contract_id = c.id order by cst.id desc limit 1) as plan
            from erp.contracts c
            where c.client_id = ' . $clientId . '
        ';

        $result = DB::connection('pgsql')->select($query);


        return $result;
    }

    public function getInfoConnection($contractId)
    {
        $contractId = $contractId;

        $query = '
                select ac.equipment_serial_number as serial, ac.wifi_name, ac.wifi_password, as2.title as pop_olt, aap.title as olt, ac.slot_olt, ac.port_olt,
                    DATE(ac.created) as date_activation, DATE(ac.modified) as date_modified from
                erp.authentication_contracts ac
                left join erp.authentication_access_points aap on aap.id = ac.authentication_access_point_id
                left join erp.authentication_sites as2 on as2.id = aap.authentication_site_id
                where ac.contract_id = ' . $contractId . '
        ';

        $result = DB::connection('pgsql')->select($query);


        return $result;
    }
}
