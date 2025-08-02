<?php
// File: helpers/auth_helper.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Memeriksa apakah pengguna sudah login dan memiliki peran yang sesuai.
 *
 * @param bool $require_admin Jika true, pengguna harus memiliki peran 'admin'.
 */
function check_auth($require_admin = false) {
    // Cek apakah user sudah login
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Anda harus login untuk mengakses halaman ini.'];
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit();
    }

    // Cek apakah user adalah admin jika diperlukan
    if ($require_admin && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')) {
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Anda tidak memiliki hak akses ke halaman ini.'];
        // Redirect ke halaman utama user jika bukan admin
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}
?>