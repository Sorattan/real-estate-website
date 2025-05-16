<?php
session_start();
require_once 'baglanti.php';

// Giriş kontrol
if (!isset($_SESSION["admin_kullanici"], $_SESSION["admin_giris"]) || $_SESSION["admin_giris"] !== true) {
    // Yönlendirme
    header("Location: index.php");
    exit();
}


// Aktif emlakçıyı al
$emlakci_adi = $_SESSION["admin_kullanici"];
$emlakci_id  = 0;

$emlakci_sorgu = mysqli_query($baglanti,"SELECT * FROM `kullanıcılar` WHERE `kullanıcı_adı` = '" . mysqli_real_escape_string($baglanti, $emlakci_adi) . "'");
if ($emlakci = mysqli_fetch_assoc($emlakci_sorgu)) {
    $emlakci_id = $emlakci['id'];
}

// Profil güncelleme
$guncellendi = false;
$hata_mesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["profil_guncelle"])) {

    $yeni_email = mysqli_real_escape_string($baglanti, $_POST["email"]);
    $yeni_tel   = mysqli_real_escape_string($baglanti, $_POST["tel"]);
    $eski_parola= $_POST["eski_parola"] ?? '';
    $yeni_parola= $_POST["yeni_parola"] ?? '';
    $yeni_parola_tekrar = $_POST["yeni_parola_tekrar"] ?? '';
    // Parola değiştirilecek mi
    $parola_degistir = ($yeni_parola !== '' && $eski_parola !== '');
    if ($parola_degistir) {
        if (!password_verify($eski_parola, $emlakci['parola'])) {
            $hata_mesaji = "Mevcut şifreniz doğru değil.";
        } elseif ($yeni_parola !== $yeni_parola_tekrar) {
            $hata_mesaji = "Yeni şifreler eşleşmiyor.";
        } else {
            // Yeni şifre hash
            $hash_parola = password_hash($yeni_parola, PASSWORD_DEFAULT);
            // Bilgi güncelleme (parola dahil)
            $guncelle = "UPDATE `kullanıcılar` SET email = '$yeni_email', tel   = '$yeni_tel', parola= '$hash_parola' WHERE id = $emlakci_id";
        }
    } else {
        // Sadece email ve telefon güncelleme
        $guncelle = "UPDATE `kullanıcılar` SET email = '$yeni_email', tel   = '$yeni_tel' WHERE id = $emlakci_id";
    }
    // Güncelle
    if ($hata_mesaji === '') {
        if (mysqli_query($baglanti, $guncelle)) {
            $guncellendi = true;
            // Güncel verileri al
            $emlakci = mysqli_fetch_assoc(mysqli_query($baglanti, "SELECT * FROM `kullanıcılar` WHERE id = $emlakci_id"));
        } else {
            $hata_mesaji = "Profil güncellenirken hata: " . mysqli_error($baglanti);
        }
    }
}

// Toplam ilan sayısı
$toplam_ilan = mysqli_fetch_assoc(mysqli_query($baglanti,"SELECT COUNT(*) AS toplam FROM `mülk` WHERE emlakçı_id = $emlakci_id"))['toplam'] ?? 0;

// Toplam randevu sayısı
$toplam_randevu  = mysqli_fetch_assoc(mysqli_query($baglanti,"SELECT COUNT(*) AS toplam FROM `randevu` WHERE emlakçı_id = $emlakci_id"))['toplam'] ?? 0;

// Toplam satış sayısı
$toplam_satis = mysqli_fetch_assoc(mysqli_query($baglanti,"SELECT COUNT(*) AS toplam FROM `satın_alım` WHERE emlakçı_id = $emlakci_id"))['toplam'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - Tosuncuk Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-bg: #f5f8fa;
            --success-color: #2ecc71;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar {
            background-color: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            padding-top: 20px;
        }
        
        .sidebar .nav-link {
            color: #444;
            border-radius: 0;
            padding: 12px 20px;
            margin-bottom: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content-wrapper {
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .profile-card {
            padding: 20px;
        }
        
        .profile-header {
            text-align: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            font-size: 50px;
            font-weight: 300;
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .profile-role {
            color: #777;
            margin-bottom: 15px;
        }
        
        .stat-card {
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #777;
            font-size: 14px;
        }
        
        .stats-icon {
            color: var(--primary-color);
        }
        
        .listings-icon {
            color: var(--secondary-color);
        }
        
        .appointments-icon {
            color: var(--accent-color);
        }
        
        .sales-icon {
            color: var(--success-color);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="acilis.php">
                <i class="fas fa-home me-2"></i>Tosuncuk Emlak
            </a>
            <div class="ms-auto d-flex align-items-center">
                <div class="me-3">
                    <i class="fas fa-user-tie me-1"></i> <?= htmlspecialchars($emlakci_adi) ?>
                </div>
                <a href="cikis.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i> Çıkış
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 px-0 sidebar">
                <div class="nav flex-column">
                    <a href="acilis.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> Kontrol Paneli
                    </a>
                    <a href="mulkler.php" class="nav-link">
                        <i class="fas fa-building"></i> Mülklerim
                    </a>
                    <a href="randevular.php" class="nav-link">
                        <i class="far fa-calendar-alt"></i> Randevular
                    </a>
                    <a href="teklifler.php" class="nav-link">
                        <i class="fas fa-hand-holding-usd"></i> Teklifler
                    </a>
                    <a href="satislar.php" class="nav-link">
                        <i class="fas fa-money-bill-wave"></i> Satışlar
                    </a>
                    <a href="profil.php" class="nav-link active">
                        <i class="fas fa-user-cog"></i> Profil
                    </a>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-lg-10 content-wrapper">
                <h3 class="mb-4">Profilim</h3>
                
                <?php if ($guncellendi): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Profil bilgileriniz başarıyla güncellendi!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hata_mesaji)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $hata_mesaji ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Profil Kartı -->
                    <div class="col-md-4">
                        <div class="card profile-card">
                            <div class="profile-header">
                                <div class="profile-avatar">
                                    <?= strtoupper(substr($emlakci_adi, 0, 1)) ?>
                                </div>
                                <div class="profile-name"><?= htmlspecialchars($emlakci_adi) ?></div>
                                <div class="profile-role">Emlakçı</div>
                                <div class="profile-info">
                                    <?php if (!empty($emlakci['email'])): ?>
                                    <div class="mb-2">
                                        <i class="fas fa-envelope me-2"></i> <?= htmlspecialchars($emlakci['email']) ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($emlakci['tel'])): ?>
                                    <div>
                                        <i class="fas fa-phone me-2"></i> <?= htmlspecialchars($emlakci['tel']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- İstatistikler -->
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="stat-card">
                                        <div class="stat-icon listings-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="stat-value"><?= number_format($toplam_ilan, 0, ',', '.') ?></div>
                                        <div class="stat-label">İlan</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-card">
                                        <div class="stat-icon appointments-icon">
                                            <i class="far fa-calendar-alt"></i>
                                        </div>
                                        <div class="stat-value"><?= number_format($toplam_randevu, 0, ',', '.') ?></div>
                                        <div class="stat-label">Randevu</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-card">
                                        <div class="stat-icon sales-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="stat-value"><?= number_format($toplam_satis, 0, ',', '.') ?></div>
                                        <div class="stat-label">Satış</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profil Düzenleme -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-user-edit me-2"></i> Profil Bilgilerimi Düzenle
                            </div>
                            <div class="card-body">
                                <form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
                                    <div class="mb-3">
                                        <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                                        <input type="text" class="form-control" id="kullanici_adi" value="<?= htmlspecialchars($emlakci_adi) ?>" disabled>
                                        <div class="form-text text-muted">Kullanıcı adı değiştirilemez.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">E-posta Adresi</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($emlakci['email'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tel" class="form-label">Telefon Numarası</label>
                                        <input type="text" class="form-control" id="tel" name="tel" value="<?= htmlspecialchars($emlakci['tel'] ?? '') ?>">
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="mb-3">
                                        <label for="eski_parola" class="form-label">Mevcut Şifre</label>
                                        <input type="password" class="form-control" id="eski_parola" name="eski_parola">
                                        <div class="form-text text-muted">Şifrenizi değiştirmek istiyorsanız, mevcut şifrenizi giriniz.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="yeni_parola" class="form-label">Yeni Şifre</label>
                                        <input type="password" class="form-control" id="yeni_parola" name="yeni_parola">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="yeni_parola_tekrar" class="form-label">Yeni Şifre Tekrar</label>
                                        <input type="password" class="form-control" id="yeni_parola_tekrar" name="yeni_parola_tekrar">
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="submit" name="profil_guncelle" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Değişiklikleri Kaydet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
