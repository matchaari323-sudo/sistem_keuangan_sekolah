<?php
require_once "../config.php";
require_admin();

$title = "Dashboard Admin";

/* ================================
   DATA DASHBOARD
   LOGIKA TETAP
================================ */

$totalSiswa = $conn->query(
    "SELECT COUNT(*) n FROM siswa"
)->fetch_assoc()['n'];

$totalBayar = $conn->query(
    "SELECT COALESCE(SUM(jumlah),0) n FROM pembayaran"
)->fetch_assoc()['n'];

$lunas = $conn->query(
    "SELECT COUNT(*) n FROM (
        SELECT 
            t.id,
            t.nominal,
            COALESCE(SUM(p.jumlah),0) dibayar
        FROM tagihan t
        LEFT JOIN pembayaran p 
            ON p.tagihan_id = t.id
        GROUP BY t.id, t.nominal
        HAVING dibayar >= t.nominal
    ) x"
)->fetch_assoc()['n'];

$belum = $conn->query(
    "SELECT COUNT(*) n FROM (
        SELECT 
            t.id,
            t.nominal,
            COALESCE(SUM(p.jumlah),0) dibayar
        FROM tagihan t
        LEFT JOIN pembayaran p 
            ON p.tagihan_id = t.id
        GROUP BY t.id, t.nominal
        HAVING dibayar < t.nominal
    ) x"
)->fetch_assoc()['n'];

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
            --navy-2: #0d0d5c;
            --blue: #5d72df;
            --blue-text: #4d7df3;
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

        /* =========================
           LAYOUT
        ========================= */
        .dashboard-wrapper {
            min-height: 100vh;
        }

        /* =========================
           SIDEBAR
        ========================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            min-height: 100vh;
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

        /* GANTI file src ini dengan foto admin milik kamu */
        .profile-photo {
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

        .profile-photo img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
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
            transition: background .18s ease, transform .18s ease;
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

        /* =========================
           MAIN
        ========================= */
        .main {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            background: var(--page);
            transition: margin-left .25s ease, width .25s ease;
        }

        /* =========================
           TOPBAR
        ========================= */
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

        /* =========================
           CONTENT
        ========================= */
        .content {
            padding: 10px 21px 18px;
        }

        .page-title {
            font-size: 14px;
            line-height: 1.25;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .page-subtitle {
            color: #737783;
            font-size: 9.5px;
            line-height: 1.3;
            margin-bottom: 12px;
        }

        /* =========================
           STAT CARDS
        ========================= */
        .cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(150px, 1fr));
    gap: 18px;
    margin-bottom: 18px;
}

        .stat-card {
            position: relative;
            min-height: 80px;
            padding: 11px 11px 10px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 5px;
        }

        .stat-icon {
            width: 31px;
            height: 31px;
            position: absolute;
            left: 11px;
            top: 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .icon-blue {
            background: #dce8ff;
            color: #5481ef;
        }

        .icon-orange {
            background: #fff0df;
            color: #ff9b39;
        }

        .icon-red {
            background: #ffe0e0;
            color: #ff5757;
        }

        .icon-green {
            background: #dcf4df;
            color: #35ae47;
        }

        .stat-content {
            margin-left: 42px;
            min-width: 0;
        }

        .stat-title {
            font-size: 9px;
            line-height: 1.2;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
        }

        .stat-value {
            font-size: 20px;
            line-height: 1.05;
            font-weight: 700;
            margin-bottom: 4px;
            white-space: nowrap;
        }

        .stat-value.blue {
            color: #4d7df3;
        }

        .stat-value.orange {
            color: #ef9840;
        }

        .stat-value.red {
            color: #ef5b5b;
        }

        .stat-value.green {
            color: #35b649;
            font-size: 15px;
        }

        .stat-card small {
            color: #4d4f55;
            font-size: 8.5px;
            line-height: 1.2;
        }

        /* =========================
           FEE CARD
        ========================= */
        .fee-card {
            padding: 11px 14px 12px;
            margin-bottom: 14px;
            background: #fff;
            border: 1px solid #d5d8e0;
            border-radius: 5px;
        }

        .fee-title {
            font-size: 12px;
            line-height: 1.25;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .fee-subtitle {
            color: #70737b;
            font-size: 9px;
            line-height: 1.3;
            margin-bottom: 16px;
        }

        /* =========================
           TABLE
        ========================= */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .fee-table {
    width: 100%;
    min-width: 650px;
    border-collapse: collapse;
    font-size: 11px;
}

        .fee-table th {
            height: 37px;
            padding: 6px 8px;
            background: #e4e8f8;
            border: 1px solid #c7cbda;
            text-align: center;
            font-weight: 700;
        }

        .fee-table td {
            height: 42px;
            padding: 6px 8px;
            background: #fff;
            border: 1px solid #c9cbd1;
            text-align: center;
            vertical-align: middle;
        }

        .fee-table td:first-child {
            width: 32%;
            padding-left: 9px;
            text-align: left;
        }

        .category-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .category-icon {
            width: 31px;
            height: 31px;
            flex: 0 0 31px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .category-icon svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.7;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .cat-green {
            background: #ddf3df;
            color: #46b856;
        }

        .cat-orange {
            background: #fff0df;
            color: #f4a24a;
        }

        .cat-purple {
            background: #eee4ff;
            color: #8957e8;
        }

        .cat-pink {
            background: #ffe0ef;
            color: #ec4d9a;
        }

        .fee-table .total-row td {
            height: 40px;
            background: #e4e8f8;
            font-size: 9.5px;
            font-weight: 600;
        }

        /* =========================
           NOTICE
        ========================= */
        .notice {
            min-height: 35px;
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 8px 10px;
            background: #e3e9ff;
            border: 1px solid #b9c4e9;
            border-radius: 3px;
            color: #3158bb;
            font-size: 8px;
            line-height: 1.3;
        }

        .notice svg {
            width: 14px;
            height: 14px;
            flex: 0 0 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* =========================
           SIDEBAR COLLAPSED
        ========================= */
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

        .dashboard-wrapper.sidebar-collapsed .menu a:last-child {
            margin-top: 10px;
        }

        /* =========================
           RESPONSIVE
        ========================= */
        @media (max-width: 1000px) {
            :root {
                --sidebar-width: 210px;
            }

            .cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }


            .cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
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

            .content {
    padding: 14px 22px 22px;
}

            .cards {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .topbar {
                padding: 0 11px;
            }

            .topbar-right {
                font-size: 12px;
            }

            .fee-table {
                min-width: 600px;
            }
        }

        @media (max-width: 450px) {
            .topbar-right span:first-child {
                display: none;
            }

            .page-title {
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-wrapper" id="dashboardWrapper">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="profile">
                <!-- GANTI src DENGAN FOTO ADMIN KAMU -->
                <div class="profile-photo">
                    <img src="../assets/img/admin.jpg" alt="Foto Admin">
                </div>

                <div class="profile-name">Admin</div>
                <div class="profile-role">Administrator</div>
            </div>

            <nav class="menu">
                <a href="dashboard_admin.php" class="active">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 10.5 12 3l9 7.5"></path>
                            <path d="M5.5 9.5V21h13V9.5"></path>
                            <path d="M9 21v-6h6v6"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Dashboard</span>
                </a>

                <a href="data_siswa.php">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <circle cx="9" cy="8" r="3"></circle>
                            <path d="M3.5 20a5.5 5.5 0 0 1 11 0"></path>
                            <circle cx="17" cy="9" r="2.2"></circle>
                            <path d="M15 14.5a4.5 4.5 0 0 1 5.5 4.5"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Data siswa</span>
                </a>

                <a href="pembayaran.php">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                            <path d="M3 10h18"></path>
                            <path d="M7 15h4"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Pembayaran</span>
                </a>

                <a href="riwayat_pembayaran.php">
                    <span class="menu-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 3h14v18H5z"></path>
                            <path d="M8 7h8M8 11h8M8 15h5"></path>
                        </svg>
                    </span>
                    <span class="menu-text">Riwayat Pembayaran</span>
                </a>

                
<a href="tunggakan.php" class="<?=basename($_SERVER['PHP_SELF']) === 'tunggakan.php' ? 'active' : ''?>"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M12 3 2.5 20h19L12 3z"></path><path d="M12 9v5"></path></svg></span><span class="menu-text">Tunggakan</span></a>
<a href="laporan_bulanan.php" class="<?=basename($_SERVER['PHP_SELF']) === 'laporan_bulanan.php' ? 'active' : ''?>"><span class="menu-icon"><svg viewBox="0 0 24 24"><path d="M5 3h14v18H5z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg></span><span class="menu-text">Laporan Bulanan</span></a>
<a href="cetak_struk.php">
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

        <!-- MAIN -->
        <main class="main">
            <header class="topbar">
                <button class="hamburger" id="sidebarToggle" type="button" aria-label="Tampilkan/sembunyikan sidebar" aria-expanded="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <div class="topbar-right">
                    <span>Halo, Admin</span>
                    <span class="notification" aria-label="Notifikasi">
                        <svg viewBox="0 0 24 24">
                            <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                            <path d="M10 21h4"></path>
                        </svg>
                    </span>
                </div>
            </header>

            <section class="content">
                <h1 class="page-title">Dashboard Admin</h1>
                <p class="page-subtitle">Ringkasan sistem pembayaran</p>

                <div class="cards">
                    <div class="stat-card">
                        <div class="stat-icon icon-blue">
                            <svg viewBox="0 0 24 24">
                                <circle cx="9" cy="8" r="3"></circle>
                                <path d="M3.5 20a5.5 5.5 0 0 1 11 0"></path>
                                <circle cx="17" cy="9" r="2.2"></circle>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-title">Total siswa</div>
                            <div class="stat-value blue"><?= $totalSiswa ?></div>
                            <small>Siswa aktif</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-orange">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8.5"></circle>
                                <path d="m8.5 12 2.3 2.3 4.8-5"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-title">Lunas bulan ini</div>
                            <div class="stat-value orange"><?= $lunas ?></div>
                            <small>Siswa</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-red">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8.5"></circle>
                                <path d="m9 9 6 6M15 9l-6 6"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-title">Belum lunas</div>
                            <div class="stat-value red"><?= $belum ?></div>
                            <small>Siswa</small>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon icon-green">
                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M9 9h4.5a2 2 0 0 1 0 4H9a2 2 0 0 0 0 4h5"></path>
                                <path d="M12 7v10"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-title">Pemasukan</div>
                            <div class="stat-value green"><?= rupiah($totalBayar) ?></div>
                            <small>Bulan ini</small>
                        </div>
                    </div>
                </div>

                <div class="fee-card">
                    <h3 class="fee-title">Rincian biaya per jurusan</h3>
                    <p class="fee-subtitle">Daftar biaya/tagihan untuk setiap kategori.</p>

                    <div class="table-container">
                        <table class="fee-table">
                            <tr>
                                <th>Kategori</th>
                                <th>Farmasi</th>
                                <th>RPL</th>
                            </tr>

                            <tr>
                                <td>
                                    <div class="category-cell">
                                        <span class="category-icon cat-green">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M5 5h14v14H5z"></path>
                                                <path d="M8 8h4v3H8zM14 8h2M14 12h2M8 15h8"></path>
                                            </svg>
                                        </span>
                                        <span>Buku modul</span>
                                    </div>
                                </td>
                                <td>Rp.80.000</td>
                                <td>Rp.60.000</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="category-cell">
                                        <span class="category-icon cat-orange">
                                            <svg viewBox="0 0 24 24">
                                                <path d="M8 5h8l3 4-3 2-1.5-2v10h-5V9L8 11 5 9z"></path>
                                            </svg>
                                        </span>
                                        <span>Baju</span>
                                    </div>
                                </td>
                                <td>Rp.250.000</td>
                                <td>Rp.150.000</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="category-cell">
                                        <span class="category-icon cat-purple">
                                            <svg viewBox="0 0 24 24">
                                                <rect x="6" y="4" width="12" height="16" rx="2"></rect>
                                                <path d="M9 8h6M9 12h6M9 16h4"></path>
                                            </svg>
                                        </span>
                                        <span>Daftar ulang</span>
                                    </div>
                                </td>
                                <td>Rp.1.000.000</td>
                                <td>Rp.800.000</td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="category-cell">
                                        <span class="category-icon cat-pink">
                                            <svg viewBox="0 0 24 24">
                                                <ellipse cx="12" cy="6" rx="6.5" ry="2.5"></ellipse>
                                                <path d="M5.5 6v6c0 1.4 2.9 2.5 6.5 2.5s6.5-1.1 6.5-2.5V6"></path>
                                                <path d="M5.5 12v6c0 1.4 2.9 2.5 6.5 2.5s6.5-1.1 6.5-2.5v-6"></path>
                                            </svg>
                                        </span>
                                        <span>SPP perbulan</span>
                                    </div>
                                </td>
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

                <div class="notice">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 10v6"></path>
                        <path d="M12 7h.01"></path>
                    </svg>
                    <span>Catatan: Semua pembayaran dilakukan secara langsung di Tata Usaha (Offline).</span>
                </div>
            </section>
        </main>
    </div>

    <script>
        const dashboardWrapper = document.getElementById('dashboardWrapper');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function() {
            dashboardWrapper.classList.toggle('sidebar-collapsed');
            const expanded = !dashboardWrapper.classList.contains('sidebar-collapsed');
            sidebarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        });
    </script>
</body>

</html>