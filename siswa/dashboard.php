<?php
require_once "../config.php";
require_siswa();

$title = "Dashboard Siswa";

/* ================================
   DATA DASHBOARD SISWA
   LOGIKA TETAP
================================ */

$sid = $_SESSION['siswa_id'];

$st = $conn->prepare("SELECT * FROM siswa WHERE id=?");
$st->bind_param("i", $sid);
$st->execute();
$s = $st->get_result()->fetch_assoc();

$st = $conn->prepare("
    SELECT 
        COALESCE(SUM(t.nominal),0) total,
        COALESCE(SUM(x.dibayar),0) dibayar
    FROM tagihan t
    LEFT JOIN (
        SELECT 
            tagihan_id,
            SUM(jumlah) dibayar
        FROM pembayaran
        GROUP BY tagihan_id
    ) x ON x.tagihan_id=t.id
    WHERE t.siswa_id=?
");

$st->bind_param("i", $sid);
$st->execute();
$sum = $st->get_result()->fetch_assoc();

$st = $conn->prepare("
    SELECT COUNT(*) n
    FROM tagihan t
    LEFT JOIN (
        SELECT 
            tagihan_id,
            SUM(jumlah) dibayar
        FROM pembayaran
        GROUP BY tagihan_id
    ) x ON x.tagihan_id=t.id
    WHERE t.siswa_id=?
    AND COALESCE(x.dibayar,0)<t.nominal
");

$st->bind_param("i", $sid);
$st->execute();

$belum = $st->get_result()->fetch_assoc()['n'];

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($title) ?></title>

    
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
 --navy:#09094b;--navy-2:#0d0d5c;--blue:#5d72df;--page:#f5f7fc;
 --border:#d8dbe5;--text:#17191f;--muted:#737783;
 --sidebar-width:210px;--sidebar-collapsed:68px
}
html,body{width:100%;min-height:100%}
body{font-family:Arial,Helvetica,sans-serif;background:var(--page);color:var(--text);overflow-x:hidden}
.dashboard-wrapper{min-height:100vh}
.sidebar{position:fixed;left:0;top:0;width:var(--sidebar-width);height:100vh;padding:18px 14px;background:var(--navy);color:#fff;z-index:1000;overflow:hidden;transition:width .25s ease,padding .25s ease}
.profile{margin-bottom:27px;min-width:0}
.profile-icon{width:45px;height:45px;border-radius:50%;overflow:hidden;background:#9fa6c2;display:flex;align-items:center;justify-content:center;margin-bottom:8px;border:2px solid rgba(255,255,255,.16);color:#fff}
.profile-icon img{width:100%;height:100%;object-fit:cover;display:block}
.profile-name{font-size:15px;line-height:1.2;font-weight:600;white-space:nowrap}
.profile-role{margin-top:2px;color:#dfe2ff;font-size:10px;line-height:1.2;white-space:nowrap}
.menu{display:flex;flex-direction:column;gap:5px}
.menu a{height:34px;display:flex;align-items:center;padding:0 9px;border-radius:6px;color:#fff;text-decoration:none;font-size:10.5px;font-weight:500;white-space:nowrap;transition:background .18s ease}
.menu a:hover{background:rgba(255,255,255,.10)}
.menu a.active{background:var(--blue);box-shadow:0 2px 8px rgba(0,0,0,.12)}
.menu-icon{width:22px;height:22px;flex:0 0 22px;margin-right:7px;display:inline-flex;align-items:center;justify-content:center}
.menu-icon svg{width:17px;height:17px;stroke:currentColor;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.menu a:last-child{margin-top:10px;padding-top:8px;height:36px;border-top:1px solid rgba(255,255,255,.15);border-radius:0}
.main{margin-left:var(--sidebar-width);width:calc(100% - var(--sidebar-width));min-height:100vh;background:var(--page);transition:margin-left .25s ease,width .25s ease}
.topbar{height:54px;padding:0 18px 0 21px;background:#fff;border-bottom:1px solid #e0e2e8;display:flex;align-items:center;justify-content:space-between}
.hamburger{width:31px;height:31px;border:0;background:transparent;color:#202126;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;border-radius:6px;transition:background .18s ease}
.hamburger:hover{background:#f0f1f6}
.hamburger svg{width:21px;height:21px;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round}
.topbar-right{display:flex;align-items:center;gap:10px;color:#15161b;font-size:13px;font-weight:600}
.notification{width:27px;height:27px;display:inline-flex;align-items:center;justify-content:center}
.notification svg{width:20px;height:20px;stroke:#17181d;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.content{padding:18px 28px 24px}
.page-title{font-size:18px;line-height:1.25;font-weight:700;margin-bottom:4px}
.page-subtitle{color:#737783;font-size:12px;line-height:1.4;margin-bottom:18px}
.cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;margin-bottom:22px}
.stat-card{position:relative;min-height:108px;padding:17px 15px 14px;background:#fff;border:1px solid var(--border);border-radius:7px}
.stat-icon{width:40px;height:40px;position:absolute;left:15px;top:17px;border-radius:8px;display:flex;align-items:center;justify-content:center}
.stat-icon svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.icon-blue{background:#dce8ff;color:#5481ef}.icon-orange{background:#fff0df;color:#ff9b39}.icon-red{background:#ffe0e0;color:#ff5757}.icon-green{background:#dcf4df;color:#35ae47}
.stat-content{margin-left:54px;min-width:0;padding-top:1px}
.stat-title{font-size:12px;line-height:1.25;font-weight:600;margin-bottom:7px;white-space:nowrap}
.stat-value{font-size:23px;line-height:1.05;font-weight:700;margin-bottom:6px;white-space:nowrap}
.stat-value.blue{color:#4d7df3}.stat-value.orange{color:#ef9840}.stat-value.red{color:#ef5b5b}.stat-value.green{color:#35b649;font-size:18px}
.stat-card small{color:#4d4f55;font-size:10px;line-height:1.3}
.announcement{background:#fff;border:1px solid #d5d8e0;border-radius:7px;padding:0;margin-bottom:18px;overflow:hidden}
.announcement-header{height:48px;display:flex;align-items:center;justify-content:space-between;padding:0 14px;border-bottom:1px solid #d5d8e0}
.announcement-header h3{font-size:14px;line-height:1.25;font-weight:700}
.announcement-icon{width:25px;height:25px;display:inline-flex;align-items:center;justify-content:center;border:0;background:transparent;cursor:pointer;border-radius:5px;padding:0}.announcement-icon:hover{background:#f0f2f7}
.announcement-icon svg{width:17px;height:17px;stroke:#222;fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;transition:transform .2s ease}.announcement.collapsed .announcement-icon svg{transform:rotate(-90deg)}.announcement.collapsed .announcement-list{display:none}
.announcement-list{padding:15px 20px 18px 38px;margin:0}
.announcement-list li{font-size:12px;line-height:1.65;color:#222;padding-left:0;margin-bottom:2px}
.notice{min-height:44px;display:flex;align-items:center;gap:7px;padding:8px 10px;background:#e3e9ff;border:1px solid #b9c4e9;border-radius:5px;color:#3158bb;font-size:10px;line-height:1.4}
.notice svg{width:14px;height:14px;flex:0 0 14px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.footer{padding:16px 28px 22px;text-align:center;color:#737783;font-size:10px}
.dashboard-wrapper.sidebar-collapsed .sidebar{width:var(--sidebar-collapsed);padding-left:10px;padding-right:10px}
.dashboard-wrapper.sidebar-collapsed .main{margin-left:var(--sidebar-collapsed);width:calc(100% - var(--sidebar-collapsed))}
.dashboard-wrapper.sidebar-collapsed .profile{display:flex;flex-direction:column;align-items:center}
.dashboard-wrapper.sidebar-collapsed .profile-name,.dashboard-wrapper.sidebar-collapsed .profile-role,.dashboard-wrapper.sidebar-collapsed .menu-text{display:none}
.dashboard-wrapper.sidebar-collapsed .menu a{justify-content:center;padding:0}
.dashboard-wrapper.sidebar-collapsed .menu-icon{margin-right:0}
@media(max-width:1100px){.cards{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){
 .sidebar{width:var(--sidebar-collapsed);padding:15px 9px}
 .profile{display:flex;flex-direction:column;align-items:center}.profile-name,.profile-role,.menu-text{display:none}
 .menu a{justify-content:center;padding:0}.menu-icon{margin-right:0}
 .main{margin-left:var(--sidebar-collapsed);width:calc(100% - var(--sidebar-collapsed))}
 .content{padding:16px 20px 22px}.cards{grid-template-columns:1fr;gap:12px}.topbar{padding:0 11px}
 .topbar-right{font-size:12px}
}
@media(max-width:450px){.topbar-right span:first-child{display:none}.page-title{font-size:16px}.content{padding:14px}.stat-card{min-height:100px}.stat-value{font-size:21px}.announcement-list li{font-size:11px}}
</style>


</head>


<body>

    <div class="dashboard-wrapper">


        <!-- =========================================
             SIDEBAR
        ========================================= -->

        <aside class="sidebar">

            <div class="profile">

                <div class="profile-icon">
                    <img src="../assets/img/siswa.jpg" alt="Foto Siswa">
                </div>

                <div class="profile-name">
                    <?= htmlspecialchars($s['nama'] ?? 'Siswa') ?>
                </div>

                <div class="profile-role">
                    <?= htmlspecialchars($s['kelas'] ?? 'Siswa') ?>
                </div>

            </div>


            <nav class="menu">

                <a href="dashboard_siswa.php" class="active">

                    <span class="menu-icon">
<svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5"></path><path d="M5.5 9.5V21h13V9.5"></path><path d="M9 21v-6h6v6"></path></svg>
                    </span>

                    <span class="menu-text">
                        Dashboard
                    </span>

                </a>


                <a href="tunggakan.php">

                    <span class="menu-icon">
<svg viewBox="0 0 24 24"><path d="M12 3 21 20H3L12 3z"></path><path d="M12 9v5"></path><path d="M12 17h.01"></path></svg>
                    </span>

                    <span class="menu-text">
                        Tunggakan
                    </span>

                </a>


                <a href="riwayat.php">

                    <span class="menu-icon">
<svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 7h8M8 11h8M8 15h5"></path></svg>
                    </span>

                    <span class="menu-text">
                        Riwayat Pembayaran
                    </span>

                </a>


                <a href="struk_siswa.php">

                    <span class="menu-icon">
<svg viewBox="0 0 24 24"><path d="M6 8V3h12v5"></path><path d="M6 17H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M6 14h12v7H6z"></path><path d="M17 11h2"></path></svg>
                    </span>

                    <span class="menu-text">
                        Cetak struk
                    </span>

                </a>


                <a href="../logout.php">

                    <span class="menu-icon">
<svg viewBox="0 0 24 24"><path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"></path><path d="m14 8 4 4-4 4"></path><path d="M9 12h9"></path></svg>
                    </span>

                    <span class="menu-text">
                        Logout
                    </span>

                </a>

            </nav>

        </aside>



        <!-- =========================================
             MAIN
        ========================================= -->

        <main class="main">


            <!-- =========================================
                 TOPBAR
            ========================================= -->

            <header class="topbar">

                <button class="hamburger" id="sidebarToggle" type="button" aria-label="Tampilkan/sembunyikan sidebar" aria-expanded="true">
                    <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>


                <div class="topbar-right">

                    <span>
                        Halo, <?= htmlspecialchars($s['nama'] ?? 'Siswa') ?>
                    </span>

                    <span class="notification" aria-label="Notifikasi">
                        <svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg>
                    </span>

                </div>

            </header>



            <!-- =========================================
                 CONTENT
            ========================================= -->

            <section class="content">


                <!-- JUDUL -->

                <h1 class="page-title">
                    Dashboard Siswa
                </h1>

                <p class="page-subtitle">
                    Selamat datang!
                </p>



                <!-- =================================
                     STATISTIC CARDS
                ================================== -->

                <div class="cards">


                    <!-- ==============================
                         TUNGGAKAN SPP
                    =============================== -->

                    <div class="stat-card">

                        <div class="stat-icon icon-red"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M9 9h4.5a2 2 0 0 1 0 4H9a2 2 0 0 0 0 4h5"></path><path d="M12 7v10"></path></svg></div>

                        <div class="stat-content">

                            <div class="stat-title">
                                Tunggakan SPP
                            </div>

                            <div class="stat-value red">

                                <?= rupiah(
                                    max(
                                        0,
                                        $sum['total'] - $sum['dibayar']
                                    )
                                ) ?>

                            </div>

                            <small>
                                <?= $belum ?> Tagihan belum lunas
                            </small>

                        </div>

                    </div>



                    <!-- ==============================
                         DAFTAR ULANG
                    =============================== -->

                    <div class="stat-card">

                        <div class="stat-icon icon-orange"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 7v6"></path><path d="M12 16h.01"></path></svg></div>

                        <div class="stat-content">

                            <div class="stat-title">
                                Daftar Ulang
                            </div>

                            <div class="stat-value orange">
                                Belum Lunas
                            </div>

                            <small>
                                Tahun Ajaran
                                <?= date('Y') - 1 ?>/<?= date('Y') ?>
                            </small>

                        </div>

                    </div>



                    <!-- ==============================
                         BUKU
                    =============================== -->

                    <div class="stat-card">

                        <div class="stat-icon icon-blue"><svg viewBox="0 0 24 24"><rect x="6" y="4" width="12" height="16" rx="2"></rect><path d="M9 8h6M9 12h6M9 16h4"></path></svg></div>

                        <div class="stat-content">

                            <div class="stat-title">
                                Buku
                            </div>

                            <div class="stat-value blue">
                                3 Buku
                            </div>

                            <small>
                                Belum Dibayar
                            </small>

                        </div>

                    </div>



                    <!-- ==============================
                         TOTAL DIBAYAR
                    =============================== -->

                    <div class="stat-card">

                        <div class="stat-icon icon-green"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M9 9h4.5a2 2 0 0 1 0 4H9a2 2 0 0 0 0 4h5"></path><path d="M12 7v10"></path></svg></div>

                        <div class="stat-content">

                            <div class="stat-title">
                                Total Dibayar
                            </div>

                            <div class="stat-value green">
                                <?= rupiah($sum['dibayar']) ?>
                            </div>

                            <small>
                                Tahun ini
                            </small>

                        </div>

                    </div>

                </div>



                <!-- =================================
                     PENGUMUMAN
                ================================== -->

                <div class="announcement">

                    <div class="announcement-header">

                        <h3>
                            Pengumuman
                        </h3>

                        <button type="button" class="announcement-icon" id="announcementToggle" aria-expanded="true" aria-controls="announcementList" title="Tampilkan/sembunyikan pengumuman"><svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"></path></svg></button>

                    </div>


                    <ul class="announcement-list" id="announcementList">

                        <li>
                            Pembayaran SPP paling lambat
                            tanggal 10 setiap bulan.
                        </li>

                        <li>
                            Daftar ulang tahun ajaran
                            dimulai sesuai jadwal sekolah.
                        </li>

                        <li>
                            Pembayaran daftar ulang dapat
                            dilakukan melalui bagian administrasi sekolah.
                        </li>

                        <li>
                            Siswa yang memiliki tunggakan SPP
                            diharapkan segera melakukan pembayaran.
                        </li>

                        <li>
                            Pengambilan buku perpustakaan wajib
                            dilakukan sesuai batas waktu yang telah ditentukan.
                        </li>

                        <li>
                            Kegiatan pembelajaran semester baru
                            dimulai sesuai jadwal yang telah ditetapkan.
                        </li>

                        <li>
                            Siswa diharapkan selalu memeriksa
                            informasi terbaru dari pihak sekolah.
                        </li>

                        <li>Silakan hubungi bagian administrasi jika membutuhkan informasi lebih lanjut mengenai tagihan.</li>
                        <li>Jika terdapat kesalahan pada data pembayaran, segera hubungi pihak administrasi sekolah.</li>
                        <li>Pembayaran yang sudah dilakukan akan tercatat pada sistem setelah proses pembayaran berhasil.</li>

                    </ul>

                </div>



                <!-- =================================
                     NOTICE
                ================================== -->

                <div class="notice">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 10v6"></path><path d="M12 7h.01"></path></svg>
                    <span>Informasi:
                    Silakan periksa tagihan dan riwayat pembayaran
                    secara berkala.</span>
                </div>


            </section>

        </main>

    </div>

<script>
const dashboardWrapper=document.querySelector('.dashboard-wrapper');
const sidebarToggle=document.getElementById('sidebarToggle');
sidebarToggle.addEventListener('click',function(){
 dashboardWrapper.classList.toggle('sidebar-collapsed');
 const expanded=!dashboardWrapper.classList.contains('sidebar-collapsed');
 sidebarToggle.setAttribute('aria-expanded',expanded?'true':'false');
});
</script>

<script>
const announcementToggle = document.getElementById('announcementToggle');
const announcementBox = document.querySelector('.announcement');

if (announcementToggle && announcementBox) {
    announcementToggle.addEventListener('click', function () {
        const collapsed = announcementBox.classList.toggle('collapsed');
        announcementToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    });
}
</script>
</body>

</html>