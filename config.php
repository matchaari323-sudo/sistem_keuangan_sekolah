<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "keuangan_sekolah";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rupiah($value) {
    return "Rp " . number_format((float)$value, 0, ',', '.');
}

function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit;
    }
}

function require_siswa() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
        header("Location: ../index.php");
        exit;
    }
}
?>
