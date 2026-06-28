#  To-Do Pekerjaan Rumah

Aplikasi manajemen tugas rumah tangga berbasis **PHP + SQLite** — tidak butuh MySQL!

---

##  Fitur

* ✅ Tambah, lihat, edit, dan hapus tugas (CRUD lengkap)
* ✅ Tandai tugas selesai / belum selesai (toggle)
* ✅ Tiga tingkat prioritas: 🔴 Tinggi · 🟡 Sedang · 🟢 Rendah
* ✅ 7 kategori: Dapur, Kamar, Ruang Tamu, Kamar Mandi, Taman, Laundry, Lainnya
* ✅ Filter berdasarkan status, kategori, dan prioritas
* ✅ Pencarian judul/deskripsi
* ✅ Target tanggal + peringatan jika terlambat (overdue)
* ✅ Ringkasan statistik (total, selesai, pending, prioritas tinggi)
* ✅ Database SQLite — otomatis dibuat, tidak perlu setup apapun

---

##  Kebutuhan

| Kebutuhan       | Minimum        |
| --------------- | -------------- |
| PHP             | 7.4 atau lebih |
| Ekstensi PDO    | Aktif (bawaan) |
| Ekstensi SQLite | Aktif (bawaan) |

Cek ekstensi di terminal:

```bash
php -m | grep -E "pdo|sqlite"
```

---

##  Cara Install & Jalankan

### Opsi A — XAMPP / WAMP / Laragon

1. Copy folder `todo-pekerjaan-rumah/` ke `htdocs/` (XAMPP) atau `www/` (WAMP)
2. Pastikan Apache & PHP aktif
3. Buka browser: `http://localhost/todo-pekerjaan-rumah/`

### Opsi B — PHP Built-in Server (termudah)

```bash
cd todo-pekerjaan-rumah
php -S localhost:8080
```

Lalu buka: `http://localhost:8080`

> **Catatan:** File `todo.db` akan dibuat otomatis di folder yang sama saat pertama kali diakses.
> Pastikan folder project dapat ditulis oleh web server.

---

##  Struktur File

```
todo-pekerjaan-rumah/
├── db.php        → Koneksi database SQLite + auto-create tabel
├── index.php     → Halaman utama (daftar tugas, stats, filter)
├── add.php       → Form tambah tugas baru
├── edit.php      → Form edit tugas yang sudah ada
├── delete.php    → Handler hapus tugas (POST)
├── toggle.php    → Handler toggle selesai/belum (POST)
├── style.css     → Stylesheet (Plus Jakarta Sans, green theme)
├── README.md     → Dokumentasi ini
└── todo.db       → Database SQLite (dibuat otomatis)
```

---

##  Skema Database

```sql
CREATE TABLE tasks (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    judul           TEXT    NOT NULL,
    deskripsi       TEXT    DEFAULT '',
    kategori        TEXT    DEFAULT 'Lainnya',
    prioritas       TEXT    DEFAULT 'Sedang',   -- Rendah / Sedang / Tinggi
    status          INTEGER DEFAULT 0,           -- 0=belum, 1=selesai
    tanggal_target  DATE,
    dibuat_pada     DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

##  Keamanan Dasar

* Semua output di-escape dengan `htmlspecialchars()`
* Query database menggunakan prepared statements (anti SQL injection)
* Handler delete & toggle hanya menerima POST request
* Redirect URL divalidasi dengan regex whitelist

---

## 📦 Lisensi

Bebas digunakan untuk pembelajaran. Silakan modifikasi sesuai kebutuhan!
