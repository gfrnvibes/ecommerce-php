<?php
// Mulai session untuk mengakses variabel session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect ke halaman login atau halaman utama
header("location: login.php");
exit;
?>