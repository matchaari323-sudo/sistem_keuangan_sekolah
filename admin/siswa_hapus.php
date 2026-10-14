<?php
require_once "../config.php";
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: data_siswa.php");
    exit;
}

// Hapus data pembayaran terlebih dahulu karena FK pembayaran -> tagihan
// memakai ON DELETE RESTRICT. Setelah itu data siswa dan tagihannya bisa dihapus.
$conn->begin_transaction();

try {
    $cek = $conn->prepare("SELECT id FROM siswa WHERE id=? LIMIT 1");
    $cek->bind_param("i", $id);
    $cek->execute();
    $ada = $cek->get_result()->fetch_assoc();
    if (!$ada) {
        throw new Exception("Data siswa tidak ditemukan.");
    }

    $hapusPembayaran = $conn->prepare(
        "DELETE p FROM pembayaran p
         INNER JOIN tagihan t ON t.id = p.tagihan_id
         WHERE t.siswa_id = ?"
    );
    $hapusPembayaran->bind_param("i", $id);
    $hapusPembayaran->execute();

    $hapusSiswa = $conn->prepare("DELETE FROM siswa WHERE id=?");
    $hapusSiswa->bind_param("i", $id);
    if (!$hapusSiswa->execute()) {
        throw new Exception("Data siswa gagal dihapus.");
    }

    $conn->commit();
    $_SESSION['flash'] = 'Data siswa berhasil dihapus beserta tagihan dan riwayat pembayarannya.';
} catch (Throwable $e) {
    $conn->rollback();
    $_SESSION['flash'] = 'Data siswa gagal dihapus. Pastikan data masih tersedia.';
}

header("Location: data_siswa.php");
exit;
?>
