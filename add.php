<?php
session_start();
require_once 'db.php';

$errors = [];
$v = [
    'judul'          => '',
    'deskripsi'      => '',
    'kategori'       => 'Lainnya',
    'prioritas'      => 'Sedang',
    'tanggal_target' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v['judul']          = trim($_POST['judul']          ?? '');
    $v['deskripsi']      = trim($_POST['deskripsi']      ?? '');
    $v['kategori']       =      $_POST['kategori']       ?? 'Lainnya';
    $v['prioritas']      =      $_POST['prioritas']      ?? 'Sedang';
    $v['tanggal_target'] = trim($_POST['tanggal_target'] ?? '');

    if ($v['judul'] === '') {
        $errors[] = 'Judul tugas tidak boleh kosong.';
    }

    if (empty($errors)) {
        $db   = getDB();
        $stmt = $db->prepare("
            INSERT INTO tasks (judul, deskripsi, kategori, prioritas, tanggal_target)
            VALUES (:judul, :deskripsi, :kategori, :prioritas, :target)
        ");
        $stmt->execute([
            ':judul'      => $v['judul'],
            ':deskripsi'  => $v['deskripsi'],
            ':kategori'   => $v['kategori'],
            ':prioritas'  => $v['prioritas'],
            ':target'     => $v['tanggal_target'] ?: null,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => '✅ Tugas berhasil ditambahkan!'];
        header('Location: index.php');
        exit;
    }
}

$kategoris    = ['Dapur','Kamar','Ruang Tamu','Kamar Mandi','Taman','Laundry','Lainnya'];
$prioritas_op = ['Rendah','Sedang','Tinggi'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Tugas — Pekerjaan Rumah</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="app-header">
  <div class="container">
    <a href="index.php" class="app-logo">
      <span class="icon">🏠</span>
      <div>
        <h1>Pekerjaan Rumah</h1>
        <p>Manajemen tugas rumah tangga</p>
      </div>
    </a>
    <a href="index.php" class="btn btn-white">← Kembali</a>
  </div>
</header>

<main>
<div class="container">
  <div class="form-wrap">

    <div class="form-heading">
      <span class="ico">➕</span>
      <h2>Tambah tugas baru</h2>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      ❌ <?= implode(' ', array_map('htmlspecialchars', $errors)) ?>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>

      <div class="form-group">
        <label class="form-label">Judul Tugas <span class="req">*</span></label>
        <input type="text" name="judul" class="form-control"
               placeholder="mis. Cuci piring, Sapu lantai, Setrika baju…"
               value="<?= htmlspecialchars($v['judul']) ?>"
               autofocus>
      </div>

      <div class="form-group">
        <label class="form-label">Deskripsi <span style="font-weight:400;color:var(--text-3)">(opsional)</span></label>
        <textarea name="deskripsi" class="form-control"
                  placeholder="Tambahkan detail tugas di sini…"><?= htmlspecialchars($v['deskripsi']) ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <select name="kategori" class="form-control">
            <?php foreach ($kategoris as $kat): ?>
            <option value="<?= htmlspecialchars($kat) ?>"
                    <?= $v['kategori']===$kat ? 'selected':'' ?>>
              <?= htmlspecialchars($kat) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Prioritas</label>
          <select name="prioritas" class="form-control">
            <?php foreach ($prioritas_op as $p): ?>
            <option value="<?= $p ?>" <?= $v['prioritas']===$p ? 'selected':'' ?>><?= $p ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Target Penyelesaian <span style="font-weight:400;color:var(--text-3)">(opsional)</span></label>
        <input type="date" name="tanggal_target" class="form-control"
               value="<?= htmlspecialchars($v['tanggal_target']) ?>"
               min="<?= date('Y-m-d') ?>">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">✅ Simpan Tugas</button>
        <a href="index.php" class="btn btn-ghost">Batal</a>
      </div>

    </form>
  </div>
</div>
</main>

</body>
</html>
