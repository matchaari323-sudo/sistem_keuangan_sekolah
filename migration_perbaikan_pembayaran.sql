USE keuangan_sekolah;

-- Jalankan jika database lama belum memiliki kolom berikut.
-- Jika memakai config.php versi revisi, kolom akan ditambahkan otomatis.
ALTER TABLE pembayaran
    ADD COLUMN IF NOT EXISTS metode ENUM('Tunai','Transfer') NOT NULL DEFAULT 'Tunai',
    ADD COLUMN IF NOT EXISTS keterangan VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS tanggal_bayar DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN IF NOT EXISTS admin_id INT DEFAULT NULL;
