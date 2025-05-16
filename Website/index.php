<?php
require_once 'baglanti.php';
session_start();

// Sayfalama
$sayfa_basina_ilan = 21;
$sayfa            = isset($_GET['sayfa']) ? (int)$_GET['sayfa'] : 1;
if ($sayfa < 1) $sayfa = 1;

// Filtreler
function get_int_or_empty($name) {
    return (isset($_GET[$name]) && $_GET[$name] !== '') ? intval($_GET[$name]) : '';
}
$min_fiyat = get_int_or_empty('min_fiyat');
$max_fiyat = get_int_or_empty('max_fiyat');
$oda_sayisi = get_int_or_empty('oda_sayisi');
$min_metrekare = get_int_or_empty('min_metrekare');
$max_metrekare = get_int_or_empty('max_metrekare');
$sehir = isset($_GET['sehir']) ? mysqli_real_escape_string($baglanti, $_GET['sehir']) : '';
$ilce = isset($_GET['ilce'])  ? mysqli_real_escape_string($baglanti, $_GET['ilce']) : '';
$tur = isset($_GET['tur'])   ? mysqli_real_escape_string($baglanti, $_GET['tur']) : '';
$durum_input = isset($_GET['durum']) ? strtolower($_GET['durum']) : '';
$durum_map = ['satılık' => 'Satılık', 'kiralık' => 'Kiralık'];
$durum = $durum_map[$durum_input] ?? '';

// Sorgu
$sql = "SELECT SQL_CALC_FOUND_ROWS m.*,
               k.`kullanıcı_adı` AS emlakci_adi
        FROM `mülk` m
        LEFT JOIN `kullanıcılar` k ON m.emlakçı_id = k.id
        WHERE 1=1";

if ($min_fiyat !== '') $sql .= " AND m.fiyat >= $min_fiyat";
if ($max_fiyat !== '') $sql .= " AND m.fiyat <= $max_fiyat";
if ($oda_sayisi !== '') $sql .= " AND m.odasayısı = $oda_sayisi";
if ($min_metrekare !== '') $sql .= " AND m.`m^2` >= $min_metrekare";
if ($max_metrekare !== '') $sql .= " AND m.`m^2` <= $max_metrekare";
if ($sehir !== '') $sql .= " AND m.şehir = '$sehir'";
if ($ilce !== '') $sql .= " AND m.ilçe  = '$ilce'";
if ($tur !== '') $sql .= " AND m.tür   = '$tur'";
if ($durum !== '') $sql .= " AND m.durum = '$durum'";

// Sayfa başına ilan sayısı
$offset = ($sayfa - 1) * $sayfa_basina_ilan;
$sql   .= " LIMIT $offset, $sayfa_basina_ilan";

$result = mysqli_query($baglanti, $sql) or die(mysqli_error($baglanti));

// Toplam ilan sayısı
$toplam_ilan       = mysqli_fetch_row(mysqli_query($baglanti, "SELECT FOUND_ROWS()"))[0];
$toplam_sayfa      = ceil($toplam_ilan / $sayfa_basina_ilan);

// Filtre listeleri
$sehirler = mysqli_query($baglanti, "SELECT DISTINCT şehir FROM `mülk` ORDER BY şehir");
$ilceler  = mysqli_query($baglanti, "SELECT DISTINCT ilçe  FROM `mülk` ORDER BY ilçe");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tosuncuk Emlak - Profesyonel Emlak Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color:rgb(4, 218, 39);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        .sidebar {
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 20px;
        }
        .filter-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        .property-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .property-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .property-price {
            color: var(--accent-color);
            font-weight: 700;
            font-size: 1.2rem;
        }
        .property-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-satilik {
            background-color: #ffeceb;
            color: var(--accent-color);
        }
        .status-kiralik {
            background-color: #e8f4fc;
            color: var(--secondary-color);
        }
        .property-features span {
            margin-right: 15px;
            color: #7f8c8d;
        }
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .login-btn {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .login-btn:hover {
            background-color: #1a252f;
            border-color: #1a252f;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .pagination .page-link {
            color: var(--primary-color);
        }
        .page-item.disabled .page-link {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
 <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-home me-2"></i>Tosuncuk Emlak
        </a>
        <div class="ms-auto">
            <?php if (isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true): ?>
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="fas fa-user me-1"></i> 
                        <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>
                    </div>
                    <a href="cikis.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i> Çıkış
                    </a>
                </div>
            <?php else: ?>
                <a href="giris.php" class="btn login-btn text-white me-2">
                    <i class="fas fa-sign-in-alt me-1"></i> Emlakçı Girişi
                </a>
                <a href="müşterigiriş.php" class="btn login-btn text-white">
                    <i class="fas fa-sign-in-alt me-1"></i> Müşteri Girişi
                </a>
            <?php endif; ?>
        </div>
    </div>
  </nav>
    

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row">
            <!-- Filtreleme Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="sidebar sticky-top" style="top: 20px;">
                    <h4 class="mb-4"><i class="fas fa-filter me-2"></i>Filtrele</h4>
                    
                    <form method="get" action="index.php">
                        <input type="hidden" name="sayfa" value="1">
                        
                        <!-- Fiyat Aralığı -->
                        <div class="mb-4">
                            <h6 class="filter-title"><i class="fas fa-tag me-2"></i>Fiyat Aralığı</h6>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" class="form-control" name="min_fiyat" placeholder="Min TL" value="<?= htmlspecialchars($min_fiyat) ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="max_fiyat" placeholder="Max TL" value="<?= htmlspecialchars($max_fiyat) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Oda Sayısı -->
                        <div class="mb-4">
                            <h6 class="filter-title"><i class="fas fa-bed me-2"></i>Oda Sayısı</h6>
                            <select class="form-select" name="oda_sayisi">
                                <option value="">Seçiniz</option>
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($oda_sayisi == $i) ? 'selected' : '' ?>>
                                        <?= $i ?> Oda
                                    </option>
                                <?php endfor; ?>
                                <option value="6" <?= ($oda_sayisi == 6) ? 'selected' : '' ?>>6+ Oda</option>
                            </select>
                        </div>
                        
                        <!-- Metrekare -->
                        <div class="mb-4">
                            <h6 class="filter-title"><i class="fas fa-ruler-combined me-2"></i>Metrekare</h6>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" class="form-control" name="min_metrekare" placeholder="Min m²" value="<?= htmlspecialchars($min_metrekare) ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="max_metrekare" placeholder="Max m²" value="<?= htmlspecialchars($max_metrekare) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Şehir -->
                        <div class="mb-4">
                            <h6 class="filter-title"><i class="fas fa-city me-2"></i>Şehir</h6>
                            <select class="form-select" name="sehir">
                                <option value="">Tüm Şehirler</option>
                                <?php while($row = mysqli_fetch_assoc($sehirler)): ?>
                                    <option value="<?= htmlspecialchars($row['şehir']) ?>" <?= ($sehir == $row['şehir']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['şehir']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- İlçe -->
                        <div class="mb-4">
                            <h6 class="filter-title"><i class="fas fa-map-marker-alt me-2"></i>İlçe</h6>
                            <select class="form-select" name="ilce">
                                <option value="">Tüm İlçeler</option>
                                <?php 
                                mysqli_data_seek($ilceler, 0);
                                while($row = mysqli_fetch_assoc($ilceler)): ?>
                                    <option value="<?= htmlspecialchars($row['ilçe']) ?>" <?= ($ilce == $row['ilçe']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['ilçe']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- Mülk Türü -->
                        <div class="mb-4">
                            <h6 class="filter-title"><i class="fas fa-building me-2"></i>Mülk Türü</h6>
                            <select class="form-select" name="tur">
                                <option value="">Tüm Türler</option>
                                <option value="Daire" <?= ($tur == 'Daire') ? 'selected' : '' ?>>Daire</option>
                                <option value="Villa" <?= ($tur == 'Villa') ? 'selected' : '' ?>>Villa</option>
                                <option value="Arsa" <?= ($tur == 'Arsa') ? 'selected' : '' ?>>Arsa</option>
                                <option value="Dükkan" <?= ($tur == 'Dükkan') ? 'selected' : '' ?>>Dükkan</option>
                                <option value="Müstakil Ev" <?= ($tur == 'Müstakil Ev') ? 'selected' : '' ?>>Müstakil Ev</option>
                            </select>
                        </div>
                        
                        <!-- Durum -->
                        <div class="mb-4">
                            <h6 class="filter-title"><i class="fas fa-info-circle me-2"></i>Durum</h6>
                            <select class="form-select" name="durum">
                                <option value="">Tüm Durumlar</option>
                                <option value="satılık" <?= ($durum == 'satılık') ? 'selected' : '' ?>>Satılık</option>
                                <option value="kiralık" <?= ($durum == 'kiralık') ? 'selected' : '' ?>>Kiralık</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-search me-1"></i> Filtrele
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i> Filtreleri Temizle
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- İlan Listesi -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="fas fa-list me-2"></i>İlanlar</h3>
                    <div class="text-muted">
                        Toplam <?= number_format($toplam_ilan, 0, ',', '.') ?> ilan bulundu
                        (<?= ($sayfa - 1) * $sayfa_basina_ilan + 1 ?>-<?= min($sayfa * $sayfa_basina_ilan, $toplam_ilan) ?> arası gösteriliyor)
                    </div>
                </div>
                
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <div class="row">
                        <?php while($ilan = mysqli_fetch_assoc($result)): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card property-card h-100">
                                    <img src="https://via.placeholder.com/400x300?text=<?= urlencode($ilan['başlık']) ?>" class="property-img card-img-top" alt="İlan Görseli">
                                    <div class="card-body">
                                        <span class="property-status <?= (mb_strtolower($ilan['durum']) === 'satılık' ? 'status-satilik' : 'status-kiralik') ?>">
                                            <?= htmlspecialchars($ilan['durum']) ?>
                                        </span>
                                        <h5 class="card-title mt-2"><?= htmlspecialchars($ilan['başlık']) ?></h5>
                                        <p class="property-price"><?= number_format($ilan['fiyat'], 0, ',', '.') ?> TL</p>
                                        <div class="property-features mb-3">
                                            <span><i class="fas fa-bed"></i> <?= htmlspecialchars($ilan['odasayısı']) ?> Oda</span>
                                            <span><i class="fas fa-ruler-combined"></i> <?= htmlspecialchars($ilan['m^2']) ?> m²</span>
                                        </div>
                                        <p class="card-text text-muted">
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ilan['ilçe']) ?>, <?= htmlspecialchars($ilan['şehir']) ?>
                                        </p>
                                        <p class="card-text"><?= nl2br(htmlspecialchars(mb_substr($ilan['açıklama'], 0, 100) . '...')) ?></p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-user-tie"></i> <?= htmlspecialchars($ilan['emlakci_adi']) ?>
                                            </small>
                                            <a href="detay.php?id=<?= $ilan['id'] ?>" class="btn btn-sm btn-outline-primary">Detaylar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Sayfalama -->
                    <nav aria-label="Sayfalama">
                        <ul class="pagination justify-content-center mt-4">
                            <?php
                            // Önceki sayfa butonu
                            if ($sayfa > 1) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="'.sayfa_linki(1).'" aria-label="İlk">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>';
                                echo '<li class="page-item">
                                    <a class="page-link" href="'.sayfa_linki($sayfa - 1).'" aria-label="Önceki">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>';
                            } else {
                                echo '<li class="page-item disabled">
                                    <span class="page-link">&laquo;&laquo;</span>
                                </li>';
                                echo '<li class="page-item disabled">
                                    <span class="page-link">&laquo;</span>
                                </li>';
                            }
                            
                            // Sayfa numaraları
                            $baslangic = max(1, $sayfa - 2);
                            $bitis = min($toplam_sayfa, $sayfa + 2);
                            
                            if ($baslangic > 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            
                            for ($i = $baslangic; $i <= $bitis; $i++) {
                                if ($i == $sayfa) {
                                    echo '<li class="page-item active"><span class="page-link">'.$i.'</span></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="'.sayfa_linki($i).'">'.$i.'</a></li>';
                                }
                            }
                            
                            if ($bitis < $toplam_sayfa) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            
                            // Sonraki sayfa butonu
                            if ($sayfa < $toplam_sayfa) {
                                echo '<li class="page-item">
                                    <a class="page-link" href="'.sayfa_linki($sayfa + 1).'" aria-label="Sonraki">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>';
                                echo '<li class="page-item">
                                    <a class="page-link" href="'.sayfa_linki($toplam_sayfa).'" aria-label="Son">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>';
                            } else {
                                echo '<li class="page-item disabled">
                                    <span class="page-link">&raquo;</span>
                                </li>';
                                echo '<li class="page-item disabled">
                                    <span class="page-link">&raquo;&raquo;</span>
                                </li>';
                            }
                            ?>
                        </ul>
                    </nav>
                    
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i> Filtreleme kriterlerinize uygun ilan bulunamadı.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Tosuncuk Emlak</h5>
                    <p>Bize güvenin.</p>
                </div>
                <div class="col-md-4">
                    <h5>İletişim</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i> 0 (212) 123 45 67</li>
                        <li><i class="fas fa-envelope me-2"></i> info@tosuncukemlak.com</li>
                    </ul>
                </div>
                
            </div>
            
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
mysqli_close($baglanti);

// Sayfa linki
function sayfa_linki($sayfa_no) {
    $query_params          = $_GET;
    $query_params['sayfa'] = $sayfa_no;
    return 'index.php?' . http_build_query($query_params);
}
?>
