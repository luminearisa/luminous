# Luminous Framework

<div align="center">

**Lightweight PHP Framework for RESTful APIs**

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)
![License](https://img.shields.io/badge/license-MIT-green)

</div>

## 🌟 Features

- ✅ **PHP Native** - No heavy framework dependencies
- ✅ **MVC Architecture** - Clean separation of concerns
- ✅ **RESTful API** - Built for modern API development
- ✅ **JWT Authentication** - Secure token-based auth
- ✅ **Database Support** - MySQL & SQLite out of the box
- ✅ **CLI Tool** - Powerful `lumi` command-line interface
- ✅ **Shared Hosting Ready** - Deploy to Hostinger, cPanel easily
- ✅ **Clean Configuration** - `.lumi` config system + `.env`

## 📋 Requirements

- PHP >= 8.1
- Composer
- MySQL or SQLite

## 🚀 Quick Start

### 1. Installation

```bash
# Clone or download the framework
cd luminous

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Configure your .env file
nano .env
```

### 2. Configuration

Edit `.env` file:

```env
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database
DB_USER=your_username
DB_PASS=your_password

JWT_SECRET=your-super-secret-key-change-this
```

### 3. Make CLI Executable

```bash
chmod +x lumi
```

### 4. Run Development Server

```bash
php -S localhost:8000
```

Visit: http://localhost:8000

## 🛠️ CLI Commands

Luminous provides a powerful CLI tool called `lumi`:

```bash
# List all commands
php lumi list

# Create a controller
php lumi make:controller UserController

# Create a model
php lumi make:model User

# Create a migration
php lumi make:migration create_users_table

# Create a middleware
php lumi make:middleware CheckRole

# Run migrations
php lumi migrate
```

## 📁 Directory Structure

```
/
├── index.php                 # Entry point (root)
├── lumi                      # CLI tool
├── composer.json             # Dependencies
├── .env                      # Environment config
├── /app
│   ├── /Core                 # Framework core
│   │   ├── Router.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── JWT.php
│   │   └── Env.php
│   ├── /Controllers          # Your controllers
│   ├── /Models               # Your models
│   ├── /Middlewares          # Middleware classes
│   ├── /Helpers              # Helper classes
│   └── /Console              # CLI commands
├── /routes
│   └── api.php               # API routes
├── /config
│   └── config.lumi           # Framework config
├── /database
│   └── /migrations           # Database migrations
└── /storage
    ├── /logs                 # Log files
    └── /cache                # Cache files
```

## 🔧 Configuration System

Luminous uses two configuration files:

### `.env` - Environment Variables
Used for credentials and environment-specific values:
```env
DB_HOST=localhost
DB_NAME=mydb
JWT_SECRET=secret
```

### `config.lumi` - Framework Configuration
Used for framework settings (JSON format):
```json
{
  "jwt": {
    "secret": "env:JWT_SECRET",
    "algo": "HS256",
    "expire": 3600
  }
}
```

Values prefixed with `env:` reference `.env` variables.

## 🛣️ Routing

Define routes in `routes/api.php`:

```php
// Simple route
$router->get('/users', 'UserController@index');

// Route with parameter
$router->get('/users/{id}', 'UserController@show');

// Route with middleware
$router->post('/posts', 'PostController@store', [AuthMiddleware::class]);

// Route group
$router->group(['prefix' => '/api', 'middleware' => AuthMiddleware::class], function ($router) {
    $router->get('/profile', 'UserController@profile');
    $router->put('/profile', 'UserController@update');
});
```

## 🎮 Controllers

Create controller using CLI or manually:

```php
<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class UserController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $response->success(['users' => []]);
    }

    public function show(Request $request, Response $response, array $params): void
    {
        $id = $params['id'];
        $response->success(['id' => $id, 'name' => 'John Doe']);
    }
}
```

## 💾 Models & Database

Create model using CLI:

```bash
php lumi make:model User
```

Use the model:

```php
use App\Models\User;

// Get all users
$users = User::all();

// Find by ID
$user = User::find(1);

// Find by condition
$user = User::firstWhere('email', 'user@example.com');

// Create
User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Update
User::update(1, ['name' => 'Jane Doe']);

// Delete
User::delete(1);
```

## 🔒 Authentication

Luminous includes JWT authentication:

### Generate Token

```php
use App\Core\JWT;

$token = JWT::generate([
    'user_id' => 1,
    'email' => 'user@example.com'
]);
```

### Verify Token

```php
$payload = JWT::verify($token);
if ($payload) {
    // Token is valid
}
```

### Protected Routes

```php
use App\Middlewares\AuthMiddleware;

$router->get('/profile', 'UserController@profile', [AuthMiddleware::class]);
```

## 📦 Database Migrations

Create migration:

```bash
php lumi make:migration create_users_table
```

Edit migration file in `database/migrations/`:

```php
public function up(): void
{
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ";
    Database::query($sql);
}
```

Run migrations:

```bash
php lumi migrate
```

## 🌐 Deploying to Shared Hosting

### 1. Upload Files

Upload all files to your hosting via FTP/cPanel File Manager.

### 2. Set Document Root

Point your domain to the root directory (where `index.php` is located).

### 3. Create .htaccess

Create `.htaccess` in root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### 4. Set Permissions

```bash
chmod -R 755 storage/
chmod 644 .env
```

### 5. Configure .env

Update database credentials in `.env` for your hosting environment.

## 📝 API Response Format

All API responses follow this format:

### Success Response
```json
{
  "status": "success",
  "message": "Operation successful",
  "data": {
    "id": 1,
    "name": "Item"
  }
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Validation failed",
  "errors": {
    "email": ["Email is required"]
  }
}
```

## 🔐 Security Features

- Password hashing with `password_hash()`
- JWT token authentication
- Request validation
- SQL injection prevention (PDO prepared statements)
- CORS middleware

## 📚 Helpers

### Hash Helper

```php
use App\Helpers\Hash;

$hashed = Hash::make('password123');
$verified = Hash::verify('password123', $hashed);
```

### String Helper

```php
use App\Helpers\Str;

$random = Str::random(16);
$slug = Str::slug('Hello World'); // hello-world
```

## 🧪 Example Application Flow

1. Request hits `index.php`
2. Environment & config loaded
3. Router matches the request to a route
4. Middleware executed (if any)
5. Controller method called
6. Response sent as JSON

## 📖 Documentation

For more detailed documentation, please visit the `/docs` folder or check individual class files.

## 🤝 Contributing

Contributions are welcome! Feel free to submit pull requests or open issues.

## 📄 License

This framework is open-sourced software licensed under the MIT license.

## 🙏 Credits

Built with ❤️ using:
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
- [firebase/php-jwt](https://github.com/firebase/php-jwt)

---

**Luminous Framework** - Build APIs the simple way! ✨
