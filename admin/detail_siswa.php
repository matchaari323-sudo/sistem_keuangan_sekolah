<?php
require_once "../config.php";
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: data_siswa.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM siswa WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$siswa = $stmt->get_result()->fetch_assoc();
if (!$siswa) die("Siswa tidak ditemukan.");

$stmt = $conn->prepare("SELECT t.id,t.periode,t.tahun,t.nominal,j.nama AS jenis,
    COALESCE(SUM(p.jumlah),0) AS dibayar
    FROM tagihan t
    JOIN jenis_pembayaran j ON j.id=t.jenis_id
    LEFT JOIN pembayaran p ON p.tagihan_id=t.id
    WHERE t.siswa_id=?
    GROUP BY t.id,t.periode,t.tahun,t.nominal,j.nama
    ORDER BY t.tahun DESC,t.id ASC");
$stmt->bind_param("i", $id);
$stmt->execute();
$tagihan = $stmt->get_result();

$totalTagihan = 0;
$totalDibayar = 0;
$jumlahLunas = 0;
$jumlahBelum = 0;
$rows = [];
while ($r = $tagihan->fetch_assoc()) {
    $r['sisa'] = max(0, (float)$r['nominal'] - (float)$r['dibayar']);
    $r['lunas'] = (float)$r['dibayar'] >= (float)$r['nominal'];
    $totalTagihan += (float)$r['nominal'];
    $totalDibayar += (float)$r['dibayar'];
    if ($r['lunas']) $jumlahLunas++; else $jumlahBelum++;
    $rows[] = $r;
}
$totalSisa = max(0, $totalTagihan - $totalDibayar);
$title = "Detail Siswa";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=e($title)?> - Sistem Keuangan Sekolah</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}:root{--navy:#09094b;--blue:#5d72df;--page:#f5f7fc;--border:#d8dbe5;--text:#17191f;--muted:#737783;--sidebar-width:210px;--sidebar-collapsed:68px}html,body{width:100%;min-height:100%}body{font-family:Arial,Helvetica,sans-serif;background:var(--page);color:var(--text);overflow-x:hidden}.dashboard-wrapper{min-height:100vh}.sidebar{position:fixed;left:0;top:0;width:var(--sidebar-width);height:100vh;padding:18px 14px;background:var(--navy);color:#fff;z-index:1000;overflow:hidden;transition:.25s}.profile{margin-bottom:27px}.profile-photo{width:45px;height:45px;border-radius:50%;overflow:hidden;background:#9fa6c2;margin-bottom:8px;border:2px solid rgba(255,255,255,.16)}.profile-photo img{width:100%;height:100%;object-fit:cover;display:block}.profile-name{font-size:15px;font-weight:600}.profile-role{margin-top:2px;color:#dfe2ff;font-size:10px}.menu{display:flex;flex-direction:column;gap:5px}.menu a{height:34px;display:flex;align-items:center;padding:0 9px;border-radius:6px;color:#fff;text-decoration:none;font-size:10.5px;font-weight:500;white-space:nowrap}.menu a:hover{background:rgba(255,255,255,.1)}.menu a.active{background:var(--blue);box-shadow:0 2px 8px rgba(0,0,0,.12)}.menu-icon{width:22px;height:22px;flex:0 0 22px;margin-right:7px;display:inline-flex;align-items:center;justify-content:center}.menu-icon svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.menu a:last-child{margin-top:10px;padding-top:8px;height:36px;border-top:1px solid rgba(255,255,255,.15);border-radius:0}.main{margin-left:var(--sidebar-width);width:calc(100% - var(--sidebar-width));min-height:100vh;background:var(--page);transition:.25s}.topbar{height:54px;padding:0 18px 0 21px;background:#fff;border-bottom:1px solid #e0e2e8;display:flex;align-items:center;justify-content:space-between}.hamburger{width:31px;height:31px;border:0;background:transparent;color:#202126;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;border-radius:6px}.hamburger svg{width:21px;height:21px;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round}.topbar-right{display:flex;align-items:center;gap:17px;font-size:10px}.notification svg{width:17px;height:17px;stroke:#555;fill:none;stroke-width:1.8}.content{padding:15px 17px 22px}.page-title{font-size:17px;font-weight:700;margin-bottom:2px}.page-subtitle{color:#737783;font-size:9.5px;line-height:1.3;margin-bottom:12px}.cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:17px;margin-bottom:18px}.card{background:#fff;border:1px solid #d5d8e0;border-radius:5px;padding:12px 14px}.stat-title{font-size:9px;font-weight:600}.stat-value{font-size:20px;font-weight:700;margin:5px 0}.stat-value.blue{color:#4d7df3}.stat-value.green{color:#35b649}.stat-value.red{color:#ef5b5b}.card small{font-size:8.5px;color:#4d4f55}.info-card{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:14px}.info-item{padding:8px 10px;background:#f8f9fd;border:1px solid #e0e3eb;border-radius:5px}.info-item span{display:block;color:#777;font-size:8.5px;margin-bottom:3px}.info-item strong{font-size:11px}.table-wrap{width:100%;overflow-x:auto}.table{width:100%;min-width:780px;border-collapse:collapse;font-size:10.5px;background:#fff}.table th{height:37px;padding:6px 8px;background:#e4e8f8;border:1px solid #c7cbda;text-align:center;font-weight:700;white-space:nowrap}.table td{height:40px;padding:6px 8px;border:1px solid #c9cbd1;text-align:center;vertical-align:middle;white-space:nowrap}.badge{display:inline-flex;align-items:center;justify-content:center;min-width:72px;min-height:24px;padding:3px 7px;border-radius:5px;font-size:9px;font-weight:700}.badge.green{background:#dcf4df;color:#2f9d40}.badge.red{background:#ffe0e0;color:#d9363e}.btn{min-height:31px;display:inline-flex;align-items:center;justify-content:center;padding:0 10px;border-radius:4px;border:1px solid transparent;text-decoration:none;font-size:10px;font-weight:600;cursor:pointer}.btn.light{background:#fff;color:#555;border-color:#cfd2da}.back{margin-top:12px}.dashboard-wrapper.sidebar-collapsed .sidebar{width:var(--sidebar-collapsed);padding:18px 9px}.dashboard-wrapper.sidebar-collapsed .main{margin-left:var(--sidebar-collapsed);width:calc(100% - var(--sidebar-collapsed))}.dashboard-wrapper.sidebar-collapsed .profile-name,.dashboard-wrapper.sidebar-collapsed .profile-role,.dashboard-wrapper.sidebar-collapsed .menu-text{display:none}@media(max-width:900px){.cards,.info-card{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:700px){.sidebar{width:68px;padding:18px 9px}.main{margin-left:68px;width:calc(100% - 68px)}.profile-name,.profile-role,.menu-text{display:none}.content{padding:12px}.cards,.info-card{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>
<div class="dashboard-wrapper" id="dashboardWrapper">
<aside class="sidebar"><div class="profile"><div class="profile-photo"><img src="../assets/img/admin.jpg" alt="Foto Admin"></div><div class="profile-name">Admin</div><div class="profile-role">Administrator</div></div><nav class="menu">
<a href="dashboard_admin.php"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"></path><path d="M5.5 9.5V21h13V9.5"></path><path d="M9 21v-6h6v6"></path></svg></span><span class="menu-text">Dashboard</span></a>
<a href="data_siswa.php" class="active"><span class="menu-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><path d="M3.5 20a5.5 5.5 0 0 1 11 0"></path><circle cx="17" cy="9" r="2.2"></circle><path d="M15 14.5a4.5 4.5 0 0 1 5.5 4.5"></path></svg></span><span class="menu-text">Data siswa</span></a>
<a href="pembayaran.php"><span class="menu-icon"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path><path d="M7 15h4"></path></svg></span><span class="menu-text">Pembayaran</span></a>
<a href="riwayat_pembayaran.php"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 7h8M8 11h8M8 15h5"></path></svg></span><span class="menu-text">Riwayat Pembayaran</span></a>
<a href="tunggakan.php"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M12 3 2.5 20h19L12 3z"></path><path d="M12 9v5M12 17v.5"></path></svg></span><span class="menu-text">Tunggakan</span></a>
<a href="laporan_bulanan.php"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg></span><span class="menu-text">Laporan Bulanan</span></a>
<a href="cetak_struk.php"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M6 8V3h12v5"></path><path d="M6 17H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M6 14h12v7H6z"></path></svg></span><span class="menu-text">Cetak struk</span></a>
<a href="../logout.php"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"></path><path d="M14 8l4 4-4 4"></path><path d="M9 12h9"></path></svg></span><span class="menu-text">Logout</span></a>
</nav></aside>
<main class="main"><header class="topbar"><button class="hamburger" id="sidebarToggle" type="button" aria-label="Tampilkan/sembunyikan sidebar"><svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg></button><div class="topbar-right"><span>Halo, Admin</span><span class="notification"><svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg></span></div></header>
<section class="content">
<h1 class="page-title">Detail Siswa</h1>
<p class="page-subtitle">Rincian tagihan, pembayaran, dan status pelunasan siswa.</p>
<div class="card" style="margin-bottom:14px;"><div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;"><div><small>NISN</small><div style="font-size:11px;margin-top:4px;"><?=e($siswa['nisn'])?></div></div><div><small>Nama Siswa</small><div style="font-size:11px;margin-top:4px;"><?=e($siswa['nama'])?></div></div><div><small>Kelas</small><div style="font-size:11px;margin-top:4px;"><?=e($siswa['kelas'])?></div></div></div></div>
<div class="cards"><div class="card"><div class="stat-title">Total Tagihan</div><div class="stat-value blue"><?=rupiah($totalTagihan)?></div><small><?=count($rows)?> tagihan</small></div><div class="card"><div class="stat-title">Sudah Dibayar</div><div class="stat-value green"><?=rupiah($totalDibayar)?></div><small><?=$jumlahLunas?> tagihan lunas</small></div><div class="card"><div class="stat-title">Tunggakan</div><div class="stat-value red"><?=rupiah($totalSisa)?></div><small><?=$jumlahBelum?> tagihan belum lunas</small></div><div class="card"><div class="stat-title">Status</div><div class="stat-value <?= $totalSisa>0?'red':'green' ?>"><?= $totalSisa>0?'Belum Lunas':'Lunas' ?></div><small>Keseluruhan tagihan</small></div></div>
<div class="card"><h3 style="font-size:12px;margin-bottom:10px;">Rincian Tagihan Siswa</h3><div class="table-wrap"><table class="table"><thead><tr><th>No</th><th>Jenis Pembayaran</th><th>Periode</th><th>Tahun</th><th>Tagihan</th><th>Sudah Dibayar</th><th>Sisa</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $i=>$r): ?><tr><td><?=$i+1?></td><td><?=e($r['jenis'])?></td><td><?=e($r['periode'] ?: '-')?></td><td><?=e($r['tahun'])?></td><td><?=rupiah($r['nominal'])?></td><td><?=rupiah($r['dibayar'])?></td><td><?=rupiah($r['sisa'])?></td><td><?php if($r['lunas']): ?><span class="badge green">Lunas</span><?php else: ?><span class="badge red">Belum Lunas</span><?php endif; ?></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="8" style="padding:18px;color:#777;">Belum ada data tagihan.</td></tr><?php endif; ?></tbody></table></div></div>
<a class="btn light back" href="data_siswa.php">← Kembali ke Data Siswa</a>
</section></main></div>
<script>const w=document.getElementById('dashboardWrapper');document.getElementById('sidebarToggle').addEventListener('click',function(){w.classList.toggle('sidebar-collapsed');});</script>
</body></html>
