<?php
require_once "../config.php";
require_admin();
$title = "Data Siswa";

$q = trim($_GET['q'] ?? '');
$kelas = trim($_GET['kelas'] ?? '');

$kelasPilihan = array(
    'XA', 'XB FARMASI', 'XB RPL', 'XIA', 'XIB FARMASI', 'XIB RPL',
    'XIIA', 'XIIB FARMASI', 'XIIB RPL'
);

$sql = "SELECT s.*,
        COALESCE(SUM(t.nominal), 0) AS total_tagihan,
        COALESCE(SUM(p.total_dibayar), 0) AS total_dibayar
        FROM siswa s
        LEFT JOIN tagihan t ON t.siswa_id = s.id
        LEFT JOIN (
            SELECT tagihan_id, SUM(jumlah) AS total_dibayar
            FROM pembayaran
            GROUP BY tagihan_id
        ) p ON p.tagihan_id = t.id";

$params = array();
$types = '';
if ($kelas !== '') {
    $sql .= " WHERE s.kelas=?";
    $params[] = $kelas;
    $types .= 's';
}

$sql .= " GROUP BY s.id ORDER BY s.nama";
$stmt = $conn->prepare($sql);
if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$data = $stmt->get_result();
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
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
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--page);
            color: var(--text);
            overflow-x: hidden;
        }

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
            border: 2px solid rgba(255,255,255,.16);
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
            transition: background .18s ease;
        }

        .menu a:hover {
            background: rgba(255,255,255,.10);
        }

        .menu a.active {
            background: var(--blue);
            box-shadow: 0 2px 8px rgba(0,0,0,.12);
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
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .menu a:last-child {
            margin-top: 10px;
            padding-top: 8px;
            height: 36px;
            border-top: 1px solid rgba(255,255,255,.15);
            border-radius: 0;
        }

        /* =========================
           MAIN / TOPBAR
        ========================= */
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

        /* =========================
           CONTENT
        ========================= */
        .data-siswa-page {
            width: 100%;
            padding: 10px 21px 18px;
            color: var(--text);
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
           STATUS CARDS
        ========================= */
        .status-cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 17px;
            margin-bottom: 18px;
        }

        .status-card {
            position: relative;
            min-height: 80px;
            padding: 11px 11px 10px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 5px;
        }

        .status-icon {
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

        .status-icon svg {
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

        .status-content {
            margin-left: 42px;
            min-width: 0;
        }

        .status-title {
            font-size: 9px;
            line-height: 1.2;
            font-weight: 600;
            margin-bottom: 5px;
            white-space: nowrap;
        }

        .status-value {
            font-size: 20px;
            line-height: 1.05;
            font-weight: 700;
            margin-bottom: 4px;
            white-space: nowrap;
        }

        .status-value.blue {
            color: #4d7df3;
        }

        .status-value.orange {
            color: #ef9840;
        }

        .status-value.red {
            color: #ef5b5b;
        }

        .status-value.green {
            color: #35b649;
        }

        .status-card small {
            color: #4d4f55;
            font-size: 8.5px;
            line-height: 1.2;
        }

        /* =========================
           TABLE CARD
        ========================= */
        .data-siswa-page > .card {
            width: 100%;
            background: #fff;
            border: 1px solid #d5d8e0;
            border-radius: 5px;
            padding: 11px 14px 12px;
            margin-bottom: 14px;
            box-shadow: none;
        }

        .toolbar {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 16px;
        }

        .search {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 0;
        }

        .search input {
            width: 100%;
            max-width: 415px;
            height: 31px;
            padding: 0 10px 0 34px;
            border: 1px solid #cfd2da;
            border-radius: 4px;
            background-color: #f8f8f8;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #222;
            outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.3-4.3'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 9px center;
            background-size: 17px;
        }

        .search input:focus {
            background-color: #fff;
            border-color: #8b9ae0;
        }

        .search input::placeholder {
            color: #999;
        }

        .search select {
            height: 31px;
            min-width: 105px;
            padding: 0 8px;
            border: 1px solid #cfd2da;
            border-radius: 4px;
            background: #f8f8f8;
            font-size: 11px;
            color: #333;
            outline: none;
        }

        .btn {
            min-height: 31px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 10px;
            border-radius: 4px;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn.primary {
            background: #5d73df;
            color: #fff;
        }

        .btn.primary:hover {
            background: #4f65d1;
        }

        .btn.dark {
            background: #fff;
            color: #555;
            border-color: #cfd2da;
        }

        .btn.dark:hover {
            background: #f5f5f5;
        }

        /* =========================
           TABLE
        ========================= */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 10.5px;
            background: #fff;
        }

        .table th {
            height: 37px;
            padding: 6px 8px;
            background: #e4e8f8;
            border: 1px solid #c7cbda;
            text-align: center;
            font-weight: 700;
            color: #17191f;
            white-space: nowrap;
        }

        .table td {
            height: 42px;
            padding: 6px 8px;
            background: #fff;
            border: 1px solid #c9cbd1;
            text-align: center;
            vertical-align: middle;
            color: #222;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table tbody tr:hover td {
            background: #fafbff;
        }

        .table th:nth-child(1),
        .table td:nth-child(1) {
            width: 5%;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 11%;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 20%;
            text-align: left;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: 10%;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 12%;
        }

        .table th:nth-child(6),
        .table td:nth-child(6) {
            width: 12%;
        }

        .table th:nth-child(7),
        .table td:nth-child(7) {
            width: 15%;
        }

        .table th:nth-child(8),
        .table td:nth-child(8) {
            width: 15%;
        }

        /* =========================
           BADGE & AKSI
        ========================= */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 62px;
            min-height: 24px;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
        }

        .badge.red {
            background: #ffe0e0;
            color: #ef5b5b;
        }

        .badge.green {
            background: #dcf4df;
            color: #35ae47;
        }

        .actions {
            text-align: center;
            white-space: nowrap;
        }

        .actions .btn {
            min-height: 25px;
            padding: 0 7px;
            margin: 1px;
            font-size: 9px;
        }

        .btn.light {
            background: #eef1ff;
            color: #4055bd;
            border-color: #d7ddff;
        }

        .btn.danger {
            background: #ffe5e5;
            color: #d93636;
            border-color: #ffcaca;
        }

        /* =========================
           FOOTER
        ========================= */
        .page-footer {
            padding: 10px 20px;
            text-align: center;
            font-size: 9px;
            color: #737783;
            border-top: 1px solid #e0e2e8;
            background: #fff;
        }

        /* =========================
           SIDEBAR COLLAPSED
        ========================= */
        .sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed);
            padding-left: 10px;
            padding-right: 10px;
        }

        .sidebar-collapsed .main {
            margin-left: var(--sidebar-collapsed);
            width: calc(100% - var(--sidebar-collapsed));
        }

        .sidebar-collapsed .profile {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .sidebar-collapsed .profile-name,
        .sidebar-collapsed .profile-role,
        .sidebar-collapsed .menu-text {
            display: none;
        }

        .sidebar-collapsed .menu a {
            justify-content: center;
            padding: 0;
        }

        .sidebar-collapsed .menu-icon {
            margin-right: 0;
        }

        /* =========================
           RESPONSIVE
        ========================= */
        @media (max-width: 1000px) {
            .status-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
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

            .data-siswa-page {
                padding: 12px 14px 18px;
            }

            .status-cards {
                grid-template-columns: 1fr 1fr;
                gap: 9px;
            }

            .toolbar {
                flex-wrap: wrap;
            }

            .search {
                width: 100%;
                flex-wrap: wrap;
            }

            .search input {
                width: 100%;
                max-width: none;
            }

            .data-siswa-page > .card {
                padding: 10px;
            }

            .table {
                min-width: 900px;
            }
        }

        @media (max-width: 450px) {
            .status-title {
                font-size: 8.5px;
            }

            .status-value {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
<div class="dashboard-wrapper" id="dashboardWrapper">

    <aside class="sidebar">
        <div class="profile">
            <div class="profile-photo">
                <img src="../assets/img/admin.jpg" alt="Foto Admin">
            </div>
            <div class="profile-name">Admin</div>
            <div class="profile-role">Administrator</div>
        </div>

        <nav class="menu">
            <a href="dashboard_admin.php">
                <span class="menu-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 10.5 12 3l9 7.5"></path>
                        <path d="M5.5 9.5V21h13V9.5"></path>
                        <path d="M9 21v-6h6v6"></path>
                    </svg>
                </span>
                <span class="menu-text">Dashboard</span>
            </a>

            <a href="data_siswa.php" class="active">
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

    <main class="main">

        <header class="topbar">
            <button class="hamburger" id="sidebarToggle" type="button"
                    aria-label="Tampilkan/sembunyikan sidebar"
                    aria-expanded="true">
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

        <div class="data-siswa-page">

            <h1 class="page-title">Data Siswa</h1>
            <p class="page-subtitle">Daftar seluruh siswa</p>
            <?php if ($flash): ?><div style="margin-bottom:12px;padding:9px 11px;border:1px solid #cfe4d2;border-radius:5px;background:#edf8ef;color:#2f7d37;font-size:10px;"><?=e($flash)?></div><?php endif; ?>

            <div class="status-cards">

                <div class="status-card">
                    <div class="status-icon icon-blue">
                        <svg viewBox="0 0 24 24">
                            <path d="M3 7h18v13H3z"></path>
                            <path d="M3 10h18"></path>
                            <path d="M7 15h4"></path>
                        </svg>
                    </div>
                    <div class="status-content">
                        <div class="status-title">Lunas SPP</div>
                        <div class="status-value blue">-</div>
                        <small>Siswa</small>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-icon icon-orange">
                        <svg viewBox="0 0 24 24">
                            <path d="M6 3h12v18H6z"></path>
                            <path d="M9 7h6M9 11h6M9 15h4"></path>
                        </svg>
                    </div>
                    <div class="status-content">
                        <div class="status-title">Lunas Modul</div>
                        <div class="status-value orange">-</div>
                        <small>Siswa</small>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-icon icon-red">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 7h14l-1 14H6L5 7z"></path>
                            <path d="M9 7V4h6v3"></path>
                            <path d="M3 7h18"></path>
                        </svg>
                    </div>
                    <div class="status-content">
                        <div class="status-title">Lunas Baju</div>
                        <div class="status-value red">-</div>
                        <small>Siswa</small>
                    </div>
                </div>

                <div class="status-card">
                    <div class="status-icon icon-green">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 6h16v14H4z"></path>
                            <path d="M8 3v6M16 3v6M4 10h16"></path>
                        </svg>
                    </div>
                    <div class="status-content">
                        <div class="status-title">Lunas D.Ulang</div>
                        <div class="status-value green">-</div>
                        <small>Siswa</small>
                    </div>
                </div>

            </div>

            <div class="card">

                <div class="toolbar">

                    <form class="search" method="get" id="studentSearchForm">

                        <input
                            type="text"
                            name="q"
                            value="<?= e($q) ?>"
                            placeholder="Cari nama siswa / NISN...">

                        <select name="kelas" id="kelasFilter">
                            <option value="">Semua Kelas</option>
                            <?php foreach ($kelasPilihan as $kp): ?>
                                <option value="<?=e($kp)?>" <?=$kelas===$kp?'selected':''?>><?=e($kp)?></option>
                            <?php endforeach; ?>
                        </select>

                        <button class="btn primary" type="submit">Cari</button>

                    </form>

                    <a class="btn dark" href="siswa_tambah.php">
                        ＋ Tambah Siswa
                    </a>

                </div>

                <div class="table-wrap">

                    <table class="table">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Jurusan</th>
                                <th>No HP</th>
                                <th>Tunggakan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $no = 1;
                            while ($s = $data->fetch_assoc()):
                                $tunggakan =
                                    $s['total_tagihan']
                                    -
                                    $s['total_dibayar'];
                            ?>

                                <tr data-name="<?=e($s['nama'])?>" data-nisn="<?=e($s['nisn'])?>" data-kelas="<?=e($s['kelas'])?>">
                                    <td><?= $no++ ?></td>

                                    <td><?= e($s['nisn']) ?></td>

                                    <td><?= e($s['nama']) ?></td>

                                    <td><?= e($s['kelas']) ?></td>

                                    <td><?= e($s['jurusan']) ?></td>

                                    <td><?= e($s['no_hp']) ?></td>

                                    <td>
                                        <?php if ($tunggakan > 0): ?>
                                            <span class="badge red">
                                                <?= rupiah($tunggakan) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge green">
                                                Lunas
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="actions">

                                        <a
                                            class="btn light"
                                            href="detail_siswa.php?id=<?= $s['id'] ?>">
                                            Detail
                                        </a>

                                        <a
                                            class="btn light"
                                            href="siswa_edit.php?id=<?= $s['id'] ?>">
                                            Edit
                                        </a>

                                        <button
                                            class="btn danger btn-hapus-siswa"
                                            type="button"
                                            data-id="<?= $s['id'] ?>"
                                            data-nama="<?=e($s['nama'])?>">
                                            Hapus
                                        </button>

                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <tr id="emptyStudentRow" style="display:none;">
                                <td colspan="8" style="padding:18px;color:#777;">Siswa yang dicari tidak ditemukan.</td>
                            </tr>

                        </tbody>

                    </table>

                </div>
            </div>
        </div>

        <!-- <footer class="page-footer">
            © 2026 Sistem Keuangan Sekolah. All Rights Reserved
        </footer> -->

    </main>
</div>

<div id="confirmModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.38);z-index:2000;align-items:center;justify-content:center;padding:20px;">
    <div style="width:100%;max-width:390px;background:#fff;border-radius:8px;padding:20px;box-shadow:0 15px 40px rgba(0,0,0,.18);">
        <h3 style="font-size:15px;margin:0 0 8px;color:#17191f;">Konfirmasi Hapus</h3>
        <p id="confirmText" style="font-size:11px;line-height:1.6;color:#666;margin:0 0 17px;"></p>
        <div style="display:flex;justify-content:flex-end;gap:8px;">
            <button type="button" id="confirmNo" class="btn dark">Tidak</button>
            <button type="button" id="confirmYes" class="btn danger">Ya, Hapus</button>
        </div>
    </div>
</div>

<script>
const dashboardWrapper = document.getElementById('dashboardWrapper');
const sidebarToggle = document.getElementById('sidebarToggle');
const searchInput = document.querySelector('.search input[name="q"]');
const kelasFilter = document.getElementById('kelasFilter');
const searchForm = document.getElementById('studentSearchForm');
const studentRows = Array.from(document.querySelectorAll('tbody tr[data-name]'));
const emptyStudentRow = document.getElementById('emptyStudentRow');

function filterStudents() {
    const keyword = (searchInput.value || '').trim().toLowerCase();
    const classValue = kelasFilter.value.trim().toLowerCase();
    let visible = 0;

    studentRows.forEach(function(row) {
        const name = (row.dataset.name || '').trim().toLowerCase();
        const nisn = (row.dataset.nisn || '').trim().toLowerCase();
        const kelas = (row.dataset.kelas || '').trim().toLowerCase();
        // Pencarian nama memakai awalan nama, bukan teks di tengah nama.
        const nameMatch = keyword === '' || name.indexOf(keyword) === 0;
        const nisnMatch = keyword === '' || nisn.indexOf(keyword) === 0;
        const classMatch = classValue === '' || kelas === classValue;
        const show = (nameMatch || nisnMatch) && classMatch;
        row.style.display = show ? '' : 'table-row';
        if (show) {
            visible++;
            row.cells[0].textContent = visible;
        }
        if (!show) row.style.display = 'none';
    });
    emptyStudentRow.style.display = visible === 0 ? 'table-row' : 'none';
}

searchForm.addEventListener('submit', function(e) {
    e.preventDefault();
    filterStudents();
});
searchInput.addEventListener('input', filterStudents);
kelasFilter.addEventListener('change', filterStudents);
filterStudents();

sidebarToggle.addEventListener('click', function() {
    dashboardWrapper.classList.toggle('sidebar-collapsed');
    const expanded = !dashboardWrapper.classList.contains('sidebar-collapsed');
    sidebarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
});

const modal = document.getElementById('confirmModal');
const confirmText = document.getElementById('confirmText');
const confirmNo = document.getElementById('confirmNo');
const confirmYes = document.getElementById('confirmYes');
let deleteId = null;

document.querySelectorAll('.btn-hapus-siswa').forEach(function(btn) {
    btn.addEventListener('click', function() {
        deleteId = btn.dataset.id;
        confirmText.textContent = 'Yakin ingin menghapus siswa ' + btn.dataset.nama + '? Semua tagihan dan riwayat pembayaran siswa ini juga akan ikut dihapus.';
        modal.style.display = 'flex';
    });
});

function closeDeleteModal() {
    modal.style.display = 'none';
    deleteId = null;
}
confirmNo.addEventListener('click', closeDeleteModal);
modal.addEventListener('click', function(e) {
    if (e.target === modal) closeDeleteModal();
});
confirmYes.addEventListener('click', function() {
    if (deleteId) window.location.href = 'siswa_hapus.php?id=' + encodeURIComponent(deleteId);
});
</script> = document.getElementById('dashboardWrapper');
const sidebarToggle = document.getElementById('sidebarToggle');

sidebarToggle.addEventListener('click', function() {
    dashboardWrapper.classList.toggle('sidebar-collapsed');

    const expanded =
        !dashboardWrapper.classList.contains('sidebar-collapsed');

    sidebarToggle.setAttribute(
        'aria-expanded',
        expanded ? 'true' : 'false'
    );
});
</script>

</body>
</html>
