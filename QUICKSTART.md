# 🚀 Luminous Framework - Quick Start Guide

## Panduan Cepat Setup & Deploy

### 📦 Step 1: Setup Project

```bash
# Install dependencies
composer install

# Copy file environment
cp .env.example .env

# Edit konfigurasi database di .env
nano .env
```

### ⚙️ Step 2: Konfigurasi .env

```env
APP_ENV=production
APP_DEBUG=false

# MySQL
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password

# JWT Secret (WAJIB diganti!)
JWT_SECRET=ganti-dengan-string-random-panjang-dan-aman
```

### 🗄️ Step 3: Setup Database

```bash
# Buat database MySQL terlebih dahulu, lalu jalankan:
php lumi migrate
```

### 🧪 Step 4: Test Locally

```bash
# Make CLI executable
chmod +x lumi

# Start development server
php -S localhost:8000

# Test API
curl http://localhost:8000
```

### 🌐 Step 5: Deploy ke Shared Hosting

#### A. Upload Files via FTP/cPanel
1. Upload semua file ke root directory hosting
2. Pastikan `.htaccess` ikut terupload

#### B. Set Permission
```bash
chmod -R 755 storage/
chmod 644 .env
chmod 644 .htaccess
```

#### C. Configure Database
1. Buat database MySQL di cPanel
2. Update `.env` dengan kredensial database hosting
3. Jalankan migration (via SSH atau script custom)

#### D. Point Domain
Pastikan domain mengarah ke folder root (dimana `index.php` berada)

### 📝 Step 6: Test API Endpoints

#### Register User
```bash
curl -X POST http://yourdomain.com/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Login
```bash
curl -X POST http://yourdomain.com/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

Response akan berisi token JWT.

#### Access Protected Route
```bash
curl http://yourdomain.com/api/user \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

### 🛠️ CLI Commands Cheat Sheet

```bash
# List semua command
php lumi list

# Generate controller
php lumi make:controller ProductController

# Generate model
php lumi make:model Product

# Generate migration
php lumi make:migration create_products_table

# Generate middleware
php lumi make:middleware CheckAdmin

# Run migrations
php lumi migrate
```

### 📂 Struktur File Penting

```
/
├── index.php           # Entry point API
├── .htaccess           # URL rewriting (Apache)
├── .env                # Environment config
├── lumi                # CLI tool
├── /app
│   ├── /Core          # Framework core (jangan diubah)
│   ├── /Controllers   # Taruh controller Anda disini
│   ├── /Models        # Taruh model Anda disini
│   ├── /Middlewares   # Taruh middleware Anda disini
├── /routes
│   └── api.php        # Define routes disini
├── /config
│   └── config.lumi    # Framework config
└── /database
    └── /migrations    # Migration files
```

### 🔐 Security Checklist

- ✅ Ganti `JWT_SECRET` dengan string random yang kuat
- ✅ Set `APP_DEBUG=false` di production
- ✅ Protect `.env` file (sudah di .htaccess)
- ✅ Gunakan HTTPS di production
- ✅ Set proper file permissions (755 untuk folder, 644 untuk file)
- ✅ Backup database secara regular

### 🐛 Troubleshooting

#### Error: "Route not found"
- Pastikan `.htaccess` ada dan mod_rewrite aktif
- Cek konfigurasi Apache/Nginx

#### Error: "Database connection failed"
- Cek kredensial di `.env`
- Pastikan database sudah dibuat
- Pastikan MySQL service running

#### Error: "Composer not found"
- Install Composer: https://getcomposer.org
- Atau upload folder `vendor` hasil composer install lokal

#### Shared Hosting: CLI tidak bisa dijalankan
- Minta akses SSH ke hosting provider
- Atau buat script PHP untuk menjalankan migration via browser (jangan lupa hapus setelah digunakan!)

### 📚 Next Steps

1. Baca [README.md](README.md) untuk dokumentasi lengkap
2. Lihat contoh di `/app/Controllers` dan `/app/Models`
3. Customize routes di `/routes/api.php`
4. Build your API! 🎉

### 💡 Tips

- Gunakan [Postman](https://postman.com) atau [Insomnia](https://insomnia.rest) untuk test API
- Enable error logging di production dengan menulis ke file
- Gunakan Git untuk version control
- Backup database sebelum migration

---

**Selamat coding! ✨**

Butuh bantuan? Buka issue atau baca dokumentasi lengkap di README.md
