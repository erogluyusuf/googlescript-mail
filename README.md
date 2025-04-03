# Google Apps Script ile Mail Gönderme

Bu proje, Google Apps Script kullanarak e-posta doğrulama ve bildirim sistemi kurma sürecini adım adım açıklamaktadır. PHP ve Google Apps Script entegrasyonu sayesinde e-posta gönderme işlemi kolaylaştırılır.

## İçerik

1. [Google Apps Script Nedir?](#google-apps-script-nedir)
2. [E-Posta Gönderme Sistemi Nasıl Çalışır?](#e-posta-gönderme-sistemi-nasıl-çalışır)
3. [Adım Adım Kurulum](#adım-adım-kurulum)
   - [HTML Sayfası ve E-posta Gönderimi](#html-sayfası-ve-e-posta-gönderimi)
   - [Mail.php ile Google Apps Script'e Veri Gönderme](#mailphp-ile-google-apps-scripte-veri-gönderme)
   - [Doğrulama Kodu Karşılaştırma](#doğrulama-kodu-karşılaştırma)
   - [Google Apps Script Projesi Oluşturma](#google-apps-script-projesi-oluşturma)
4. [Google Apps Script ile E-Posta Göndermenin Avantajları](#google-apps-script-ile-e-posta-göndermenin-avantajları)
5. [Sonuç](#sonuç)

---

## Google Apps Script Nedir?

Google Apps Script, Google Workspace uygulamalarını otomatikleştirmek için kullanılan JavaScript tabanlı bir betik dilidir. Gmail, Google Sheets, Docs, Drive ve diğer Google hizmetleriyle kolayca entegre çalışabilir.

---

## E-Posta Gönderme Sistemi Nasıl Çalışır?

Bu projede, kullanıcıların web sayfası üzerinden e-posta adreslerini alacak ve doğrulama kodunu onlara e-posta yoluyla göndereceğiz. İşleyiş şu şekildedir:
1. Kullanıcı, web sitesinde e-posta adresini girer.
2. PHP, rastgele bir doğrulama kodu oluşturur ve bunu oturuma kaydeder.
3. PHP, e-posta ve doğrulama kodunu JSON formatında Google Apps Script'e gönderir.
4. Google Apps Script, Gmail API ile bu bilgileri kullanarak kullanıcıya e-posta gönderir.
5. Kullanıcı, gelen e-postadaki doğrulama kodunu girerek doğrulamayı tamamlar.

---

## Adım Adım Kurulum

### HTML Sayfası ve E-posta Gönderimi

Web sayfası üzerinden kullanıcıdan e-posta adresi alırız. Bu adres, PHP dosyasına gönderildikten sonra Google Apps Script'e iletilir.

```
<div id="UpdatePanel1" class="newsletterContent">
    <input type="email" class="newstext textbox" name="txtbxNewsletterMail" id="txtbxNewsletterMail" placeholder="E-posta adresinizi yazın..." required>
    <a id="btnMailKaydet" href="javascript:void(0)" class="newsbutton button" onclick="sendVerificationCode();">Gönder</a>
</div>

<script>
            function sendVerificationCode() {
    var email = document.getElementById('txtbxNewsletterMail').value;

    if (email === "") {
        Swal.fire({
            icon: "warning",
            title: "Uyarı!",
            text: "E-posta adresi boş olamaz!",
            confirmButtonColor: "#3085d6",
            confirmButtonText: "Tamam"
        });
        return;
    }

    var emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(email)) {
        Swal.fire({
            icon: "error",
            title: "Geçersiz E-posta!",
            text: "Lütfen geçerli bir e-posta adresi girin.",
            confirmButtonColor: "#d33",
            confirmButtonText: "Tamam"
        });
        return;
    }

    Swal.fire({
                title: '<h2 style="color: black; font-weight: bold;">Lütfen Bekleyin</h2>',

                allowOutsideClick: false,
                showConfirmButton: false, // Butonu kaldırıyoruz
                didOpen: () => {
                    Swal.showLoading();
                    
                    // Yükleme ikonunu büyütme
                    document.querySelector('.swal2-loader').style.width = '4rem';
                    document.querySelector('.swal2-loader').style.height = '4rem';
                }
            });

    // E-posta gönderme işlemi
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "mail.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Sunucudan başarılı yanıt aldıktan sonra
            Swal.fire({
                title: "Kod Gönderildi!",
                text: "Lütfen e-postanızı kontrol edin ve doğrulama kodunu girin.",
                icon: "info",
                input: "text",
                inputPlaceholder: "Doğrulama kodunu girin",
                showCancelButton: true,
                confirmButtonText: "Doğrula",
                cancelButtonText: "İptal",
                confirmButtonColor: "#28a745",
                preConfirm: (code) => {
                    if (!code) {
                        Swal.showValidationMessage("Doğrulama kodu boş olamaz!");
                    }
                    return code;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    verifyCode(email, result.value); // Kod doğrulaması yapılır
                }
            });
        }
    };

    // E-posta adresini sunucuya gönderme
    xhr.send("email=" + encodeURIComponent(email));
}

function verifyCode(email, code) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "verify.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Başarılı!",
                    text: response.message,
                    confirmButtonColor: "#28a745",
                    confirmButtonText: "Tamam"
                }).then(() => {
                    // E-posta doğrulama başarılı, şimdi newsletter.php'ye e-posta gönder
                    var email = response.email; // Doğrulama başarılıysa, e-posta adresini al
                    var xhr2 = new XMLHttpRequest();
                    xhr2.open("POST", "newsletter.php", true);
                    xhr2.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    xhr2.onreadystatechange = function () {
                        if (xhr2.readyState == 4 && xhr2.status == 200) {
                            var result = xhr2.responseText;
                            // İstediğiniz şekilde işlem yapabilirsiniz (örneğin, bir mesaj gösterebilirsiniz)
                            console.log(result);
                        }
                    };

                    xhr2.send("email=" + encodeURIComponent(email)); // E-posta adresini gönder
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Hata!",
                    text: response.message,
                    confirmButtonColor: "#d33",
                    confirmButtonText: "Tekrar Dene"
                });
            }
        }
    };

    xhr.send("email=" + encodeURIComponent(email) + "&code=" + encodeURIComponent(code));
}


                    </script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

```
Mail.php ile Google Apps Script'e Veri Gönderme

Mail adresi ve doğrulama kodu PHP dosyasında oluşturulup Google Apps Script API'sine gönderilir.
```
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
```

Doğrulama Kodu Karşılaştırma

Kullanıcıdan alınan doğrulama kodu PHP ile doğrulanır.


// verify.php

```
<?php
session_start(); // Session başlat

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"]) && isset($_POST["code"])) {
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $inputCode = $_POST["code"];
    
    // Session'dan doğrulama kodunu al
    $storedCode = $_SESSION['verificationCode'];
    
    // Kodu kontrol et
    if ($inputCode == $storedCode) {
        // Kod doğru, başarılı yanıt
        $response = array(
            "status" => "success",
            "message" => "Doğrulama başarılı!",
            "email" => $email // E-posta adresini yanıta dahil et
        );
    } else {
        // Kod yanlış, hata yanıtı
        $response = array(
            "status" => "error",
            "message" => "Doğrulama kodu yanlış!"
        );
    }

    // JSON formatında yanıt gönder
    echo json_encode($response);
} else {
    echo json_encode(array("status" => "error", "message" => "Geçersiz işlem!"));
}
?>
```

Google Apps Script Projesi Oluşturma

1.    Google Apps Script'e giriş yapın: Google Apps Script

2.   Yeni bir proje oluşturun.

2.   Aşağıdaki kodu yapıştırın ve kaydedin.
```
const doPost = (request = {}) => {
  const { parameter, postData: { contents, type } = {} } = request;
  if (type === 'application/json') {
 const jsonData = JSON.parse(contents);
 // check quota first
 var emailQuotaRemaining = MailApp.getRemainingDailyQuota();
 if (emailQuotaRemaining == 0) {
   result = {
     status: 'error',
     message: 'Your daily quota is exceeded.'
   };
   return ContentService.createTextOutput(JSON.stringify(result));
 }

 to = jsonData.to ? jsonData.to : '';
 cc = jsonData.cc ? jsonData.cc : '';
 bcc = jsonData.bcc ? jsonData.bcc : '';
 subject = jsonData.subject ? jsonData.subject : '';
 message = jsonData.body ? jsonData.body : '';
 name = jsonData.name ? jsonData.name : '';
 replyTo = jsonData.replyTo ? jsonData.replyTo : '';

 if(to == '' || subject == '' || message == '') {
   result = {
     status: 'error',
     message: 'to, subject and message are required.'
   };
   return ContentService.createTextOutput(JSON.stringify(result));
 }

 arr_email = {to: to, subject: subject, htmlBody: message};

 if(name != '') {
   arr_email.name = name;
 }
 if(cc != '') {
   arr_email.cc = cc;
 }
 if(bcc != '') {
   arr_email.bcc = bcc;
 }
 if(replyTo != '') {
   arr_email.replyTo = replyTo;
 }
 try {
   attachements = jsonData.files ? jsonData.files : [];
   if(attachements.length > 0) {
     arr_files = [];
     for(var i = 0; i < attachements.length; i++) {
       url = attachements[i];
       response = UrlFetchApp.fetch(url);
       blob = response.getBlob();
       ctype = blob.getContentType();
       filename = blob.getName();
       file = blob.setContentType(ctype).setName(filename);
       arr_files.push(file);
     }
     arr_email.attachments = arr_files;
   }

   MailApp.sendEmail(arr_email);

   result = {
     status: 'success',
     message: 'Email is sent successfully.',
     email_quota_remain: MailApp.getRemainingDailyQuota()
   };
   return ContentService.createTextOutput(JSON.stringify(result));
 } catch(e) {
   result = {
     status: 'error',
     message: e.message
   };
   return ContentService.createTextOutput(JSON.stringify(result));
 }
  }
}
```


Google Apps Script ile E-Posta Göndermenin Avantajları

1.    Ücretsiz ve hızlı: Google'ın sunduğu ücretsiz bir hizmettir.

2.    Kolay entegrasyon: Gmail, Drive ve diğer Google hizmetleriyle kolayca entegre edilebilir.

3.   Özelleştirilebilir: Kendi e-posta şablonlarınızı oluşturabilir ve farklı uygulamalarla entegre edebilirsiniz.
