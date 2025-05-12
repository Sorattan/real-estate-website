<?php
// Oturum başlatma
session_start();

// Veritabanı bağlantısı
require_once 'baglanti.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION["admin_kullanici"]) || !isset($_SESSION["admin_giris"]) || $_SESSION["admin_giris"] !== true) {
    // Giriş yapılmamış, yönlendir
    header("Location: giris.php");
    exit();
}

// Aktif emlakçı bilgilerini al
$emlakci_adi = $_SESSION["admin_kullanici"];
$emlakci_id = 0;

$emlakci_sorgu = mysqli_query($baglanti, "SELECT id FROM kullanicilar WHERE kullanici_adi = '$emlakci_adi'");
if ($emlakci = mysqli_fetch_assoc($emlakci_sorgu)) {
    $emlakci_id = $emlakci['id'];
}

// Randevu silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $randevu_id = intval($_GET['sil']);
    $sil_sorgu = mysqli_query($baglanti, "DELETE FROM randevu WHERE id = $randevu_id");
    
    if ($sil_sorgu) {
        $silme_mesaji = "Randevu başarıyla silindi.";
        $silme_durum = "success";
    } else {
        $silme_mesaji = "Randevu silinirken bir hata oluştu: " . mysqli_error($baglanti);
        $silme_durum = "danger";
    }
}

// Bugünün tarihini al
$bugun = date('Y-m-d');

// Bugünkü randevuları getir
$bugun_randevular_sorgu = mysqli_query($baglanti, "
    SELECT r.*, m.başlık as mulk_adi, mu.kullanici_adi as musteri_adi 
    FROM randevu r
    LEFT JOIN mülk m ON r.ilan_id = m.id
    LEFT JOIN müşteri mu ON r.müşteri_id = mu.id
    WHERE r.randevu_tarihi = '$bugun' AND m.emlakçı_id = '$emlakci_id'
    ORDER BY r.randevu_saat ASC
");

// Gelecek randevuları getir
$gelecek_randevular_sorgu = mysqli_query($baglanti, "
    SELECT r.*, m.başlık as mulk_adi, mu.kullanici_adi as musteri_adi 
    FROM randevu r
    LEFT JOIN mülk m ON r.ilan_id = m.id
    LEFT JOIN müşteri mu ON r.müşteri_id = mu.id
    WHERE r.randevu_tarihi > '$bugun' AND m.emlakçı_id = '$emlakci_id'
    ORDER BY r.randevu_tarihi ASC, r.randevu_saat ASC
");

// Geçmiş randevuları getir
$gecmis_randevular_sorgu = mysqli_query($baglanti, "
    SELECT r.*, m.başlık as mulk_adi, mu.kullanici_adi as musteri_adi 
    FROM randevu r
    LEFT JOIN mülk m ON r.ilan_id = m.id
    LEFT JOIN müşteri mu ON r.müşteri_id = mu.id
    WHERE r.randevu_tarihi < '$bugun' AND m.emlakçı_id = '$emlakci_id'
    ORDER BY r.randevu_tarihi DESC, r.randevu_saat DESC
    LIMIT 20
");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Randevu Yönetimi - Tosuncuk Emlak</title>
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
        
        .appointment-today {
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .appointment-past {
            opacity: 0.7;
        }
        
        .tab-pane {
            padding: 20px 0;
        }
        
        .nav-tabs .nav-link {
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--secondary-color);
            font-weight: 600;
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
                    <a href="randevular.php" class="nav-link active">
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
                <h3 class="mb-4">Randevu Yönetimi</h3>
                
                <?php if (isset($silme_mesaji)): ?>
                <div class="alert alert-<?= $silme_durum ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= ($silme_durum == 'success') ? 'check' : 'exclamation' ?>-circle me-2"></i> <?= $silme_mesaji ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" id="appointmentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button" role="tab" aria-controls="today" aria-selected="true">
                                    <i class="fas fa-calendar-day me-1"></i> Bugünkü Randevular
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab" aria-controls="upcoming" aria-selected="false">
                                    <i class="fas fa-calendar-plus me-1"></i> Gelecek Randevular
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="past-tab" data-bs-toggle="tab" data-bs-target="#past" type="button" role="tab" aria-controls="past" aria-selected="false">
                                    <i class="fas fa-calendar-minus me-1"></i> Geçmiş Randevular
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="appointmentTabsContent">
                            <!-- Bugünkü Randevular -->
                            <div class="tab-pane fade show active" id="today" role="tabpanel" aria-labelledby="today-tab">
                                <?php if (mysqli_num_rows($bugun_randevular_sorgu) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Müşteri</th>
                                                    <th>Mülk</th>
                                                    <th>Saat</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($randevu = mysqli_fetch_assoc($bugun_randevular_sorgu)): ?>
                                                    <tr class="appointment-today">
                                                        <td><?= $randevu['id'] ?></td>
                                                        <td><?= htmlspecialchars($randevu['musteri_adi'] ?? 'Bilinmeyen Müşteri') ?></td>
                                                        <td><?= htmlspecialchars($randevu['mulk_adi'] ?? 'Bilinmeyen Mülk') ?></td>
                                                        <td><strong><?= date('H:i', strtotime($randevu['randevu_saat'])) ?></strong></td>
                                                        <td>
                                                            <a href="randevular.php?sil=<?= $randevu['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?')">
                                                                <i class="fas fa-trash"></i> Sil
                                                            </a>
                                                            <a href="detay.php?id=<?= $randevu['ilan_id'] ?>" class="btn btn-sm btn-info" target="_blank">
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
                                        <i class="fas fa-info-circle me-2"></i> Bugün için planlanmış randevu bulunmuyor.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Gelecek Randevular -->
                            <div class="tab-pane fade" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                                <?php if (mysqli_num_rows($gelecek_randevular_sorgu) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Müşteri</th>
                                                    <th>Mülk</th>
                                                    <th>Tarih</th>
                                                    <th>Saat</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($randevu = mysqli_fetch_assoc($gelecek_randevular_sorgu)): ?>
                                                    <tr>
                                                        <td><?= $randevu['id'] ?></td>
                                                        <td><?= htmlspecialchars($randevu['musteri_adi'] ?? 'Bilinmeyen Müşteri') ?></td>
                                                        <td><?= htmlspecialchars($randevu['mulk_adi'] ?? 'Bilinmeyen Mülk') ?></td>
                                                        <td><?= date('d.m.Y', strtotime($randevu['randevu_tarihi'])) ?></td>
                                                        <td><?= date('H:i', strtotime($randevu['randevu_saat'])) ?></td>
                                                        <td>
                                                            <a href="randevular.php?sil=<?= $randevu['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?')">
                                                                <i class="fas fa-trash"></i> Sil
                                                            </a>
                                                            <a href="detay.php?id=<?= $randevu['ilan_id'] ?>" class="btn btn-sm btn-info" target="_blank">
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
                                        <i class="fas fa-info-circle me-2"></i> Gelecek için planlanmış randevu bulunmuyor.
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Geçmiş Randevular -->
                            <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                                <?php if (mysqli_num_rows($gecmis_randevular_sorgu) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Müşteri</th>
                                                    <th>Mülk</th>
                                                    <th>Tarih</th>
                                                    <th>Saat</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($randevu = mysqli_fetch_assoc($gecmis_randevular_sorgu)): ?>
                                                    <tr class="appointment-past">
                                                        <td><?= $randevu['id'] ?></td>
                                                        <td><?= htmlspecialchars($randevu['musteri_adi'] ?? 'Bilinmeyen Müşteri') ?></td>
                                                        <td><?= htmlspecialchars($randevu['mulk_adi'] ?? 'Bilinmeyen Mülk') ?></td>
                                                        <td><?= date('d.m.Y', strtotime($randevu['randevu_tarihi'])) ?></td>
                                                        <td><?= date('H:i', strtotime($randevu['randevu_saat'])) ?></td>
                                                        <td>
                                                            <a href="randevular.php?sil=<?= $randevu['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bu randevuyu silmek istediğinizden emin misiniz?')">
                                                                <i class="fas fa-trash"></i> Sil
                                                            </a>
                                                            <a href="detay.php?id=<?= $randevu['ilan_id'] ?>" class="btn btn-sm btn-info" target="_blank">
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
                                        <i class="fas fa-info-circle me-2"></i> Geçmiş randevu kaydı bulunmuyor.
                                    </div>
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