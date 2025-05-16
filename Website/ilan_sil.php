<?php
session_start();
require_once 'baglanti.php';

// Giriş kontrolü
if (!isset($_SESSION["admin_kullanici"], $_SESSION["admin_giris"]) || $_SESSION["admin_giris"] !== true) {
    // Giriş yapılmamışsa "giris"e git
    header("Location: giris.php");
    exit();
}

// Aktif emlakçıyı bul
$emlakci_adi = $_SESSION["admin_kullanici"];
$emlakci_id  = 0;
$emlakci_sorgu = mysqli_query($baglanti,"SELECT id FROM `kullanıcılar` WHERE `kullanıcı_adı` = '" . mysqli_real_escape_string($baglanti, $emlakci_adi) . "'");
if ($emlakci = mysqli_fetch_assoc($emlakci_sorgu)) {
    $emlakci_id = $emlakci['id'];
}

// İlan silme
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ilan_id = intval($_GET['id']);

    // İlanın var mı, emlakçının mı
    $ilan_sorgu = mysqli_query($baglanti, "SELECT * FROM `mülk` WHERE id = $ilan_id");
    if (mysqli_num_rows($ilan_sorgu) > 0) {

        // İlanı sil
        if (mysqli_query($baglanti, "DELETE FROM `mülk` WHERE id = $ilan_id")) {
            header("Location: mulkler.php?silindi=1");
            exit();
        } else {
            header("Location: mulkler.php?hata=silme");
            exit();
        }
    } else {
        header("Location: mulkler.php?hata=bulunamadi");
        exit();
    }
} else {
    // İlan yok
    header("Location: mulkler.php?hata=gecersiz_id");
    exit();
}
?>
