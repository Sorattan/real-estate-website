<?php
session_start();
require_once 'baglanti.php';

// Giriş kontrolü
if (!isset($_SESSION["admin_kullanici"], $_SESSION["admin_giris"]) || $_SESSION["admin_giris"] !== true) {
    // Giriş yapılmamışsa "giriş"e yönlendiriyor
    header("Location: giris.php");
    exit();
}

// Aktif emlakçı bilgilerini al
$emlakci_adi = $_SESSION["admin_kullanici"];
$emlakci_id = 0;

// Emlakçı ID’si alma
$emlakci_sorgu = mysqli_query($baglanti,"SELECT id FROM `kullanıcılar` WHERE `kullanıcı_adı` = '" . mysqli_real_escape_string($baglanti, $emlakci_adi) . "'");
if ($emlakci = mysqli_fetch_assoc($emlakci_sorgu)) {
    $emlakci_id = $emlakci['id'];
}

// Emlakçıya ait toplam mülk sayısı
$toplam_mulk = mysqli_fetch_assoc(mysqli_query($baglanti, "SELECT COUNT(*) AS toplam FROM `mülk` WHERE emlakçı_id = '$emlakci_id'"))['toplam'];
// Emlakçıya ait kiralık mülk sayısı
$kiralik_mulk = mysqli_fetch_assoc(mysqli_query($baglanti, "SELECT COUNT(*) AS toplam FROM `mülk` WHERE durum = 'Kiralık' AND emlakçı_id = '$emlakci_id'"))['toplam'];
// Emlakçıya ait satılık mülk sayısı
$satilik_mulk = mysqli_fetch_assoc(mysqli_query($baglanti, "SELECT COUNT(*) AS toplam FROM `mülk` WHERE durum = 'Satılık' AND emlakçı_id = '$emlakci_id'"))['toplam'];
// Satılan/kiralanan mülk sayısı (aktif=0 olanlar)
$satilan_kiralanan = mysqli_fetch_assoc(mysqli_query($baglanti, "SELECT COUNT(*) AS toplam FROM `mülk` WHERE aktif = 0 AND emlakçı_id = '$emlakci_id'"))['toplam'];

// Emlakçının son 3 müşterisi
$son_musteriler_sorgu = mysqli_query($baglanti, 
    "SELECT DISTINCT mu.`kullanıcı_adı` AS kullanici_adi, mu.id
    FROM   `müşteri` mu
    INNER  JOIN `randevu` r ON mu.id = r.müşteri_id
    INNER  JOIN `mülk`   m ON r.ilan_id = m.id
    WHERE  m.emlakçı_id = '$emlakci_id'
    ORDER  BY mu.id DESC
    LIMIT 3"
);

// Emlakçının yaklaşan 5 randevusu
$yaklasan_randevular_sorgu = mysqli_query($baglanti, 
    "SELECT r.*, m.başlık AS mulk_adi, mu.`kullanıcı_adı` AS musteri_adi
    FROM `randevu` r
    LEFT JOIN `mülk` m ON r.ilan_id = m.id
    LEFT JOIN `müşteri` mu ON r.müşteri_id = mu.id
    WHERE r.randevu_tarihi >= CURDATE() AND m.emlakçı_id = '$emlakci_id'
    ORDER BY r.randevu_tarihi ASC, r.randevu_saat ASC
    LIMIT 5"
);

// Emlakçının son 3 satışı/kiralaması
$son_satislar_sorgu = mysqli_query($baglanti, 
    "SELECT s.*, m.başlık AS mulk_adi, mu.`kullanıcı_adı` AS musteri_adi
    FROM `satın_alım` s
    LEFT JOIN `mülk` m ON s.ilan_id = m.id
    LEFT JOIN `müşteri` mu ON s.müşteri_id = mu.id
    WHERE s.emlakçı_id = '$emlakci_id'
    ORDER BY s.alım_tarihi DESC
    LIMIT 3"
);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tosuncuk Emlak - Emlakçı Paneli</title>
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
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card {
            padding: 20px;
            text-align: center;
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .total-icon {
            color: var(--primary-color);
        }
        
        .rent-icon {
            color: var(--secondary-color);
        }
        
        .sale-icon {
            color: var(--accent-color);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #777;
            font-size: 14px;
        }
        
        .table td, .table th {
            vertical-align: middle;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .badge-kiralik {
            background-color: var(--secondary-color);
        }
        
        .badge-satilik {
            background-color: var(--accent-color);
        }
        
        .text-secondary {
            color: var(--secondary-color) !important;
        }
        
        .text-accent {
            color: var(--accent-color) !important;
        }
        
        .appointment-item {
            border-left: 3px solid var(--secondary-color);
            margin-bottom: 10px;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 0 5px 5px 0;
        }
        
        .sale-item {
            border-left: 3px solid var(--accent-color);
            margin-bottom: 10px;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 0 5px 5px 0;
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
                    <a href="acilis.php" class="nav-link active">
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
                    <a href="profil.php" class="nav-link">
                        <i class="fas fa-user-cog"></i> Profil
                    </a>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-lg-10 content-wrapper">
                <h3 class="mb-4">Hoş Geldiniz, <?= htmlspecialchars($emlakci_adi) ?></h3>
                
                <!-- İstatistikler -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon total-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-value"><?= number_format($toplam_mulk, 0, ',', '.') ?></div>
                            <div class="stat-label">Toplam Mülkleriniz</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon rent-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="stat-value"><?= number_format($kiralik_mulk, 0, ',', '.') ?></div>
                            <div class="stat-label">Kiralık Mülkleriniz</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon sale-icon">
                                <i class="fas fa-tag"></i>
                            </div>
                            <div class="stat-value"><?= number_format($satilik_mulk, 0, ',', '.') ?></div>
                            <div class="stat-label">Satılık Mülkleriniz</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="stat-icon" style="color: var(--success-color);">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="stat-value"><?= number_format($satilan_kiralanan, 0, ',', '.') ?></div>
                            <div class="stat-label">Satılan/Kiralanan Mülkler</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Müşterileriniz -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-user-plus me-2"></i> Müşterileriniz
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($son_musteriler_sorgu) > 0): ?>
                                    <ul class="list-group list-group-flush">
                                        <?php while ($musteri = mysqli_fetch_assoc($son_musteriler_sorgu)): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="fas fa-user text-secondary me-2"></i>
                                                    <?= htmlspecialchars($musteri['kullanici_adi']) ?>
                                                </span>
                                                <small class="text-muted">ID: <?= $musteri['id'] ?></small>
                                            </li>
                                        <?php endwhile; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-center text-muted py-3">Henüz müşteriniz bulunmuyor.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Son Satışlarınız -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-money-bill-wave me-2"></i> Son Satışlarınız
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($son_satislar_sorgu) > 0): ?>
                                    <div class="sale-list">
                                        <?php while ($satis = mysqli_fetch_assoc($son_satislar_sorgu)): ?>
                                            <div class="sale-item">
                                                <div class="fw-bold"><?= htmlspecialchars($satis['mulk_adi'] ?? 'İsimsiz Mülk') ?></div>
                                                <div>
                                                    <i class="far fa-user me-1"></i> 
                                                    <?= htmlspecialchars($satis['musteri_adi'] ?? 'Bilinmeyen Müşteri') ?>
                                                </div>
                                                <div>
                                                    <i class="far fa-calendar me-1"></i> 
                                                    <?= date('d.m.Y', strtotime($satis['alım_tarihi'])) ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-muted py-3">Henüz satışınız bulunmuyor.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Yaklaşan Randevularınız -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="far fa-calendar-check me-2"></i> Yaklaşan Randevularınız
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($yaklasan_randevular_sorgu) > 0): ?>
                                    <div class="appointment-list">
                                        <?php while ($randevu = mysqli_fetch_assoc($yaklasan_randevular_sorgu)): ?>
                                            <div class="appointment-item">
                                                <div class="fw-bold"><?= htmlspecialchars($randevu['mulk_adi'] ?? 'İsimsiz Mülk') ?></div>
                                                <div>
                                                    <i class="far fa-user me-1"></i> 
                                                    <?= htmlspecialchars($randevu['musteri_adi'] ?? 'Bilinmeyen Müşteri') ?>
                                                </div>
                                                <div>
                                                    <i class="far fa-calendar me-1"></i> 
                                                    <?= date('d.m.Y', strtotime($randevu['randevu_tarihi'])) ?>
                                                </div>
                                                <div>
                                                    <i class="far fa-clock me-1"></i> 
                                                    <?= date('H:i', strtotime($randevu['randevu_saat'])) ?>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-muted py-3">Yaklaşan randevunuz bulunmuyor.</p>
                                <?php endif; ?>
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
