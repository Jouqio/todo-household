<?php
session_start();

/* hanya terima POST request */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

require_once 'db.php';

$id       = (int)($_POST['id']       ?? 0);
$redirect = trim($_POST['redirect'] ?? 'index.php');

/* validasi redirect */
if (!preg_match('/^[a-zA-Z0-9\/_?=&%+.~-]+$/', $redirect)) {
    $redirect = 'index.php';
}

if ($id > 0) {
    $db   = getDB();
    $stmt = $db->prepare("
        UPDATE tasks
        SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END
        WHERE id = ?
    ");
    $stmt->execute([$id]);
}

header('Location: ' . $redirect);
exit;
