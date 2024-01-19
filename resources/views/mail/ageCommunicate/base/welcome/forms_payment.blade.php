<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Boas-Vindas!</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body style="font-family: 'Montserrat', sans-serif; margin: 0; padding: 0; color: #41444A;">
<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="0" bgcolor="#FFFDF9">
                <tr id="header">
                    <td
                        style="background: url('https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Grupo+100.png') no-repeat center; background-size: cover; height: 400px; padding: 0; margin: 0;">
                        <!-- Reduzir a altura da tabela de espaçamento, se necessário -->
                        <table height="350px" width="100%" style="padding: 0; margin: 0;">
                            <tr>
                                <td style="padding: 0; margin: 0;"></td>
                            </tr>
                        </table>
                        <!-- Texto sobreposto -->
                        <table style="margin: 0 auto;">
                            <tr>
                                <td style="text-align: center;">
                                    <p style="margin: 0; color: #000000; font-size: 32px;">Escolha a melhor forma
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr id="body">
                    <td align="center" style="padding: 0; margin: 0;">
                        <table width="550" style="padding: 0; margin: 0;">
                            <tr>
                                <td
                                    style="font-size: 15px; padding: 0 45px 10px 45px; text-align: center; line-height: 1.5; background: #ffffff; border: 1px solid #ffffff; border-radius: 0 0 25px 25px; margin: 0;">
                                        <span style="font-size: 32px; font-weight: 500; color: #000000;">para o <b
                                                style="color: #FF7200;">seu pagamento!</b></span>
                                    <p>Olá, {{ mb_convert_case($data->nameclient, MB_CASE_TITLE, 'UTF-8')  }}</p>
                                    <p><b style="color: #FF7200;">Sua
                                            fatura estará disponível com 5 dias úteis de antecedência</b> em relação
                                        à data
                                        de vencimento escolhida.<b> Você a receberá por e-mail, ou se preferir,
                                            poderá
                                            acessá-la pelo site ou aplicativo.</b>

                                    </p>
                                    <p>No app ou portal, você poderá escolher a melhor forma de pagamento:<b>pix,
                                            boleto, cartão de crédito,</b> pagamento único e recorrente DCC
                                        (mensalidades
                                        são descontadas todo mês no cartão, sem comprometer o limite de crédito).
                                    </p>
                                    <table width="575">
                                        <tr>
                                            <td align="center" style="padding: 20px 0">
                                                <table style="border: 2px solid #FF7200; border-radius: 30px;">
                                                    <tr>
                                                        <td align="center">
                                                                <span style="text-align: center;">Faça o login no Portal
                                                                    Age ou entre pelo aplicativo</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" style="padding: 20px 20px 0 20px;">
                                                            <a style="cursor: pointer" href="https://portal.agetelecom.com.br/auth/login" target="_blank">
                                                                <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Grupo+-1.png"
                                                                     alt="">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="center" style="padding: 20px 20px 0 20px;">
                                                            <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Imagem+4.png"
                                                                 alt="">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 20px; font-size: 15px;">
                                                                <span><b>Insira o CPF</b> (sem traços ou pontos) no
                                                                    usuário e
                                                                    senha;<br>
                                                                    Clique em <b>“Boleto"</b></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="padding: 0 20px 0 20px;">
                                                            <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Imagem+5.png"
                                                                 alt="">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="padding: 10px 20px 0 20px;">
                                                            <span>Clique no <b>$ (cifrão)</b></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="padding: 10px 20px 0 20px;">
                                                            <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Captura+de+tela+2024-01-04+090234.png"
                                                                 alt="">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="padding: 10px 20px 0 20px;">
                                                            <span>Escolha sua <b>forma de pagamento.</b></span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="padding: 10px 20px 10px 20px;">
                                                            <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Captura+de+tela+2024-01-04+090216.png"
                                                                 alt="">
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="padding: 5px 0;">
                                                    <span>Atenciosamente,<br><b
                                                            style="color: #FF7200; font-weight: 700;">Time Age
                                                            Telecom</b></span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td align="center">
                                    <table>
                                        <tr>
                                            <td align="center" colspan="2">
                                                    <span
                                                        style="text-align: center; display: block; padding: 10px 0;">Baixe
                                                        o aplicativo <b style="color: #FF7200; font-weight: 700;">Age
                                                            Telecom</b></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center">
                                                <a
                                                    href="https://play.google.com/store/apps/details?id=br.com.portal.age&pcampaignid=web_share">
                                                    <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Grupo+96.png"
                                                         alt="Descrição da Imagem"
                                                         style="width: 100%; height: auto; padding: 0 5px;">
                                                </a>
                                            </td>
                                            <td align="center">
                                                <a href="https://apps.apple.com/br/app/age-telecom/id1574228265">
                                                    <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Grupo+97.png"
                                                         alt="Descrição da Imagem"
                                                         style="width: 100%; height: auto; padding: 0 5px;">
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <table width="400" style="padding: 20px 0 0 0">
                            <tr>
                                <td style="text-align: center; width: 25%;">
                                    <a href="https://portal.agetelecom.com.br" target="_blank">
                                        <img style="width: 50%;"
                                             src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Grupo+5.png"
                                             alt="">
                                    </a>
                                    <p style="font-size: 12px">Portal</p>
                                </td>
                                <td style="text-align: center; width: 25%;">
                                    <a href="https://api.whatsapp.com/send?phone=556140404040&text=Ol%C3%A1%21+Gostaria+de+contratar+os+planos+de+internet+fibra+da+Age"
                                       target="_blank">
                                        <img style="width: 50%;"
                                             src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Grupo+4.png"
                                             alt="">
                                    </a>
                                    <p style="font-size: 12px">WhatsApp</p>
                                </td>
                                <td style="text-align: center; width: 25%;">
                                    <a href="https://www.instagram.com/agetelecom/" target="_blank">
                                        <img style="width: 50%;"
                                             src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/boasVindas/Grupo+6.png"
                                             alt="">
                                    </a>
                                    <p style="font-size: 12px;">Instagram</p>
                                </td>

                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <table width="400">
                            <tr>
                                <td style="border-top: 1px solid #9CA0A8; text-align: center; font-size: 12px;">
                                    <p>Canais de Atendimento ao cliente:</p>
                                    <p>www.portal.agetelecom.com.br</p>
                                    <p>WhatsApp: (61) 4040-4040</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: center; font-size: 12px;">
                                    <p>SIA Trecho 17 - Guará, Brasília - DF, 71200-228</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>
