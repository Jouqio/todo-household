<?php
/**
 * Database connection menggunakan SQLite
 * File todo.db akan dibuat otomatis di folder yang sama
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dbPath = __DIR__ . '/todo.db';
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tasks (
                id              INTEGER PRIMARY KEY AUTOINCREMENT,
                judul           TEXT    NOT NULL,
                deskripsi       TEXT    DEFAULT '',
                kategori        TEXT    DEFAULT 'Lainnya',
                prioritas       TEXT    DEFAULT 'Sedang',
                status          INTEGER DEFAULT 0,
                tanggal_target  DATE,
                dibuat_pada     DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    return $pdo;
}
