# SPK Kedisiplinan Karyawan – Metode SAW
**Sistem Pendukung Keputusan Penilaian Kedisiplinan Karyawan Berdasarkan Data Kehadiran**

---

## 🔐 Kredensial Login

| Field    | Value             |
|----------|-------------------|
| Email    | `admin@spk.com`   |
| Password | `password`        |

---

## 📁 Struktur Folder

```
saw_spk/
├── index.php                    ← Dashboard utama
├── login.php                    ← Halaman login
├── logout.php                   ← Handler logout
├── database.sql                 ← DDL + data dummy
│
├── config/
│   └── koneksi.php              ← Konfigurasi & koneksi PDO (Singleton)
│
├── algorithm/
│   └── SAW.php                  ← Logika murni metode SAW (tanpa DB/UI)
│
├── includes/
│   ├── session.php              ← Auth, flash message, CSRF, helper
│   ├── header.php               ← Template sidebar + topbar
│   └── footer.php               ← Penutup HTML
│
├── pages/
│   ├── karyawan/
│   │   ├── index.php            ← Daftar karyawan (R)
│   │   ├── create.php           ← Tambah karyawan (C)
│   │   ├── edit.php             ← Edit karyawan (U)
│   │   └── delete.php           ← Hapus karyawan (D)
│   │
│   ├── kriteria/
│   │   ├── index.php            ← Daftar kriteria (R)
│   │   ├── create.php           ← Tambah kriteria (C)
│   │   ├── edit.php             ← Edit kriteria (U)
│   │   └── delete.php           ← Hapus kriteria (D)
│   │
│   └── penilaian/
│       ├── input.php            ← Input nilai absensi (CRUD nilai)
│       ├── hasil.php            ← Hasil SAW + tombol cetak/laporan
│       └── export.php           ← Laporan HTML standalone
│
└── assets/
    ├── css/
    │   └── style.css            ← Design system lengkap
    ├── js/
    │   └── app.js               ← JS ringan (toggle, konfirmasi)
    └── img/
        └── logo.png             ← (Letakkan logo Anda di sini)
```

---

## ⚙️ Cara Instalasi

### 1. Import Database
```sql
-- Di phpMyAdmin atau MySQL CLI:
source /path/to/saw_spk/database.sql;
```

### 2. Konfigurasi Koneksi
Edit file `config/koneksi.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'spk_kedisiplinan');
define('DB_USER', 'root');       // sesuaikan
define('DB_PASS', '');           // sesuaikan
```

### 3. Konfigurasi BASE_URL
Di setiap file `index.php` dan halaman, `BASE_URL` di-set otomatis via:
```php
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
```
Jika project di subfolder (misal `http://localhost/saw_spk/`), ini otomatis benar.

### 4. Tempatkan di Web Server
- **XAMPP/Laragon**: Taruh di folder `htdocs/saw_spk/`
- Akses: `http://localhost/saw_spk/`

### 5. Logo Aplikasi
Taruh file logo di `assets/img/logo.png` (disarankan 100×100px).

---

## 🧮 Cara Kerja Metode SAW

### Langkah 1 – Matriks Keputusan
Nilai mentah tiap karyawan (alternatif) untuk setiap kriteria.

### Langkah 2 – Normalisasi
```
Benefit : r_ij = x_ij / max(x_ij)
Cost    : r_ij = min(x_ij) / x_ij
```

### Langkah 3 – Perangkingan
```
V_i = Σ (w_j × r_ij)
```
Karyawan dengan nilai V_i tertinggi = paling disiplin.

---

## 📊 Kriteria Penilaian (Data Dummy)

| Kode | Nama                        | Bobot | Atribut |
|------|-----------------------------|-------|---------|
| C1   | Tingkat Kehadiran (%)       | 30%   | Benefit |
| C2   | Jumlah Keterlambatan (hari) | 25%   | Cost    |
| C3   | Izin Tidak Resmi (hari)     | 20%   | Cost    |
| C4   | Ketepatan Waktu Lembur (%)  | 15%   | Benefit |
| C5   | Pelanggaran Tata Tertib     | 10%   | Cost    |

**Total Bobot = 100%** ✓

---

## 🛠 Teknologi

- **PHP** 8.0+ (Native, tanpa framework)
- **MySQL** 5.7+ / MariaDB
- **PDO** dengan Prepared Statements
- **CSS** Custom (tanpa Bootstrap/Tailwind)
- **JavaScript** Vanilla (tanpa jQuery)
