<?php
include("baglanti.php");

$kayit_basarili = false;
$hata_mesaji = "";

if (isset($_POST["kayit_ol"]))
{
    $kullanici_adi = mysqli_real_escape_string($baglanti, $_POST["kullanici_adi"]);
    $email = mysqli_real_escape_string($baglanti, $_POST["email"]);
    $tel = mysqli_real_escape_string($baglanti, $_POST["tel"]);
    $parola = mysqli_real_escape_string($baglanti, $_POST["parola"]);
    $parola_tekrar = mysqli_real_escape_string($baglanti, $_POST["parola_tekrar"]);
    
    // Parolaların eşleşip eşleşmediğini kontrol et
    if ($parola !== $parola_tekrar) {
        $hata_mesaji = "Girdiğiniz parolalar eşleşmiyor!";
    } else {
        // Kullanıcı adı veya email'in zaten var olup olmadığını kontrol et
        $kontrol = "SELECT * FROM müşteri WHERE kullanici_adi = '$kullanici_adi' OR email = '$email'";
        $kontrol_sonuc = mysqli_query($baglanti, $kontrol);
        
        if (mysqli_num_rows($kontrol_sonuc) > 0) {
            $hata_mesaji = "Bu kullanıcı adı veya e-posta zaten kayıtlı!";
        } else {
            $ekle = "INSERT INTO müşteri (kullanici_adi, email, parola, tel) 
                     VALUES ('$kullanici_adi','$email','$parola','$tel')";
                     
            $calistirekle = mysqli_query($baglanti, $ekle);

            if ($calistirekle) {
                $kayit_basarili = true;
            } else {
                $hata_mesaji = "Kayıt başarısız: " . mysqli_error($baglanti);
            }
        }
    }
    mysqli_close($baglanti);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Müşteri Kayıt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/index.css" rel="stylesheet">
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
      <h2 class="text-center">Müşteri Kayıt</h2>

      <?php if ($kayit_basarili): ?>
        <div class="alert alert-success" role="alert">
          Kayıt başarıyla oluşturuldu!
          <div class="mt-2">
            <a href="müşterigiriş.php" class="btn btn-sm btn-primary">Giriş Ekranına Dön</a>
          </div>
        </div>
      <?php elseif (!empty($hata_mesaji)): ?>
        <div class="alert alert-danger" role="alert">
          <?php echo $hata_mesaji; ?>
        </div>
      <?php endif; ?>

      <?php if (!$kayit_basarili): ?>
      <!-- Kayıt Formu -->
      <form action="müşterikayit.php" method="POST">
         <div class="mb-3">
           <label for="email" class="form-label">E-posta</label>
           <input type="email" class="form-control" id="email" name="email" required>
         </div>

         <div class="mb-3">
          <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
          <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required>
         </div>

         <div class="mb-3">
           <label for="tel" class="form-label">Telefon</label>
           <input type="text" class="form-control" id="tel" name="tel" required>
         </div>

          <div class="mb-3">
            <label for="parola" class="form-label">Şifre</label>
            <input type="password" class="form-control" id="parola" name="parola" required>
          </div>
          
          <div class="mb-3">
            <label for="parola_tekrar" class="form-label">Şifre Tekrar</label>
            <input type="password" class="form-control" id="parola_tekrar" name="parola_tekrar" required>
          </div>

          <button type="submit" name="kayit_ol" class="btn btn-primary w-100">Kayıt Ol</button>
       </form>

      <div class="text-center mt-3">
        <p>Zaten hesabınız var mı? <a href="müşterigiriş.php">Giriş Yap</a></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>