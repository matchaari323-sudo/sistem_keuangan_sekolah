<?php require_admin(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> - Keuangan Sekolah</title>
    <link rel="stylesheet" href="">
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">SMK FARMASI<br>CENDIKIA FARMA HUSADA <small>Administrator</small></div>
            <div class="profile">
                <div class="avatar"></div><b><?= e($_SESSION['nama']) ?></b><br><small>Administrator</small>
            </div>
            <nav class="nav">
                <a href="dashboard_admin.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard_admin.php' ? 'active' : '' ?>"> &nbsp; Dashboard</a>
                <a href="data_siswa.php" class="<?= basename($_SERVER['PHP_SELF']) === 'data_siswa.php' ? 'active' : '' ?>"> &nbsp; Data siswa</a>
                <a href="pembayaran.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['pembayaran.php']) ? 'active' : '' ?>"> &nbsp; Pembayaran</a>
                <a href="riwayat_pembayaran.php" class="<?= basename($_SERVER['PHP_SELF']) === 'riwayat_pembayaran.php' ? 'active' : '' ?>"> &nbsp; Riwayat Pembayaran</a>
                <a href="tunggakan.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['tunggakan.php']) ? 'active' : '' ?>"> &nbsp; Tunggakan</a>
                <a href="laporan_bulanan.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['laporan_bulanan.php']) ? 'active' : '' ?>"> &nbsp; Laporan Bulanan</a>
                <a href="cetak_struk.php" class="<?= basename($_SERVER['PHP_SELF']) === 'cetak_struk.php' ? 'active' : '' ?>"> &nbsp; Cetak struk</a>
                <a href="../logout.php">↪ &nbsp; Logout</a>
            </nav>
        </aside>
        <main class="main">
            <header class="topbar"><span>☰</span><span class="user">Halo, <?= e($_SESSION['nama']) ?> &nbsp; </span></header>
            <section class="content">