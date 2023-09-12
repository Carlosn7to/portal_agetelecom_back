<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lembrete do pagamento via email">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <title>SCPC - Comunicado</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        .container {
            text-align: justify;
            /* Alinha o conteúdo dentro do container no centro */
            margin: 0 auto;
            /* Centraliza o container horizontalmente */
            max-width: 71vh;
            /* Define uma largura máxima para o container */
        }

        .mainContent {
            background-color: #FFFFFF;
        }

        .imgHeader {
            width: 71vh;
        }

        .imgFooter {
            width: 71vh;
        }

        .div-table {
            margin-bottom: 5vh;
            font-size: .7rem;

            .table-title {
                text-align: center;
            }
        }

        .div-alert {
            font-size: .7rem;
            color: red;
            text-align: center;
        }

        div {
            margin-bottom: 5vh;
        }

        table,
        td {
            border: 1px solid #333;
        }

        thead,
        tfoot {
            background-color: #333;
            color: #fff;
        }
    </style>
</head>

<body>
<div class="container">
    <header>
        <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/scpc/header-logo.png"
             alt="Header Image" class="imgHeader">
    </header>
    <main class="mainContent">
        <div>
            <p>Brasília, 06 de Setembro de 2023</p>
        </div>
        <div>
            <p><b>{{ $data['nameClient']  }}</b></p>
            <p><b>CPF: {{ $data['cpf']  }}</b></p>
        </div>
        <div>
            <span>Conforme art. 43, § 2º, do Código de Defesa do Consumidor, comunicamos a abertura de cadastro para o
                seu nome, onde os credores poderão registrar as obrigações de sua responsabilidade. Tendo em vista que
                foi averiguado atraso de mais de 30 dias sobre faturas vencidas em seu nome, a AGE TELECOMUNICAÇÕES
                solicitou a inclusão do(s) seguinte(s) débito(s) em seu nome nas bases de dados dos serviços de proteção
                ao crédito Serasa Experian, Boa Vista SCPC e Quod:
            </span>
        </div>
        <div>
            <p><b>CNPJ do Credor:</b> {{ $data['cnpj']  }}</p>
        </div>
        <div>
            <p><b>Endereço do Credor:</b> {{ $data['addressClient']  }}</p>
        </div>


        <div class="div-table">
            <table>
                <thead>
                <tr>
                    <th class="table-title" colspan="4" style="text-align: center">
                        Dados do Débito(s)
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>
                        <b>CONTRATO</b>
                    </td>
                    <td>
                        <b>NATUREZA DA OPERAÇÃO</b>
                    </td>
                    <td>
                        <b>VALOR DO DÉBITO</b>
                    </td>
                    <td>
                        <b>DATA DO DÉBITO</b>
                    </td>
                </tr>
                <tr>
                    <td>{{ $data['contractClient']  }}</td>
                    <td>{{ $data['financialNature']  }}</td>
                    <td>{{ $data['valueDebit']  }}</td>
                    <td>{{ $data['dateDebit']  }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div>
            <span>Você tem o prazo de 10 dias a contar da data de emissão desta notificação para regularizar o(s) débito(s). Após esse prazo, não havendo quitação do débito pelo devedor ou manifestação pelo credor, a(s) informação(ões) será(ão) disponibilizada(s) para consulta no(s) banco(s) de dados de proteção ao crédito.</span>
        </div>
        <div>
            <span>Para a sua conveniência, anexamos o boleto correspondente a esta dívida, para que você possa regularizá-la de forma rápida e evitar possíveis inconvenientes futuros.</span>
        </div>
        <div>
            <span>Destacamos que a quitação do(s) débito(s) aqui indicado(s) não satisfaz a quitação de eventuais outros débitos em aberto em seu nome que porventura não tenham atingido 30 dias de inadimplência.</span>
        </div>
        <div>
            <span>Caso tenha dúvidas acerca do débito ou precise de auxílio adicional, nossa equipe de atendimento está à disposição para ajudá-lo(a), por meios dos seguintes canais de comunicação:</span>
        </div>
        <div>
            <span><b>AGE TELECOM</b></span><br>
            <span><b>Site:</b> www.agetelecom.com.br</span><br>
            <span><b>Central de Relacionamento:</b> (61) 4040-4040</span>
        </div>
        <div class="div-alert">
            <p>Caso já tenha efetuado o pagamento, favor desconsiderar este comunicado.</p>
        </div>
        <div>
            <p><b>AGE TELECOM</b></p>
        </div>
    </main>
    <footer class="footerContent">
        <a href="https://linktree.com/agetelecom"><img
                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/scpc/footer-logo.png"
                alt="Footer Image" class="imgFooter"></a>
    </footer>
</div>
</body>

</html>
