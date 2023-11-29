<?php

namespace App\Http\Controllers\Voalle;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractFineController extends Controller
{


    public function getStatus($token, $contractId)
    {

        if($token !== 'FiU%4HdzsLJ5yRAD$j%B5uLVV8nJhAor') {
            return response()->json('Token inválido', 401);
        }

        set_time_limit(200000000);

        $contract = $contractId;

        $query = 'select
            f.contract_id,
            p.tx_id as "CPF_CNPJ",
            ROUND((((c.billing_final_date - CURRENT_DATE) / 30.0) * 950/12), 2) as "MULTA",
            current_date - f.expiration_date as "Dias_Vencimento",
            c.cancellation_date,
            c.v_stage,
            c.v_status
        from erp.financial_receivable_titles f
        inner join erp.people p on p.id = f.client_id
        inner join erp.contracts c on c.id = f.contract_id
        where f.title like \'%FAT%\'
        and c.billing_final_date not between to_date(\'01/01/2050\', \'DD/MM/YYYY\') and to_date(\'31/12/2050\', \'DD/MM/YYYY\')
        and f.deleted != \'TRUE\'
        and not exists (select * from erp.financial_receipt_titles t
                        where f.id = t.financial_receivable_title_id)
        and current_date - f.expiration_date >= 90
        and c.v_status != \'Cancelado\'
        and c.id = '.$contract.'
        and ROUND((((c.billing_final_date - CURRENT_DATE) / 30.0) * 950/12), 2) > 0
        order by 4 desc';


        $result = DB::connection('pgsql')->select($query);

        if(! empty($result)) {
            return response()->json(true, 200);
        } else {
            return response()->json(false, 200);
        }
    }
}
