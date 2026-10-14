<?php
require_once "../config.php";
require_admin();
$title = "Data Siswa";

$q = trim($_GET['q'] ?? '');
$kelas = trim($_GET['kelas'] ?? '');

$sql = "SELECT s.*,
        COALESCE(SUM(t.nominal), 0) AS total_tagihan,
        COALESCE(SUM(p.total_dibayar), 0) AS total_dibayar
        FROM siswa s
        LEFT JOIN tagihan t ON t.siswa_id = s.id
        LEFT JOIN (
            SELECT tagihan_id, SUM(jumlah) AS total_dibayar
            FROM pembayaran
            GROUP BY tagihan_id
        ) p ON p.tagihan_id = t.id
        WHERE (s.nama LIKE CONCAT('%',?,'%') OR s.nisn LIKE CONCAT('%',?,'%'))";

if ($kelas !== '') {
    $sql .= " AND s.kelas=?";
}

$sql .= " GROUP BY s.id
          ORDER BY s.nama";

$stmt = $conn->prepare($sql);

if ($kelas !== '') {
    $stmt->bind_param("sss", $q, $q, $kelas);
} else {
    $stmt->bind_param("ss", $q, $q);
}

$stmt->execute();
$data = $stmt->get_result();

$kelasRes = $conn->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas");

include "_header.php";
?>

<h1 class="page-title">Data Siswa</h1>
<p class="page-subtitle">Kelola data siswa berdasarkan nama, NISN, kelas, dan tunggakan.</p>

<div class="card">
    <div class="toolbar">
        <form class="search" method="get">
            <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari nama siswa / NISN...">

```
        <select name="kelas">
            <option value="">Semua Kelas</option>

            <?php while ($k = $kelasRes->fetch_assoc()): ?>
                <option <?= $kelas === $k['kelas'] ? 'selected' : '' ?>>
                    <?= e($k['kelas']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button class="btn primary">Cari</button>
    </form>

    <a class="btn dark" href="siswa_tambah.php">＋ Tambah Siswa</a>
</div>

<div class="table-wrap">
    <table class="table">
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

        <?php
        $no = 1;
        while ($s = $data->fetch_assoc()):
            $tunggakan = $s['total_tagihan'] - $s['total_dibayar'];
        ?>

            <tr>
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
                    <a class="btn light" href="siswa_edit.php?id=<?= $s['id'] ?>">
                        Edit
                    </a>

                    <a class="btn danger"
                       href="siswa_hapus.php?id=<?= $s['id'] ?>"
                       onclick="return confirm('Hapus siswa ini?')">
                        Hapus
                    </a>
                </td>
            </tr>

        <?php endwhile; ?>
    </table>
</div>
```

</div>

<?php include "_footer.php"; ?>
