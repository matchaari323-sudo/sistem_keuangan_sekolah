<?php
require_once "../config.php";require_admin();$id=(int)($_GET['id']??0);
$stmt=$conn->prepare("SELECT p.*,s.nisn,s.nama,s.kelas,s.jurusan,j.nama jenis,t.periode,t.tahun,u.nama petugas FROM pembayaran p JOIN tagihan t ON t.id=p.tagihan_id JOIN siswa s ON s.id=t.siswa_id JOIN jenis_pembayaran j ON j.id=t.jenis_id LEFT JOIN users u ON u.id=p.admin_id WHERE p.id=?");$stmt->bind_param("i",$id);$stmt->execute();$r=$stmt->get_result()->fetch_assoc();if(!$r)die("Struk tidak ditemukan.");$title="Struk Pembayaran";include "_header.php";
?>
<div class="print-actions"><button class="btn primary" onclick="window.print()">▣ Cetak</button><a class="btn light" href="javascript:history.back()">⇩ Download</a></div>
<div class="card receipt">
<div class="receipt-head"><img src="../assets/img/cfd.png" alt="Logo"><div class="school-name">SMK FARMASI<br>CENDIKIA FARMA HUSADA</div><small>Bandar Lampung</small></div>
<h2 class="receipt-title">Struk pembayaran</h2><div class="receipt-number">No. Struk: <?=e($r['no_struk'])?></div>
<div class="receipt-body">
<div class="receipt-line"><span>Nama Siswa</span><span><?=e($r['nama'])?></span></div><div class="receipt-line"><span>NIS</span><span><?=e($r['nisn'])?></span></div><div class="receipt-line"><span>Tanggal pembayaran</span><span><?=date('d F Y',strtotime($r['tanggal_bayar']))?></span></div><div class="receipt-line"><span>Metode pembayaran</span><span><?=e($r['metode']==='Transfer'?'Bank':$r['metode'])?></span></div><div class="receipt-line"><span>Jenis pembayaran</span><span><?=e($r['jenis'])?></span></div>
<div class="receipt-total"><div><b>Total pembayaran</b><small>Terbilang</small></div><b><?=rupiah($r['jumlah'])?></b></div>
</div><p class="thanks">Terimakasih atas pembayarannya</p><small class="receipt-date"><?=date('d M Y H:i',strtotime($r['tanggal_bayar']))?></small>
</div>
<?php include "_footer.php"; ?>
