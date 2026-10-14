SISTEM KEUANGAN SEKOLAH - PHP NATIVE + MYSQL

1. REQUIREMENTS
- Laragon/XAMPP
- PHP 8.x atau lebih baru
- MySQL/MariaDB
- Browser
- VS Code

2. INSTALLASI
a. Copy folder "sistem_keuangan_sekolah" ke:
   C:\laragon\www\
b. Jalankan Apache/Nginx dan MySQL di Laragon.
c. Buka phpMyAdmin.
d. Import file database.sql.
e. Pastikan database yang terbentuk bernama:
   keuangan_sekolah
f. Jika username/password MySQL Laragon berbeda, ubah config.php.

3. MENJALANKAN
Buka:
http://localhost/sistem_keuangan_sekolah/

4. AKUN DEMO
Admin:
username: admin
password: admin123

Siswa:
NISN: 2023001
password: admin123

5. FITUR ADMIN
- Dashboard ringkasan
- Data siswa
- Search nama/NISN
- Filter kelas
- Tambah siswa
- Edit siswa
- Hapus siswa
- Input pembayaran
- Jenis pembayaran: SPP, Uang Baju, Uang Buku, Daftar Ulang
- Riwayat pembayaran
- Daftar tunggakan
- Cetak struk menggunakan dialog print browser

6. FITUR SISWA
- Login menggunakan NISN + password
- Dashboard
- Melihat total tagihan
- Melihat total pembayaran
- Melihat sisa tunggakan
- Melihat status Lunas/Belum Lunas
- Melihat riwayat pembayaran

CATATAN
- Saat siswa baru ditambahkan, sistem otomatis membuat tagihan SPP Januari-Desember
  serta Uang Baju, Uang Buku, dan Daftar Ulang berdasarkan nominal_default pada tabel jenis_pembayaran.
- Nominal default dapat diubah melalui phpMyAdmin pada tabel jenis_pembayaran.
- Password disimpan menggunakan password_hash().
- Struk tidak membutuhkan library PDF; tombol Cetak memakai window.print() dan CSS print.
