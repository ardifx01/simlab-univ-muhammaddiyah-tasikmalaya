# SIMLab UMTAS  
*Laboratorium & Sistem Informasi Universitas Muhammadiyah Tasikmalaya*

---

## 📋 Deskripsi
SIMLab UMTAS adalah proyek sistem informasi yang dikembangkan untuk mendukung kegiatan laboratorium dan layanan penelitian di Universitas Muhammadiyah Tasikmalaya.  
Tujuan utamanya adalah:

- Memfasilitasi pengelolaan data laboratorium (alat, pengguna, penggunaan).  
- Menyediakan dashboard untuk pemantauan & laporan.  
- Mendukung kolaborasi internal dan aksesibilitas data.  

---

## 🚀 Fitur Utama

| Fitur | Keterangan |
|---|---|
| Manajemen Laboratorium | Input / edit data alat, stok, kondisi, pemeliharaan |
| Pengguna | Admin & pengguna laboratorium dengan hak akses berbeda |
| Dashboard | Statistik penggunaan, alat yang sering dipakai, laporan |
| Keamanan | Autentikasi (login), validasi input |
| Responsive | Bisa diakses dari desktop & mobile |

---

## 🛠️ Teknologi

- **Backend:** [CodeIgniter 4](https://codeigniter.com/) – framework PHP yang sederhana, cepat, dan memiliki arsitektur MVC sehingga memudahkan dalam pengembangan aplikasi yang terstruktur dan mudah dipelihara.  
- **Frontend:** View engine bawaan CodeIgniter yang dapat dipadukan dengan CSS framework seperti **Bootstrap** atau **TailwindCSS** untuk membangun antarmuka yang responsif dan user-friendly.  
- **Database:** **MySQL/MariaDB** (dengan dukungan untuk PostgreSQL) digunakan sebagai sistem manajemen basis data untuk menyimpan dan mengelola informasi laboratorium.  
- **Dependency Management:** **Composer**, yang memudahkan dalam pengelolaan library eksternal dan mempercepat alur pengembangan.  
- **Testing:** **PHPUnit** digunakan sebagai kerangka kerja pengujian untuk memastikan stabilitas serta keandalan fitur yang dikembangkan.  

---

## 📁 Struktur Folder
```
/simlab-univ-muhammaddiyah-tasikmalaya
├── backend/ ← kode backend
├── frontend/ ← kode frontend / UI
├── migrations/ ← skrip migrasi database
├── public/ ← aset publik (gambar, CSS, JS)
└── README.md ← dokumentasi proyek
```

---

## 🔧 Cara Jalankan (Local)

1. Clone repositori  
   ```bash
   git clone https://github.com/ardifx01/simlab-univ-muhammaddiyah-tasikmalaya.git
   ```
2. Instal dependensi backend & frontend
   ```
   cd backend && npm install     # atau composer install
   cd frontend && npm install
   ```
3. Setup database .env
   ```
   # DATABASE
   # database.default.hostname = localhost
   # database.default.database = ci4
   # database.default.username = root
   # database.default.password = root
   # database.default.DBDriver = MySQLi
   # database.default.DBPrefix =
   # database.default.port = 3306
   ```
4. Jalankan server
   ```
   npm run dev       # backend
   npm run serve     # frontend
   ```
---

## 📂 Deployment

- Pastikan **environment variable** seperti DB connection, secret key, domain sudah dikonfigurasi.  
- Gunakan penyimpanan aset (gambar, lampiran) yang permanen.  
- Pastikan akses ke server / hosting aman.  

---
## 🌐 Link URL

Aplikasi ini dapat diakses secara langsung melalui link berikut:  
👉 [SIMLab UMTAS - Live URL](https://simlab.umtas.ac.id/)


---
## 👥 Kontribusi

Kontribusi sangat dipersilakan! Berikut cara yang bisa kamu bantu:

- 🐞 Bug fixes  
- ✨ Fitur baru (misalnya export laporan, notifikasi)  
- 📝 Dokumentasi / screenshot  
- 🎨 Optimasi UI & UX  

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE)

---

## 📫 Kontak

- 👤 Sama saya: **[ardifx01](https://github.com/ardifx01)**  
- 📧 Email: *[nadhifkarim89@gmail.com]*  
- 🏫 Universitas Muhammadiyah Tasikmalaya  

---



