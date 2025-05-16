<?php
session_start();
require_once 'baglanti.php';

// Giriş kontrolü
if (!isset($_SESSION["admin_kullanici"], $_SESSION["admin_giris"]) || $_SESSION["admin_giris"] !== true) {
    // Giriş yapılmamış "giris"e git
    header("Location: giris.php");
    exit();
}

// Aktif emlakçıyı bul
$emlakci_adi = $_SESSION["admin_kullanici"];
$emlakci_id  = 0;
$emlakci_sorgu = mysqli_query($baglanti, "SELECT id FROM `kullanıcılar` WHERE `kullanıcı_adı` = '" . mysqli_real_escape_string($baglanti, $emlakci_adi) . "'");
if ($emlakci = mysqli_fetch_assoc($emlakci_sorgu)) {
    $emlakci_id = $emlakci['id'];
}
$ilan = null;
$guncellendi = false;
$hata_mesaji = "";

// ID kontrolü
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ilan_id = intval($_GET['id']);
    
    // İlanın var mı yok mu
    $ilan_sorgu = mysqli_query($baglanti, "SELECT * FROM `mülk` WHERE id = $ilan_id");
    if (mysqli_num_rows($ilan_sorgu) > 0) {
        $ilan = mysqli_fetch_assoc($ilan_sorgu);
    } else {
        // İlan yok
        header("Location: mulkler.php?hata=bulunamadi");
        exit();
    }
} else {
    // ID geçersiz
    header("Location: mulkler.php?hata=gecersiz_id");
    exit();
}

// Form gönderildi mi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ilan_guncelle"])) {
    $baslik = mysqli_real_escape_string($baglanti, $_POST["baslik"]);
    $aciklama = mysqli_real_escape_string($baglanti, $_POST["aciklama"]);
    $fiyat = intval($_POST["fiyat"]);
    $oda_sayisi = intval($_POST["oda_sayisi"]);
    $metrekare  = intval($_POST["metrekare"]);
    $sehir = mysqli_real_escape_string($baglanti, $_POST["sehir"]);
    $ilce = mysqli_real_escape_string($baglanti, $_POST["ilce"]);
    $tur  = mysqli_real_escape_string($baglanti, $_POST["tur"]);
    $durum_input = strtolower(mysqli_real_escape_string($baglanti, $_POST["durum"]));
    $durum_map = ["satılık" => "Satılık", "kiralık" => "Kiralık", "satıldı/kiralandı" => "Satıldı/Kiralandı"];
    $durum = $durum_map[$durum_input] ?? "Satılık";
    
    // Form verilerini kontrol et
    if (empty($baslik) || empty($aciklama) || $fiyat <= 0 || $metrekare <= 0 || empty($sehir) || empty($ilce)) {
        $hata_mesaji = "Lütfen tüm alanları doldurun.";
    } else {
        $guncelle_sorgu = "UPDATE `mülk` SET başlık = '$baslik', açıklama = '$aciklama', fiyat = $fiyat, odasayısı= $oda_sayisi, `m^2` = $metrekare, şehir = '$sehir', ilçe = '$ilce', tür = '$tur', durum = '$durum' WHERE id = $ilan_id";
        
        if (mysqli_query($baglanti, $guncelle_sorgu)) {
            $guncellendi = true;
            $ilan = mysqli_fetch_assoc(mysqli_query($baglanti, "SELECT * FROM `mülk` WHERE id = $ilan_id"));
        } else {
            $hata_mesaji = "İlan güncellenirken hata: " . mysqli_error($baglanti);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Düzenle - Tosuncuk Emlak</title>
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
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
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
                    <a href="mulkler.php" class="nav-link active">
                        <i class="fas fa-building"></i> Mülklerim
                    </a>
                    <a href="ilanekle.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i> İlan Ekle
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
                    <a href="musteriler.php" class="nav-link">
                        <i class="fas fa-users"></i> Müşteriler
                    </a>
                    <a href="profil.php" class="nav-link">
                        <i class="fas fa-user-cog"></i> Profil
                    </a>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-lg-10 content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>İlan Düzenle</h3>
                    <a href="mulkler.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Mülklere Dön
                    </a>
                </div>
                
                <?php if ($guncellendi): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> İlan başarıyla güncellendi!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($hata_mesaji)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $hata_mesaji ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-edit me-2"></i> İlan Bilgilerini Düzenle
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= $_SERVER['PHP_SELF'] . '?id=' . $ilan_id ?>">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="baslik" class="form-label">İlan Başlığı *</label>
                                    <input type="text" class="form-control" id="baslik" name="baslik" value="<?= htmlspecialchars($ilan['başlık']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="aciklama" class="form-label">Açıklama *</label>
                                    <textarea class="form-control" id="aciklama" name="aciklama" rows="4" required><?= htmlspecialchars($ilan['açıklama']) ?></textarea>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="fiyat" class="form-label">Fiyat (TL) *</label>
                                    <input type="number" class="form-control" id="fiyat" name="fiyat" min="1" value="<?= $ilan['fiyat'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="durum" class="form-label">Durum *</label>
                                    <select class="form-select" id="durum" name="durum" required>
                                    <option value="">Seçiniz</option>
                                    <option value="Satılık" <?= ($ilan['durum'] == 'Satılık') ? 'selected' : '' ?>>Satılık</option>
                                    <option value="Kiralık" <?= ($ilan['durum'] == 'Kiralık') ? 'selected' : '' ?>>Kiralık</option>
                                </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sehir" class="form-label">Şehir *</label>
                                    <input type="text" class="form-control" id="sehir" name="sehir" value="<?= htmlspecialchars($ilan['şehir']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ilce" class="form-label">İlçe *</label>
                                    <input type="text" class="form-control" id="ilce" name="ilce" value="<?= htmlspecialchars($ilan['ilçe']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="oda_sayisi" class="form-label">Oda Sayısı *</label>
                                    <input type="number" class="form-control" id="oda_sayisi" name="oda_sayisi" min="0" value="<?= $ilan['odasayısı'] ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="metrekare" class="form-label">Metrekare *</label>
                                    <input type="number" class="form-control" id="metrekare" name="metrekare" min="1" value="<?= $ilan['m^2'] ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="tur" class="form-label">Mülk Türü *</label>
                                    <select class="form-select" id="tur" name="tur" required>
                                        <option value="">Seçiniz</option>
                                        <option value="Daire" <?= ($ilan['tür'] == 'Daire') ? 'selected' : '' ?>>Daire</option>
                                        <option value="Villa" <?= ($ilan['tür'] == 'Villa') ? 'selected' : '' ?>>Villa</option>
                                        <option value="Arsa" <?= ($ilan['tür'] == 'Arsa') ? 'selected' : '' ?>>Arsa</option>
                                        <option value="Dükkan" <?= ($ilan['tür'] == 'Dükkan') ? 'selected' : '' ?>>Dükkan</option>
                                        <option value="Müstakil Ev" <?= ($ilan['tür'] == 'Müstakil Ev') ? 'selected' : '' ?>>Müstakil Ev</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="mulkler.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times me-1"></i> İptal
                                </a>
                                <button type="submit" name="ilan_guncelle" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Değişiklikleri Kaydet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
