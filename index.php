<?php
require_once "config.php";

if (isset($_SESSION['username'])) {
    if ($_SESSION['role'] === 'admin') {
        header("location: admin/dashboard.php");
    } else {
        header("location: siswa/dashboard.php");
    }
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $role = $_POST['role'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Hapus session login sebelumnya agar role tidak terbawa
    unset($_SESSION['role']);
    unset($_SESSION['user_id']);
    unset($_SESSION['siswa_id']);
    unset($_SESSION['nama']);

    if ($role === 'admin') {

        $stmt = $conn->prepare(
            "SELECT * FROM users WHERE username=? LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];

            header("Location: admin/dashboard_admin.php");
            exit;
        }

    } elseif ($role === 'siswa') {

        $stmt = $conn->prepare(
            "SELECT * FROM siswa WHERE nisn=? LIMIT 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $siswa = $result->fetch_assoc();

        /*
         * Bisa membaca password hash maupun password
         * yang masih tersimpan sebagai teks biasa.
         */
        if (
            $siswa &&
            (
                password_verify($password, $siswa['password']) ||
                $password === $siswa['password']
            )
        ) {

            $_SESSION['role'] = 'siswa';
            $_SESSION['siswa_id'] = $siswa['id'];
            $_SESSION['nama'] = $siswa['nama'];

            header("Location: siswa/dashboard.php");
            exit;
        }
    }

    $error = "Username/NISN atau password salah.";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Sistem Keuangan Sekolah</title>
<style>
/* =========================================================
   LOGIN PAGE - TAMPILAN RAPI
   Hanya mengubah CSS, alur PHP/HTML/JS tetap sama.
   ========================================================= */

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html,
body {
    width: 100%;
    min-height: 100%;
}

body.login-page {
    min-height: 100vh;
    font-family: Arial, Helvetica, sans-serif;
    background: #ffffff;
    overflow: hidden;
}

/* =========================
   LAYOUT
   ========================= */

.login-shell {
    width: 100%;
    min-height: 100vh;
    height: 100vh;
    display: flex;
    background: #ffffff;
}

/* =========================
   PANEL KIRI
   ========================= */

.login-panel {
    width: 49.5%;
    height: 100vh;
    min-height: 580px;

    background: #08084a;
    color: #ffffff;

    display: flex;
    flex-direction: column;
    align-items: center;

    padding: 43px 35px 30px;
}

/* =========================
   LOGO + NAMA SEKOLAH
   ========================= */

.login-brand {
    width: 100%;
    text-align: center;
    margin-bottom: 30px;
}

.login-brand img {
    width: 88px;
    height: 88px;
    object-fit: contain;
    display: block;
    margin: 0 auto 12px;
}

.login-brand div {
    color: #ffffff;
    font-size: 15px;
    font-weight: 400;
    line-height: 1.55;
    letter-spacing: .15px;
}

/* =========================
   JUDUL
   ========================= */

.login-panel h2 {
    color: #ffffff;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
    text-align: center;
    margin-bottom: 6px;
    letter-spacing: .2px;
}

.login-panel .muted {
    color: #d9d9e6;
    font-size: 12.5px;
    font-weight: 400;
    line-height: 1.4;
    text-align: center;
    margin-bottom: 34px;
}

/* =========================
   FORM
   ========================= */

.login-panel form {
    width: min(360px, 100%);
    margin: 0 auto;
}

.login-panel form > label {
    display: none;
}

/* =========================
   ROLE SELECT
   ========================= */

.login-panel select {
    width: 100%;
    height: 50px;

    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px;

    background-color: #ffffff;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23333333' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 17px 17px;

    color: #333333;

    padding: 0 43px 0 16px;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;

    outline: none;
    margin-bottom: 13px;
    cursor: pointer;

    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;

    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}

/* Hilangkan arrow bawaan select agar posisi chevron konsisten */
.login-panel select::-ms-expand {
    display: none;
}

/* =========================
   INPUT
   ========================= */

.login-panel input[type="text"],
.login-panel input[type="password"] {
    width: 100%;
    height: 50px;

    border: 1px solid rgba(0,0,0,.04);
    border-radius: 8px;

    background-color: #ffffff;
    color: #333333;

    padding: 0 16px 0 47px;

    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;

    outline: none;
    margin-bottom: 14px;

    box-shadow: 0 2px 8px rgba(0,0,0,.08);

    transition:
        border-color .2s ease,
        box-shadow .2s ease;
}

/* Ikon user */
.login-panel input[type="text"] {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23' height='23' viewBox='0 0 24 24' fill='none' stroke='%23333333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 21a8 8 0 0 0-16 0'/%3E%3Ccircle cx='12' cy='7' r='4'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 15px center;
    background-size: 22px 22px;
}

/* Ikon password */
.login-panel input[type="password"] {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='23' height='23' viewBox='0 0 24 24' fill='none' stroke='%23333333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='11' width='18' height='10' rx='2'/%3E%3Cpath d='M7 11V7a5 5 0 0 1 10 0v4'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: 15px center;
    background-size: 22px 22px;
}

.login-panel input::placeholder {
    color: #555555;
    opacity: 1;
}

.login-panel input:focus,
.login-panel select:focus {
    outline: none;
    border-color: rgba(40, 70, 180, .55);
    box-shadow: 0 0 0 3px rgba(40, 70, 180, .18);
}

/* =========================
   BUTTON LOGIN
   ========================= */

.login-panel .btn.primary {
    width: 100%;
    height: 50px;

    border: none;
    border-radius: 7px;

    background: #1938aa;
    color: #ffffff;

    font-family: Arial, Helvetica, sans-serif;
    font-size: 21px;
    font-weight: 700;

    text-align: center;
    cursor: pointer;

    margin: 3px 0 0;

    box-shadow: 0 3px 8px rgba(0,0,0,.12);

    transition:
        background .2s ease,
        transform .1s ease,
        box-shadow .2s ease;
}

.login-panel .btn.primary:hover {
    background: #2448c2;
    box-shadow: 0 5px 12px rgba(0,0,0,.18);
}

.login-panel .btn.primary:active {
    transform: scale(.99);
}

/* =========================
   ERROR
   ========================= */

.alert.danger {
    width: min(360px, 100%);
    background: #ffe3e3;
    color: #b42318;
    border-radius: 8px;
    padding: 10px 14px;
    margin: 0 auto 15px;
    font-size: 13px;
    line-height: 1.4;
    text-align: center;
}

/* =========================
   HINT
   ========================= */

.login-hint {
    width: min(360px, 100%);
    color: #c9c9d7;
    font-size: 10.5px;
    line-height: 1.5;
    text-align: center;
    margin-top: 13px;
}

.login-hint b {
    color: #ffffff;
}

/* =========================
   PANEL KANAN / FOTO GEDUNG
   ========================= */

.login-info {
    width: 50.5%;
    height: 100vh;

    position: relative;
    overflow: hidden;

    background: #eaf0ed;
}

.login-info img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
    object-position: center center;

    filter:
        brightness(1.08)
        saturate(.84);
}

/* Lapisan putih lembut seperti contoh */
.login-info::after {
    content: "";
    position: absolute;
    inset: 0;

    background: rgba(245, 248, 247, .24);

    pointer-events: none;
}

/* Tidak menampilkan teks kanan */
.login-info h1,
.login-info p {
    display: none;
}

/* =========================
   DESKTOP
   ========================= */

@media (min-width: 1200px) {
    .login-panel {
        padding-top: 42px;
    }

    .login-brand img {
        width: 90px;
        height: 90px;
    }

    .login-brand {
        margin-bottom: 29px;
    }

    .login-panel form {
        width: 365px;
    }

    .login-panel select,
    .login-panel input[type="text"],
    .login-panel input[type="password"],
    .login-panel .btn.primary {
        height: 50px;
    }
}

/* =========================
   TABLET
   ========================= */

@media (max-width: 900px) {
    .login-panel {
        width: 50%;
        padding: 38px 25px 25px;
    }

    .login-info {
        width: 50%;
    }

    .login-brand img {
        width: 78px;
        height: 78px;
    }

    .login-brand div {
        font-size: 14px;
    }

    .login-panel h2 {
        font-size: 22px;
    }

    .login-panel form,
    .login-hint,
    .alert.danger {
        width: min(330px, 100%);
    }
}

/* =========================
   MOBILE
   ========================= */

@media (max-width: 700px) {
    body.login-page {
        overflow: auto;
    }

    .login-shell {
        height: auto;
        min-height: 100vh;
    }

    .login-info {
        display: none;
    }

    .login-panel {
        width: 100%;
        min-height: 100vh;
        height: auto;
        padding: 42px 25px 30px;
    }

    .login-brand {
        margin-bottom: 29px;
    }

    .login-brand img {
        width: 86px;
        height: 86px;
    }

    .login-brand div {
        font-size: 14px;
    }

    .login-panel h2 {
        font-size: 23px;
    }

    .login-panel .muted {
        margin-bottom: 30px;
    }

    .login-panel form,
    .login-hint,
    .alert.danger {
        width: 100%;
        max-width: 380px;
    }

    .login-panel select,
    .login-panel input[type="text"],
    .login-panel input[type="password"],
    .login-panel .btn.primary {
        height: 52px;
    }
}
</style>
</head>

<body class="login-page">

    <div class="login-shell">

        <!-- ================================
             PANEL KIRI
             ================================ -->

        <div class="login-panel">

            <div class="login-brand">

                <img
                    src="assets/img/logo.png"
                    alt="Logo sekolah"
                >

                <div>
                    SMK FARMASI<br>
                    CENDIKIA FARMA HUSADA
                </div>

            </div>

            <h2>LOGIN</h2>

            <p class="muted">
                Silakan masuk untuk melanjutkan
            </p>

            <?php if ($error): ?>

                <div class="alert danger">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>

            <form method="post">

                <label>
                    Login sebagai
                </label>

                <select
                    name="role"
                    id="role"
                    onchange="ubahLabel()"
                    required
                >
                    <option value="admin">
                        Admin
                    </option>

                    <option value="siswa">
                        Siswa
                    </option>
                </select>

                <label id="usernameLabel">
                    Username
                </label>

                <input
                    type="text"
                    name="username"
                    id="username"
                    placeholder="Username admin"
                    required
                >

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

                <button
                    class="btn primary full"
                    type="submit"
                >
                    Login
                </button>

            </form>

            <div class="login-hint">
                Admin: <b>admin</b> / <b>admin123</b><br>
                Siswa: gunakan NISN dan password akun siswa.
            </div>

        </div>


        <!-- ================================
             PANEL KANAN
             ================================ -->

        <div class="login-info">

            <img
                src="assets/img/cefada.png"
                alt="Gedung sekolah"
            >

        </div>

    </div>


    <script>
        function ubahLabel() {

            const siswa =
                document.getElementById('role').value === 'siswa';

            document.getElementById('usernameLabel').textContent =
                siswa ? 'NISN Siswa' : 'Username';

            document.getElementById('username').placeholder =
                siswa ? 'Masukkan NISN' : 'Username admin';
        }
    </script>

</body>

</html>