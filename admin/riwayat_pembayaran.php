<?php
require_once "../config.php";
require_admin();
$title = "Riwayat Pembayaran";

$awal = $_GET['awal'] ?? date('Y-m-d', strtotime('-30 days'));
$akhir = $_GET['akhir'] ?? date('Y-m-d');

$stmt = $conn->prepare("
    SELECT
        p.*,
        s.nama,
        s.nisn,
        j.nama jenis
    FROM pembayaran p
    JOIN tagihan t ON t.id=p.tagihan_id
    JOIN siswa s ON s.id=t.siswa_id
    JOIN jenis_pembayaran j ON j.id=t.jenis_id
    WHERE DATE(p.tanggal_bayar) BETWEEN ? AND ?
    ORDER BY p.tanggal_bayar DESC
");
$stmt->bind_param("ss", $awal, $akhir);
$stmt->execute();
$res = $stmt->get_result();
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
<style>
.history-page { width:100%; padding:10px 21px 18px; }
.history-page .page-title { font-size:14px; line-height:1.25; font-weight:700; margin-bottom:2px; }
.history-page .page-subtitle { color:#737783; font-size:9.5px; line-height:1.3; margin-bottom:15px; }
.date-filter { width:100%; display:grid; grid-template-columns:200px 200px 128px; gap:30px; align-items:end; margin-bottom:17px; }
.date-filter > div { display:flex; flex-direction:column; }
.date-filter label { margin-bottom:5px; color:#222; font-size:10.5px; }
.date-filter input { width:100%; height:31px; padding:0 9px; background:#fff; border:1px solid #cfd2da; border-radius:4px; color:#444; font-family:Arial,Helvetica,sans-serif; font-size:10.5px; outline:none; }
.date-filter input:focus { border-color:#8b9ae0; box-shadow:0 0 0 2px rgba(93,115,223,.08); }
.date-filter .btn { height:31px; min-height:31px; }
.history-card { width:100%; background:#fff; border:1px solid #d5d8e0; border-radius:5px; padding:11px 14px 12px; box-shadow:none; }
.history-table { width:100%; min-width:820px; border-collapse:collapse; font-size:10.5px; background:#fff; }
.history-table th { height:37px; padding:6px 8px; background:#e4e8f8; border:1px solid #c7cbda; text-align:center; font-weight:700; color:#17191f; white-space:nowrap; }
.history-table td { height:42px; padding:6px 8px; background:#fff; border:1px solid #c9cbd1; text-align:center; vertical-align:middle; color:#222; white-space:nowrap; }
.history-table tbody tr:hover td { background:#fafbff; }
.history-table th:nth-child(1),.history-table td:nth-child(1){width:6%;}
.history-table th:nth-child(2),.history-table td:nth-child(2){width:13%;}
.history-table th:nth-child(3),.history-table td:nth-child(3){width:22%;}
.history-table th:nth-child(4),.history-table td:nth-child(4){width:14%;}
.history-table th:nth-child(5),.history-table td:nth-child(5){width:15%;}
.history-table th:nth-child(6),.history-table td:nth-child(6){width:14%;}
.history-table th:nth-child(7),.history-table td:nth-child(7){width:16%;}
.history-table .badge { display:inline-flex; align-items:center; justify-content:center; min-width:62px; min-height:24px; padding:3px 7px; border-radius:4px; font-size:9px; font-weight:600; }
.history-table .badge.green { background:#dcf4df; color:#35ae47; }
.history-table .badge.blue { background:#e0e7ff; color:#4055bd; }
.history-table .small-btn { min-height:25px; padding:0 9px; font-size:9px; }
@media(max-width:900px){.date-filter{grid-template-columns:1fr 1fr 120px;gap:15px}.history-table{min-width:820px}}
@media(max-width:700px){.history-page{padding:12px 14px 18px}.date-filter{grid-template-columns:1fr;gap:10px}.date-filter .btn{width:100%}.history-card{padding:10px}.history-table{min-width:820px}}

/* Penyesuaian agar tampilan tidak terlalu gepeng */
.history-page { padding-top: 14px; }
.history-page .page-title { font-size: 15px; }
.history-page .page-subtitle { font-size: 10.5px; margin-bottom: 20px; }
.date-filter { grid-template-columns: 230px 230px 135px; gap: 22px; margin-bottom: 22px; }
.date-filter label { font-size: 11px; margin-bottom: 7px; }
.date-filter input, .date-filter .btn { height: 37px; min-height: 37px; font-size: 11px; }
.history-card { padding: 14px 15px 16px; border-radius: 6px; }
.history-table { font-size: 11px; }
.history-table th { height: 48px; padding: 8px 9px; font-size: 11px; }
.history-table td { height: 50px; padding: 8px 9px; font-size: 11px; }
.history-table .badge { min-width: 70px; min-height: 27px; padding: 4px 9px; font-size: 10px; }
.history-table .small-btn { min-height: 28px; padding: 0 11px; font-size: 10px; }
@media(max-width:900px){.date-filter{grid-template-columns:1fr 1fr 120px;gap:15px}}
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

            <a href="riwayat_pembayaran.php" class="active">
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

        
        <div class="history-page">
            <h1 class="page-title">Riwayat Pembayaran</h1>
            <p class="page-subtitle">Lihat seluruh riwayat pembayaran siswa.</p>

            <form class="date-filter" method="get">
                <div>
                    <label for="awal">Tanggal awal</label>
                    <input id="awal" type="date" name="awal" value="<?= e($awal) ?>">
                </div>
                <div>
                    <label for="akhir">Tanggal akhir</label>
                    <input id="akhir" type="date" name="akhir" value="<?= e($akhir) ?>">
                </div>
                <button class="btn primary" type="submit">Filter</button>
            </form>

            <div class="history-card">
                <div class="table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama siswa</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Metode</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($r = $res->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= date('d/m/Y', strtotime($r['tanggal_bayar'])) ?></td>
                                    <td><?= e($r['nama']) ?></td>
                                    <td><?= e($r['jenis']) ?></td>
                                    <td><?= rupiah($r['jumlah']) ?></td>
                                    <td>
                                        <span class="badge <?= strtolower($r['metode']) === 'tunai' ? 'green' : 'blue' ?>">
                                            <?= e($r['metode'] === 'Transfer' ? 'Bank' : $r['metode']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a class="btn primary small-btn" href="cetak_struk.php?id=<?= $r['id'] ?>">Cetak</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
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

<script>
const dashboardWrapper = document.getElementById('dashboardWrapper');
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
