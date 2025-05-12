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

// ID kontrolü
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ilan_id = intval($_GET['id']);
    
    // İlanın var olup olmadığını ve bu emlakçıya ait olup olmadığını kontrol et
    $ilan_sorgu = mysqli_query($baglanti, "SELECT * FROM mülk WHERE id = $ilan_id");
    
    if (mysqli_num_rows($ilan_sorgu) > 0) {
        $ilan = mysqli_fetch_assoc($ilan_sorgu);
        
        // İlanı sil
        $sil_sorgu = mysqli_query($baglanti, "DELETE FROM mülk WHERE id = $ilan_id");
        
        if ($sil_sorgu) {
            // Başarılı silme, mülkler sayfasına yönlendir
            header("Location: mulkler.php?silindi=1");
            exit();
        } else {
            // Silme hatası
            header("Location: mulkler.php?hata=silme");
            exit();
        }
    } else {
        // İlan bulunamadı
        header("Location: mulkler.php?hata=bulunamadi");
        exit();
    }
} else {
    // Geçersiz ID
    header("Location: mulkler.php?hata=gecersiz_id");
    exit();
}
?>