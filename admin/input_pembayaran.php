<?php
require_once "../config.php";
require_admin();
$title="Pembayaran";
$error="";
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $siswa_id=(int)($_POST['siswa_id']??0); $jenis_id=(int)($_POST['jenis_id']??0);
    $jumlah=(float)($_POST['jumlah']??0); $metode=$_POST['metode']??'Tunai'; $ket=trim($_POST['keterangan']??'');
    $tanggal=$_POST['tanggal_bayar']??date('Y-m-d H:i:s');
    if($siswa_id<=0||$jenis_id<=0||$jumlah<=0){$error="Data pembayaran belum lengkap.";} else {
        $q=$conn->prepare("SELECT t.id,t.nominal,t.periode,s.nama,s.nisn,j.nama jenis FROM tagihan t JOIN siswa s ON s.id=t.siswa_id JOIN jenis_pembayaran j ON j.id=t.jenis_id WHERE t.siswa_id=? AND t.jenis_id=? ORDER BY t.id DESC LIMIT 1");
        $q->bind_param("ii",$siswa_id,$jenis_id);$q->execute();$tag=$q->get_result()->fetch_assoc();
        if(!$tag){$error="Tagihan untuk siswa dan jenis pembayaran tersebut belum tersedia.";}else{
            $q2=$conn->prepare("SELECT COALESCE(SUM(jumlah),0) dibayar FROM pembayaran WHERE tagihan_id=?");$q2->bind_param("i",$tag['id']);$q2->execute();$dibayar=(float)$q2->get_result()->fetch_assoc()['dibayar'];$sisa=(float)$tag['nominal']-$dibayar;
            if($sisa<=0)$error="Tagihan ini sudah lunas."; elseif($jumlah>$sisa)$error="Jumlah pembayaran melebihi sisa tagihan (".rupiah($sisa).")."; else{
                $no="TRX".date('YmdHis').rand(10,99);$admin=$_SESSION['user_id'];
                $st=$conn->prepare("INSERT INTO pembayaran(no_struk,tagihan_id,jumlah,metode,keterangan,tanggal_bayar,admin_id) VALUES(?,?,?,?,?,?,?)");
                $st->bind_param("sidsssi",$no,$tag['id'],$jumlah,$metode,$ket,$tanggal,$admin);
                if($st->execute()){header("Location: struk.php?id=".$st->insert_id);exit;}else{$error="Pembayaran gagal disimpan.";}
            }
        }
    }
}
$siswa=$conn->query("SELECT id,nisn,nama,kelas FROM siswa ORDER BY nama");
$jenis=$conn->query("SELECT id,nama FROM jenis_pembayaran ORDER BY id");
include "_header.php";
?>
<h1 class="page-title">Pembayaran</h1><p class="page-subtitle">Input dan kelola pembayaran siswa.</p>
<div class="card payment-form">
<?php if($error):?><div class="alert danger"><?=e($error)?></div><?php endif;?>
<form method="post">
<div class="form-grid">
<div class="form-group"><label>Pilih Siswa</label><select name="siswa_id" required><option value="">Cari nama siswa....</option><?php while($s=$siswa->fetch_assoc()):?><option value="<?=$s['id']?>"><?=e($s['nama'])?> - <?=e($s['nisn'])?></option><?php endwhile;?></select></div>
<div class="form-group"><label>Jenis Pembayaran</label><select name="jenis_id" required><option value="">Pilih jenis pembayaran</option><?php while($j=$jenis->fetch_assoc()):?><option value="<?=$j['id']?>"><?=e($j['nama'])?></option><?php endwhile;?></select></div>
</div>
<div class="form-group"><label>Jumlah</label><div class="money-input"><span>Rp</span><input type="number" name="jumlah" min="1" placeholder="Masukkan nominal" required></div></div>
<div class="form-group"><label>Metode pembayaran</label><select name="metode"><option value="Tunai">Tunai</option><option value="Transfer">Bank</option></select></div>
<div class="form-group"><label>Tanggal pembayaran</label><input type="datetime-local" name="tanggal_bayar" value="<?=date('Y-m-d\TH:i')?>" required></div>
<div class="form-group"><label>Keterangan <span class="optional">(opsional)</span></label><input type="text" name="keterangan"></div>
<button class="btn primary full" type="submit">▣ &nbsp; Simpan pembayaran</button>
</form></div>
<?php include "_footer.php"; ?>
