# Luminous Framework - Architecture & Flow

## 📐 Arsitektur Framework

Luminous menggunakan arsitektur **MVC (Model-View-Controller)** yang telah disesuaikan untuk RESTful API development.

```
┌─────────────────────────────────────────────────────────┐
│                     HTTP REQUEST                        │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                   index.php                             │
│  • Load autoloader                                      │
│  • Initialize environment (.env)                        │
│  • Load configuration (config.lumi)                     │
│  • Setup error handling                                 │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Create Request & Response                  │
│  • Request: Parse HTTP request                          │
│  • Response: Prepare response handler                   │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                  Load Routes                            │
│  • routes/api.php                                       │
│  • Define all API endpoints                             │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Router::dispatch()                         │
│  • Match request URI & method                           │
│  • Extract route parameters                             │
│  • Execute middlewares                                  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ├──► Middleware 1 (e.g., CORS)
                     │    └──► Continue or Stop?
                     │
                     ├──► Middleware 2 (e.g., Auth)
                     │    └──► Verify JWT token
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                   Controller                            │
│  • Receive Request & Response                           │
│  • Validate input data                                  │
│  • Process business logic                               │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                     Model                               │
│  • Interact with Database                               │
│  • CRUD operations                                      │
│  • Data transformation                                  │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                   Database                              │
│  • PDO Connection (MySQL/SQLite)                        │
│  • Execute queries                                      │
│  • Return results                                       │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Response::json()                           │
│  • Format data as JSON                                  │
│  • Set HTTP status code                                 │
│  • Send headers                                         │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                 JSON RESPONSE                           │
│  { status, message, data }                              │
└─────────────────────────────────────────────────────────┘
```

---

## 🔄 Request Lifecycle

### 1. **Entry Point** (`index.php`)
```php
// Load dependencies
require 'vendor/autoload.php';

// Initialize environment
Env::load('.env');
Env::loadConfig('config/config.lumi');

// Create instances
$request = new Request();
$response = new Response();
$router = new Router();

// Load routes
require 'routes/api.php';

// Dispatch
$router->dispatch($request, $response);
```

### 2. **Routing** (`routes/api.php`)
```php
// Define routes
$router->get('/users', 'UserController@index');
$router->post('/users', 'UserController@store', [AuthMiddleware::class]);

// Route groups
$router->group(['prefix' => '/api', 'middleware' => AuthMiddleware::class], function($router) {
    $router->get('/profile', 'UserController@profile');
});
```

### 3. **Router Dispatch**
- Match request URI dengan pattern route
- Extract parameter dari URI (e.g., `/users/{id}`)
- Execute middleware chain
- Call controller method

### 4. **Middleware Execution**
```php
// AuthMiddleware
public function handle(Request $request, Response $response): bool
{
    $token = $request->bearerToken();
    $payload = JWT::verify($token);
    
    if (!$payload) {
        $response->unauthorized();
        return false; // Stop processing
    }
    
    $request->user = $payload;
    return true; // Continue
}
```

### 5. **Controller Processing**
```php
public function store(Request $request, Response $response): void
{
    // Get input
    $data = $request->all();
    
    // Validate
    $errors = $this->validate($data, [
        'name' => 'required|min:3'
    ]);
    
    if (!empty($errors)) {
        $response->validationError($errors);
        return;
    }
    
    // Process
    User::create($data);
    
    // Response
    $response->created($data);
}
```

### 6. **Model & Database**
```php
// Model operation
User::create(['name' => 'John', 'email' => 'john@example.com']);

// Internally calls Database class
Database::insert('users', ['name' => 'John', 'email' => 'john@example.com']);

// Which executes PDO prepared statement
$stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
$stmt->execute(['John', 'john@example.com']);
```

### 7. **Response**
```php
$response->success([
    'id' => 1,
    'name' => 'John'
], 'User created');

// Outputs:
{
    "status": "success",
    "message": "User created",
    "data": {
        "id": 1,
        "name": "John"
    }
}
```

---

## 🛠️ CLI Command Flow

### CLI Entry Point (`lumi`)
```
php lumi make:controller UserController
    │
    ▼
┌────────────────────────────────┐
│   Console Kernel               │
│   • Parse command name         │
│   • Parse arguments            │
│   • Map to command class       │
└──────────┬─────────────────────┘
           │
           ▼
┌────────────────────────────────┐
│   Command Class                │
│   • MakeController             │
│   • execute($arguments)        │
│   • Generate file content      │
│   • Create file                │
└──────────┬─────────────────────┘
           │
           ▼
┌────────────────────────────────┐
│   File Created                 │
│   app/Controllers/             │
│   UserController.php           │
└────────────────────────────────┘
```

### Available Commands

| Command | Purpose | Example |
|---------|---------|---------|
| `make:controller` | Create controller | `php lumi make:controller ProductController` |
| `make:model` | Create model | `php lumi make:model Product` |
| `make:migration` | Create migration | `php lumi make:migration create_products_table` |
| `make:middleware` | Create middleware | `php lumi make:middleware CheckRole` |
| `migrate` | Run migrations | `php lumi migrate` |
| `list` | Show all commands | `php lumi list` |

---

## 🔐 Authentication Flow

### Registration
```
POST /auth/register
    │
    ▼
Validate input
    │
    ▼
Check email exists?
    │
    ├─ Yes → Error 409
    │
    └─ No
       │
       ▼
   Hash password
       │
       ▼
   Insert to database
       │
       ▼
   Generate JWT token
       │
       ▼
   Return token + user data
```

### Login
```
POST /auth/login
    │
    ▼
Validate input
    │
    ▼
Find user by email
    │
    ├─ Not found → Error 401
    │
    └─ Found
       │
       ▼
   Verify password
       │
       ├─ Invalid → Error 401
       │
       └─ Valid
          │
          ▼
      Generate JWT token
          │
          ▼
      Return token + user data
```

### Protected Route Access
```
GET /api/profile
Authorization: Bearer {token}
    │
    ▼
AuthMiddleware
    │
    ▼
Extract token from header
    │
    ▼
Verify & decode JWT
    │
    ├─ Invalid → Error 401
    │
    └─ Valid
       │
       ▼
   Attach user to request
       │
       ▼
   Continue to controller
       │
       ▼
   Controller access user data
       │
       ▼
   Return response
```

---

## 💾 Database Operations

### Connection (Lazy Loading)
```php
// First call
Database::connection();
    │
    ▼
Check if connection exists?
    │
    ├─ Yes → Return existing
    │
    └─ No
       │
       ▼
   Read DB_CONNECTION from .env
       │
       ├─ mysql → Create MySQL PDO
       │
       └─ sqlite → Create SQLite PDO
           │
           ▼
       Set PDO attributes
           │
           ▼
       Return connection
```

### Query Execution
```php
Database::insert('users', ['name' => 'John']);
    │
    ▼
Build SQL query
    │
    ▼
Prepare statement
    │
    ▼
Bind parameters
    │
    ▼
Execute
    │
    ▼
Return result
```

---

## ⚙️ Configuration System

### Environment Variables (.env)
```env
DB_HOST=localhost
DB_NAME=mydb
JWT_SECRET=secret123
```

### Framework Config (config.lumi)
```json
{
  "jwt": {
    "secret": "env:JWT_SECRET",
    "expire": 3600
  }
}
```

### Usage in Code
```php
// Direct env access
$host = Env::get('DB_HOST');

// Config with env reference
$secret = Env::config('jwt.secret'); // Returns value from JWT_SECRET env
$expire = Env::config('jwt.expire'); // Returns 3600
```

### Config Priority
```
1. config.lumi (with env: prefix resolution)
2. .env file
3. $_ENV superglobal
4. $_SERVER superglobal
5. Default value
```

---

## 🔒 Security Features

### 1. **Password Hashing**
```php
// Hash
$hash = Hash::make('password123');
// Uses: password_hash($password, PASSWORD_BCRYPT)

// Verify
$valid = Hash::verify('password123', $hash);
// Uses: password_verify($password, $hash)
```

### 2. **JWT Token**
```php
// Generate
$token = JWT::generate(['user_id' => 1]);
// Includes: iat (issued at), exp (expiration), data

// Verify
$payload = JWT::verify($token);
// Returns data if valid, null if invalid/expired
```

### 3. **SQL Injection Prevention**
```php
// Always uses prepared statements
Database::query("SELECT * FROM users WHERE id = ?", [$id]);
// PDO automatically escapes parameters
```

### 4. **Input Validation**
```php
$errors = $this->validate($data, [
    'email' => 'required|email',
    'password' => 'required|min:8'
]);
```

### 5. **CORS Protection**
```php
// CorsMiddleware handles:
- Access-Control-Allow-Origin
- Access-Control-Allow-Methods
- Access-Control-Allow-Headers
```

---

## 📁 File Structure Explained

```
luminous/
├── index.php              # Entry point for web requests
├── lumi                   # Entry point for CLI commands
│
├── app/                   # Application code
│   ├── Core/             # Framework core (don't modify)
│   │   ├── Router.php    # Route matching & dispatching
│   │   ├── Request.php   # HTTP request handling
│   │   ├── Response.php  # HTTP response handling
│   │   ├── Controller.php # Base controller
│   │   ├── Database.php  # Database abstraction
│   │   ├── JWT.php       # JWT helper
│   │   └── Env.php       # Environment & config loader
│   │
│   ├── Controllers/      # Your controllers here
│   ├── Models/           # Your models here
│   ├── Middlewares/      # Your middlewares here
│   ├── Helpers/          # Helper functions
│   └── Console/          # CLI commands
│       ├── Kernel.php    # CLI kernel
│       └── Commands/     # Command classes
│
├── routes/
│   └── api.php           # Route definitions
│
├── config/
│   └── config.lumi       # Framework configuration (JSON)
│
├── database/
│   └── migrations/       # Database migrations
│
├── storage/
│   ├── logs/            # Log files
│   └── cache/           # Cache files
│
├── vendor/              # Composer dependencies
│
├── .env                 # Environment variables (credentials)
├── .htaccess            # Apache URL rewriting
├── composer.json        # PHP dependencies
└── README.md            # Documentation
```

---

## 🎯 Design Principles

### 1. **Simplicity**
- Native PHP, no complex abstractions
- Easy to understand and modify
- Minimal dependencies

### 2. **Separation of Concerns**
- Clear MVC structure
- Each class has single responsibility
- Middleware for cross-cutting concerns

### 3. **Flexibility**
- Easy to extend
- PSR-4 autoloading
- Dependency injection ready

### 4. **Production Ready**
- Error handling
- Security features
- Database abstraction
- Configuration management

### 5. **Shared Hosting Compatible**
- No special server requirements
- .htaccess for URL rewriting
- Works on Apache/Nginx
- No CLI requirement (optional)

---

## 🚀 Best Practices

### Controller
```php
class UserController extends Controller
{
    // Keep controllers thin
    // Delegate business logic to services
    // Return JSON responses only
}
```

### Model
```php
class User
{
    // Static methods for database operations
    // No business logic
    // Data access layer only
}
```

### Routing
```php
// Group related routes
$router->group(['prefix' => '/api'], function($router) {
    // All routes here will have /api prefix
});

// Use middleware for authentication
$router->post('/users', 'UserController@store', [AuthMiddleware::class]);
```

### Configuration
```php
// Use .env for secrets
JWT_SECRET=very-secret-key

// Use config.lumi for application config
{
  "jwt": {
    "secret": "env:JWT_SECRET",
    "expire": 3600
  }
}
```

---

**Luminous Framework** - Simple, powerful, production-ready! ✨
