<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lembrete do pagamento via email">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <title>Seu CPF será negativado</title>
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
        <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/emailAssets/Cabe%C3%A7alho-Regua-de-Cobran%C3%A7a-Fatura-em-Atraso.png"
             alt="Header Image" class="imgHeader">
    </header>
    <main class="mainContent">
        <section class="mainTitle">
            <h1 style="color: #E60201; text-align: start">Olá, {usuario}</h1>
        </section>
        <section>
            <div class="content">
                <div style="text-align: justify; font-weight: 500;">
                    <p>Ainda não recebemos o pagamento referente à sua fatura que está vencida há <b>02 DIAS</b>.
                        Valorizamos a sua parceria e queremos ajudá-lo a regularizar sua situação o mais rápido
                        possível.</p>
                    <p>Anexamos a fatura a este e-mail para sua comodidade. Caso prefira, agora oferecemos uma nova
                        opção de pagamento por meio do Pix. Basta escanear o QR Code abaixo com a câmera do seu
                        celular e efetuar o pagamento de forma rápida e segura.
                    </p>
                </div>
                <div class="codeBar" style="text-align: center;">
                    <p>Aqui está o código de barras para o pagamento da sua fatura:</p>
                    <span>xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx</span>
                </div>
                <div style="text-align: justify; font-weight: 500;">
                    <p>Caso você esteja enfrentando alguma dificuldade ou precise de ajuda para efetuar o pagamento,
                        nossa equipe de suporte está disponível para auxilia-lo. Não hesite em entrar em contato
                        conosco para obter o suporte necessário.
                    </p>
                    <p>Agradecemos a sua atenção e reforçamos que você é muito importante para nós. Estamos ansiosos
                        para vê-lo(a) novamente aproveitando todos os benefícios da nossa internet de alta
                        velocidade.
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
