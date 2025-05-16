<?php
session_start();
require_once 'baglanti.php';

// Giriş kontrolü
if (!isset($_SESSION["admin_kullanici"]) || !isset($_SESSION["admin_giris"]) || $_SESSION["admin_giris"] !== true) {
    // Giriş yapılmamış, yönlendir
    header("Location: giris.php");
    exit();
}
// Aktif emlakçı bilgileri
$emlakci_adi = $_SESSION["admin_kullanici"];
$emlakci_id = 0;
$emlakci_sorgu = mysqli_query($baglanti, "SELECT id FROM `kullanıcılar` WHERE `kullanıcı_adı` = '" . mysqli_real_escape_string($baglanti, $emlakci_adi) . "'");
if ($emlakci = mysqli_fetch_assoc($emlakci_sorgu)) {
    $emlakci_id = $emlakci['id'];
}
// Satış silme
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $satis_id = intval($_GET['sil']);
    $sil_sorgu = mysqli_query($baglanti, "DELETE FROM satın_alım WHERE id = $satis_id AND emlakçı_id = $emlakci_id");
    
    if ($sil_sorgu) {
        $silme_mesaji = "Satış kaydı başarıyla silindi.";
        $silme_durum = "success";
    } else {
        $silme_mesaji = "Satış kaydı silinirken bir hata oluştu: " . mysqli_error($baglanti);
        $silme_durum = "danger";
    }
}
// Emlakçının satışlar
$satislar_sorgu = mysqli_query($baglanti, "SELECT  s.*, 
    m.başlık AS mulk_adi, 
    m.fiyat AS mulk_fiyat,
    m.durum AS mulk_durum,
    mu.`kullanıcı_adı` AS musteri_adi,  
    k.`kullanıcı_adı` AS emlakci_adi
    FROM `satın_alım` s
    LEFT JOIN `mülk` m  ON s.ilan_id = m.id
    LEFT JOIN `müşteri` mu ON s.müşteri_id  = mu.id
    LEFT JOIN `kullanıcılar` k ON s.emlakçı_id  = k.id
    WHERE s.emlakçı_id = '$emlakci_id'
    ORDER BY s.alım_tarihi DESC
") or die(mysqli_error($baglanti));
// Emlakçının toplam satış tutarı
$toplam_satis_sorgu = mysqli_query($baglanti, 
    "SELECT SUM(m.fiyat) as toplam_satis
    FROM satın_alım s
    LEFT JOIN mülk m ON s.ilan_id = m.id
    WHERE s.emlakçı_id = '$emlakci_id'"
);
$toplam_satis = mysqli_fetch_assoc($toplam_satis_sorgu)['toplam_satis'] ?? 0;
// Emlakçının son 30 günlük satışları
$son_ay = date('Y-m-d', strtotime('-30 days'));
$son_ay_satislar_sorgu = mysqli_query($baglanti, 
    "SELECT SUM(m.fiyat) as toplam_satis
    FROM satın_alım s
    LEFT JOIN mülk m ON s.ilan_id = m.id
    WHERE s.alım_tarihi >= '$son_ay' AND s.emlakçı_id = '$emlakci_id'"
);
$son_ay_satis = mysqli_fetch_assoc($son_ay_satislar_sorgu)['toplam_satis'] ?? 0;
// Toplam satış
$toplam_satis_sayisi = mysqli_num_rows($satislar_sorgu);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satış Yönetimi - Tosuncuk Emlak</title>
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
        
        .table th {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .stat-card {
            padding: 20px;
            text-align: center;
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
        
        .total-sales {
            color: var(--success-color);
        }
        
        .month-sales {
            color: var(--secondary-color);
        }
        
        .sales-count {
            color: var(--primary-color);
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
                    <a href="satislar.php" class="nav-link active">
                        <i class="fas fa-money-bill-wave"></i> Satışlar
                    </a>
                    <a href="profil.php" class="nav-link">
                        <i class="fas fa-user-cog"></i> Profil
                    </a>
                </div>
            </div>

            <!-- Ana İçerik -->
            <div class="col-lg-10 content-wrapper">
                <h3 class="mb-4">Satış Yönetimi</h3>
                
                <?php if (isset($silme_mesaji)): ?>
                <div class="alert alert-<?= $silme_durum ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= ($silme_durum == 'success') ? 'check' : 'exclamation' ?>-circle me-2"></i> <?= $silme_mesaji ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Satış İstatistikleri -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-icon total-sales">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-value"><?= number_format($toplam_satis, 0, ',', '.') ?> TL</div>
                            <div class="stat-label">Toplam Satış Tutarı</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-icon month-sales">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-value"><?= number_format($son_ay_satis, 0, ',', '.') ?> TL</div>
                            <div class="stat-label">Son 30 Gün Satış</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card stat-card">
                            <div class="stat-icon sales-count">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="stat-value"><?= number_format($toplam_satis_sayisi, 0, ',', '.') ?></div>
                            <div class="stat-label">Toplam Satış Sayısı</div>
                        </div>
                    </div>
                </div>
                
                <!-- Satış Listesi -->
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-list me-2"></i> Satış Geçmişi
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($satislar_sorgu) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Mülk</th>
                                            <th>Müşteri</th>
                                            <th>Emlakçı</th>
                                            <th>Satış Fiyatı</th>
                                            <th>Satış Tarihi</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        mysqli_data_seek($satislar_sorgu, 0); // Sorguyu başa sar
                                        while ($satis = mysqli_fetch_assoc($satislar_sorgu)): 
                                        ?>
                                            <tr>
                                                <td><?= $satis['id'] ?></td>
                                                <td><?= htmlspecialchars($satis['mulk_adi'] ?? 'Bilinmeyen Mülk') ?></td>
                                                <td><?= htmlspecialchars($satis['musteri_adi'] ?? 'Bilinmeyen Müşteri') ?></td>
                                                <td><?= htmlspecialchars($satis['emlakci_adi'] ?? 'Bilinmeyen Emlakçı') ?></td>
                                                <td><strong><?= number_format($satis['mulk_fiyat'] ?? 0, 0, ',', '.') ?> TL</strong></td>
                                                <td><?= date('d.m.Y H:i', strtotime($satis['alım_tarihi'])) ?></td>
                                                <td>
                                                    <a href="satislar.php?sil=<?= $satis['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu satış kaydını silmek istediğinizden emin misiniz?')">
                                                        <i class="fas fa-trash"></i> Sil
                                                    </a>
                                                    <a href="detay.php?id=<?= $satis['ilan_id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-eye"></i> İlanı Gör
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Henüz satış kaydı bulunmuyor.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
