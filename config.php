<?php
// =====================================
// DATABASE CONFIGURATION
// =====================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'surat_db');

// Membuat koneksi database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Timezone Indonesia
date_default_timezone_set('Asia/Jakarta');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================
// HELPER FUNCTIONS
// =====================================

// Function untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function untuk cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function untuk cek jabatan
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    
    if (is_array($roles)) {
        return in_array($_SESSION['jabatan'], $roles);
    }
    return $_SESSION['jabatan'] === $roles;
}

// Function untuk proteksi halaman
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// Function untuk escape string
function clean($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Function untuk format tanggal Indonesia
function formatTanggal($datetime) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $timestamp = strtotime($datetime);
    $tgl = date('d', $timestamp);
    $bln = $bulan[(int)date('m', $timestamp)];
    $thn = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return "$tgl $bln $thn, $jam WIB";
}

// Function untuk get user info
function getUserInfo($user_id) {
    global $conn;
    $query = "SELECT u.*, b.nama_bagian, s.nama_subbag 
              FROM users u 
              LEFT JOIN bagian b ON u.bagian_id = b.id_bagian
              LEFT JOIN subbag s ON u.subbag_id = s.id_subbag
              WHERE u.id_user = " . (int)$user_id;
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Function untuk hitung notifikasi belum dibaca
function countUnreadNotif($user_id) {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM notifikasi 
              WHERE id_user = " . (int)$user_id . " AND status = 'unread'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}
?>