<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lembrete do pagamento via email">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <title>É Hoje</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        b {
            font-family: 'Montserrat', sans-serif;
            color: #03257E;
        }

        .container {
            text-align: center;
            /* Alinha o conteúdo dentro do container no centro */
            margin: 0 auto;
            /* Centraliza o container horizontalmente */
            max-width: 65vh;
            /* Define uma largura máxima para o container */
        }

        .imgHeader {
            width: 71vh;
        }

        .mainTitle {
            color: #03257E;
            font-weight: 700;
            text-align: start;
            position: relative;
            left: 6.2vh;
            font-size: large;
        }

        .content {
            position: relative;
            left: 6vh;
            text-align: center;
            color: #000;
            margin: 1vh 0 10vh 0;
            font-size: medium;
            line-height: 2.5vh;
        }

        .codeBar {
            font-weight: bolder;
        }

        .lastContent {
            font-weight: 700;
        }

        .subContent {
            color: #F93822;
            font-weight: 700;
            position: relative;
            left: 5vh;
            text-align: center;
            font-size: small;
            margin: 10vh 0 0 0;
        }

        .alertContent {
            color: #000;
            font-size: smaller;
            text-align: center;
            font-weight: 200;
            margin: 0 0 1vh 0;
        }

        .imgFooter {
            width: 71vh;
        }
    </style>
</head>

<body>
<div class="container">
    <header>
        <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/emailAssets/Cabe%C3%A7alho-Regua-de-Cobran%C3%A7a-Hoje.png"
             alt="Header Image" class="imgHeader">
    </header>
    <main class="mainContent">
        <section class="mainTitle">
            <h1 style="color: #03257E; text-align: start">Olá, {usuario}</h1>
        </section>
        <section>
            <div class="content">
                <div style="text-align: justify; font-weight: 500;">
                    <p><b>Hoje é o último dia</b> para pagar seu boleto sem a cobrança de juros e multa!
                    </p>
                    <p>Não deixe passar essa data e efetue o pagamento o quanto antes para para continuar
                        desfrutando ao máximo de nossos serviços.</p>
                </div>
                <div class="codeBar" style="text-align: center;">
                    <p>Aqui está o código de barras para o pagamento da sua fatura:</p>
                    <span>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
                </div>
                <div style="text-align: justify; font-weight: 500;">
                    <p>Agradecemos sua escolha pela Age Telecom como provedora de internet. Estamos aqui para
                        ajudar!
                    </p>
                    <div class="lastContent">
                        <p>Atenciosamente,<br>Time Age Telecom.</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="subContent">
            <p style="color: #F93822; font-weight: 700;">Caso já tenha efetuado o pagamento,
                desconsidere esta mensagem.</p>
            <div class="alertContent">
                <i>Esta é uma mensagem automática. Por favor, não responda este e-mail</i>
            </div>
        </section>
    </main>
    <footer class="footerContent">
        <a href="https://linktree.com/agetelecom"><img
                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/emailAssets/Rodap%C3%A9-Regua-de-Cobran%C3%A7a.png"
                alt="Footer Image" class="imgFooter"></a>
    </footer>
</div>
</body>

</html>
