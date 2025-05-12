<?php
require_once 'baglanti.php'; // Üyelik veritabanı bağlantısı

// Oturum başlatma
session_start();

// İlan ID'sini al
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ilan_id = mysqli_real_escape_string($baglanti, $_GET['id']);

    // İlan ve emlakçı bilgilerini çek
    $ilan_sorgu = mysqli_query($baglanti, "SELECT m.*, k.kullanici_adi AS emlakci_adi, k.email AS emlakci_email, k.tel AS emlakci_tel, k.id AS emlakci_id
                                           FROM mülk m
                                           LEFT JOIN kullanicilar k ON m.emlakçı_id = k.id
                                           WHERE m.id = $ilan_id");

    if ($ilan = mysqli_fetch_assoc($ilan_sorgu)) {
        // Satılmış/kiralanmış ilan kontrolü yapalım ama ayrı sayfa göstermeyelim
        $is_sold = ($ilan['durum'] == 'Satıldı/Kiralandı');
        
        // Teklif gönderme işlemi
        if (isset($_POST['gonder_teklif']) && isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true && !$is_sold) {
            $teklif_miktari = mysqli_real_escape_string($baglanti, $_POST['teklif_miktari']);
            $musteri_id = mysqli_real_escape_string($baglanti, $_POST['musteri_id']);
            $emlakci_id = mysqli_real_escape_string($baglanti, $_POST['emlakci_id']);
            
            $teklif_ekle = "INSERT INTO teklif (ilan_id, müşteri_id, emlakçı_id, Teklif) 
                            VALUES ('$ilan_id', '$musteri_id', '$emlakci_id', '$teklif_miktari')";
            
            if (mysqli_query($baglanti, $teklif_ekle)) {
                $teklif_mesaj = "Teklifiniz başarıyla gönderildi!";
                $teklif_durum = "success";
            } else {
                $teklif_mesaj = "Teklif gönderilirken bir hata oluştu: " . mysqli_error($baglanti);
                $teklif_durum = "danger";
            }
        }
        
        // Randevu alma işlemi
        if (isset($_POST['randevu_al']) && isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true && !$is_sold) {
            $randevu_tarihi = mysqli_real_escape_string($baglanti, $_POST['randevu_tarihi']);
            $randevu_saati = mysqli_real_escape_string($baglanti, $_POST['randevu_saati']);
            $musteri_id = mysqli_real_escape_string($baglanti, $_POST['musteri_id']);
            $emlakci_id = mysqli_real_escape_string($baglanti, $_POST['emlakci_id']);
            
            // Müşteri ID'sinin kullanicilar tablosunda var olup olmadığını kontrol et
            $musteri_kontrol = mysqli_query($baglanti, "SELECT id FROM kullanicilar WHERE id = '$musteri_id'");
            
            if (mysqli_num_rows($musteri_kontrol) > 0) {
                // Müşteri ID'si kullanicilar tablosunda var, randevu ekle
                $randevu_ekle = "INSERT INTO randevu (ilan_id, müşteri_id, emlakçı_id, randevu_saat, randevu_tarihi) 
                                 VALUES ('$ilan_id', '$musteri_id', '$emlakci_id', '$randevu_saati', '$randevu_tarihi')";
                
                if (mysqli_query($baglanti, $randevu_ekle)) {
                    $randevu_mesaj = "Randevunuz başarıyla oluşturuldu!";
                    $randevu_durum = "success";
                } else {
                    $randevu_mesaj = "Randevu oluşturulurken bir hata oluştu: " . mysqli_error($baglanti);
                    $randevu_durum = "danger";
                }
            } else {
                // Müşteri ID'si kullanicilar tablosunda yok, hata mesajı göster
                $randevu_mesaj = "Sistem hatası: Müşteri bilgisi bulunamadı. Lütfen site yöneticisi ile iletişime geçin.";
                $randevu_durum = "danger";
            }
        }
        
        // Satın alma işlemi
        if (isset($_POST['satin_al']) && isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true && !$is_sold) {
            $musteri_id = mysqli_real_escape_string($baglanti, $_POST['musteri_id']);
            $emlakci_id = mysqli_real_escape_string($baglanti, $_POST['emlakci_id']);
            
            $satin_al_ekle = "INSERT INTO satın_alım (ilan_id, müşteri_id, emlakçı_id, alım_tarihi) 
                              VALUES ('$ilan_id', '$musteri_id', '$emlakci_id', NOW())";
            
            if (mysqli_query($baglanti, $satin_al_ekle)) {
                $satin_al_mesaj = "Satın / Kiralama  işleminiz başarıyla gerçekleştirildi!";
                $satin_al_durum = "success";
            } else {
                $satin_al_mesaj = "Satın  / Kiralama işlemi sırasında bir hata oluştu: " . mysqli_error($baglanti);
                $satin_al_durum = "danger";
            }
        }
        
        // Müşteri ID'sini al (eğer giriş yapılmışsa)
        $musteri_id = 0;
        if (isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true && isset($_SESSION['kullanici_adi'])) {
            $kullanici_adi = mysqli_real_escape_string($baglanti, $_SESSION['kullanici_adi']);
            $musteri_sorgu = mysqli_query($baglanti, "SELECT id FROM müşteri WHERE kullanici_adi = '$kullanici_adi'");
            
            if ($musteri = mysqli_fetch_assoc($musteri_sorgu)) {
                $musteri_id = $musteri['id'];
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= htmlspecialchars($ilan['başlık']) ?> - Tosuncuk Emlak</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                :root {
                    --primary-color: #2c3e50;
                    --secondary-color: #3498db;
                    --accent-color: #e74c3c;
                    --success-color: #2ecc71;
                }
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f8f9fa;
                }
                .navbar-brand {
                    font-weight: 700;
                    color: var(--primary-color) !important;
                }
                .property-detail {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 0 15px rgba(0,0,0,0.1);
                    margin-top: 20px;
                }
                .property-title {
                    color: var(--primary-color);
                    margin-bottom: 20px;
                }
                .property-price {
                    color: var(--accent-color);
                    font-size: 1.5rem;
                    font-weight: bold;
                    margin-bottom: 15px;
                }
                .property-info-item {
                    margin-bottom: 10px;
                    color: #555;
                }
                .property-info-item strong {
                    color: var(--primary-color);
                    font-weight: 600;
                    margin-right: 5px;
                }
                .agent-info {
                    background-color: #f0f0f0;
                    padding: 20px;
                    border-radius: 8px;
                    margin-top: 30px;
                }
                .agent-title {
                    color: var(--secondary-color);
                    margin-bottom: 15px;
                }
                .agent-detail {
                    margin-bottom: 8px;
                }
                .action-buttons {
                    margin-top: 25px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                }
                .action-buttons .btn {
                    margin-right: 10px;
                    margin-bottom: 10px;
                }
                .go-back-btn {
                    margin-top: 20px;
                }
                .modal-dialog {
                    max-width: 600px;
                    margin: 1.75rem auto;
                }
                .btn-teklif {
                    background-color: var(--accent-color);
                    color: white;
                }
                .btn-teklif:hover {
                    background-color: #c0392b;
                    color: white;
                }
                .btn-randevu {
                    background-color: var(--secondary-color);
                    color: white;
                }
                .btn-randevu:hover {
                    background-color: #2980b9;
                    color: white;
                }
                .btn-satin-al {
                    background-color: var(--success-color);
                    color: white;
                }
                .btn-satin-al:hover {
                    background-color: #27ae60;
                    color: white;
                }
                .unavailable-notice {
                    background-color: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    text-align: center;
                }
                .unavailable-notice h4 {
                    font-weight: bold;
                    margin-bottom: 10px;
                }
                .btn-disabled {
                    opacity: 0.65;
                    cursor: not-allowed;
                    pointer-events: none;
                }
            </style>
        </head>
        <body>
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand" href="index.php">
                        <i class="fas fa-home me-2"></i>Tosuncuk Emlak
                    </a>
                    <div class="d-flex align-items-center">
                        <?php if (isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true): ?>
                            <div class="me-3">
                                <i class="fas fa-user me-1"></i> 
                                <?= htmlspecialchars($_SESSION['kullanici_adi']) ?>
                            </div>
                            <a href="cikis.php" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-sign-out-alt me-1"></i> Çıkış
                            </a>
                        <?php else: ?>
                            <a href="müşterigiriş.php" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-sign-in-alt me-1"></i> Giriş
                            </a>
                            <a href="müşterikayit.php" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus me-1"></i> Kayıt Ol
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>

            <div class="container py-5">
                <!-- Bildirimler -->
                <?php if (isset($teklif_mesaj)): ?>
                    <div class="alert alert-<?= $teklif_durum ?> alert-dismissible fade show" role="alert">
                        <?= $teklif_mesaj ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($randevu_mesaj)): ?>
                    <div class="alert alert-<?= $randevu_durum ?> alert-dismissible fade show" role="alert">
                        <?= $randevu_mesaj ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($satin_al_mesaj)): ?>
                    <div class="alert alert-<?= $satin_al_durum ?> alert-dismissible fade show" role="alert">
                        <?= $satin_al_mesaj ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="<?= $is_sold ? 'col-md-8' : 'col-md-12' ?>">
                        <div class="property-detail">
                            <h2 class="property-title"><?= htmlspecialchars($ilan['başlık']) ?></h2>
                            <p class="property-price"><?= number_format($ilan['fiyat'], 0, ',', '.') ?> TL - <span class="text-muted"><?= htmlspecialchars($ilan['durum']) ?></span></p>
                            <p class="property-info-item"><strong>Açıklama:</strong> <?= nl2br(htmlspecialchars($ilan['açıklama'])) ?></p>
                            <p class="property-info-item"><strong>Şehir:</strong> <?= htmlspecialchars($ilan['şehir']) ?></p>
                            <p class="property-info-item"><strong>İlçe:</strong> <?= htmlspecialchars($ilan['ilçe']) ?></p>
                            <p class="property-info-item"><strong>Oda Sayısı:</strong> <?= htmlspecialchars($ilan['odasayısı']) ?></p>
                            <p class="property-info-item"><strong>Metrekare:</strong> <?= htmlspecialchars($ilan['m^2']) ?> m²</p>
                            <p class="property-info-item"><strong>Mülk Türü:</strong> <?= htmlspecialchars($ilan['tür']) ?></p>

                            <?php if ($ilan['emlakci_adi']): ?>
                                <div class="agent-info">
                                    <h4 class="agent-title"><i class="fas fa-user-tie me-2"></i>Emlakçı Bilgileri</h4>
                                    <p class="agent-detail"><strong>Adı Soyadı:</strong> <?= htmlspecialchars($ilan['emlakci_adi']) ?></p>
                                    <p class="agent-detail"><strong>E-posta:</strong> <?= htmlspecialchars($ilan['emlakci_email']) ?></p>
                                    <p class="agent-detail"><strong>Telefon:</strong> <?= htmlspecialchars($ilan['emlakci_tel']) ?></p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i> Emlakçı bilgileri bulunamadı.
                                </div>
                            <?php endif; ?>

                            <!-- İşlem Butonları -->
                            <div class="action-buttons">
                                <?php if (isset($_SESSION['giris_yapildi']) && $_SESSION['giris_yapildi'] === true): ?>
                                    <!-- Teklif Ver Butonu -->
                                    <button type="button" class="btn btn-teklif <?= $is_sold ? 'btn-disabled' : '' ?>" <?= $is_sold ? '' : 'data-bs-toggle="modal" data-bs-target="#teklifModal"' ?>>
                                        <i class="fas fa-money-bill-wave me-1"></i> Teklif Ver
                                    </button>
                                    
                                    <!-- Randevu Al Butonu -->
                                    <button type="button" class="btn btn-randevu <?= $is_sold ? 'btn-disabled' : '' ?>" <?= $is_sold ? '' : 'data-bs-toggle="modal" data-bs-target="#randevuModal"' ?>>
                                        <i class="far fa-calendar-alt me-1"></i> Randevu Al
                                    </button>
                                    
                                    <!-- Satın Al Butonu -->
                                    <button type="button" class="btn btn-satin-al <?= $is_sold ? 'btn-disabled' : '' ?>" <?= $is_sold ? '' : 'data-bs-toggle="modal" data-bs-target="#satinAlModal"' ?>>
                                        <i class="fas fa-shopping-cart me-1"></i> Satın Al/ Kirala
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i> 
                                        Bu ilan için teklif vermek, randevu almak veya satın/kiralamak  için 
                                        <a href="müşterigiriş.php" class="alert-link">giriş yapmanız</a> gerekmektedir.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <a href="index.php" class="btn btn-secondary go-back-btn">
                                <i class="fas fa-arrow-left me-1"></i> İlanlara Geri Dön
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($is_sold): ?>
                    <div class="col-md-4">
                        <div class="unavailable-notice">
                            <h4><i class="fas fa-exclamation-circle me-2"></i>Bu İlan Artık Mevcut Değil</h4>
                            <p>Bu mülk satılmış veya kiralanmıştır.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Teklif Verme Modal -->
            <div class="modal fade" id="teklifModal" tabindex="-1" aria-labelledby="teklifModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="teklifModalLabel">Teklif Ver</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="teklif_miktari" class="form-label">Teklif Miktarı (TL)</label>
                                    <input type="number" class="form-control" id="teklif_miktari" name="teklif_miktari" min="1" required>
                                </div>
                                <input type="hidden" name="musteri_id" value="<?= $musteri_id ?>">
                                <input type="hidden" name="emlakci_id" value="<?= $ilan['emlakci_id'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="submit" name="gonder_teklif" class="btn btn-teklif">Teklifi Gönder</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Randevu Alma Modal -->
            <div class="modal fade" id="randevuModal" tabindex="-1" aria-labelledby="randevuModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="randevuModalLabel">Randevu Al</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="randevu_tarihi" class="form-label">Randevu Tarihi</label>
                                    <input type="date" class="form-control" id="randevu_tarihi" name="randevu_tarihi" required>
                                </div>
                                <div class="mb-3">
                                    <label for="randevu_saati" class="form-label">Randevu Saati</label>
                                    <input type="time" class="form-control" id="randevu_saati" name="randevu_saati" required>
                                </div>
                                <input type="hidden" name="musteri_id" value="<?= $musteri_id ?>">
                                <input type="hidden" name="emlakci_id" value="<?= $ilan['emlakci_id'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="submit" name="randevu_al" class="btn btn-randevu">Randevu Oluştur</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Satın Alma Modal -->
            <div class="modal fade" id="satinAlModal" tabindex="-1" aria-labelledby="satinAlModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="satinAlModalLabel">Satın / Kiralama Onayı</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="post">
                            <div class="modal-body">
                                <p class="mb-0">Bu mülkü <?= number_format($ilan['fiyat'], 0, ',', '.') ?> TL fiyatıyla satın / kiralama  istediğinizden emin misiniz?</p>
                                <input type="hidden" name="musteri_id" value="<?= $musteri_id ?>">
                                <input type="hidden" name="emlakci_id" value="<?= $ilan['emlakci_id'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="submit" name="satin_al" class="btn btn-satin-al">Satın Al / Kiralama</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <footer class="bg-dark text-white py-4 mt-5">
                <div class="container text-center">
                    <p class="mb-0">&copy; 2025 Tosuncuk Emlak. Tüm hakları saklıdır.</p>
                </div>
            </footer>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    } else {
        echo "İlan bulunamadı.";
    }
    mysqli_close($baglanti);
} else {
    echo "Geçersiz ilan ID'si.";
}
?>