CREATE DATABASE IF NOT EXISTS keuangan_sekolah
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE keuangan_sekolah;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS tagihan;
DROP TABLE IF EXISTS jenis_pembayaran;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nisn VARCHAR(20) NOT NULL UNIQUE,
    nis VARCHAR(20) DEFAULT NULL,
    nama VARCHAR(100) NOT NULL,
    kelas VARCHAR(30) NOT NULL,
    jurusan VARCHAR(100) DEFAULT NULL,
    jenis_kelamin ENUM('L','P') DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE jenis_pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL UNIQUE,
    nominal_default DECIMAL(12,2) NOT NULL DEFAULT 0
);

CREATE TABLE tagihan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    jenis_id INT NOT NULL,
    periode VARCHAR(30) DEFAULT NULL,
    tahun INT NOT NULL,
    nominal DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_tagihan (siswa_id, jenis_id, periode, tahun),
    CONSTRAINT fk_tagihan_siswa FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    CONSTRAINT fk_tagihan_jenis FOREIGN KEY (jenis_id) REFERENCES jenis_pembayaran(id) ON DELETE RESTRICT
);

CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_struk VARCHAR(30) NOT NULL UNIQUE,
    tagihan_id INT NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    metode ENUM('Tunai','Transfer') NOT NULL DEFAULT 'Tunai',
    keterangan VARCHAR(255) DEFAULT NULL,
    tanggal_bayar DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    admin_id INT DEFAULT NULL,
    CONSTRAINT fk_pembayaran_tagihan FOREIGN KEY (tagihan_id) REFERENCES tagihan(id) ON DELETE RESTRICT,
    CONSTRAINT fk_pembayaran_admin FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (username, password, nama, role)
VALUES ('admin', '$2y$12$gJU/NVvPP0lHcjTSrWeAFOf4FTd4CZ9m0XNbVynUkm.Trsq9IxLXW', 'Administrator', 'admin');

INSERT INTO jenis_pembayaran (nama, nominal_default) VALUES
('SPP', 250000),
('Uang Baju', 500000),
('Uang Buku', 350000),
('Daftar Ulang', 1000000);

INSERT INTO siswa (nisn, nis, nama, kelas, jurusan, jenis_kelamin, alamat, no_hp, password) VALUES
('2023001','23001','Ahmad Fauzi','XI Farmasi 1','Farmasi Klinis dan Komunitas','L','Bandar Lampung','081234567890','$2y$12$gJU/NVvPP0lHcjTSrWeAFOf4FTd4CZ9m0XNbVynUkm.Trsq9IxLXW'),
('2023002','23002','Siti Aisyah','XI Farmasi 1','Farmasi Klinis dan Komunitas','P','Bandar Lampung','081234567891','$2y$12$gJU/NVvPP0lHcjTSrWeAFOf4FTd4CZ9m0XNbVynUkm.Trsq9IxLXW'),
('2023003','23003','Rina Safitri','XI Farmasi 2','Farmasi Klinis dan Komunitas','P','Bandar Lampung','081234567892','$2y$12$gJU/NVvPP0lHcjTSrWeAFOf4FTd4CZ9m0XNbVynUkm.Trsq9IxLXW');

INSERT INTO tagihan (siswa_id, jenis_id, periode, tahun, nominal)
SELECT s.id, j.id, m.bulan, YEAR(CURDATE()), 250000
FROM siswa s
JOIN jenis_pembayaran j ON j.nama='SPP'
JOIN (
    SELECT 'Januari' bulan UNION ALL SELECT 'Februari' UNION ALL SELECT 'Maret'
    UNION ALL SELECT 'April' UNION ALL SELECT 'Mei' UNION ALL SELECT 'Juni'
    UNION ALL SELECT 'Juli' UNION ALL SELECT 'Agustus' UNION ALL SELECT 'September'
    UNION ALL SELECT 'Oktober' UNION ALL SELECT 'November' UNION ALL SELECT 'Desember'
) m;

INSERT INTO tagihan (siswa_id, jenis_id, periode, tahun, nominal)
SELECT s.id, j.id, NULL, YEAR(CURDATE()), j.nominal_default
FROM siswa s CROSS JOIN jenis_pembayaran j
WHERE j.nama IN ('Uang Baju','Uang Buku','Daftar Ulang');
