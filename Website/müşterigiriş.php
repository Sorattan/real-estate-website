<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Müşteri Giriş Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="css/index.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            
            
            <h2 class="text-center">Müşteri Giriş</h2>
            
            <?php
            session_start();
            require_once 'baglanti.php';

            $hata = $mesaj = "";

            
            // Tablo kontrolü, gerekiyosa oluşturma
            function tabloKontrolEt($baglanti) {
                $varMi = $baglanti->query("SHOW TABLES LIKE 'müşteri'");
                if ($varMi->num_rows == 0) {
                    $sql = "CREATE TABLE `müşteri` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        `kullanıcı_adı` VARCHAR(50) NOT NULL UNIQUE,
                        parola VARCHAR(255) NOT NULL,
                        email VARCHAR(50) DEFAULT '',
                        tel VARCHAR(20) DEFAULT '',
                        kayıt_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                    if (!$baglanti->query($sql)) {
                        return "Tablo oluşturma hatası: " . $baglanti->error;
                    }
                }
                return "";
            }
            
            // İlk kez açılınca tablo kontrolü
            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                $mesaj = tabloKontrolEt($baglanti);
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $kullanici_adi = $_POST["kullanici_adi"] ?? '';
                $parola = $_POST["parola"] ?? '';

                if ($kullanici_adi === '' || $parola === '') {
                    $hata = "Kullanıcı adı ve parola alanları boş bırakılamaz.";
                } else {
                    $kullanici_adi = $baglanti->real_escape_string($kullanici_adi);
                    $sql = "SELECT * FROM `müşteri` WHERE `kullanıcı_adı` = '$kullanici_adi'";
                    $result = $baglanti->query($sql);

                    if ($result && $result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        if ($row["parola"] == $parola) {
                            $_SESSION["kullanici_adi"] = $kullanici_adi;
                            $_SESSION["giris_yapildi"] = true;
                            header("Location: index.php");
                            exit;
                        } else {
                            $hata = "Hatalı parola!";
                        }
                    } else {
                        $hata = "Kullanıcı adı bulunamadı!";
                    }
                }
            }
            ?>
            
            <?php if (!empty($hata)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $hata; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($mesaj)): ?>
                <div class="alert alert-info" role="alert">
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-3">
                    <label for="kullanici_adi" class="form-label">Kullanıcı Adı:</label>
                    <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" value="<?php echo isset($_POST['kullanici_adi']) ? htmlspecialchars($_POST['kullanici_adi']) : ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="parola" class="form-label">Parola:</label>
                    <input type="password" class="form-control" id="parola" name="parola" required>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p>Hesabınız yok mu? <a href="müşterikayit.php">Kayıt Ol</a></p>
    
                <a href="index.php" class="btn btn-secondary w-100 mt-3">
                    <i class="fas fa-arrow-left me-2"></i>İlanlar Sayfasına Geri Dön
                 </a>
            </div>
        </div>
    </div>
</body>
</html>
