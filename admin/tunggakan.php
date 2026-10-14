<?php
require_once "../config.php";
require_admin();
$title = "Tunggakan Pembayaran";
$q = trim($_GET['q'] ?? '');
$stmt = $conn->prepare("SELECT t.*,s.nisn,s.nama,s.kelas,j.nama jenis,COALESCE(SUM(p.jumlah),0) dibayar FROM tagihan t JOIN siswa s ON s.id=t.siswa_id JOIN jenis_pembayaran j ON j.id=t.jenis_id LEFT JOIN pembayaran p ON p.tagihan_id=t.id WHERE (s.nama LIKE CONCAT('%',?,'%') OR s.nisn LIKE CONCAT('%',?,'%')) GROUP BY t.id,t.nominal,s.nisn,s.nama,s.kelas,j.nama HAVING dibayar<t.nominal ORDER BY s.nama,t.tahun,t.id");
$stmt->bind_param("ss", $q, $q);
$stmt->execute();
$data = $stmt->get_result();
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
           CONTENT SPECIFIC STYLES
        ========================= */
        .tunggakan-page {
            width: 100%;
            padding: 14px 21px 18px;
        }

        .page-title {
            font-size: 15px;
            line-height: 1.25;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .page-subtitle {
            color: #737783;
            font-size: 10.5px;
            line-height: 1.3;
            margin-bottom: 20px;
        }

        .card {
            width: 100%;
            background: #fff;
            border: 1px solid #d5d8e0;
            border-radius: 6px;
            padding: 14px 15px 16px;
            box-shadow: none;
        }

        .search {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 500px;
            margin-bottom: 16px;
        }

        .search input {
            width: 100%;
            height: 37px;
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
            box-shadow: 0 0 0 2px rgba(93,115,223,.08);
        }

        .search input::placeholder {
            color: #999;
        }

        .btn {
            height: 37px;
            min-height: 37px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 14px;
            border-radius: 4px;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 11px;
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

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            font-size: 11px;
            background: #fff;
        }

        .table th {
            height: 48px;
            padding: 8px 9px;
            background: #e4e8f8;
            border: 1px solid #c7cbda;
            text-align: center;
            font-weight: 700;
            color: #17191f;
            white-space: nowrap;
        }

        .table td {
            height: 50px;
            padding: 8px 9px;
            background: #fff;
            border: 1px solid #c9cbd1;
            text-align: center;
            vertical-align: middle;
            color: #222;
            white-space: nowrap;
        }

        .table tbody tr:hover td {
            background: #fafbff;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            text-align: left;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 70px;
            min-height: 27px;
            padding: 4px 9px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge.red {
            background: #ffe0e0;
            color: #ef5b5b;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            padding: 12px 14px;
            background: #f8f9fd;
            border: 1px solid #d5d8e0;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 600;
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

            .tunggakan-page {
                padding: 12px 14px 18px;
            }

            .search {
                max-width: none;
                flex-wrap: wrap;
            }

            .card {
                padding: 10px;
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

            <a href="tunggakan.php" class="active">
                <span class="menu-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 3 2.5 20h19L12 3z"></path>
                        <path d="M12 9v5"></path>
                    </svg>
                </span>
                <span class="menu-text">Tunggakan</span>
            </a>

            <a href="laporan_bulanan.php">
                <span class="menu-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M5 3h14v18H5z"></path>
                        <path d="M8 8h8M8 12h8M8 16h5"></path>
                    </svg>
                </span>
                <span class="menu-text">Laporan Bulanan</span>
            </a>

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

        <div class="tunggakan-page">
            <h1 class="page-title">Tunggakan Pembayaran</h1>
            <p class="page-subtitle">Daftar tagihan yang belum lunas.</p>
            
            <div class="card">
                <form class="search" method="get" id="tunggakanSearchForm">
                    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama / NISN...">
                    <button class="btn primary" type="submit">Cari</button>
                </form>
                
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Jenis</th>
                                <th>Periode</th>
                                <th>Tagihan</th>
                                <th>Dibayar</th>
                                <th>Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            $total = 0;
                            while ($r = $data->fetch_assoc()): $sisa = $r['nominal'] - $r['dibayar'];
                                $total += $sisa; ?>
                                <tr data-name="<?= e($r['nama']) ?>" data-nisn="<?= e($r['nisn']) ?>">
                                    <td><?= $no++ ?></td>
                                    <td><?= e($r['nisn']) ?></td>
                                    <td><?= e($r['nama']) ?></td>
                                    <td><?= e($r['kelas']) ?></td>
                                    <td><?= e($r['jenis']) ?></td>
                                    <td><?= e($r['periode'] ?: '-') ?></td>
                                    <td><?= rupiah($r['nominal']) ?></td>
                                    <td><?= rupiah($r['dibayar']) ?></td>
                                    <td><span class="badge red"><?= rupiah($sisa) ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                            <tr id="emptyTunggakanRow" style="display:none;">
                                <td colspan="9" style="padding:18px;color:#777;text-align:center;">Data tunggakan yang dicari tidak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="summary-row">
                    <span>Total seluruh tunggakan</span>
                    <strong style="color:#d9363e"><?= rupiah($total) ?></strong>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
const dashboardWrapper = document.getElementById('dashboardWrapper');
const sidebarToggle = document.getElementById('sidebarToggle');
const searchInput = document.querySelector('.search input[name="q"]');
const searchForm = document.getElementById('tunggakanSearchForm');
const tunggakanRows = Array.from(document.querySelectorAll('tbody tr[data-name]'));
const emptyTunggakanRow = document.getElementById('emptyTunggakanRow');

function filterTunggakan() {
    const keyword = (searchInput.value || '').trim().toLowerCase();
    let visible = 0;

    tunggakanRows.forEach(function(row) {
        const name = (row.dataset.name || '').trim().toLowerCase();
        const nisn = (row.dataset.nisn || '').trim().toLowerCase();
        
        // Pencocokan awalan kata (mirip seperti pada halaman data siswa)
        const nameMatch = keyword === '' || name.indexOf(keyword) === 0;
        const nisnMatch = keyword === '' || nisn.indexOf(keyword) === 0;
        const show = nameMatch || nisnMatch;

        if (show) {
            visible++;
            row.style.display = '';
            row.cells[0].textContent = visible;
        } else {
            row.style.display = 'none';
        }
    });
    
    if (emptyTunggakanRow) {
        emptyTunggakanRow.style.display = visible === 0 ? 'table-row' : 'none';
    }
}

searchForm.addEventListener('submit', function(e) {
    e.preventDefault();
    filterTunggakan();
});

searchInput.addEventListener('input', filterTunggakan);
filterTunggakan();

sidebarToggle.addEventListener('click', function() {
    dashboardWrapper.classList.toggle('sidebar-collapsed');

    const expanded = !dashboardWrapper.classList.contains('sidebar-collapsed');
    sidebarToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
});
</script>

</body>
</html>