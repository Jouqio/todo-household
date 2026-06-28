<?php
session_start();
require_once 'db.php';

$db = getDB();

/* ── Flash message ──────────────────────── */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ── Filters dari URL ───────────────────── */
$f_status    = $_GET['status']    ?? 'semua';
$f_kategori  = $_GET['kategori']  ?? 'semua';
$f_prioritas = $_GET['prioritas'] ?? 'semua';
$q           = trim($_GET['q']    ?? '');

/* ── Build Query ────────────────────────── */
$where  = ['1=1'];
$params = [];

if ($f_status === 'selesai') {
    $where[] = 'status = 1';
} elseif ($f_status === 'belum') {
    $where[] = 'status = 0';
}

if ($f_kategori !== 'semua') {
    $where[] = 'kategori = :kat';
    $params[':kat'] = $f_kategori;
}

if ($f_prioritas !== 'semua') {
    $where[] = 'prioritas = :prio';
    $params[':prio'] = $f_prioritas;
}

if ($q !== '') {
    $where[] = '(judul LIKE :q OR deskripsi LIKE :q)';
    $params[':q'] = "%{$q}%";
}

$whereStr = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT * FROM tasks
    WHERE {$whereStr}
    ORDER BY
        status ASC,
        CASE prioritas WHEN 'Tinggi' THEN 1 WHEN 'Sedang' THEN 2 ELSE 3 END ASC,
        dibuat_pada DESC
");
$stmt->execute($params);
$tasks = $stmt->fetchAll();

/* ── Stats ──────────────────────────────── */
$stats = $db->query("
    SELECT
        COUNT(*)  AS total,
        SUM(status)  AS selesai,
        SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS belum,
        SUM(CASE WHEN prioritas = 'Tinggi' AND status = 0 THEN 1 ELSE 0 END) AS tinggi
    FROM tasks
")->fetch();

$today     = date('Y-m-d');
$kategoris = ['Dapur','Kamar','Ruang Tamu','Kamar Mandi','Taman','Laundry','Lainnya'];
$hasFilter = ($f_status !== 'semua' || $f_kategori !== 'semua' || $f_prioritas !== 'semua' || $q !== '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pekerjaan Rumah — To-Do List</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ── Header ── -->
<header class="app-header">
  <div class="container">
    <a href="index.php" class="app-logo">
      <span class="icon">🏠</span>
      <div>
        <h1>Pekerjaan Rumah</h1>
        <p>Manajemen tugas rumah tangga</p>
      </div>
    </a>
    <a href="add.php" class="btn btn-white">➕ Tambah Tugas</a>
  </div>
</header>

<main>
<div class="container">

  <!-- Flash -->
  <?php if ($flash): ?>
  <div style="margin-top:1.25rem;">
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
      <?= $flash['type'] === 'success' ? '✅' : '❌' ?>
      <?= htmlspecialchars($flash['message']) ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="lbl">📋 Total Tugas</div>
      <div class="val"><?= (int)$stats['total'] ?></div>
    </div>
    <div class="stat-card s-selesai">
      <div class="lbl">✅ Selesai</div>
      <div class="val"><?= (int)$stats['selesai'] ?></div>
    </div>
    <div class="stat-card s-pending">
      <div class="lbl">⏳ Belum Selesai</div>
      <div class="val"><?= (int)$stats['belum'] ?></div>
    </div>
    <div class="stat-card s-tinggi">
      <div class="lbl">🔴 Prioritas Tinggi</div>
      <div class="val"><?= (int)$stats['tinggi'] ?></div>
    </div>
  </div>

  <!-- Filters -->
  <form method="GET" action="index.php" class="filter-bar">
    <span class="filter-lbl">Filter:</span>

    <select name="status" class="filter-select" onchange="this.form.submit()">
      <option value="semua"  <?= $f_status==='semua'   ? 'selected':'' ?>>Semua Status</option>
      <option value="belum"  <?= $f_status==='belum'   ? 'selected':'' ?>>⏳ Belum Selesai</option>
      <option value="selesai"<?= $f_status==='selesai' ? 'selected':'' ?>>✅ Sudah Selesai</option>
    </select>

    <select name="kategori" class="filter-select" onchange="this.form.submit()">
      <option value="semua" <?= $f_kategori==='semua' ? 'selected':'' ?>>Semua Kategori</option>
      <?php foreach ($kategoris as $kat): ?>
      <option value="<?= htmlspecialchars($kat) ?>"
              <?= $f_kategori===$kat ? 'selected':'' ?>><?= htmlspecialchars($kat) ?></option>
      <?php endforeach; ?>
    </select>

    <select name="prioritas" class="filter-select" onchange="this.form.submit()">
      <option value="semua"  <?= $f_prioritas==='semua'  ? 'selected':'' ?>>Semua Prioritas</option>
      <option value="Tinggi" <?= $f_prioritas==='Tinggi' ? 'selected':'' ?>>🔴 Tinggi</option>
      <option value="Sedang" <?= $f_prioritas==='Sedang' ? 'selected':'' ?>>🟡 Sedang</option>
      <option value="Rendah" <?= $f_prioritas==='Rendah' ? 'selected':'' ?>>🟢 Rendah</option>
    </select>

    <div class="search-wrap">
      <input type="search" name="q" class="search-input"
             placeholder="🔍 Cari tugas..."
             value="<?= htmlspecialchars($q) ?>">
      <button type="submit" class="btn btn-outline btn-sm btn-icon" title="Cari">🔍</button>
    </div>

    <?php if ($hasFilter): ?>
    <a href="index.php" class="btn btn-ghost btn-sm">✕ Reset</a>
    <?php endif; ?>
  </form>

  <!-- List header -->
  <div class="section-hdr">
    <h2>Daftar Tugas <?= $hasFilter ? '<span style="font-weight:500;color:var(--text-3)">(filter aktif)</span>' : '' ?></h2>
    <span class="count-pill"><?= count($tasks) ?> tugas</span>
  </div>

  <!-- Empty state -->
  <?php if (empty($tasks)): ?>
  <div class="empty">
    <div class="ico">📋</div>
    <h3>Tidak ada tugas ditemukan</h3>
    <p><?= $hasFilter
        ? 'Coba ubah atau reset filter di atas.'
        : 'Yuk mulai catat pekerjaan rumah kamu!' ?></p>
    <?php if (!$hasFilter): ?>
    <a href="add.php" class="btn btn-primary">➕ Tambah Tugas Pertama</a>
    <?php else: ?>
    <a href="index.php" class="btn btn-outline">✕ Reset Filter</a>
    <?php endif; ?>
  </div>

  <?php else: ?>
  <!-- Task cards -->
  <div class="task-list">
    <?php foreach ($tasks as $task): ?>
    <?php
      $done      = $task['status'] == 1;
      $prioLower = strtolower($task['prioritas']);
      $overdue   = !$done && $task['tanggal_target'] && $task['tanggal_target'] < $today;
      $curUri    = htmlspecialchars($_SERVER['REQUEST_URI']);
    ?>
    <div class="task-card prio-<?= $prioLower ?> <?= $done ? 'done' : '' ?>">

      <!-- Toggle -->
      <div class="task-toggle">
        <form method="POST" action="toggle.php">
          <input type="hidden" name="id" value="<?= $task['id'] ?>">
          <input type="hidden" name="redirect" value="<?= $curUri ?>">
          <button type="submit" class="toggle-btn"
                  title="<?= $done ? 'Tandai Belum Selesai' : 'Tandai Selesai' ?>">
            <?= $done ? '✓' : '' ?>
          </button>
        </form>
      </div>

      <!-- Content -->
      <div class="task-body">
        <div class="task-judul"><?= htmlspecialchars($task['judul']) ?></div>
        <?php if (!empty($task['deskripsi'])): ?>
        <div class="task-desc"><?= htmlspecialchars($task['deskripsi']) ?></div>
        <?php endif; ?>
        <div class="task-meta">
          <span class="badge badge-kat">📁 <?= htmlspecialchars($task['kategori']) ?></span>
          <span class="badge badge-<?= $prioLower ?>">
            <?= $task['prioritas']==='Tinggi' ? '🔴' : ($task['prioritas']==='Sedang' ? '🟡' : '🟢') ?>
            <?= htmlspecialchars($task['prioritas']) ?>
          </span>
          <?php if ($task['tanggal_target']): ?>
          <span class="badge-due <?= $overdue ? 'overdue' : '' ?>">
            <?= $overdue ? '⚠️ Terlambat! ' : '📅 ' ?>
            <?= date('d M Y', strtotime($task['tanggal_target'])) ?>
          </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Actions -->
      <div class="task-actions">
        <a href="edit.php?id=<?= $task['id'] ?>" class="btn btn-outline btn-sm" title="Edit tugas">✏️</a>
        <form method="POST" action="delete.php"
              onsubmit="return confirm('Hapus tugas "<?= addslashes(htmlspecialchars($task['judul'])) ?>"?')">
          <input type="hidden" name="id" value="<?= $task['id'] ?>">
          <input type="hidden" name="redirect" value="<?= $curUri ?>">
          <button type="submit" class="btn btn-danger btn-sm" title="Hapus tugas">🗑️</button>
        </form>
      </div>

    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="spacer"></div>
</div><!-- /.container -->
</main>

</body>
</html>
