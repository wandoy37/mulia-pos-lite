# Laravel 13 + Vue 3 + Inertia + Bootstrap 5.3 Starter Kit

Proyek ini adalah _starter kit_ yang dibangun dari instalasi _fresh_ Laravel 13 tanpa package bawaan. Proyek ini telah dikonfigurasi secara manual untuk menggunakan Vue.js 3, Inertia.js, Bootstrap 5.3 (dengan Popper.js), dan Ziggy untuk pengelolaan _routing_ di frontend.

## 🛠️ Tech Stack

- **Backend:** Laravel 13
- **Frontend:** Vue.js 3 & Inertia.js
- **Styling:** Bootstrap 5.3 & @popperjs/core
- **Routing Utility:** Ziggy (untuk menggunakan _route name_ Laravel di Vue)
- **Bundler:** Vite

## 💻 System Requirements

Proyek ini dikembangkan dan diuji menggunakan **Laragon** dengan spesifikasi _environment_ berikut:

- **PHP:** >= 8.3
- **MySQL:** >= 8.4
- **Node.js:** >= v24
- **Composer:** (Terbaru)
- **Git:** (Terbaru)

## 🚀 Instalasi & Setup

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek ini di _local environment_ Anda.

### 1. Clone Repository

```bash
git clone https://github.com/wandoy37/mulia-pos-lite.git
```

```bash
cd https://github.com/wandoy37/mulia-pos-lite.git
```

### 2. Install Dependencies PHP (Composer)

```bash
composer install
```

### 3. Install Dependencies JavaScript (NPM)

```bash
npm install
```

### 4. Setup Environment File

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Konfigurasi Database

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Jalankan Migrasi Database

```bash
php artisan migrate
```

### 8. Jalankan Server Development

```bash
npm run dev
```

## 💡 Catatan Tambahan (Penggunaan Ziggy)

Fungsi route() telah tersedia secara global berkat Ziggy. Anda tidak perlu melakukan hardcode URL secara manual di frontend. Anda bisa langsung memanggil nama rute Laravel di dalam komponen Vue menggunakan komponen bawaan Inertia:

```bash
<template>
  <Link :href="route('home')">Ke Halaman Home</Link>
</template>
```
