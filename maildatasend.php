<?php
session_start();  // Session başlat
// Formdan gelen e-posta adresini al
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"])) {
    $toEmail = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
} else {
    die("Geçersiz e-posta adresi!");
}

// 6 haneli doğrulama kodu oluştur
$verificationCode = rand(100000, 999999);

// Session'a doğrulama kodunu kaydet
$_SESSION['verificationCode'] = $verificationCode;

// E-posta içeriği
$body = '
<div style="margin:0;padding:0;word-spacing:normal">
    <div role="article">
        <table role="presentation" style="width:100%;border:none;border-spacing:0">
            <tbody>
                <tr>
                    <td align="center" style="padding:0">
                        <table role="presentation" style="width:96%;max-width:700px;border:none;border-spacing:0;text-align:left;font-family:\'Roboto\',Verdana,sans-serif;font-size:16px;line-height:26px">
                            <tbody>
                                <tr>
                                    <td style="padding:16px 0px 16px 24px;text-align:left;background-color:white;line-height:32px">
                                        <a href="https://ematoyzz.com/" style="color:#ffffff;text-decoration:none;font-family:Arial,sans-serif;display:block" target="_blank">
                                            <img alt="Ematoyzz" src="https://i.hizliresim.com/5x20hcl.jpg" style="display:block;border:none;width:auto;height:auto;">
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:24px 24px 24px 24px;text-align:left">
                                        Talep edilen güvenlik kodunuz:
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0px 24px 24px 24px;text-align:center">
                                        <table role="presentation" style="font-size:20px;border:none;border-spacing:4px;margin-left:auto;margin-right:auto">
                                            <tbody>
                                                <tr>
                                                    <td align="center" style="padding:4px 8px;border:1px solid #999999;border-radius:3px;font-size:24px;font-weight:bold;">
                                                        ' . $verificationCode . '
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0px 24px 24px 24px;text-align:left">
                                        Bu güvenlik kodu 30 dakika için geçerlidir. Lütfen ilgili giriş alanına girin.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>';

$data = array(
    'to' => $toEmail,
    'cc' => '',
    'bcc' => '',
    'subject' => 'Mail Doğrulama Kodu',
    'body' => $body,
    'name' => 'globipedi.com',
);

$payload = json_encode($data);

$url = "https://script.google.com/macros/s/XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX/exec";
// google apps sciprt api kodu buraya yapıştırılacaktır.
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$response = json_decode($result);
echo $response->message;
?>
