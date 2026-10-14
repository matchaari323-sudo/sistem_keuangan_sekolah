<!-- <?php

// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistem_keuangan_sekolah";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal");
}


// =========================
// CEK LOGIN ADMIN
// =========================
function require_admin()
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit;
    }
}


// =========================
// CEK LOGIN SISWA
// =========================
function require_siswa()
{
    if (
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== 'siswa' ||
        !isset($_SESSION['siswa_id'])
    ) {
        header("Location: ../index.php");
        exit;
    }
}



function rupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}


// =========================
// ESCAPE HTML
// =========================
function e($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

?> -->