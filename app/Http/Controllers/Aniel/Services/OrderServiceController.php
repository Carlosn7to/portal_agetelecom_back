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


    public function importData(Request $request)
    {
        set_time_limit(20000);

        $this->req = $request;


        $query = $this->getQuery();





        $result = DB::connection('pgsql')->select($query);
        $addressFormatted = '';
        $client = new Client();



        foreach($result as $key => $value) {


            $query = 'select DATE(s.start_date), EXTRACT(HOUR FROM s.start_date) as "hour" from erp.schedules s where s.assignment_id = '.$value->assignment_id.' order by s.id desc limit 1';

            $consult = DB::connection('pgsql')->select($query);


            if(!empty($consult)) {
//                $result[$key]->Data_Agendamento = $consult[0]->date;



                if($consult[0]->hour >= '06' && $consult[0]->hour < '12') {
                    $result[$key]->Periodo = 'Manhã';
                } else if($consult[0]->hour >= '12' && $consult[0]->hour < '18') {
                    $result[$key]->Periodo = 'Tarde';
                } else if($consult[0]->hour >= '18' && $consult[0]->hour < '23') {
                    $result[$key]->Periodo = 'Noite';
                } else {
                    $result[$key]->Periodo = 'SEM TURNO MARCADO';
                }

            } else {
//                $result[$key]->Data_Agendamento = null;
            }


            unset($result[$key]->assignment_id);
        }


        foreach($result as $key => $value) {


            $addressFormatted = "$value->Endereço $value->Numero $value->Bairro $value->Cidade";
            $addressFormatted = str_replace(' ', '+', $addressFormatted);

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


        $headers  = [
            'Contrato',
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
            'Tel Celular',
            'Tel Residencial',
            'Tipo de Imovel',
            'Tipo de Serviço',
            'Node',
            'Área de Despacho',
            'Observação',
            'Grupo',
            'Data Agendamento',
            'Período'
        ];



        return \Maatwebsite\Excel\Facades\Excel::download(new ReportExport($result, $headers), 'import_orders.xlsx');




    }

    private function importAniel()
    {

    }

    private function getQuery () {

        $hour = Carbon::now();



        $query = '
         select distinct coalesce(contract_service_tags.contract_id, \'000000\') as "Contrato",
        assignments.requestor_id as "Numero do Cliente",
        assignment_incidents.protocol as "Protocolo",
        (select max(pa.neighborhood) from erp.people_addresses pa where pa.person_id = cliente.id limit 1) AS "Bairro",
        (select max(pa.postal_code) from erp.people_addresses pa where pa.person_id = cliente.id limit 1) AS "CEP",
        cliente.tx_id as "CPF/CNPJ",
        (select max(pa.city) from erp.people_addresses pa where pa.person_id = cliente.id limit 1)  AS "Cidade",
        cliente.name as "Cliente",
        (select max(pa.address_complement) from erp.people_addresses pa where pa.person_id = cliente.id limit 1) AS "Complemento",
        TO_CHAR(assignments.beginning_date::DATE,\'YYYY-MM-DD\') as "Abertura",
        people.email as "E-mail",
        people.lat as "Latitude",
        (select max(pa.street) from erp.people_addresses pa where pa.person_id = cliente.id limit 1)  AS "Endereço",
        people.lng as "Longitude",
        cliente.number AS "Numero",
        cliente.cell_phone_1 as "Tel Celular",
        cliente.phone as  "Tel Residencial",
        \'INDIFERENTE\' as "Tipo de Imovel",
        incident_types.title as "Tipo de Serviço",
        case WHEN cliente.neighborhood = \'Recanto das Emas\' THEN \'RECANTO DAS EMAS\'
        WHEN cliente.neighborhood = \'Samambaia Sul (Samambaia)\' THEN \'Samambaia\'
        WHEN cliente.neighborhood = \'Ceilândia Norte (Ceilândia)\' THEN \'Ceilândia Norte\'
        WHEN cliente.neighborhood = \'Samambaia Norte (Samambaia)\' THEN \'Samambaia Norte\'
        WHEN cliente.neighborhood = \'Ceilândia Sul (Ceilândia)\' THEN \'Ceilândia Sul\'
        WHEN cliente.neighborhood = \'Santa Maria\' THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'Riacho Fundo II\' THEN \'Riacho Fundo II\'
        WHEN cliente.neighborhood = \'Taguatinga Norte (Taguatinga)\' THEN \'taguatinga Norte\'
        WHEN cliente.neighborhood = \'Paranoá\' THEN \'Paranoá\'
        WHEN cliente.neighborhood = \'Riacho Fundo I\' THEN \'Riacho Fundo I\'
        WHEN cliente.neighborhood = \'Setor Habitacional Vicente Pires\' THEN \'Vicente Pires\'
        WHEN cliente.neighborhood = \'Setor Oeste (Gama)\' THEN \'Setor Oeste\'
        WHEN cliente.neighborhood = \'Ponte Alta Norte (Gama)\' THEN \'PONTE ALTA (GAMA)\'
        WHEN cliente.neighborhood in (\'Sobradinho\', \'Nova Colina (Sobradinho)\', \'Setor Econômico de Sobradinho (Sobradinho)\', \'Setor Oeste (Sobradinho II)\') THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Setor Sul (Gama)\' THEN \'Setor Sul\'
        WHEN cliente.neighborhood = \'Areal (Águas Claras)\' THEN \'Areal\'
        WHEN cliente.neighborhood = \'Taguatinga Sul (Taguatinga)\' THEN \'Taguatinga Sul\'
        WHEN cliente.neighborhood = \'Varjão\' THEN \'Varjão\'
        WHEN cliente.neighborhood = \'Paranoá Parque (Paranoá)\' THEN \'Paranoã Parque\'
        WHEN cliente.neighborhood = \'Setor Central (Gama)\' THEN \'GAMA\'
        WHEN cliente.neighborhood = \'Guará II\' THEN \'Guará II\'
        WHEN cliente.neighborhood = \'Asa Sul\' THEN \'Asa Sul\'
        WHEN cliente.neighborhood = \'Asa Norte\' THEN \'Asa Norte\'
        WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\'
        WHEN cliente.neighborhood = \'Cruzeiro Velho\' THEN \'Cruzeiro Velho\'
        WHEN cliente.neighborhood = \'Samambaia\' THEN \'Samambaia\'
        WHEN cliente.neighborhood in (\'Setor Norte (Planaltina)\', \'Arapoanga (Planaltina)\', \'Vale do Amanhecer (Planaltina)\', \'Setor Residencial Leste (Planaltina)\') THEN \'Planaltina\'
        WHEN cliente.neighborhood = \'Setor de Mansões de Sobradinho\' THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Setor Habitacional Jardim Botânico\' THEN \'Jardim Botânico\'
        WHEN cliente.neighborhood = \'Setor Industrial (Taguatinga)\' THEN \'Setor Industrial\'
        WHEN cliente.neighborhood = \'Sudoeste/Octogonal\' THEN \'Sudoeste\'
        WHEN cliente.neighborhood = \'Núcleo Bandeirante\' THEN \'Núcleo Bandeirante\'
        WHEN cliente.neighborhood = \'Candangolândia\' THEN \'Candangolândia\'
        WHEN cliente.neighborhood = \'Águas Claras\' THEN \'Aguas Claras\'
        WHEN cliente.neighborhood = \'Riacho Fundo\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Noroeste\' THEN \'Noroeste\'
        WHEN cliente.neighborhood = \'Guará I\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Estrutural\' THEN \'Estrutural\'
        WHEN cliente.neighborhood = \'Cruzeiro Novo\' THEN \'Cruzeiro\'
        WHEN cliente.neighborhood = \'SIA\' THEN \'SIA\'
        WHEN cliente.neighborhood = \'SOF Sul\' THEN \'Sof Sul\'
        WHEN cliente.neighborhood = \'SOF Norte\' THEN \'Sof Norte\'
        WHEN cliente.neighborhood = \'SCIA\' THEN \'Scia\'
        WHEN cliente.neighborhood = \'Santa Maria\' THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'Saan\' THEN \'Saan\'
        WHEN cliente.neighborhood = \'Guará\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Fercal\' THEN \'Fercal\'
        WHEN cliente.neighborhood in (\'Itapoã\', \'Fazendinha (Itapoã)\', \'Del Lago II (Itapoã)\', \'Del Lago I (Itapoã)\') THEN \'Itapoã\'
        WHEN cliente.neighborhood = \'Ponte Alta (Gama)\' THEN \'Ponte\'
        WHEN cliente.neighborhood IN (\'Sobradinho\', \'Sobradinho I\', \'Sobradinho II\', \'Condomínio Mansões Sobradinho (Sobradinho)\', \'Setor Habitacional Contagem (Sobradinho)\') THEN \'Sobradinh\'
        WHEN cliente.neighborhood IN (\'Taguatinga\', \'Taguatinga Norte\', \'Taguatinga Sul\', \'taguatinga Norte\',\'Setor Habitacional Vereda Grande (Taguatinga)\', \'Taguatinga Centro (Taguatinga)\', \'Taguatinga Sul\') THEN \'Taguatinga\'
        WHEN cliente.neighborhood IN (\'São Sebastião\', \'Setor Tradicional (São Sebastião)\') THEN \'São Sebastião\'
        WHEN cliente.neighborhood IN (\'Park Way\') THEN \'Park Way\'
        WHEN cliente.neighborhood IN (\'Candangolândia\') THEN \'Candangolândia\'
        WHEN cliente.neighborhood IN (\'Aguas Claras\', \'Areal\', \'Areal (Águas Claras)\', \'Área de Desenvolvimento Econômico (Águas Claras)\', \'Setor Habitacional Arniqueira (Águas Claras)\') THEN \'Águas Claras\'
        WHEN cliente.neighborhood IN (\'Asa Sul\', \'Asa Norte\') THEN \'Asa Sul/Norte\'
        WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\'
        WHEN cliente.neighborhood IN (\'Noroeste\') THEN \'Noroeste\'
        WHEN cliente.neighborhood IN (\'Sol Nascente\', \'Pôr do Sol\') THEN \'Sol Nascente/Pôr do Sol\'
        WHEN cliente.neighborhood IN (\'Arniqueira\', \'Arniqueiras\') THEN \'Arniqueira\'
        WHEN cliente.neighborhood IN (\'Brazlândia\') THEN \'Brazlândia\'
        WHEN cliente.neighborhood IN (\'Polo JK\') THEN \'Polo JK\'
        WHEN cliente.neighborhood IN (\'Vila Nossa Senhora de Fátima (Planaltina)\', \'Planaltina\', \'Quintas do Amanhecer II (Planaltina)\', \'Jardim Roriz (Planaltina)\', \'Estância Planaltina (Planaltina)\',\'Setor Residencial Leste (Planaltina)\', \'Setor Tradicional (Planaltina)\') THEN \'Planaltina\'
        WHEN cliente.neighborhood IN (\'Lago Norte\') THEN \'Lago Norte\'
        WHEN cliente.neighborhood IN (\'Taquari\') THEN \'Taquari\'
        WHEN cliente.neighborhood IN (\'Núcleo Bandeirante\', \'Setor Placa da Mercedes (Núcleo Bandeirante)\', \'Setor de Indústrias Bernardo Sayão (Núcleo Bandeirante)\', \'Metropolitana (Núcleo Bandeirante)\') THEN \'Núcleo Bandeirante\'
        WHEN cliente.neighborhood IN (\'Vila Cauhy\') THEN \'Vila Cauhy\'
        WHEN cliente.neighborhood IN (\'Setor Habitacional Jardim Botânico\') THEN \'Jardim Botânico\'
        WHEN cliente.neighborhood IN (\'Setor Residencial Oeste (São Sebastião)\', \'Vila Nova (São Sebastião)\', \'Vila São José (São Sebastião)\') THEN \'São Sebastião\'
        WHEN cliente.neighborhood IN (\'Setor Habitacional Ribeirão (Santa Maria)\') THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'PADRE MIGUEL\' THEN \'PADRE MIGUEL\'
        WHEN cliente.neighborhood in (\'Setor Tradicional (Brazlândia)\', \'Setor Sul (Brazlândia)\', \'Setor Norte (Brazlândia)\', \'Vila São José (Brazlândia)\') then \'Brazlândia\'
        WHEN cliente.neighborhood = (\'Setor Habitacional Vicente Pires - Trecho 3\') then \'Vicente Pires\'
        ELSE cliente.neighborhood
    	END as "Node",
    	case WHEN cliente.neighborhood in (\'Recantos das Emas \', \'RECANTO DS EMAS\', \'RECANTO DAS EMAS \', \'RECANTO DAS EMAS\', \'Recanto Das Emas\', \'Recanto das Emas \', \'Recanto das Emas\', \'RECANTO DA EMAS\', \'Área Rural do Recanto das Emas\', \'Recanto das Emas\') THEN \'Recanto das Emas\'
        WHEN cliente.neighborhood = \'Samambaia Sul (Samambaia)\' THEN \'Samambaia\'
        WHEN cliente.neighborhood = \'Ceilândia Norte (Ceilândia)\' THEN \'Ceilândia\'
        WHEN cliente.neighborhood = \'Samambaia Norte (Samambaia)\' THEN \'Samambaia\'
        WHEN cliente.neighborhood in (\'Ceilandia Norte Ceilandia\', \'Ceilândia Sul (Ceilândia)\', \'Setor Habitacional Pôr do Sol (Ceilândia)\') THEN \'Ceilândia\'
        WHEN cliente.neighborhood = \'Santa Maria\' THEN \'Santa Maria\'
        WHEN cliente.neighborhood = \'Riacho Fundo II\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Taguatinga Norte (Taguatinga)\' THEN \'Taguatinga\'
        WHEN cliente.neighborhood in (\'PARANOA PARQUE\', \'paranoa parque \', \'Paranoá\', \'paranoá\',\'PARANOA\', \'Paranoa\', \'Paranoá\', \'Paranoá Parque (Paranoá)\') THEN \'Paranoá\'
        WHEN cliente.neighborhood = \'Riacho Fundo I\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Setor Habitacional Vicente Pires\' THEN \'Vicente Pires\'
        WHEN cliente.neighborhood in (\'Vila Rabelo I (Sobradinho)\', \'Vila Rabelo II (Sobradinho)\', \'Vale das Acácias (Sobradinho)\', \'Sol nascente\', \'Sobradinho  II\', \' (Sobradinho II)\', \'sobradinho df\', \'Sobradinho 2\', \'Sobradinho 1\', \'Sobradinho 01 vila  DNOCS\', \'SOBRADINHO\', \'Sobradinho \', \'Sobradinho\', \'sobradinho\', \'Setor Economico de Sobradinho (Sobradinho)\', \'Grande Colorado (Sobradinho)\',\'Condomínio Mirante da Serra (Sobradinho)\', \'Condomínio Império dos Nobres (Sobradinho)\', \'Condomínio Comercial e Residencial Sobradinho (Sobradinho)\', \'Buriti 2 \', \'Buritizinho (Sobradinho II) \', \'Sobradinho\', \'Nova Colina (Sobradinho)\', \'Setor Econômico de Sobradinho (Sobradinho)\', \'Setor Oeste (Sobradinho II)\', \'Setor de Habitações Individuais Sul\') THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Areal (Águas Claras)\' THEN \'Águas Claras\'
        WHEN cliente.neighborhood = \'Taguatinga Sul (Taguatinga)\' THEN \'Taguatinga\'
        WHEN cliente.neighborhood = \'Varjão\' THEN \'Varjão\'
        WHEN cliente.neighborhood in (\'Zona Industrial (Guará)\', \'Zona Industrial Guara\', \'Zona Industrial\', \'Vila Estrutural\', \'Setor Oeste (Vila Estrutural)\', \'Guará II POLO DE MODAS\', \'GUARA II\', \'Guara II\', \'Guara I\', \'Guará 2\', \'GUARÁ\', \'Guará\', \'Guará II\') THEN \'Guará\'
        WHEN cliente.neighborhood = \'Asa Sul\' THEN \'Asa Sul\'
        WHEN cliente.neighborhood = \'Asa Norte\' THEN \'Asa Norte\'
        WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\'
        WHEN cliente.neighborhood = \'Cruzeiro Velho\' THEN \'Cruzeiro\'
        WHEN cliente.neighborhood = \'Samambaia\' THEN \'Samambaia\'
        WHEN cliente.neighborhood in (\'Vila Feliz (Planaltina)\', \'Vale do Sol (Planaltina)\', \'Vale do Amanhecer Planaltina\', \'Setor Habitacional Mestre DArmas Planaltina\', \'Setor Comercial Central (Planaltina)\', \' ( horta comunitária) Setor Residencial Leste (Planaltina)\', \'Fazendinha \', \'Fazendinha\', \'Fazenda Mestre DArmas (Etapa II - Planaltina)\',\'Estancia 5 (Planaltina)\', \'Estância 3\', \'Estância 1 Planaltina (Planaltina)\', \'Condomínio São Francisco I (Planaltina) Araponga\', \'Condomínio Santa Mônica (Planaltina)\', \'Condomínio Residencial Morada Nobre (Planaltina)\', \'Condomínio Prado (Planaltina)\', \'Condomínio Porto Rico\',\'Condomínio Parque Mônaco (Planaltina)\', \'Condomínio Parque Mônaco II (Planaltina)\', \'Condomínio Nova Esperança (Planaltina)\', \'Condomínio Nosso Lar (Planaltina)\', \'Condominio Mestre DArmas IV (Planaltina)\',\'Condomínio Guirra (Planaltina)\', \'Condomínio Coohaplan - Itiquira (Planaltina)\', \'Vila Nossa Senhora de Fátima (Planaltina)\',\'San Sebastian (Planaltina)\', \'Setor Norte (Planaltina)\', \'Arapoanga (Planaltina)\', \'Vale do Amanhecer (Planaltina)\', \'Setor Residencial Leste (Planaltina)\', \'Recanto Feliz (Planaltina)\') THEN \'Planaltina\'
        WHEN cliente.neighborhood = \'Setor de Mansões de Sobradinho\' THEN \'Sobradinho\'
        WHEN cliente.neighborhood = \'Setor Habitacional Jardim Botânico\' THEN \'Jardim Botânico\'
        WHEN cliente.neighborhood = \'Setor Industrial (Taguatinga)\' THEN \'Taguatinga\'
        WHEN cliente.neighborhood = \'Sudoeste/Octogonal\' THEN \'Sudoeste\'
        WHEN cliente.neighborhood = \'Núcleo Bandeirante\' THEN \'Núcleo Bandeirante\'
        WHEN cliente.neighborhood = \'Candangolândia\' THEN \'Candangolândia\'
        WHEN cliente.neighborhood = \'Águas Claras\' THEN \'Águas Claras\'
        WHEN cliente.neighborhood = \'Riacho Fundo\' THEN \'Riacho Fundo\'
        WHEN cliente.neighborhood = \'Noroeste\' THEN \'Noroeste\'
        WHEN cliente.neighborhood = \'Guará I\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Estrutural\' THEN \'Estrutural\'
        WHEN cliente.neighborhood = \'Cruzeiro Novo\' THEN \'Cruzeiro\'
        WHEN cliente.neighborhood = \'SIA\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'SOF Sul\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'SOF Norte\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'SCIA\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Saan\' THEN \'Guará\'
        WHEN cliente.neighborhood = \'Guará\' THEN \'Guará\'
        WHEN cliente.neighborhood in (\'NOVA COLINA (Sobradinho)\', \'Condomínio Vale dos Pinheiros (Sobradinho)\', \'Fercal\') THEN \'Sobradinho\'
        WHEN cliente.neighborhood in (\'Região dos Lagos  Itapoã\', \'Itapoã Parque (Itapoã)\', \'ITAPOAN II\', \'Itapoã I\', \' (Itapoã I)\', \'Itapoa I\', \'Itapoa,del lago\', \'ITAPOÃ 2\', \'Itapoã 1 \', \'ITAPOÃ\', \'Itapoã \', \'Itapoã\', \'ITAPOA\', \'Fazendinha (Itapoã II)\', \'Fazendinha Itapoã I\', \'Fazendinha Itapoa\',\'Fazendinha 2 (Itapoã)\', \'Del Lago (Itapoã)\', \'Del Lago I Itapoa\', \'Del Lago II Itapoa\',\'Itapoã\', \'Fazendinha (Itapoã)\', \'Del Lago II (Itapoã)\', \'Del Lago I (Itapoã)\', \'Itapoã II\') THEN \'Itapoã\'
        WHEN cliente.neighborhood = \'Lago Sul\' THEN \'Lago Sul\'
        WHEN cliente.neighborhood = \'Lago Norte\' THEN \'Lago Norte\'
        WHEN cliente.neighborhood = \'Arniqueira\' THEN \'Arniqueiras\'
        WHEN cliente.neighborhood = \'Sol Nascente/Pôr do Sol\' THEN \'Ceilândia\'
        WHEN cliente.neighborhood = \'Brazlândia\' THEN \'Brazlândia\'
        WHEN cliente.neighborhood in (\'Parque da Barragem Setor 12\', \'Park Way \', \'Park Way\', \'Park Way\', \'Núcleo Rural Vargem Bonita (Park Way)\') THEN \'Park Way\'
        WHEN cliente.neighborhood IN (\'Setor Habitacional Fercal (Sobradinho)\', \'Setor Habitacional Colonia agricula Samambaia - Trecho 3\', \'Samambaia SUL(Samambaia)\', \'Samambaia SUL (Samambaia)\', \'Samambaia Sul Samambaia\', \'Samambaia Sul (Samambai\', \'SAMAMBAIA SUL\', \'Samambaia Sul \', \'Samambaia sul \', \'Samambaia sul\', \'Samambaia Norte(Samambaia)\', \'Samambaia Norte Samambaia\', \'SAMAMBAIA NORTE\', \'Samambaia Norte \', \'SAMAMBAIA \', \'SAMAMBAIA\', \'Samambaia \', \'Samambaia\', \'SAMABAIA\', \'COL SAMAMBAIA / Setor Habitacional Vicente Pires - Trecho 3\', \'COLONIA AGRICULA SAMAMBAIA\', \'Colônia Agrícola Samambaia\', \'colônia agrícola samambaia \', \'colonia agricola samambaia\', \'Samambaia Norte\', \'Samambaia Sul\', \'Samambaia\') THEN \'Samambaia\'
        WHEN cliente.neighborhood IN (\'Setor Leste\', \'Setor Norte\', \'Setor Oeste\', \'Setor Sul\', \'Quadras Econômicas Lúcio Costa (Guará)\', \'Setor Industrial\', \'Sia\', \'Cidade do Automóvel\', \'Saan\', \'Sof Norte\', \'Sof Sul\', \'sia\', \'Scia\', \'MICRO ÁREA\', \'SIA\') THEN \'Guará\'
        WHEN cliente.neighborhood = \'Varejão\' THEN \'Varejão\'
        WHEN cliente.neighborhood IN (\'Paranoã Parque\', \'Paranoá\') THEN \'Paranoá\'
        WHEN cliente.neighborhood IN (\'SOL NASCENTE\', \'SH Sol Nascente QCS 2 Conj. I - Ceilândia, Brasília - DF\', \'Setor Residencial Leste Buritis 4 \', \'  SETOR P SUL \', \'INGRA - CEILANDIA \', \'Expansão do Setor O Ceilândia Norte (Ceilândia)\', \'Expansão do setor O\', \'Condomínio Vencedor- sol nascente\', \'Condominio Prive Lucena Roriz Ceilandia\', \'Ceilndia Norte Ceilndia\', \'Ceilândia Sul (Ceilândia) Vila Madureira\', \'Ceilândia Sul (Ceilândia)  Sol nascente\', \'Ceilandia Sul Ceilandia\', \'CEILANDIA SUL\', \'Ceilândia P Sul \', \'Ceilândia Norte (Ceilândia) Setor O\', \'CEILÂNDIA NORTE (CEILÂNDIA)\', \'Ceilândia NORTE (Ceilândia)\', \'Ceilândia norte (Ceilândia)\', \'Ceilandia Norte Ceilândia\', \'Ceilândia Norte \', \'CEILANDIA NORTE\', \'Ceilandia Norte \', \'Ceilandia Norte\', \'Ceilandia \', \'Área de Desenvolvimento Econômico (Ceilândia)\', \'Ceilândia Norte (Ceilândia)\', \'Ceilândia Norte\', \'Ceilândia Sul\', \'Ceilândia Centro (Ceilândia)\',\'Setor Habitacional Sol Nascente (Ceilândia)\', \'Condomínio Privê Lucena Roriz (Ceilândia)\') THEN \'Ceilândia\'
        WHEN cliente.neighborhood IN (\'Riacho Fundo 2\', \'riacho fundo 2\', \'SETOR RESIDENCIAL NORTE\', \'RIACHO II\', \'riacho fundo l\', \'RIACHO FUNDO II\', \'Riacho Fundo I ( COLONIA AGRÍCOLA SUCUPIRA)\', \'RIACHO FUNDO I\', \'Riacho Fundo 2 \', \'Riacho Fundo 2\'\', riacho fundo 2\', \'Riacho Fundo\', \'Riacho Fundo I\', \'Riacho Fundo II\', \'Riacho Fundo\') THEN \'Riacho Fundo\' WHEN cliente.neighborhood IN (\'Vicente Pires\', \'Setor Habitacional Vicente Pires\') THEN \'Vicente Pires\' WHEN cliente.neighborhood IN (\'Recanto das Emas\', \'RECANTO DAS EMAS\') THEN \'Recanto das Emas\' WHEN cliente.neighborhood IN (\'Setor sul (Gama) area verde\', \'SETOR SUL GAMA\', \'Setor Sul Gama\', \'Setor Oeste(Gama)\',\'Setor Oeste - Gama\', \'Setor Oeste Gama\', \'Setor oeste (Gama)\', \'Setor oeste (Gama)\', \'setor oeste (Gama)\', \'Setor Norte (Gama)\', \'Setor Noroeste\', \'Setorl Sul(Gama)\', \'Setor Leste Gama \', \'Setor Leste Gama\', \'SETOR LESTE \', \'Setor Industrial (Gama Leste)\', \'Setor Industrial Gama\', \'Setor Central (Gama) \', \'Setor Central Gama\', \'PONTE ALTA\', \'Setor Central (Gama)\', \'Setor Oeste (Gama)\', \'Ponte Alta Norte (Gama)\', \'Setor Sul (Gama)\', \'Setor leste (Gama)\', \'N. RURAL PONTE ALTA NORTE\', \'GAMA SETOR SUL\', \'GAMA ( PONTE ALTA)\', \'GAMA OESTE\', \'Gama Oeste\', \'GAMA LESTE\', \'GAMA\', \'Gama\', \'Ponte Alta (Gama)\', \'GAMA\', \' Ponte Alta Norte Gama DF\', \'PONTE ALTA NORTE GAMA \', \'PONTE ALTA NORTE GAMA\', \'Ponte Alta Norte (Gama) \', \'Ponte Alta Norte Gama\', \'ponte alta norte do Gama\', \'PONTE ALTA NORTE\', \'PONTE ALTA \', \'Ponte Alta \', \' NÚCLERO RURAL GAMA \', \'Engenho das Lages (Gama)\', \'GAMA\', \'PONTE ALTA (GAMA)\', \'Setor Oeste (Gama)\', \'Setor Sul (Gama)\', \'Setor Central (Gama)\',\'Setor Industrial (Gama)\', \'Setor Leste (Gama)\') THEN \'Gama\' WHEN cliente.neighborhood IN (\'SIG\', \'Setor Norte (Vila Estrutural)\', \'Setor Leste (Vila Estrutural)\', \'Guará\', \'Guará II\', \'Guará I\') THEN \'Guará\' WHEN cliente.neighborhood IN (\'Estrutural\', \'Cidade do Automóvel\') THEN \'Estrutural\' WHEN cliente.neighborhood IN (\'Sudoeste\', \'Octogonal\') THEN \'Sudoeste\' WHEN cliente.neighborhood IN (\'Setor de Mansões Dom Bosco (Lago Sul)\',\'Setor de Habitações Individuais Sul / LAGO SUL \', \'Setor de Habitações Individuais Sul - Lago Sul\', \'Lago Sul\', \'Lake Side\') THEN \'Lago Sul\' WHEN cliente.neighborhood IN (\'Cruzeiro Velho\', \'Cruzeiro\', \'Cruzeiro Novo\') THEN \'Cruzeiro\' WHEN cliente.neighborhood IN (\'Vila Varjão do Torto\', \'varjão do torto\', \'VARJÃO\', \'Varjão\', \'Varjao\', \'Setor de Habitacoes Individuais Sul\', \'Setor de Habitações Individuais Norte/Vila Varjão do Torto\', \'Setor de Habitações Individuais Norte - Varjão \', \'Setor de Habitações Individuais Norte\', \'Varjão\', \'Setor de Habitacoes Individuais Norte\') THEN \'Varjão\' WHEN cliente.neighborhood IN (\'Setor Oeste Sobradinho II\', \'Setor Oeste (Sobradinho I)\', \'Setor Oeste sobradinho\', \'Setor Industrial (Sobradinho)\', \'Setor de Mansões de Sobradinho 2\', \'Serra Azul (Sobradinho)\', \'Região dos Lagos (Sobradinho)\', \'Núcleo Rural Lago Oeste (Sobradinho)\',\'Sobradinho\',\'Alto da Boa Vista (Sobradinho)\', \'Sobradinho I\', \'Sobradinho II\', \'Condomínio Mansões Sobradinho (Sobradinho)\', \'Setor Habitacional Contagem (Sobradinho)\') THEN \'Sobradinho\' WHEN cliente.neighborhood IN (\'Taguatinha Sul\', \'Taguatinga SUL (Taguatinga)\', \'Taguatinga Sul Taguatinga\', \'Taguatinga Sul Taguatinga\', \'TAGUATINGA NORTE (TAGUATINGA)\', \'Taguatinga NORTE (Taguatinga)\', \'Taguatinga Norte Taguatinga\', \'Taguatinga Norte (aguatinga\', \'TAGUATINGA NORTE\', \'Taguatinga - DF\', \'TAGUATINGA\', \'Taguatinga \', \'Taguatinga\', \'Setor de Desenvolvimento Econômico (Taguatinga)\', \'Taguatinga\', \'Taguatinga Norte\', \'Taguatinga Sul\', \'taguatinga Norte\', \'Setor Habitacional Vereda Grande (Taguatinga)\', \'Taguatinga Centro (Taguatinga)\', \'Taguatinga Sul\') THEN \'Taguatinga\' WHEN cliente.neighborhood IN (\'São Sebastião\', \'Setor Tradicional (São Sebastião)\') THEN \'São Sebastião\' WHEN cliente.neighborhood IN (\'Park Way\') THEN \'Park Way\' WHEN cliente.neighborhood IN (\'Candangolândia\') THEN \'Candangolândia\' WHEN cliente.neighborhood IN (\'Sul (Águas Claras)\', \'Norte (Águas Claras)\', \'Areal (Águas Claras - Taguatinga Sul)\', \'AGUAS CLARAS\', \'Aguas Claras\', \'Areal\', \'Areal (Águas Claras)\', \'Área de Desenvolvimento Econômico (Águas Claras)\', \'Setor Habitacional Arniqueira (Águas Claras)\') THEN \'Águas Claras\' WHEN cliente.neighborhood = \'Vila Planalto\' THEN \'Vila Planalto\' WHEN cliente.neighborhood IN (\'Noroeste\') THEN \'Noroeste\' WHEN cliente.neighborhood IN (\'Setor Industrial (Ceilândia)\', \'Sol Nascente\', \'Pôr do Sol\') THEN \'Ceilândia\' WHEN cliente.neighborhood IN (\'Arniqueiras - Colônia Agrícola\', \'ARNIQUEIRAS\', \'Arniqueiras \', \'Área de Desenvolvimento Econômico (ARNIQUEIRA )\', \'Área de Desenvolvimento Econômico (Águas Claras) \', \'Arniqueira\', \'Arniqueiras\', \'Area de Desenvolvimento Econômico aguas Claras\') THEN \'Águas Claras\' WHEN cliente.neighborhood IN (\'Brazlândia\') THEN \'Brazlândia\' WHEN cliente.neighborhood IN (\'Villa Rabello \', \'Vila Vicentina (Planaltina)\', \'Vila Vicentina\', \'Vila Dimas (Planaltina)\', \'vila buritis - Planaltina df \',\'Veneza I (Planaltina)\', \'Veneza II (Planaltina)\', \'Veneza III (Planaltina)\', \'Veneza I Arapoanga (Planaltina)\', \'Taquara (Planaltina)\', \'Setor tradicional (Planaltina)\', \'Setor Sul (Planaltina)\', \'Setor Residencial Oeste (Planaltina)\', \'Setor Residencial Norte (Planaltina)\', \'Setor Mansões Itiquira (Planaltina)\', \'Setor Hospitalar (Planaltina)\', \'Setor de Mansões Mestre D armas (Planaltina)\',\'Setor de Hotéis e Diversões (Planaltina)\', \'Setor de Educação (Planaltina)\', \'Residencial Sarandy (Planaltina)\', \'Residencial São Francisco I (Planaltina)\', \'Residencial São Francisco II (Planaltina)\', \'Residencial Sandray (Planaltina)\', \'Residencial Paiva I\', \'Residencial Nova Planaltina (Planaltina)\', \'Residencial Nova Esperança (Planaltina)\', \'Residencial Jardim Coimbra\', \'Residencial Flamboyant (Planaltina)\', \'Residencial Condomínio Marissol (Planaltina)\', \'Residencial Bica do DER (Gleba B - Planaltina)\', \'Quintas do Amanhecer III (Planaltina)\', \'Portal do Amanhecer V (Privê - Planaltina)\', \'Portal do Amanhecer V (Planaltina)\', \'Portal do Amanhecer (Planaltina)\', \'Portal do Amanhecer I (Planaltina)\', \'PLANALTINA DF\', \' PLANALTINA - DF\', \'Planaltina DF \', \'Planaltina - DF\', \'Planaltina Arapoanga\', \'PLANALTINA\', \'Planaltina \', \' (Planaltina)\', \'Planaltina\', \'Planalatina\', \'Nossa Senhora de Fátima (Planaltina)\', \'Mansões do Amanhecer (Planaltina)\', \'Arapoanga \', \'Arapongas - Planaltina \', \'ARAPOANGAS\', \'ARAPOANGA\', \'ARAPOANGA (Planaltina)\', \'Planaltina\', \'Quintas do Amanhecer II (Planaltina)\', \'Jardim Roriz (Planaltina)\', \'Estância Planaltina (Planaltina)\', \'Setor Tradicional (Planaltina)\') THEN \'Planaltina\' WHEN cliente.neighborhood IN (\'Setor de Mansões do Lago Norte\', \'Lago Norte\', \'Setor Habitacional Taquari (Lago Norte)\') THEN \'Lago Norte\' WHEN cliente.neighborhood IN (\'Taquari\') THEN \'Ceilândia\' WHEN cliente.neighborhood IN (\'Vila Cauhy (Núcleo Bandeirante)\', \'Vila cauhy núcleo bandeirante\',\'Setor de Postos e Motéis Sul (Núcleo Bandeirante)\', \'Nucleo bandeirante\', \'Núcleo Bandeirante\', \'Nucleo Bandeirante\', \'Vila Cauhy\', \'Nucleo bandeirante , \'\'Núcleo Bandeirante\',\'Área de Desenvolvimento Econômico (Núcleo Bandeirante)\', \'Setor Placa da Mercedes (Núcleo Bandeirante)\', \'Setor de Indústrias Bernardo Sayão (Núcleo Bandeirante)\', \'Metropolitana (Núcleo Bandeirante)\') THEN \'Núcleo Bandeirante\' WHEN cliente.neighborhood IN (\'Setor Habitacional Tororó (Jardim Botânico)\', \'Setor Habitacional Jardim Botânico 3\', \'Setor Habitacional Jardim Botanico\', \'Jardins Mangueiral (Jardim Botânico)\', \'Setor Habitacional Jardim Botânico\') THEN \'Jardim Botânico\' WHEN cliente.neighborhood IN (\'Zumbi dos Palmares - São Sebastião \', \'Vila do Boa (São Sebastião)\', \'Setor Sudoeste\', \'SETOR RESIDENCIAL OESTE (SÃO SEBASTIÃO)\', \'Sebastião/DF\', \'SÃO SEBASTIÃO\', \'São Sebastião\', \'(São Sebastião)\', \'São Gabriel (São Sebastião)\', \'São Francisco (São Sebastião)\', \'São Bartolomeu (São Sebastião)\', \'Residencial Vitória (São Sebastião)\', \'Residencial Morro da Cruz (São Sebastião)\', \'Residencial do Bosque (São Sebastião)\', \'MORRO AZUL (SÃO SEBASTIÃO)\', \'Morro Azul (São Sebastião)\', \'Morro azul(São Sebastião)\', \'João Cândido (São Sebastião)\', \'Crixá (São Sebastião)\', \'Bonsucesso (São Sebastião)\', \'Bela Vista (São Sebastião)\', \'BAIRRO CENTRO (São Sebastião)\', \'Setor Residencial Oeste (São Sebastião)\', \'Vila Nova (São Sebastião)\', \'Vila São José (São Sebastião)\', \'Centro (São Sebastião)\') THEN \'São Sebastião\' WHEN cliente.neighborhood IN (\'Setor Meireles Santa Maria\', \'Setor Meireles (Santa Maria)\', \'Polo JK\',\'Setor Habitacional Ribeirao Santa Maria\', \'Santa Maria Sul \', \'Santa Maria - Sul\', \'Santa Maria Sul\', \'Santa Maria sul \', \'Santa Maria sul\', \'Santa Maria Norte \', \'Santa Maria Norte\', \'Santa Maria norte\', \'Santa Maria - Condomínio Porto Rico\', \'SANTA MARIA \', \'SANTA MARIA\', \'Santa Maria\', \'Residencial Santos Dumont (Santa Maria)\', \'Núcleo Rural Santa Maria\', \'Núcleo Rural Alagados (Santa Maria)\', \'Condomínio Residencial Santa Maria (Santa Maria)\', \'CONDOMÍNIO PORTO RICO SANTA MARIA\', \'Cidade Nova (Santa Maria)\', \'Área Rural de São Sebastião\', \'Setor Habitacional Ribeirão (Santa Maria)\') THEN \'Santa Maria\' WHEN cliente.neighborhood = \'PADRE MIGUEL\' THEN \'PADRE MIGUEL\' WHEN cliente.neighborhood in (\'Setor Tradicional (Brazlândia)\', \'Setor Sul (Brazlândia)\', \'Setor Norte (Brazlândia)\',\'Veredas (Brazlândia)\', \'Vila São José (Brazlândia)\') then \'Brazlândia\' WHEN cliente.neighborhood in (\'Vila São José (Vicente Pires)\', \'Vila São Jose Vicente Pires\', \'VILA SAO JOSE (VICENTE PIRES)\', \'VILA SÃO JOSÉ \', \'Vila São José\', \'Vicente Pires DF\', \'VICENTE PIRES \', \'VICENTE PIRES\', \'Vicente Pires\', \'Vicente pires\', \'Setor Habitacional Vicente Pires - Trecho 3 \', \'Setor Habitacional Vicente Pires Trecho 1\', \'Setor Habitacional Vicente Pires - Trecho 1\', \'Setor Habitacional Vicente Pires- CONDOMINIO ATHENAS\', \'Setor Habitacional Vicente Pires / COL SAMAMBAIA\', \'SETOR HABITACIONAL VICENTE PIRES \', \'Setor Habitacional VICENTE PIRES\', \'Setor Habitacional Samambaia (Vicente Pires)\', \'Setor Habitacional Vicente Pires - Trecho 3\') then \'Vicente Pires\'
        ELSE cliente.neighborhood
    	END as "Área de Despacho",
        regexp_replace(assignments.description, \'<[^>]+>\', \'\', \'g\') as "Observação",
    	\'DISTRITO FEDERAL\' as "Grupo",
    	assignments.id as "assignment_id",
    	TO_CHAR(s.start_date::DATE, \'YYYY-MM-DD\') as "Data Agendamento"
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
        inner join erp.schedules s on s.assignment_id = assignments.id
        where incident_types.active = \'1\' and assignments.deleted = \'0\' and incident_types.deleted = \'0\'
        and TO_CHAR( s.start_date, \'%Y-%m-%d\' ) <> \'0000-00-00\' and people.deleted = \'0\'
        and people.deleted = \'0\'
         and people.deleted = \'0\' and incident_status.id <> \'8\'
         and incident_types.id in (\'1074\', \'1090\', \'1080\', \'1081\', \'1082\', \'1088\', \'1071\', \'1087\',\'1058\',\'1067\', \'1036\', \'1091\', \'1094\', \'1011\', \'1026\', \'1027\', \'1028\', \'1029\',\'1086\',\'1086\',\'1020\')

        ';


        if($this->req->has('protocols')) {
            $protocols = implode(', ', $this->req->protocols);
            if($protocols != '') {

                $query .= ' and assignment_incidents.protocol in ('.$protocols.') ';
            } else {
                $query .= ' and s.created between to_timestamp(current_date || \' '.Carbon::now()->format('H').':00:00\', \'YYYY-MM-DD HH24:MI:SS\') and to_timestamp(current_date || \' '.Carbon::now()->addHour()->format('H').':00:00\', \'YYYY-MM-DD HH24:MI:SS\')';
            }

        }


        $query .= 'order by 2 desc';
        return $query;



    }
}
