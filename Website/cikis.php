<?php
session_start();

// Oturum değişkenlerini temizle
$_SESSION = array();

// Oturum çerezini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

session_destroy();

// Anasayfaya yönlendir
header("Location: index.php");
exit;
?>
