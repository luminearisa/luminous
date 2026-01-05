# 🚀 Luminous Framework - Deployment Guide

## Panduan Deploy ke Shared Hosting

Framework Luminous dirancang untuk mudah di-deploy ke shared hosting seperti Hostinger, Niagahoster, Rumahweb, dll.

---

## 📋 Persiapan

### 1. Pastikan Hosting Support:
- PHP >= 8.1
- MySQL atau SQLite
- mod_rewrite (Apache)
- Composer (optional, bisa install lokal)

### 2. File yang Perlu Disiapkan:
- Semua file project
- `.htaccess` untuk URL rewriting
- `.env` dengan konfigurasi production

---

## 🌐 Deployment ke Hostinger

### Step 1: Upload Files

1. Login ke **Hostinger hPanel**
2. Buka **File Manager**
3. Navigate ke folder `public_html` atau folder domain Anda
4. Upload semua file Luminous ke folder tersebut
5. Pastikan struktur seperti ini:

```
public_html/
├── index.php
├── .htaccess
├── .env
├── lumi
├── composer.json
├── /app
├── /routes
├── /config
├── /database
├── /storage
└── /vendor (jika sudah composer install)
```

### Step 2: Install Dependencies

#### Opsi A: Via SSH (Jika tersedia)
```bash
ssh user@your-server.com
cd public_html
composer install --no-dev --optimize-autoloader
```

#### Opsi B: Upload vendor folder
```bash
# Di local
composer install --no-dev --optimize-autoloader

# Zip folder vendor
zip -r vendor.zip vendor/

# Upload vendor.zip via File Manager
# Extract di server
```

### Step 3: Setup Database

1. Buka **MySQL Databases** di hPanel
2. Create new database
3. Create database user
4. Assign user to database
5. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### Step 4: Configure .env

Edit file `.env` via File Manager atau FTP:

```env
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u123456_dbname
DB_USER=u123456_dbuser
DB_PASS=your_secure_password

JWT_SECRET=your-very-long-random-secret-key-change-this
```

### Step 5: Run Migrations

#### Via SSH:
```bash
cd public_html
php lumi migrate
```

#### Via Custom Script (jika tidak ada SSH):

Buat file `migrate.php` di root:
```php
<?php
require 'vendor/autoload.php';
use App\Console\Commands\Migrate;
use App\Core\Env;

Env::load();
Env::loadConfig();

$migrate = new Migrate();
$migrate->execute([]);

echo "Migration completed!";
```

Akses: `https://yourdomain.com/migrate.php`

⚠️ **PENTING: Hapus file ini setelah migration!**

### Step 6: Set Permissions

```bash
chmod 755 storage/
chmod 755 storage/logs/
chmod 755 storage/cache/
chmod 644 .env
chmod 644 .htaccess
```

### Step 7: Test

Akses: `https://yourdomain.com`

Expected response:
```json
{
  "status": "success",
  "message": "Welcome to Luminous Framework",
  "data": {
    "framework": "Luminous",
    "version": "1.0.0"
  }
}
```

---

## 🔧 Deployment ke cPanel (General)

### Step 1: Login cPanel

Login ke cPanel hosting Anda

### Step 2: File Manager

1. Buka **File Manager**
2. Navigate ke `public_html` atau subdomain folder
3. Upload semua file Luminous

### Step 3: MySQL Database

1. Buka **MySQL Database Wizard**
2. Create database → Create user → Assign privileges
3. Note credentials

### Step 4: Configure

Edit `.env` dengan credentials yang benar

### Step 5: PHP Version

1. Buka **Select PHP Version**
2. Pilih PHP 8.1 atau lebih tinggi
3. Enable extensions:
   - pdo
   - pdo_mysql
   - mbstring
   - json

### Step 6: SSL Certificate

1. Buka **SSL/TLS Status**
2. Enable AutoSSL untuk domain Anda
3. Atau install Let's Encrypt

---

## 🐳 Deployment dengan Docker (VPS)

### Dockerfile

```dockerfile
FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . /var/www/html

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage

EXPOSE 80

CMD ["apache2-foreground"]
```

### docker-compose.yml

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html
    environment:
      - APP_ENV=production
      - DB_HOST=db
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpass
      MYSQL_DATABASE: luminous
      MYSQL_USER: luminous
      MYSQL_PASSWORD: secret
    volumes:
      - dbdata:/var/lib/mysql

volumes:
  dbdata:
```

Deploy:
```bash
docker-compose up -d
docker-compose exec app php lumi migrate
```

---

## ⚡ Deployment ke VPS (Nginx)

### Nginx Configuration

`/etc/nginx/sites-available/luminous`

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/luminous;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }

    location ~ \.(env|lumi|json|lock|md)$ {
        deny all;
    }
}
```

Enable site:
```bash
ln -s /etc/nginx/sites-available/luminous /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 🔒 Production Checklist

### Security
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Use strong `JWT_SECRET` (32+ characters random)
- [ ] Enable HTTPS/SSL
- [ ] Set proper file permissions (644 files, 755 folders)
- [ ] Protect `.env` file (already in `.htaccess`)
- [ ] Disable directory listing
- [ ] Regular security updates

### Performance
- [ ] Enable OPcache in PHP
- [ ] Use `--optimize-autoloader` with Composer
- [ ] Enable GZIP compression
- [ ] Use CDN for static assets
- [ ] Database indexing
- [ ] Implement caching strategy

### Monitoring
- [ ] Setup error logging
- [ ] Monitor disk space
- [ ] Monitor database performance
- [ ] Setup uptime monitoring
- [ ] Regular backups

---

## 🔄 Update/Rollback Strategy

### Update Application

```bash
# Backup current version
cp -r /var/www/html /var/www/html.backup

# Pull new version
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php lumi migrate

# Clear cache if any
```

### Rollback

```bash
# Restore backup
rm -rf /var/www/html
mv /var/www/html.backup /var/www/html

# Restore database from backup
mysql -u user -p database < backup.sql
```

---

## 📊 Monitoring & Logs

### Enable Error Logging

Add to `index.php`:

```php
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/storage/logs/error.log');
```

### Log Viewer (Simple)

Create `logs.php` (protect with auth!):

```php
<?php
// Add authentication here!
$log = file_get_contents(__DIR__ . '/storage/logs/error.log');
echo '<pre>' . htmlspecialchars($log) . '</pre>';
```

---

## 🆘 Troubleshooting

### 500 Internal Server Error
- Check PHP error logs
- Verify `.htaccess` syntax
- Check file permissions
- Verify PHP version >= 8.1

### Database Connection Error
- Verify credentials in `.env`
- Check if database exists
- Verify database host
- Check user privileges

### Routes Not Working
- Enable `mod_rewrite` in Apache
- Check `.htaccess` uploaded correctly
- Verify AllowOverride in Apache config

### Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/html
chmod -R 755 storage/
```

---

## 📞 Support

Jika mengalami kesulitan deployment:
1. Cek error logs di `storage/logs/`
2. Verify PHP version & extensions
3. Test database connection
4. Check `.htaccess` configuration

---

**Happy Deploying! 🎉**
