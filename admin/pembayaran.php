<?php
require_once "../config.php";
require_admin();
$title = "Pembayaran";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siswa_id = (int)($_POST['siswa_id'] ?? 0);
    $jenis_id = (int)($_POST['jenis_id'] ?? 0);
    $jumlah = (float)($_POST['jumlah'] ?? 0);
    $metode = $_POST['metode'] ?? 'Tunai';
    $ket = trim($_POST['keterangan'] ?? '');
    $tanggal = $_POST['tanggal_bayar'] ?? date('Y-m-d H:i:s');

    if ($siswa_id <= 0 || $jenis_id <= 0 || $jumlah <= 0) {
        $error = "Data pembayaran belum lengkap.";
    } else {
        $q = $conn->prepare("
            SELECT
                t.id,
                t.nominal,
                t.periode,
                s.nama,
                s.nisn,
                j.nama jenis
            FROM tagihan t
            JOIN siswa s ON s.id = t.siswa_id
            JOIN jenis_pembayaran j ON j.id = t.jenis_id
            WHERE t.siswa_id=?
            AND t.jenis_id=?
            ORDER BY t.id DESC
            LIMIT 1
        ");
        $q->bind_param("ii", $siswa_id, $jenis_id);
        $q->execute();
        $tag = $q->get_result()->fetch_assoc();

        if (!$tag) {
            $error = "Tagihan untuk siswa dan jenis pembayaran tersebut belum tersedia.";
        } else {
            $q2 = $conn->prepare("
                SELECT COALESCE(SUM(jumlah),0) dibayar
                FROM pembayaran
                WHERE tagihan_id=?
            ");
            $q2->bind_param("i", $tag['id']);
            $q2->execute();
            $dibayar = (float)$q2->get_result()->fetch_assoc()['dibayar'];
            $sisa = (float)$tag['nominal'] - $dibayar;

            if ($sisa <= 0) {
                $error = "Tagihan ini sudah lunas.";
            } elseif ($jumlah > $sisa) {
                $error = "Jumlah pembayaran melebihi sisa tagihan (" . rupiah($sisa) . ").";
            } else {
                $no = "TRX" . date('YmdHis') . rand(10, 99);
                $admin = $_SESSION['user_id'];
                $st = $conn->prepare("
                    INSERT INTO pembayaran(
                        no_struk,
                        tagihan_id,
                        jumlah,
                        metode,
                        keterangan,
                        tanggal_bayar,
                        admin_id
                    )
                    VALUES(?,?,?,?,?,?,?)
                ");
                $st->bind_param(
                    "sidsssi",
                    $no,
                    $tag['id'],
                    $jumlah,
                    $metode,
                    $ket,
                    $tanggal,
                    $admin
                );
                if ($st->execute()) {
                    header("Location: cetak_struk.php?id=" . $st->insert_id);
                    exit;
                } else {
                    $error = "Pembayaran gagal disimpan.";
                }
            }
        }
    }
}

$siswa = $conn->query("
    SELECT id,nisn,nama,kelas
    FROM siswa
    ORDER BY nama
");

$jenis = $conn->query("
    SELECT id,nama
    FROM jenis_pembayaran
    ORDER BY id
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
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
    
        /* =========================
           PEMBAYARAN
        ========================= */
        .payment-page {
            padding: 10px 21px 18px;
        }

        .payment-card {
            width: 100%;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 16px 18px 18px;
        }

        .alert.danger {
            margin-bottom: 14px;
            padding: 9px 11px;
            background: #ffe7e7;
            border: 1px solid #ffcaca;
            border-radius: 6px;
            color: #d93636;
            font-size: 11px;
        }

        .payment-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px 22px;
        }

        .form-group {
            min-width: 0;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text);
            font-size: 11px;
            font-weight: 600;
        }

        .form-group .optional {
            color: var(--muted);
            font-weight: 400;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            height: 38px;
            padding: 0 11px;
            border: 1px solid #cfd2dc;
            border-radius: 6px;
            background: #fff;
            color: #333;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            outline: none;
            transition: .15s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 2px rgba(93,114,223,.10);
        }

        .money-input {
            display: flex;
            width: 100%;
            height: 38px;
            overflow: hidden;
            border: 1px solid #cfd2dc;
            border-radius: 6px;
            background: #fff;
        }

        .money-input span {
            width: 38px;
            flex: 0 0 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f8;
            border-right: 1px solid #cfd2dc;
            color: #555;
            font-size: 11px;
            font-weight: 700;
        }

        .money-input input {
            height: 36px;
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        .money-input input:focus {
            border: 0;
            box-shadow: none;
        }

        .payment-actions {
            margin-top: 17px;
            display: flex;
            gap: 8px;
        }

        .payment-actions .btn {
            min-width: 145px;
            height: 36px;
            border: 0;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .payment-actions .btn.primary {
            background: var(--blue);
            color: #fff;
        }

        .payment-actions .btn.primary:hover {
            background: #4f65d1;
        }

        .payment-actions .btn.light {
            background: #eef0f5;
            color: #444;
        }

        .payment-actions .btn.light:hover {
            background: #e2e5ec;
        }

        @media (max-width: 700px) {
            .payment-page {
                padding: 10px 12px 16px;
            }
            .payment-form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full {
                grid-column: auto;
            }
            .payment-card {
                padding: 14px;
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

            <a href="pembayaran.php" class="active">
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

        <div class="payment-page">
            <h1 class="page-title">Pembayaran</h1>
            <p class="page-subtitle">Input dan kelola pembayaran siswa.</p>

            <div class="payment-card">
                <?php if ($error): ?>
                    <div class="alert danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="payment-form-grid">
                        <div class="form-group">
                            <label>Pilih Siswa</label>
                            <select name="siswa_id" required>
                                <option value="">Cari nama siswa.....</option>
                                <?php while ($s = $siswa->fetch_assoc()): ?>
                                    <option value="<?= $s['id'] ?>" <?= ((int)($_POST['siswa_id'] ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
                                        <?= e($s['nama']) ?> - <?= e($s['nisn']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Jenis Pembayaran</label>
                            <select name="jenis_id" required>
                                <option value="">Pilih jenis pembayaran</option>
                                <?php while ($j = $jenis->fetch_assoc()): ?>
                                    <option value="<?= $j['id'] ?>" <?= ((int)($_POST['jenis_id'] ?? 0) === (int)$j['id']) ? 'selected' : '' ?>>
                                        <?= e($j['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Jumlah</label>
                            <div class="money-input">
                                <span>Rp</span>
                                <input type="number" name="jumlah" min="1" placeholder="Masukkan nominal" value="<?= e($_POST['jumlah'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Metode pembayaran</label>
                            <select name="metode">
                                <option value="Tunai" <?= (($_POST['metode'] ?? 'Tunai') === 'Tunai') ? 'selected' : '' ?>>Tunai</option>
                                <option value="Transfer" <?= (($_POST['metode'] ?? '') === 'Transfer') ? 'selected' : '' ?>>Bank</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Tanggal pembayaran</label>
                            <input type="datetime-local" name="tanggal_bayar" value="<?= e($_POST['tanggal_bayar'] ?? date('Y-m-d\TH:i')) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Keterangan <span class="optional">(opsional)</span></label>
                            <input type="text" name="keterangan" value="<?= e($_POST['keterangan'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="payment-actions">
                        <button class="btn primary" type="submit">
                             🧾 Simpan Pembayaran
                        </button>
                    </div>
                </form>
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

</main>
</div>
</script></body>
</html>
