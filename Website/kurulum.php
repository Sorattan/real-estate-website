<?php
// Veritabanı bağlantısı
require_once 'baglanti.php';

// Hata mesajlarını göster
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// SQL dosyasını okuma ve çalıştırma fonksiyonu
function runSQLFile($file, $baglanti) {
    try {
        // SQL dosyasını oku
        $sql = file_get_contents($file);
        
        // SQL dosyasında özellikle trigger için DELIMITER // kullanıldığından
        // Her bir SQL komutunu ayrı ayrı çalıştırmalıyız
        
        // Önce Index ve View komutlarını çalıştır
        // CREATE INDEX ve CREATE VIEW ifadelerini içeren satırları bul
        preg_match_all('/CREATE\s+(INDEX|VIEW)\s+.*?;/is', $sql, $matches);
        
        foreach ($matches[0] as $query) {
            try {
                mysqli_query($baglanti, $query);
                echo "<div class='alert alert-success'>SQL sorgusu başarıyla çalıştırıldı: " . htmlspecialchars(substr($query, 0, 50)) . "...</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-warning'>Sorgu çalıştırılamadı (muhtemelen zaten var): " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        
        // Trigger komutlarını çalıştır
        // DELIMITER // ... DELIMITER ; bloklarını bul
        preg_match_all('/DELIMITER \/\/(.*?)DELIMITER ;/is', $sql, $matches);
        
        foreach ($matches[1] as $block) {
            $triggers = explode('END//', $block);
            foreach ($triggers as $trigger) {
                if (trim($trigger) == "") continue;
                
                $trigger = $trigger . "END";
                try {
                    mysqli_query($baglanti, $trigger);
                    echo "<div class='alert alert-success'>Trigger başarıyla oluşturuldu.</div>";
                } catch (Exception $e) {
                    echo "<div class='alert alert-warning'>Trigger oluşturulamadı (muhtemelen zaten var): " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
        }
        
        // Bildirimler tablosunu oluştur (eğer yoksa)
        preg_match('/CREATE TABLE IF NOT EXISTS bildirimler(.*?);/is', $sql, $matches);
        if (isset($matches[0])) {
            try {
                mysqli_query($baglanti, $matches[0]);
                echo "<div class='alert alert-success'>Bildirimler tablosu kontrol edildi.</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-warning'>Bildirimler tablosu oluşturulamadı: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        
        return true;
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Hata: " . htmlspecialchars($e->getMessage()) . "</div>";
        return false;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veritabanı Kurulum - Tosuncuk Emlak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        .card-header {
            background-color: #3498db;
            color: white;
            font-weight: 600;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .alert {
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-database me-2"></i>Veritabanı Kurulum</h2>
                <p class="mb-0">Index, View ve Trigger Yapılarını Kurma</p>
            </div>
            <div class="card-body">
                <?php
                // Kurulum butonuna basıldıysa
                if (isset($_POST['install'])) {
                    // SQL dosyasını çalıştır
                    if (file_exists('setup.sql')) {
                        if (runSQLFile('setup.sql', $baglanti)) {
                            echo '<div class="alert alert-success">
                                <h4><i class="fas fa-check-circle me-2"></i>Kurulum Tamamlandı!</h4>
                                <p>Tüm veritabanı yapıları başarıyla kuruldu. Sisteminiz artık daha hızlı ve etkin çalışacak.</p>
                                <p>Eklenen yapılar:</p>
                                <ul>
                                    <li><strong>Index Yapıları:</strong> Arama ve sorguları hızlandıran indeksler</li>
                                    <li><strong>View Yapıları:</strong> Karmaşık sorguları basitleştiren hazır görünümler</li>
                                    <li><strong>Trigger Yapıları:</strong> Otomatik işlemleri gerçekleştiren tetikleyiciler</li>
                                    <li><strong>Bildirimler Tablosu:</strong> Sistem bildirimlerini saklayacak yeni bir tablo</li>
                                </ul>
                                <a href="acilis.php" class="btn btn-primary mt-3">Panele Dön</a>
                            </div>';
                        } else {
                            echo '<div class="alert alert-danger">
                                <h4><i class="fas fa-exclamation-triangle me-2"></i>Kurulum Tamamlanamadı!</h4>
                                <p>Kurulum sırasında hatalar oluştu. Lütfen yukarıdaki hata mesajlarını kontrol edin.</p>
                                <a href="kurulum.php" class="btn btn-warning mt-3">Tekrar Dene</a>
                            </div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i>SQL Dosyası Bulunamadı!</h4>
                            <p>setup.sql dosyası bulunamadı. Lütfen bu dosyanın kurulum.php ile aynı dizinde olduğundan emin olun.</p>
                        </div>';
                    }
                } else {
                    // Kurulum başlatma formu
                    ?>
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle me-2"></i>Veritabanı Yapılarını Kurma</h4>
                        <p>Bu kurulum, veritabanınıza aşağıdaki yapıları ekleyecektir:</p>
                        <ul>
                            <li><strong>Index Yapıları:</strong> Arama ve listeleme işlemlerini hızlandırır</li>
                            <li><strong>View Yapıları:</strong> Karmaşık sorguları basitleştirir</li>
                            <li><strong>Trigger Yapıları:</strong> Otomatik işlemleri gerçekleştirir</li>
                            <li><strong>Bildirimler Tablosu:</strong> Sistem bildirimlerini saklar</li>
                        </ul>
                        <p class="mb-0">Bu yapıların kurulumu, sistemin performansını artıracak ve yeni özellikler ekleyecektir.</p>
                    </div>
                    
                    <div class="alert alert-warning">
                        <p><strong>Uyarı:</strong> Kurulum öncesi veritabanı yedeği almanız önerilir. Bu işlem, mevcut veritabanı yapılarını değiştirecektir.</p>
                    </div>
                    
                    <form method="post" action="">
                        <div class="d-grid gap-2">
                            <button type="submit" name="install" class="btn btn-primary">Kurulumu Başlat</button>
                            <a href="acilis.php" class="btn btn-secondary">İptal</a>
                        </div>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Font Awesome ve Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>