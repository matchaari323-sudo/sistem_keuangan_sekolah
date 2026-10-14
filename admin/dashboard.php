<?php
require_once "../config.php";
require_admin();
$title = "Dashboard Admin";
$totalSiswa = $conn->query("SELECT COUNT(*) n FROM siswa")->fetch_assoc()['n'];
$totalBayar = $conn->query("SELECT COALESCE(SUM(jumlah),0) n FROM pembayaran")->fetch_assoc()['n'];
$lunas = $conn->query("SELECT COUNT(*) n FROM (SELECT t.id,t.nominal,COALESCE(SUM(p.jumlah),0) dibayar FROM tagihan t LEFT JOIN pembayaran p ON p.tagihan_id=t.id GROUP BY t.id,t.nominal HAVING dibayar>=t.nominal) x")->fetch_assoc()['n'];
$belum = $conn->query("SELECT COUNT(*) n FROM (SELECT t.id,t.nominal,COALESCE(SUM(p.jumlah),0) dibayar FROM tagihan t LEFT JOIN pembayaran p ON p.tagihan_id=t.id GROUP BY t.id,t.nominal HAVING dibayar<t.nominal) x")->fetch_assoc()['n'];
include "_header.php";
?>
<h1 class="page-title">Dashboard Admin</h1>
<p class="page-subtitle">Ringkasan sistem pembayaran</p>
<div class="cards">
    <div class="card">
        <div class="stat-title">Total siswa</div>
        <div class="stat-value blue"><?= $totalSiswa ?></div><small>Siswa aktif</small>
    </div>
    <div class="card">
        <div class="stat-title">Lunas bulan ini</div>
        <div class="stat-value green"><?= $lunas ?></div><small>Siswa</small>
    </div>
    <div class="card">
        <div class="stat-title">Belum lunas</div>
        <div class="stat-value red"><?= $belum ?></div><small>Siswa</small>
    </div>
    <div class="card">
        <div class="stat-title">Pemasukan</div>
        <div class="stat-value green"><?= rupiah($totalBayar) ?></div><small>Bulan ini</small>
    </div>
</div>
<div class="card">
    <h3>Rincian biaya per jurusan</h3>
    <p class="page-subtitle">Daftar biaya/tagihan untuk setiap kategori.</p>
    <div class="table-wrap">
        <table class="table fee-table">
            <tr>
                <th>Kategori</th>
                <th>Farmasi</th>
                <th>RPL</th>
            </tr>
            <tr>
                <td>Buku modul</td>
                <td>Rp.80.000</td>
                <td>Rp.60.000</td>
            </tr>
            <tr>
                <td> Baju</td>
                <td>Rp.250.000</td>
                <td>Rp.150.000</td>
            </tr>
            <tr>
                <td> Daftar ulang</td>
                <td>Rp.1.000.000</td>
                <td>Rp.800.000</td>
            </tr>
            <tr>
                <td> SPP perbulan</td>
                <td>Rp.400.000</td>
                <td>Rp.350.000</td>
            </tr>
            <tr class="total-row">
                <td><b>Total biaya</b></td>
                <td><b>Rp.1.730.000</b></td>
                <td><b>Rp.1.360.000</b></td>
            </tr>
        </table>
    </div>
</div>
<div class="notice">ⓘ Catatan: Semua pembayaran dilakukan secara langsung di Tata Usaha (Offline).</div>
<?php include "_footer.php"; ?>