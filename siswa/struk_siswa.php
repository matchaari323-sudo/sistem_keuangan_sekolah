<?php

require_once "../config.php";

require_siswa();

$title = "Struk Pembayaran";
$sid = $_SESSION['siswa_id'];

/*
|--------------------------------------------------------------------------
| Ambil pembayaran terakhir milik siswa
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        p.*,
        s.nisn,
        s.nama,
        s.kelas,
        s.jurusan,
        j.nama AS jenis,
        t.periode,
        t.tahun,
        u.nama AS petugas
    FROM pembayaran p
    JOIN tagihan t ON t.id = p.tagihan_id
    JOIN siswa s ON s.id = t.siswa_id
    JOIN jenis_pembayaran j ON j.id = t.jenis_id
    LEFT JOIN users u ON u.id = p.admin_id
    WHERE t.siswa_id = ?
    ORDER BY p.tanggal_bayar DESC, p.id DESC
    LIMIT 1
");

$stmt->bind_param("i", $sid);
$stmt->execute();

$r = $stmt->get_result()->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Data siswa untuk sidebar
|--------------------------------------------------------------------------
*/

$st_siswa = $conn->prepare("SELECT * FROM siswa WHERE id = ?");
$st_siswa->bind_param("i", $sid);
$st_siswa->execute();

$s = $st_siswa->get_result()->fetch_assoc();

if (!$s && $r) {
    $s = [
        'nama'  => $r['nama'] ?? 'Siswa',
        'kelas' => $r['kelas'] ?? 'Siswa'
    ];
}

if (!$s) {
    $s = [
        'nama'  => 'Siswa',
        'kelas' => 'Siswa'
    ];
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --navy: #09094b;
            --blue: #5d72df;
            --page: #f5f7fc;
            --border: #d8dbe5;
            --text: #17191f;
            --muted: #737783;
            --sidebar-width: 210px;
            --sidebar-collapsed: 68px;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: var(--page);
            color: var(--text);
            overflow-x: hidden;
        }

        .dashboard-wrapper {
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            padding: 18px 14px;
            background: var(--navy);
            color: #fff;
            z-index: 1000;
            overflow: hidden;
            transition: width .25s ease, padding .25s ease;
        }

        .profile {
            margin-bottom: 27px;
            min-width: 0;
        }

        .profile-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            background: #9fa6c2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            border: 2px solid rgba(255, 255, 255, .16);
        }

        .profile-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .profile-name {
            font-size: 15px;
            line-height: 1.2;
            font-weight: 600;
            white-space: nowrap;
        }

        .profile-role {
            margin-top: 2px;
            color: #dfe2ff;
            font-size: 10px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .menu a {
            height: 34px;
            display: flex;
            align-items: center;
            padding: 0 9px;
            border-radius: 6px;
            color: #fff;
            text-decoration: none;
            font-size: 10.5px;
            font-weight: 500;
            white-space: nowrap;
            transition: background .18s ease;
        }

        .menu a:hover {
            background: rgba(255, 255, 255, .10);
        }

        .menu a.active {
            background: var(--blue);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        }

        .menu-icon {
            width: 22px;
            height: 22px;
            flex: 0 0 22px;
            margin-right: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .menu-icon svg {
            width: 17px;
            height: 17px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .menu a:last-child {
            margin-top: 10px;
            padding-top: 8px;
            height: 36px;
            border-top: 1px solid rgba(255, 255, 255, .15);
            border-radius: 0;
        }

        /* MAIN / TOPBAR */
        .main {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            background: var(--page);
            transition: margin-left .25s ease, width .25s ease;
        }

        .topbar {
            height: 54px;
            padding: 0 18px 0 21px;
            background: #fff;
            border-bottom: 1px solid #e0e2e8;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hamburger {
            width: 31px;
            height: 31px;
            border: 0;
            background: transparent;
            color: #202126;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 6px;
            transition: background .18s ease;
        }

        .hamburger:hover {
            background: #f0f1f6;
        }

        .hamburger svg {
            width: 21px;
            height: 21px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.9;
            stroke-linecap: round;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #15161b;
            font-size: 13px;
            font-weight: 600;
        }

        .notification {
            width: 27px;
            height: 27px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .notification svg {
            width: 20px;
            height: 20px;
            stroke: #17181d;
            fill: none;
            stroke-width: 1.9;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* CONTENT */
        .receipt-page {
            position: relative;
            width: 100%;
            padding: 12px 21px 24px;
        }

        .receipt-page .page-title {
            margin: 0 0 2px;
            font-size: 15px;
            line-height: 1.3;
            font-weight: 700;
            color: #111827;
        }

        .receipt-page .page-subtitle {
            margin: 0 0 20px;
            font-size: 11px;
            line-height: 1.4;
            color: #6b7280;
        }

        .receipt-page .print-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .receipt-page .print-actions .btn {
            height: 25px;
            min-width: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            border-radius: 4px;
            font-family: inherit;
            font-size: 10px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: .2s ease;
        }

        .receipt-page .print-actions .btn.primary {
            background: #5d73df;
            border: 1px solid #5d73df;
            color: #fff;
        }

        .receipt-page .print-actions .btn.primary:hover {
            background: #4d63d0;
        }

        .receipt-page .print-actions .btn.light {
            background: #fff;
            border: 1px solid #cbd5f5;
            color: #5d73df;
        }

        .receipt-page .print-actions .btn.light:hover {
            background: #f1f4ff;
        }

        /* STRUK */
        /* STRUK */
        .receipt-page .receipt {
            width: 360px;
            min-height: 500px;
            position: relative;
            margin: 0;
            padding: 20px 20px 16px;
            background: #fff;
            border: 1px solid #cfd3dc;
            border-radius: 3px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .06);
        }

        /* HEADER SEKOLAH */
        .receipt-page .receipt-head {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 72px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid #222;
        }

        .receipt-page .receipt-head img {
            width: 58px;
            height: 58px;
            object-fit: contain;
            flex-shrink: 0;
            margin-right: 12px;
        }

        .receipt-page .receipt-head .school-name {
            font-size: 12px;
            line-height: 1.35;
            font-weight: 700;
            color: #111;
            letter-spacing: .1px;
        }

        .receipt-page .receipt-head small {
            position: absolute;
            left: 70px;
            bottom: 3px;
            font-size: 8px;
            color: #555;
        }

        /* JUDUL */
        .receipt-page .receipt-title {
            margin: 18px 0 5px;
            text-align: center;
            font-size: 15px;
            line-height: 1.2;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #111;
        }

        .receipt-page .receipt-number {
            margin-bottom: 18px;
            text-align: center;
            font-size: 8px;
            color: #777;
        }

        /* DATA PEMBAYARAN */
        .receipt-page .receipt-body {
            width: 100%;
        }

        .receipt-page .receipt-line {
            display: grid;
            grid-template-columns: 132px 12px 1fr;
            min-height: 26px;
            align-items: start;
            font-size: 9.5px;
            line-height: 1.4;
            color: #333;
        }

        .receipt-page .receipt-line span:first-child {
            position: relative;
            color: #555;
        }

        .receipt-page .receipt-line span:first-child::after {
            content: ":";
            position: absolute;
            right: 2px;
        }

        .receipt-page .receipt-line span:last-child {
            grid-column: 3;
            min-width: 0;
            overflow-wrap: anywhere;
            color: #111;
            font-weight: 500;
        }

        /* TOTAL */
        .receipt-page .receipt-total {
            width: 100%;
            min-height: 62px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-top: 12px;
            padding: 11px 13px;
            background: #f7f8ff;
            border: 1px solid #aebcf2;
            border-radius: 4px;
            color: #24419b;
        }

        .receipt-page .receipt-total>div {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .receipt-page .receipt-total b {
            font-size: 10px;
            color: #243b8f;
        }

        .receipt-page .receipt-total small {
            font-size: 7.5px;
            color: #7b86aa;
        }

        .receipt-page .receipt-total>b {
            margin: 0;
            font-size: 14px;
            color: #24419b;
            white-space: nowrap;
        }

        /* FOOTER STRUK */
        .receipt-page .thanks {
            position: absolute;
            left: 20px;
            right: 20px;
            bottom: 36px;
            margin: 0;
            padding-top: 11px;
            border-top: 1px dashed #999;
            text-align: center;
            font-size: 8px;
            color: #555;
        }

        .receipt-page .receipt-date {
            position: absolute;
            right: 20px;
            bottom: 14px;
            font-size: 7px;
            color: #777;
        }

        /* EMPTY */
        .empty-struk {
            width: 330px;
            margin: 0;
            padding: 30px 20px;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-align: center;
        }

        .empty-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .empty-struk h2 {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .empty-struk p {
            font-size: 9px;
            line-height: 1.5;
            color: #666;
            margin-bottom: 15px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 11px;
            background: #5d72df;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
            font-size: 9px;
            font-weight: 600;
        }

        /* COLLAPSE */
        .dashboard-wrapper.sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed);
            padding-left: 10px;
            padding-right: 10px;
        }

        .dashboard-wrapper.sidebar-collapsed .main {
            margin-left: var(--sidebar-collapsed);
            width: calc(100% - var(--sidebar-collapsed));
        }

        .dashboard-wrapper.sidebar-collapsed .profile {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .dashboard-wrapper.sidebar-collapsed .profile-name,
        .dashboard-wrapper.sidebar-collapsed .profile-role,
        .dashboard-wrapper.sidebar-collapsed .menu-text {
            display: none;
        }

        .dashboard-wrapper.sidebar-collapsed .menu a {
            justify-content: center;
            padding: 0;
        }

        .dashboard-wrapper.sidebar-collapsed .menu-icon {
            margin-right: 0;
        }

        @media (max-width: 700px) {
            .sidebar {
                width: var(--sidebar-collapsed);
                padding: 15px 9px;
            }

            .profile {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .profile-name,
            .profile-role,
            .menu-text {
                display: none;
            }

            .menu a {
                justify-content: center;
                padding: 0;
            }

            .menu-icon {
                margin-right: 0;
            }

            .main {
                margin-left: var(--sidebar-collapsed);
                width: calc(100% - var(--sidebar-collapsed));
            }

            .receipt-page {
                padding: 12px 14px 20px;
            }

            .receipt-page .print-actions {
                justify-content: flex-start;
            }

            .receipt-page .receipt {
                width: 330px;
                max-width: 100%;
            }

            .empty-struk {
                width: 330px;
                max-width: 100%;
            }
        }

        @media (max-width: 450px) {
            .receipt-page .receipt {
                padding: 15px 12px 10px;
            }

            .receipt-page .receipt-line {
                grid-template-columns: 135px 1fr;
                font-size: 9px;
            }

            .receipt-page .receipt-head .school-name {
                font-size: 11px;
            }
        }

        @media print {
            body {
                background: #fff !important;
            }

            .sidebar,
            .topbar,
            .print-actions {
                display: none !important;
            }

            .main {
                width: 100% !important;
                margin-left: 0 !important;
                background: #fff !important;
            }

            .receipt-page {
                padding: 0 !important;
            }

            .receipt-page .page-title,
            .receipt-page .page-subtitle {
                display: none !important;
            }

            .receipt-page .receipt {
                margin: 0 auto;
                border: 1px solid #aaa;
                box-shadow: none;
            }
        }
    </style>
</head>

<body>

    <div class="dashboard-wrapper" id="dashboardWrapper">

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

                <a href="dashboard.php">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 10.5 12 3l9 7.5"></path>
                            <path d="M5.5 9.5V21h13V9.5"></path>
                            <path d="M9 21v-6h6v6"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Dashboard</span>
                </a>

                <a href="tunggakan.php">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 3 21 20H3L12 3z"></path>
                            <path d="M12 9v5"></path>
                            <path d="M12 17h.01"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Tunggakan</span>
                </a>

                <a href="riwayat.php">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 3h14v18H5z"></path>
                            <path d="M8 7h8M8 11h8M8 15h5"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Riwayat Pembayaran</span>
                </a>

                <a href="struk_siswa.php" class="active">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 8V3h12v5"></path>
                            <path d="M6 17H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <path d="M6 14h12v7H6z"></path>
                            <path d="M17 11h2"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Cetak struk</span>
                </a>

                <a href="../logout.php">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"></path>
                            <path d="M14 8l4 4-4 4"></path>
                            <path d="M9 12h9"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Logout</span>
                </a>

            </nav>

        </aside>

        <main class="main">

            <header class="topbar">

                <button
                    class="hamburger"
                    id="sidebarToggle"
                    type="button"
                    aria-label="Tampilkan/sembunyikan sidebar"
                    aria-expanded="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div class="topbar-right">

                    <span>
                        Halo, <?= htmlspecialchars($s['nama'] ?? 'Siswa') ?>
                    </span>

                    <span class="notification" aria-label="Notifikasi">
                        <svg viewBox="0 0 24 24">
                            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                            <path d="M10 21h4"></path>
                        </svg>
                    </span>

                </div>

            </header>

            <div class="receipt-page">

                <h1 class="page-title">Struk pembayaran</h1>
                <p class="page-subtitle">Detail pembayaran</p>

                <?php if (!$r): ?>

                    <div class="empty-struk">

                        <div class="empty-icon">▣</div>

                        <h2>Belum Ada Pembayaran</h2>

                        <p>
                            Belum terdapat pembayaran yang dapat dicetak sebagai struk.
                        </p>

                        <a href="riwayat.php" class="btn-back">
                            Lihat Riwayat Pembayaran
                        </a>

                    </div>

                <?php else: ?>

                    <div class="print-actions">
                        <button class="btn primary" onclick="window.print()" type="button">
                            🖨️ Cetak
                        </button>

                        <a class="btn light" href="riwayat.php">
                            📋 Riwayat
                        </a>
                    </div>

                    <div class="card receipt">

                        <div class="receipt-head">

                            <img
                                src="../assets/img/logo.png"
                                alt="Logo">

                            <div class="school-name">
                                SMK FARMASI<br>
                                CENDIKIA FARMA HUSADA
                            </div>

                            <small>Bandar Lampung</small>

                        </div>

                        <h2 class="receipt-title">
                            Struk pembayaran
                        </h2>

                        <div class="receipt-number">
                            No. Struk: <?= e($r['no_struk']) ?>
                        </div>

                        <div class="receipt-body">

                            <div class="receipt-line">
                                <span>Nama Siswa</span>
                                <span><?= e($r['nama']) ?></span>
                            </div>

                            <div class="receipt-line">
                                <span>NIS</span>
                                <span><?= e($r['nisn']) ?></span>
                            </div>

                            <div class="receipt-line">
                                <span>Kelas</span>
                                <span><?= e($r['kelas']) ?></span>
                            </div>

                            <div class="receipt-line">
                                <span>Jurusan</span>
                                <span><?= e($r['jurusan']) ?></span>
                            </div>

                            <div class="receipt-line">
                                <span>Tanggal pembayaran</span>
                                <span>
                                    <?= date('d F Y', strtotime($r['tanggal_bayar'])) ?>
                                </span>
                            </div>

                            <div class="receipt-line">
                                <span>Metode pembayaran</span>
                                <span>
                                    <?= e(
                                        $r['metode'] === 'Transfer'
                                            ? 'Bank'
                                            : $r['metode']
                                    ) ?>
                                </span>
                            </div>

                            <div class="receipt-line">
                                <span>Jenis pembayaran</span>
                                <span><?= e($r['jenis']) ?></span>
                            </div>

                            <div class="receipt-line">
                                <span>Periode</span>
                                <span><?= e($r['periode'] ?: '-') ?></span>
                            </div>

                            <div class="receipt-total">

                                <div>
                                    <b>Total pembayaran</b>
                                    <small>Terbilang</small>
                                </div>

                                <b>
                                    <?= rupiah($r['jumlah']) ?>
                                </b>

                            </div>

                        </div>

                        <p class="thanks">
                            Terimakasih atas pembayarannya
                        </p>

                        <small class="receipt-date">
                            <?= date('d M Y H:i', strtotime($r['tanggal_bayar'])) ?>
                        </small>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>

    <script>
        const dashboardWrapper = document.getElementById('dashboardWrapper');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function() {

            dashboardWrapper.classList.toggle('sidebar-collapsed');

            const expanded = !dashboardWrapper.classList.contains('sidebar-collapsed');

            sidebarToggle.setAttribute(
                'aria-expanded',
                expanded ? 'true' : 'false'
            );

        });
    </script>

</body>

</html>