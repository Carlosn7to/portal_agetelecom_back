<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Send Email</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,900&display=swap');

        * {
            box-sizing: border-box;
            font-family: 'Roboto';
        }

        h1 {
            font-size: 20px;
            color: #333333;
            margin-bottom: 2vh;
            font-style: italic;
            font-weight: 400;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th:nth-child(1), td:nth-child(1) {
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: 600;
            font-size: 14px;
            color: #333333;
        }

        td {
            font-size: 12px;
        }

        tr:nth-child(even) {
            background-color: #f2f2f230;
        }

    </style>

</head>
<body>
<div style="box-sizing: border-box ; width: 80vw; padding: 2vh">

    <h1>

        <i>
            Mensagem automática
        </i>

    </h1>

    <table>
        <tr>
            <th>Nome do cliente</th>
            <th>Contrato</th>
            <th>Status</th>
            <th>Evento</th>
            <th>Data da suspensão</th>
            <th>Dias suspenso</th>
            <th>Atendente</th>
        </tr>
        @foreach($data as $item)
            <tr>
                <td>{{ $item['client'] }}</td>
                <td>{{ $item['contract']  }}</td>
                <td>{{ $item['status']  }}</td>
                <td>{{ $item['event']  }}</td>
                <td>{{ $item['date']  }}</td>
                <td>{{ $item['days']  }}</td>
                <td>{{ $item['attendant']  }}</td>
            </tr>
        @endforeach
        <!-- Adicione mais linhas conforme necessário -->
    </table>
</div>
</body>
</html>
