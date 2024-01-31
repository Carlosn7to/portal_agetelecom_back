<?php

namespace App\Http\Controllers\AgeCommunicate\BlockedClients;

use App\Http\Controllers\Controller;
use App\Mail\AgeCommunicate\BlockedClients\SendBlockedClients;
use App\Mail\AgeCommunicate\Suspension\SendSuspencion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BlockedClientsController extends Controller
{

    public function __invoke()
    {
        return $this->response();
    }

    public function response()
    {

        $response = $this->sendEmail();

        return $response;

    }

    private function getData()
    {
        $result = DB::connection('pgsql')->select($this->getQuery());

        $data = new Collection();

        foreach($result as $key => $value) {
            $data->push([
                'date' => $value->date,
                'hour' => $value->hour,
                'units' => $value->units
            ]);
        }

        $data = $data->groupBy('hour')->map(function ($items) {
            return [
                'date' => Carbon::parse($items->first()['date'])->format('d/m/Y'),
                'hour' => $items->first()['hour'].':00',
                'units' => $items->sum('units')
            ];
        })->values();

        return $data->sortBy('hour', SORT_REGULAR, true);

    }

    private function sendEmail()
    {
        $to = 'bloqueio@agetelecom.com.br';
        $data = $this->getData();

        $mail = Mail::mailer('portal')->to($to)->send(new SendBlockedClients($data));


    }


    private function getQuery()
    {

        $query = '
            select distinct ce."date", extract(HOUR FROM ce."date" + INTERVAL \'30 seconds\') as hour, count(*) as units from erp.contract_events ce
                left join erp.contracts c on c.id = ce.contract_id
                --left join erp.contract_event_types cet on cet.id = ce.contract_event_type_id
                --left join erp.people p on p.id = c.client_id
                where ce.contract_event_type_id = 40
                group by ce."date"
        ';

        return $query;

    }

}
