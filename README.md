# Laravel Approval

![Laravel Approval](https://img.shields.io/badge/Laravel-11-red.svg)

Laravel Approval adalah sistem berbasis Laravel yang digunakan untuk proses persetujuan atau approval dalam suatu workflow. Aplikasi ini memungkinkan pengguna untuk mengajukan permohonan dan mendapatkan persetujuan berdasarkan aturan yang telah ditentukan.

## 📌 Fitur Utama
- ✅ Manajemen pengguna dan peran (role-based access control)
- ✅ Pembuatan dan pengelolaan permohonan approval
- ✅ Proses persetujuan dengan berbagai status (pending, approved, rejected)

## 🚀 Instalasi
Ikuti langkah-langkah berikut untuk menginstall dan menjalankan Laravel Approval di lingkungan lokal.

### 1. Clone Repository
```sh
git clone https://github.com/RidwanChip/FMS.git
cd FMS
```

### 2. Install Dependensi
Pastikan Composer telah terinstall di sistem, lalu jalankan:
```sh
composer install
```

### 3. Konfigurasi Environment
Buat file `.env` dengan menyalin `.env.example`:
```sh
cp .env.example .env
```
Edit file `.env` dan sesuaikan dengan konfigurasi database:
```
APP_URL=http://127.0.0.1:8000 (Sesuaikan IP Local dan Port)

--Sesuaikan Konfigurasi Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Key & Jalankan Migration
```sh
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### 5. Jalankan Server
```sh
php artisan serve
```
Aplikasi akan berjalan di `http://127.0.0.1:8000`

## 📖 Penggunaan

1. **Login**
   - Login sebagai admin sesuai dengan yang tertera pada Seeder.
   
2. **Buat Permohonan Approval**
   - Masuk ApprovalFlow/ Approval Request Menu.

3. **Proses Approval**
   - Pilih user untuk melakukan pengajuan (dapat lebih dari satu).

## 🔧 Teknologi yang Digunakan
- 🖥️ Laravel 11
- 🛢️ MySQL
- ⚡ Livewire

## 🤝 Kontribusi
Kontribusi sangat diterima! Jika ingin berkontribusi, silakan fork repository ini dan buat pull request.

## 📜 Lisensi
Proyek ini menggunakan lisensi [MIT](LICENSE).


