<?php
session_start();
require_once 'db.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: index.php');
    exit;
}

/* Ambil data tugas */
$q = $db->prepare("SELECT * FROM tasks WHERE id = ?");
$q->execute([$id]);
$task = $q->fetch();

if (!$task) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tugas tidak ditemukan.'];
    header('Location: index.php');
    exit;
}

$errors = [];
$v      = $task; // pre-fill dengan data lama

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
        $stmt = $db->prepare("
            UPDATE tasks
            SET judul          = :judul,
                deskripsi      = :deskripsi,
                kategori       = :kategori,
                prioritas      = :prioritas,
                tanggal_target = :target
            WHERE id = :id
        ");
        $stmt->execute([
            ':judul'      => $v['judul'],
            ':deskripsi'  => $v['deskripsi'],
            ':kategori'   => $v['kategori'],
            ':prioritas'  => $v['prioritas'],
            ':target'     => $v['tanggal_target'] ?: null,
            ':id'         => $id,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => '✏️ Tugas berhasil diperbarui!'];
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
  <title>Edit Tugas — Pekerjaan Rumah</title>
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
      <span class="ico">✏️</span>
      <h2>Edit Tugas</h2>
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
                  placeholder="Tambahkan detail tugas di sini…"><?= htmlspecialchars($v['deskripsi'] ?? '') ?></textarea>
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
               value="<?= htmlspecialchars($v['tanggal_target'] ?? '') ?>">
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
        <a href="index.php" class="btn btn-ghost">Batal</a>
      </div>

    </form>
  </div>
</div>
</main>

</body>
</html>
