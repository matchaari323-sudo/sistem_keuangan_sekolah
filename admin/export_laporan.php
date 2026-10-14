<?php
require_once "../config.php";
require_admin();

$format = strtolower($_GET['format'] ?? 'excel');
$bulan = (int)($_GET['bulan'] ?? date('n'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));
if ($bulan < 1 || $bulan > 12) $bulan = (int)date('n');
if ($tahun < 2000 || $tahun > 2100) $tahun = (int)date('Y');
$namaBulan = array('', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
$stmt = $conn->prepare("SELECT p.no_struk,p.jumlah,p.metode,p.keterangan,p.tanggal_bayar,s.nisn,s.nama,s.kelas,j.nama AS jenis,t.periode,t.tahun
FROM pembayaran p JOIN tagihan t ON t.id=p.tagihan_id JOIN siswa s ON s.id=t.siswa_id JOIN jenis_pembayaran j ON j.id=t.jenis_id
WHERE MONTH(p.tanggal_bayar)=? AND YEAR(p.tanggal_bayar)=? ORDER BY p.tanggal_bayar ASC,p.id ASC");
$stmt->bind_param("ii", $bulan, $tahun); $stmt->execute(); $result=$stmt->get_result();
$rows=[];$total=0;while($r=$result->fetch_assoc()){ $rows[]=$r; $total+=(float)$r['jumlah']; }
$judul='Laporan Pembayaran Bulanan - '.$namaBulan[$bulan].' '.$tahun;

if ($format === 'word') {
    header("Content-Type: application/msword; charset=UTF-8");
    header("Content-Disposition: attachment; filename=laporan_bulanan_{$tahun}_{$bulan}.doc");
} else {
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=laporan_bulanan_{$tahun}_{$bulan}.xls");
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Arial,sans-serif;font-size:11px}h2{text-align:center}table{border-collapse:collapse;width:100%}th,td{border:1px solid #999;padding:6px;text-align:center}th{background:#e4e8f8}.left{text-align:left}.total{font-weight:bold;background:#f1f1f1}</style></head><body>
<h2><?=htmlspecialchars($judul,ENT_QUOTES,'UTF-8')?></h2>
<p>Total transaksi: <?=count($rows)?> | Total pemasukan: <?=rupiah($total)?></p>
<table><tr><th>No</th><th>Tanggal</th><th>No Struk</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Jenis</th><th>Periode</th><th>Metode</th><th>Jumlah</th></tr>
<?php foreach($rows as $i=>$r): ?><tr><td><?=$i+1?></td><td><?=date('d-m-Y H:i',strtotime($r['tanggal_bayar']))?></td><td><?=htmlspecialchars($r['no_struk'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['nisn'],ENT_QUOTES,'UTF-8')?></td><td class="left"><?=htmlspecialchars($r['nama'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['kelas'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['jenis'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['periode'] ?: '-',ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['metode'],ENT_QUOTES,'UTF-8')?></td><td><?=rupiah($r['jumlah'])?></td></tr><?php endforeach; ?>
<tr class="total"><td colspan="9">TOTAL</td><td><?=rupiah($total)?></td></tr></table></body></html>
