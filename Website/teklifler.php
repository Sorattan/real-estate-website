<?php
session_start();
require_once 'baglanti.php';

// Giriş kontrol
if (!isset($_SESSION["admin_kullanici"]) || !isset($_SESSION["admin_giris"]) || $_SESSION["admin_giris"] !== true) {
    // yönlendirme
    header("Location: giris.php");
    exit();
}

// Aktif emlakçı bilgileri
$emlakci_adi = $_SESSION["admin_kullanici"];
$emlakci_id = 0;
$emlakci_sorgu = mysqli_query($baglanti,"SELECT id FROM `kullanıcılar` WHERE `kullanıcı_adı` = '" . mysqli_real_escape_string($baglanti, $emlakci_adi) . "'");
if ($emlakci = mysqli_fetch_assoc($emlakci_sorgu)) {
    $emlakci_id = $emlakci['id'];
}

// Teklif silme
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $teklif_id = intval($_GET['sil']);
    $sil_sorgu = mysqli_query($baglanti, "DELETE FROM teklif WHERE id = $teklif_id");
    
    if ($sil_sorgu) {
        $silme_mesaji = "Teklif başarıyla silindi.";
        $silme_durum = "success";
    } else {
        $silme_mesaji = "Teklif silinirken bir hata oluştu: " . mysqli_error($baglanti);
        $silme_durum = "danger";
    }
}

// Teklif onaylama
if (isset($_GET['onayla']) && is_numeric($_GET['onayla'])) {
    $teklif_id = intval($_GET['onayla']);
    $onay_mesaji = "Teklif başarıyla onaylandı!";
    $onay_durum = "success";
}

// Yeni teklifler
$yeni_teklifler_sorgu = mysqli_query($baglanti, 
    "SELECT  t.*,
    m.başlık AS mulk_adi,
    m.fiyat AS mulk_fiyat,
    m.durum AS mulk_durum,
    mu.`kullanıcı_adı` AS musteri_adi
    FROM `teklif` t
    LEFT JOIN `mülk` m  ON t.ilan_id = m.id
    LEFT JOIN `müşteri` mu ON t.müşteri_id  = mu.id
    WHERE m.emlakçı_id = '$emlakci_id'
    ORDER BY t.id DESC"
) or die(mysqli_error($baglanti));

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teklif Yönetimi - Tosuncuk Emlak</title>
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
        
        .badge-kiralik {
            background-color: var(--secondary-color);
        }
        
        .badge-satilik {
            background-color: var(--accent-color);
        }
        
        .good-offer {
            background-color: rgba(46, 204, 113, 0.1);
        }
        
        .low-offer {
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .medium-offer {
            background-color: rgba(241, 196, 15, 0.1);
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
                    <a href="teklifler.php" class="nav-link active">
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
                <h3 class="mb-4">Teklif Yönetimi</h3>
                
                <?php if (isset($silme_mesaji)): ?>
                <div class="alert alert-<?= $silme_durum ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= ($silme_durum == 'success') ? 'check' : 'exclamation' ?>-circle me-2"></i> <?= $silme_mesaji ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($onay_mesaji)): ?>
                <div class="alert alert-<?= $onay_durum ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= ($onay_durum == 'success') ? 'check' : 'exclamation' ?>-circle me-2"></i> <?= $onay_mesaji ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-hand-holding-usd me-2"></i> Gelen Teklifler
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($yeni_teklifler_sorgu) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Müşteri</th>
                                            <th>Mülk</th>
                                            <th>Mülk Fiyatı</th>
                                            <th>Teklif</th>
                                            <th>Fark %</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($teklif = mysqli_fetch_assoc($yeni_teklifler_sorgu)): 
                                            // Teklif ve mülk fiyatı karşılaştırması
                                            $mulk_fiyat = $teklif['mulk_fiyat'] ?? 0;
                                            $teklif_miktar = $teklif['Teklif'] ?? 0;
                                            
                                            $fark_yuzde = 0;
                                            $offer_class = '';
                                            
                                            if ($mulk_fiyat > 0) {
                                                $fark_yuzde = (($teklif_miktar - $mulk_fiyat) / $mulk_fiyat) * 100;
                                                
                                                // Teklifin durumuna göre satır renklendirme
                                                if ($fark_yuzde >= -5) {
                                                    $offer_class = 'good-offer';
                                                } elseif ($fark_yuzde >= -15) {
                                                    $offer_class = 'medium-offer';
                                                } else {
                                                    $offer_class = 'low-offer';
                                                }
                                            }
                                        ?>
                                            <tr class="<?= $offer_class ?>">
                                                <td><?= $teklif['id'] ?></td>
                                                <td><?= htmlspecialchars($teklif['musteri_adi'] ?? 'Bilinmeyen Müşteri') ?></td>
                                                <td><?= htmlspecialchars($teklif['mulk_adi'] ?? 'Bilinmeyen Mülk') ?></td>
                                                <td><?= number_format($mulk_fiyat, 0, ',', '.') ?> TL</td>
                                                <td><strong><?= number_format($teklif_miktar, 0, ',', '.') ?> TL</strong></td>
                                                <td>
                                                    <?php if ($mulk_fiyat > 0): ?>
                                                        <span class="badge <?= ($fark_yuzde >= 0) ? 'bg-success' : 'bg-danger' ?>">
                                                            <?= number_format($fark_yuzde, 1, ',', '.') ?>%
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Hesaplanamadı</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="teklifler.php?onayla=<?= $teklif['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Bu teklifi onaylamak istediğinizden emin misiniz?')">
                                                        <i class="fas fa-check"></i> Onayla
                                                    </a>
                                                    <a href="teklifler.php?sil=<?= $teklif['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu teklifi silmek istediğinizden emin misiniz?')">
                                                        <i class="fas fa-trash"></i> Reddet
                                                    </a>
                                                    <a href="detay.php?id=<?= $teklif['ilan_id'] ?>" class="btn btn-sm btn-info" target="_blank">
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
                                <i class="fas fa-info-circle me-2"></i> Henüz teklif bulunmuyor.
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
