# Suka Petshop - Sistem POS (Point of Sale)

Sistem Informasi Manajemen Inventaris dan Penjualan untuk toko perlengkapan hewan "Suka Petshop". Proyek ini dikembangkan secara komprehensif untuk memenuhi tugas besar mata kuliah Pemrograman Berorientasi Objek (PBO) dan Manajemen Basis Data, Program Studi Informatika, Universitas Tanjungpura.

## 📝 Deskripsi Proyek
Aplikasi ini dirancang untuk mendigitalisasi seluruh proses operasional pada Suka Petshop, mulai dari pengelolaan stok barang hingga pencatatan transaksi penjualan secara akurat dan efisien. Fokus utama dari pengembangan ini adalah penerapan arsitektur web yang terstruktur, pemisahan hak akses keamanan, serta pemeliharaan integritas dan konsistensi data pada sistem basis data relasional menggunakan MySQL.

---

## ✨ Fitur Utama & Pembaruan Sistem
Sistem ini telah diperbarui dengan fungsionalitas tingkat lanjut (*advanced features*) untuk memenuhi kebutuhan operasional toko yang nyata:

* **Autentikasi Multi-User (Role-Based Access Control):** Sistem memisahkan hak akses menjadi dua peran utama dengan proteksi menu yang ketat:
    * **Owner:** Memiliki kendali penuh atas manajemen produk (CRUD), kategori barang (CRUD), data petugas kasir (CRUD), serta pemantauan riwayat log transaksi beserta rincian detail nota belanja.
    * **Kasir:** Memiliki hak akses operasional terbatas untuk melihat daftar produk/kategori (*read-only*) tanpa tombol manipulasi data, memproses transaksi baru, serta melihat riwayat log nota belanja beserta detailnya untuk verifikasi pelanggan. Menu pengelolaan kasir diblokir sepenuhnya bagi peran ini.
* **Dasbor Ringkasan Statistik:** Menyediakan visualisasi pemantauan aset secara *real-time* di bagian atas halaman panel menggunakan fungsi agregat SQL untuk menghitung Total Inventaris Produk, Estimasi Nilai Jual Aset, dan Indikator Otomatis Produk dengan Stok Kritis (di bawah 10 unit).
* **Terminal POS & Keranjang Belanja Multi-Item:** Form transaksi baru kini mendukung input banyak barang sekaligus dalam satu nota penjualan (*shopping cart* berbasis JavaScript Array). Sistem secara otomatis mengkalkulasi subtotal dan memvalidasi kuantitas agar tidak melebihi sisa stok fisik yang tersedia.
* **Multi-Metode Pembayaran:** Pencatatan jalur pembayaran yang fleksibel untuk memfasilitasi transaksi non-tunai maupun tunai, mencakup opsi **Cash**, **QRIS**, dan **Kartu Kredit**.
* **Otomatisasi Penomoran Sekuensial Konten:** Sistem secara dinamis mencari data terakhir di dalam basis data untuk menyusun ID secara urut dan otomatis:
    * **ID Transaksi:** Format sekuensial otomatis berbasis tanggal, contoh: `TRX-260529-101`, `TRX-260529-102`, dan seterusnya.
    * **ID Detail Transaksi:** Format indeks urut independen yang melanjutkan entri terakhir di database, contoh: `DTL-101`, `DTL-102`, dan seterusnya.
* **Database Transaction Integrity (PDO):** Pengolahan transaksi multi-item menggunakan mekanisme transaksi basis data (`beginTransaction`, `commit`, `rollBack`). Jika salah satu item keranjang gagal diproses atau mengalami galat stok, seluruh rangkaian transaksi dibatalkan demi mencegah ketidaksesuaian data keuangan dan sisa stok barang.
* **Keamanan Query Terproteksi:** Implementasi *Prepared Statements* menggunakan PHP Data Objects (PDO) pada seluruh query backend untuk memitigasi risiko keamanan dari serangan *SQL Injection*.

---

## 📂 Struktur Folder Proyek
Proyek ini mengadopsi struktur pengelompokan berkas yang rapi untuk memisahkan logika pemrosesan dan tampilan antarmuka:

* **`/config`**: Berisi file konfigurasi global dan inisialisasi koneksi basis data PDO (`database.php`).
* **`/process`**: Berisi seluruh file logika pemrosesan data backend, proses validasi, query manipulasi, dan kalkulasi array transaksi (`insert_transaksi.php`, `update_kasir.php`, `delete_kategori.php`, dsb.).
* **`/public`**: Berisi file antarmuka pengguna (UI) utama halaman panel aplikasi (`index.php`, `tambah_transaksi.php`, `edit_kasir.php`, `login.php`, dsb.).
* **`/public/images`**: Direktori penyimpanan file aset gambar atau foto fisik dari produk inventaris toko.

---

## 🚀 Instruksi Instalasi & Konfigurasi Lokal
Ikuti langkah-langkah berikut untuk menjalankan aplikasi Suka Petshop pada lingkungan server lokal (*localhost*):

1.  **Persiapan Lingkungan:** Pastikan aplikasi **XAMPP** (atau aplikasi web server sejenis) telah terpasang di komputer.
2.  **Pemindahan Berkas:** Ekstrak seluruh folder arsip proyek ini dan letakkan ke dalam direktori root server lokal XAMPP yang berada di:  
    `C:/xampp/htdocs/suka_petshop/`
3.  **Aktivasi Service:** Buka *XAMPP Control Panel* dan aktifkan modul **Apache** serta **MySQL**.
4.  **Konfigurasi Basis Data (phpMyAdmin):**
    * Buka peramban (*browser*) dan akses alamat: `http://localhost/phpmyadmin/`
    * Buat sebuah basis data baru dengan nama: `suka_petshop`
    * Pilih nama basis data tersebut, masuk ke tab **Import**, pilih file struktur SQL database yang tersedia di folder proyek, kemudian klik **Go/Import**.
5.  **Verifikasi Kredensial Database:** Buka berkas `config/database.php` menggunakan teks editor dan pastikan konfigurasi host, nama database, dan user host lokal telah sesuai dengan konfigurasi XAMPP standar (User: `root`, Password: `""`).
6.  **Menjalankan Aplikasi:** Buka peramban dan akses alamat tautan berikut untuk menuju halaman autentikasi:  
    `http://localhost/suka_petshop/public/login.php`

---

## 👥 Tim Pengembang (Kelompok 6)
Proyek ini disusun dan diselesaikan oleh Mahasiswa Program Studi Informatika, Universitas Tanjungpura:
* **Dhyanesa Amirah Kanandhi** (NIM: D1041241011)
* **Dwi Nayla Cintia** (NIM: D1041241017)
* **Feni Dwi Putri** (NIM: D1041241041)
