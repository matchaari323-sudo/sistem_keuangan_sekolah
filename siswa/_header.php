<!-- <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config.php";
require_siswa();

if (!isset($title)) {
    $title = "Sistem Informasi Siswa";
}

/* ================================
   DATA SISWA
================================ */

$sid = $_SESSION['siswa_id'];

$st_header = $conn->prepare("
    SELECT *
    FROM siswa
    WHERE id=?
");

$st_header->bind_param("i", $sid);
$st_header->execute();

$siswa_header = $st_header->get_result()->fetch_assoc();

$nama_siswa = $siswa_header['nama'] ?? 'Siswa';
$kelas_siswa = $siswa_header['kelas'] ?? 'Siswa';

/* ================================
   HALAMAN AKTIF
================================ */

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($title) ?>
    </title>

    <style>
        /* =========================================
           RESET
        ========================================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f8fc;
            color: #171717;
        }


        /* =========================================
           LAYOUT
        ========================================= */

        .dashboard-wrapper {
            min-height: 100vh;
            display: flex;
        }


        /* =========================================
           SIDEBAR
        ========================================= */

        .sidebar {
            width: 210px;
            min-height: 100vh;

            background: #09094b;
            color: white;

            position: fixed;
            left: 0;
            top: 0;

            padding: 20px 16px;

            z-index: 1000;
        }


        /* =========================================
           PROFILE
        ========================================= */

        .profile {
            margin-bottom: 28px;
        }

        .profile-icon {
            width: 48px;
            height: 48px;

            background: #9fa6c2;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            margin-bottom: 9px;

            font-size: 23px;
        }

        .profile-name {
            font-size: 16px;
            font-weight: 500;
        }

        .profile-role {
            font-size: 11px;
            color: #fff;
            margin-top: 2px;
        }


        /* =========================================
           MENU
        ========================================= */

        .menu {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .menu a {
            display: flex;
            align-items: center;

            height: 36px;

            padding: 0 10px;

            border-radius: 7px;

            color: white;
            text-decoration: none;

            font-size: 11px;
            font-weight: 500;

            transition: .2s ease;
        }

        .menu a:hover {
            background: rgba(255, 255, 255, .10);
        }

        .menu a.active {
            background: #5c72df;
        }

        .menu-icon {
            width: 23px;

            font-size: 15px;

            margin-right: 8px;

            text-align: center;
        }


        /* =========================================
           LOGOUT
        ========================================= */

        .menu a.logout {
            margin-top: 13px;

            padding-top: 9px;

            height: 37px;

            border-top: 1px solid rgba(255, 255, 255, .15);

            border-radius: 0;
        }


        /* =========================================
           MAIN
        ========================================= */

        .main {
            margin-left: 210px;

            width: calc(100% - 210px);

            min-height: 100vh;

            background: #f8f9fd;

            display: flex;
            flex-direction: column;
        }


        /* =========================================
           TOPBAR
        ========================================= */

        .topbar {
            height: 54px;

            background: white;

            border-bottom: 1px solid #dedede;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 22px;
        }

        .hamburger {
            font-size: 22px;
            color: #222;
        }

        .topbar-right {
            display: flex;
            align-items: center;

            gap: 13px;

            font-size: 14px;
            font-weight: 500;
        }

        .notification {
            font-size: 21px;
        }


        /* =========================================
           CONTENT
        ========================================= */

        .content {
            padding: 12px 22px 20px;

            flex: 1;
        }


        /* =========================================
           FOOTER
        ========================================= */

        .site-footer {
            margin-left: 210px;

            background: #fff;

            border-top: 1px solid #dedede;

            padding: 12px 22px;

            font-size: 9px;

            color: #777;

            text-align: center;
        }


        /* =========================================
           RESPONSIVE TABLET
        ========================================= */

        @media (max-width: 1000px) {

            .sidebar {
                width: 200px;
            }

            .main {
                margin-left: 200px;

                width: calc(100% - 200px);
            }

            .site-footer {
                margin-left: 200px;
            }

        }


        /* =========================================
           RESPONSIVE HP
        ========================================= */

        @media (max-width: 700px) {

            .sidebar {
                width: 65px;

                padding: 15px 8px;
            }

            .profile-name,
            .profile-role,
            .menu-text {
                display: none;
            }

            .profile {
                display: flex;
                justify-content: center;
            }

            .menu a {
                justify-content: center;
                padding: 0;
            }

            .menu-icon {
                margin: 0;
            }

            .main {
                margin-left: 65px;

                width: calc(100% - 65px);
            }

            .content {
                padding: 12px;
            }

            .topbar {
                padding: 0 12px;
            }

            .site-footer {
                margin-left: 65px;

                padding: 10px;

                font-size: 8px;
            }

        }


        /* =========================================
           HP KECIL
        ========================================= */

        @media (max-width: 450px) {

            .topbar-right {
                font-size: 12px;
            }

            .content {
                padding: 10px;
            }

        }
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
                    👤
                </div>

                <div class="profile-name">
                    <?= htmlspecialchars($nama_siswa) ?>
                </div>

                <div class="profile-role">
                    <?= htmlspecialchars($kelas_siswa) ?>
                </div>

            </div>


            <nav class="menu">


                <!-- DASHBOARD -->

                <a href="dashboard.php"
                    class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">

                    <span class="menu-icon">
                        ⌂
                    </span>

                    <span class="menu-text">
                        Dashboard
                    </span>

                </a>


                <!-- TUNGGAKAN -->

                <a href="tunggakan.php"
                    class="<?= $current_page == 'tunggakan.php' ? 'active' : '' ?>">

                    <span class="menu-icon">
                        △
                    </span>

                    <span class="menu-text">
                        Tunggakan
                    </span>

                </a>


                <!-- RIWAYAT -->

                <a href="riwayat.php"
                    class="<?= $current_page == 'riwayat.php' ? 'active' : '' ?>">

                    <span class="menu-icon">
                        ▣
                    </span>

                    <span class="menu-text">
                        Riwayat Pembayaran
                    </span>

                </a>


                <a href="struk_siswa.php"
                    class="<?= $current_page == 'struk_siswa.php' ? 'active' : '' ?>">

                    <span class="menu-icon">
                        ♧
                    </span>

                    <span class="menu-text">
                        Cetak Struk
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

                <div class="hamburger">
                    ☷
                </div>

                <div class="topbar-right">

                    <span>
                        Halo, <?= htmlspecialchars($nama_siswa) ?>
                    </span>

                    <span class="notification">
                        ♧
                    </span>

                </div>

            </header>


            <!-- =========================================
             CONTENT
             
             ISI HALAMAN AKAN DIMULAI DI SINI
        ========================================= -->

            <section class="content"> -->