<?php
session_start();

/* Hanya terima POST request */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$id       = (int)($_POST['id']       ?? 0);
$redirect = trim($_POST['redirect'] ?? 'index.php');

/* Validasi redirect — hanya boleh relative path di server ini */
if (!preg_match('/^[a-zA-Z0-9\/_?=&%+.~-]+$/', $redirect)) {
    $redirect = 'index.php';
}

if ($id > 0) {
    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Tugas berhasil dihapus.'];
    } else {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Tugas tidak ditemukan.'];
    }
}

header('Location: ' . $redirect);
exit;
