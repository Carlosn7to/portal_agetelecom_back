<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lembrete do pagamento via email">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Seu sinal foi suspenso</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        b {
            font-family: 'Montserrat', sans-serif;
            color: #E60201;
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
            text-align: justify;
            position: relative;
            left: 6.2vh;
            font-size: large;
        }

        .content {
            position: relative;
            left: 6vh;
            text-align: justify;
            color: #000;
            margin: 1vh 0 10vh 0;
            font-size: small;
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
        <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/emailAssets/Cabe%C3%A7alho-Regua-de-Cobran%C3%A7a-Alerta-de-Suspens%C3%A3o.png"
             alt="Header Image" class="imgHeader">
    </header>
    <main class="mainContent">
        <section class="mainTitle">
            <h1 style="color: #E60201; text-align: start">Olá, {usuario}</h1>
        </section>
        <section>
            <div class="content">
                <div style="text-align: justify; font-weight: 500;">
                    <p>Chegamos ao prazo limite para o pagamento da sua fatura em atraso. Infelizmente, não
                        identificamos o seu pagamento e, conforme avisos anteriores, infelizmente seu serviço
                        será suspenso amanhã.</p>
                    <p>Para evitar a suspensão, faça o pagamento do seu boleto o mais rápido possível. Anexamos
                        novamente a fatura a este e-mail para sua comodidade. Você também pode utilizar a opção
                        de
                        pagamento por meio do Pix, escaneando o QR Code abaixo com a câmera do seu celular.
                    </p>
                    <p>Aproveitamos para ressaltar que a baixa do pagamento por Pix é imediata, garantindo a
                        reativação do seu serviço sem demora. Caso já tenha efetuado o pagamento, por favor,
                        desconsidere este e-mail.
                    </p>
                </div>
                <div class="codeBar" style="text-align: center;">
                    <p>Aqui está o código de barras para o pagamento da sua fatura:</p>
                    <span>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
                </div>
                <div style="text-align: justify; font-weight: 500;">
                    <div>
                        <p>Estamos à disposição para ajudá-lo(a) caso tenha alguma dúvida ou precise de suporte.
                            Sua satisfação é importante para nós, e queremos que você desfrute da nossa internet
                            de qualidade novamente.</p>
                    </div>
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
