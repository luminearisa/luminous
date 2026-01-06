# CORS Management - Luminous Framework

## 📋 Overview

Luminous Framework menyediakan sistem **CORS (Cross-Origin Resource Sharing)** yang powerful dan mudah dikonfigurasi untuk mengontrol akses API dari origin yang berbeda.

## ✨ Fitur

- ✅ **Configurable Whitelist** - Kontrol origin mana yang diizinkan
- ✅ **Endpoint-Specific Rules** - Atur CORS per endpoint atau wildcard
- ✅ **Method-Specific** - Tentukan HTTP method yang diizinkan per origin
- ✅ **CLI Management** - Kelola CORS via command line
- ✅ **Allow All Mode** - Mode development untuk allow semua origin
- ✅ **Preflight Handling** - Otomatis handle OPTIONS request
- ✅ **Credentials Support** - Support untuk cookies dan authentication

## 📁 File Structure

```
/config
└── cors.lumi                 # CORS configuration

/app
├── /Core
│   └── CorsConfig.php        # CORS config manager
└── /Middlewares
    └── CorsMiddleware.php    # CORS middleware

/app/Console/Commands
├── CorsAdd.php               # Add origin command
├── CorsRemove.php            # Remove origin command
├── CorsList.php              # List origins command
├── CorsAllowAll.php          # Allow all mode
├── CorsDisallowAll.php       # Disable CORS
└── CorsWhitelist.php         # Enable whitelist mode
```

## ⚙️ Configuration

### File: `config/cors.lumi`

```json
{
  "enabled": true,
  "allow_all": false,
  "allowed_origins": [
    {
      "origin": "http://localhost:3000",
      "methods": ["GET", "POST", "PUT", "PATCH", "DELETE"],
      "endpoints": ["/api/*"]
    }
  ],
  "allowed_headers": [
    "Content-Type",
    "Authorization",
    "X-Requested-With"
  ],
  "exposed_headers": [],
  "supports_credentials": true,
  "max_age": 86400
}
```

### Configuration Options

| Option | Type | Description |
|--------|------|-------------|
| `enabled` | boolean | Enable/disable CORS globally |
| `allow_all` | boolean | Allow all origins (set to `*`) |
| `allowed_origins` | array | Whitelist of allowed origins |
| `allowed_headers` | array | Headers allowed in requests |
| `exposed_headers` | array | Headers exposed to frontend |
| `supports_credentials` | boolean | Allow credentials (cookies) |
| `max_age` | integer | Preflight cache duration (seconds) |

### Origin Configuration

Each origin in whitelist has:

```json
{
  "origin": "https://example.com",
  "methods": ["GET", "POST"],
  "endpoints": ["/api/*", "/public/*"]
}
```

- **origin**: URL origin (must include protocol)
- **methods**: Allowed HTTP methods for this origin
- **endpoints**: Endpoint patterns (support wildcard `*`)

## 🔧 CLI Commands

### 1. Add Origin to Whitelist

```bash
php lumi cors:add https://example.com /api/*
```

**Options:**
- First argument: Origin URL (required)
- Second argument: Endpoint pattern (optional, default: `/api/*`)

**Examples:**
```bash
# Add with default endpoint
php lumi cors:add https://example.com

# Add with specific endpoint
php lumi cors:add https://example.com /api/users/*

# Add with multiple endpoints (comma-separated)
php lumi cors:add https://example.com "/api/*,/public/*"

# Allow all origins from domain
php lumi cors:add "*"
```

### 2. Remove Origin from Whitelist

```bash
php lumi cors:remove https://example.com
```

### 3. List CORS Configuration

```bash
php lumi cors:list
```

**Output:**
```
CORS Configuration
==================

✓ Status: Enabled
ℹ Mode: Whitelist

Whitelisted Origins:

[1] https://example.com
    Methods: GET, POST, PUT, PATCH, DELETE
    Endpoints: /api/*

[2] http://localhost:3000
    Methods: GET, POST
    Endpoints: /api/*, /public/*

Settings:
- Credentials: Enabled
- Max Age: 86400 seconds
- Allowed Headers: Content-Type, Authorization, X-Requested-With
```

### 4. Allow All Origins (Development Mode)

```bash
php lumi cors:allow-all
```

⚠️ **Warning:** This allows requests from ANY origin. Use only in development!

**Effect:**
- Sets `enabled = true`
- Sets `allow_all = true`
- Ignores whitelist
- Sets `Access-Control-Allow-Origin: *`

### 5. Disable All CORS

```bash
php lumi cors:disallow-all
```

**Effect:**
- Sets `enabled = false`
- Blocks all cross-origin requests
- No CORS headers sent

### 6. Enable Whitelist Mode

```bash
php lumi cors:whitelist
```

**Effect:**
- Sets `enabled = true`
- Sets `allow_all = false`
- Uses whitelist for origin validation

## 🔄 How It Works

### Request Flow

```
1. Client sends request from origin (e.g., https://example.com)
   │
   ▼
2. Request hits index.php
   │
   ▼
3. CorsMiddleware executes
   │
   ├─► If CORS disabled → Continue without CORS headers
   │
   ├─► If allow_all = true → Set Access-Control-Allow-Origin: *
   │
   └─► If whitelist mode:
       │
       ├─► Check origin in whitelist
       ├─► Check endpoint matches pattern
       ├─► Check method is allowed
       │
       ├─► If valid → Set CORS headers with origin
       └─► If invalid → No CORS headers (browser blocks)
```

### Preflight Request (OPTIONS)

When browser sends preflight OPTIONS request:

```
1. Browser sends OPTIONS request
   │
   ▼
2. CorsMiddleware detects OPTIONS method
   │
   ▼
3. Validate origin, endpoint, method
   │
   ├─► Valid → Return 204 with CORS headers
   └─► Invalid → Return 403
```

### Endpoint Pattern Matching

Patterns support wildcard `*`:

```
Pattern: /api/*
Matches: /api/users, /api/posts, /api/users/123

Pattern: /api/users/*
Matches: /api/users/123, /api/users/profile

Pattern: /public
Matches: /public (exact match)
```

## 📝 Usage Examples

### Example 1: Development Setup

Allow all origins for local development:

```bash
php lumi cors:allow-all
```

### Example 2: Production Setup

Enable whitelist and add your frontend domain:

```bash
php lumi cors:whitelist
php lumi cors:add https://myapp.com /api/*
php lumi cors:add https://admin.myapp.com /api/admin/*
```

### Example 3: Multiple Environments

Add multiple origins for different environments:

```bash
php lumi cors:add https://staging.myapp.com /api/*
php lumi cors:add https://production.myapp.com /api/*
php lumi cors:add http://localhost:3000 /api/*
```

### Example 4: Remove Origin

```bash
php lumi cors:remove http://localhost:3000
```

### Example 5: Check Configuration

```bash
php lumi cors:list
```

## 🔐 Security Best Practices

### ✅ DO:

1. **Use Whitelist in Production**
   ```bash
   php lumi cors:whitelist
   php lumi cors:add https://yourdomain.com /api/*
   ```

2. **Be Specific with Endpoints**
   ```bash
   # Good: Specific endpoint
   php lumi cors:add https://example.com /api/public/*
   
   # Avoid: Too broad
   php lumi cors:add https://example.com /*
   ```

3. **Limit Methods**
   Edit `cors.lumi` to restrict methods:
   ```json
   {
     "origin": "https://example.com",
     "methods": ["GET"],  // Read-only
     "endpoints": ["/api/data/*"]
   }
   ```

4. **Use HTTPS**
   ```bash
   php lumi cors:add https://example.com  # ✓ Secure
   ```

### ❌ DON'T:

1. **Don't Use Allow All in Production**
   ```bash
   # NEVER do this in production:
   php lumi cors:allow-all
   ```

2. **Don't Allow All Origins Wildcard**
   ```bash
   # Dangerous:
   php lumi cors:add "*"
   ```

3. **Don't Use HTTP in Production**
   ```bash
   # Not secure:
   php lumi cors:add http://example.com
   ```

## 🧪 Testing CORS

### Test with cURL

```bash
# Test preflight
curl -X OPTIONS http://localhost:8000/api/users \
  -H "Origin: https://example.com" \
  -H "Access-Control-Request-Method: POST" \
  -v

# Test actual request
curl -X GET http://localhost:8000/api/users \
  -H "Origin: https://example.com" \
  -v
```

### Test with JavaScript

```javascript
// In browser console from https://example.com
fetch('http://localhost:8000/api/users', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('CORS Error:', error));
```

## 🔍 Troubleshooting

### Issue: CORS error in browser

**Check:**
```bash
php lumi cors:list
```

**Solutions:**
1. Add your origin to whitelist
2. Check endpoint pattern matches
3. Verify CORS is enabled

### Issue: Origin not being added

**Possible causes:**
- Origin already exists (remove first)
- Invalid origin format (must include protocol)

**Fix:**
```bash
# Remove and re-add
php lumi cors:remove https://example.com
php lumi cors:add https://example.com /api/*
```

### Issue: Preflight request failing

**Check:**
1. OPTIONS method is included in allowed methods
2. Origin is in whitelist
3. Endpoint pattern matches

### Issue: Credentials not working

**Solution:**
Edit `config/cors.lumi`:
```json
{
  "supports_credentials": true
}
```

**Frontend:**
```javascript
fetch('http://api.example.com/data', {
  credentials: 'include'  // Important!
})
```

## 📊 Common Scenarios

### Scenario 1: Single Page Application (SPA)

```bash
# Frontend: http://localhost:3000
# Backend: http://localhost:8000

php lumi cors:add http://localhost:3000 /api/*
```

### Scenario 2: Multiple Frontend Apps

```bash
# Main app
php lumi cors:add https://app.mysite.com /api/*

# Admin panel
php lumi cors:add https://admin.mysite.com /api/admin/*

# Mobile app webview
php lumi cors:add https://mobile.mysite.com /api/mobile/*
```

### Scenario 3: Public API + Private API

```json
{
  "allowed_origins": [
    {
      "origin": "*",
      "methods": ["GET"],
      "endpoints": ["/api/public/*"]
    },
    {
      "origin": "https://dashboard.myapp.com",
      "methods": ["GET", "POST", "PUT", "DELETE"],
      "endpoints": ["/api/admin/*"]
    }
  ]
}
```

## 🎯 Integration with Routes

CORS middleware is automatically applied globally. Just ensure it's registered:

```php
// routes/api.php
use App\Middlewares\CorsMiddleware;

// Already applied globally in index.php
$router->group(['middleware' => CorsMiddleware::class], function ($router) {
    // Your routes here
});
```

Or apply to specific routes:

```php
$router->get('/api/data', 'DataController@index', [CorsMiddleware::class]);
```

## 📚 Additional Resources

- [MDN: CORS](https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS)
- [W3C CORS Specification](https://www.w3.org/TR/cors/)

---

**Luminous Framework** - CORS made simple! 🌟
