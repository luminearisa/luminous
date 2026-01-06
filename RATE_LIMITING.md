# Rate Limiting - Luminous Framework

## 📋 Overview

Luminous Framework menyediakan **Rate Limiting** sederhana dan lightweight untuk melindungi API dari abuse dan overload. Sistem ini menggunakan file-based storage tanpa dependency tambahan.

## ✨ Fitur

- ✅ **Simple & Lightweight** - File-based, no Redis/Memcached needed
- ✅ **Per-IP Limiting** - Track requests per IP address
- ✅ **Configurable** - Easy CLI configuration
- ✅ **Global Rate Limit** - Applies to all endpoints
- ✅ **Standard Headers** - Returns proper HTTP 429 and headers
- ✅ **Auto Cleanup** - Old rate limit files are cleaned up automatically

## 📁 File Structure

```
/config
└── rate.lumi                    # Rate limit configuration

/app/Middlewares
└── RateLimitMiddleware.php      # Rate limit middleware

/app/Console/Commands
└── RateLimitSet.php             # CLI command to set limit

/storage/cache
└── rate_limit_*.json            # Per-IP rate data (auto-generated)
```

## ⚙️ Configuration

### File: `config/rate.lumi`

```json
{
  "enabled": true,
  "limit_per_minute": 60
}
```

### Configuration Options

| Option | Type | Description |
|--------|------|-------------|
| `enabled` | boolean | Enable/disable rate limiting |
| `limit_per_minute` | integer | Max requests per minute per IP |

**Note:** When `enabled = false`, no rate limiting is applied.

## 🔧 CLI Commands

### Set Rate Limit

```bash
# Set limit to 60 requests per minute
php lumi limit:set 60

# Set limit to 600 requests per minute
php lumi limit:set 600

# Disable rate limiting (unlimited)
php lumi limit:set unlimited
```

**Examples:**

```bash
# Development: Allow more requests
php lumi limit:set 600

# Production: Strict limit
php lumi limit:set 60

# Testing: Disable completely
php lumi limit:set unlimited
```

## 🔄 How It Works

### Request Flow

```
1. Client sends request from IP (e.g., 192.168.1.100)
   │
   ▼
2. Request hits index.php
   │
   ▼
3. RateLimitMiddleware executes
   │
   ├─► If rate limiting disabled → Continue
   │
   └─► If enabled:
       │
       ├─► Load rate data for IP from cache file
       │
       ├─► Check if within 1-minute window
       │
       ├─► If limit not exceeded:
       │   ├─► Increment counter
       │   ├─► Add rate limit headers
       │   └─► Continue to route
       │
       └─► If limit exceeded:
           ├─► Return HTTP 429
           ├─► Add Retry-After header
           └─► Block request
```

### Storage Mechanism

For each IP address, a cache file is created:

**File:** `storage/cache/rate_limit_192_168_1_100.json`

**Content:**
```json
{
  "count": 45,
  "reset_time": 1704556800
}
```

- **count**: Number of requests in current window
- **reset_time**: Unix timestamp when window resets

### Window Reset

The rate limit window is **1 minute (60 seconds)** from the first request.

Example:
```
First request:  10:00:00 → Window until 10:01:00
Request #45:    10:00:50 → Still in window
Request #61:    10:00:59 → Blocked (limit 60)
Request #1:     10:01:01 → New window starts
```

## 📤 Response Format

### When Limit Not Exceeded

Request continues normally with headers:

```
HTTP/1.1 200 OK
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 15
X-RateLimit-Reset: 1704556800
```

### When Limit Exceeded

```
HTTP/1.1 429 Too Many Requests
Retry-After: 45
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1704556800
Content-Type: application/json

{
  "status": "error",
  "message": "Too many requests. Please try again later.",
  "retry_after": 45
}
```

**Headers Explained:**
- `X-RateLimit-Limit`: Max requests allowed per window
- `X-RateLimit-Remaining`: Requests remaining in window
- `X-RateLimit-Reset`: Unix timestamp when window resets
- `Retry-After`: Seconds until client can retry

## 🎯 Usage Examples

### Example 1: Basic Setup

```bash
# Set reasonable limit for production API
php lumi limit:set 60
```

### Example 2: High Traffic API

```bash
# Allow more requests for high-traffic scenarios
php lumi limit:set 600
```

### Example 3: Development Mode

```bash
# Disable rate limiting during development
php lumi limit:set unlimited
```

### Example 4: Check Current Settings

View current configuration:

```bash
cat config/rate.lumi
```

Output:
```json
{
  "enabled": true,
  "limit_per_minute": 60
}
```

## 🔐 Security Considerations

### ✅ Good Practices

1. **Use Reasonable Limits**
   ```bash
   # Good for most APIs
   php lumi limit:set 60
   
   # For high-traffic public APIs
   php lumi limit:set 300
   ```

2. **Enable in Production**
   ```bash
   # Always enable for production
   php lumi limit:set 60
   ```

3. **Monitor Rate Limit Files**
   ```bash
   # Check cache directory size
   du -sh storage/cache/
   
   # Count rate limit files
   ls storage/cache/rate_limit_*.json | wc -l
   ```

### ❌ Common Mistakes

1. **Don't Set Too Low**
   ```bash
   # Too restrictive - users will be blocked frequently
   php lumi limit:set 10
   ```

2. **Don't Disable in Production**
   ```bash
   # Never do this in production:
   php lumi limit:set unlimited
   ```

3. **Don't Forget to Enable After Testing**
   ```bash
   # After testing, re-enable:
   php lumi limit:set 60
   ```

## 🧹 Maintenance

### Automatic Cleanup

The middleware includes automatic cleanup of expired rate limit files. Files older than 5 minutes after expiry are automatically deleted.

### Manual Cleanup

If needed, you can manually clean up old files:

```bash
# Remove all rate limit cache files
rm storage/cache/rate_limit_*.json
```

### Check Storage Usage

```bash
# View rate limit files
ls -lh storage/cache/rate_limit_*.json

# Count active rate limits
ls storage/cache/rate_limit_*.json 2>/dev/null | wc -l
```

## 🧪 Testing Rate Limits

### Test with cURL

```bash
# Make multiple requests quickly
for i in {1..65}; do
  curl -s http://localhost:8000/api/users -o /dev/null -w "Request $i: %{http_code}\n"
  sleep 0.5
done
```

Expected output:
```
Request 1: 200
Request 2: 200
...
Request 60: 200
Request 61: 429
Request 62: 429
```

### Test with Apache Bench

```bash
# Send 100 requests with 10 concurrent
ab -n 100 -c 10 http://localhost:8000/api/users
```

### Check Headers

```bash
curl -v http://localhost:8000/api/users 2>&1 | grep -i ratelimit
```

Output:
```
< X-RateLimit-Limit: 60
< X-RateLimit-Remaining: 59
< X-RateLimit-Reset: 1704556800
```

## 📊 Common Scenarios

### Scenario 1: Standard Web API

```bash
# 60 requests/minute is reasonable for most users
php lumi limit:set 60
```

**Calculation:**
- 1 request per second average
- Allows for bursts

### Scenario 2: Public Data API

```bash
# Higher limit for public APIs
php lumi limit:set 300
```

**Calculation:**
- 5 requests per second average
- Good for data-heavy applications

### Scenario 3: Internal API

```bash
# Very high limit for internal use
php lumi limit:set 1000
```

**Calculation:**
- ~16 requests per second
- Suitable for internal microservices

### Scenario 4: Webhook Endpoints

```bash
# Moderate limit for webhooks
php lumi limit:set 120
```

**Calculation:**
- 2 requests per second average
- Prevents webhook abuse

## 🚨 Troubleshooting

### Issue: Getting 429 Too Quickly

**Check current limit:**
```bash
cat config/rate.lumi
```

**Solution:**
```bash
# Increase limit
php lumi limit:set 120
```

### Issue: Rate Limit Not Working

**Check if enabled:**
```bash
cat config/rate.lumi
```

**Solution:**
```bash
# Ensure it's enabled
php lumi limit:set 60
```

### Issue: Storage Filling Up

**Check cache size:**
```bash
du -sh storage/cache/
```

**Solution:**
```bash
# Clean old files
find storage/cache/ -name "rate_limit_*.json" -mtime +1 -delete
```

### Issue: Behind Proxy/Load Balancer

If your API is behind a proxy (Cloudflare, nginx, etc.), the middleware checks these headers in order:
1. `X-Forwarded-For`
2. `X-Real-IP`
3. `REMOTE_ADDR`

Ensure your proxy forwards the real client IP.

## 🎯 Integration

Rate limiting is automatically applied globally via middleware. To enable it in your API:

### Option 1: Apply Globally (Recommended)

In [routes/api.php](routes/api.php):

```php
use App\Middlewares\RateLimitMiddleware;

$router->group(['middleware' => RateLimitMiddleware::class], function ($router) {
    // All routes here are rate-limited
    $router->get('/api/users', 'UserController@index');
    $router->post('/api/posts', 'PostController@store');
});
```

### Option 2: Apply to Specific Routes

```php
use App\Middlewares\RateLimitMiddleware;

// Rate limited
$router->get('/api/data', 'DataController@index', [RateLimitMiddleware::class]);

// Not rate limited
$router->get('/health', 'HealthController@check');
```

## 📈 Recommended Limits

| Use Case | Limit | Reason |
|----------|-------|--------|
| Personal API | 60/min | 1 req/sec average |
| Public API | 300/min | 5 req/sec average |
| Internal API | 1000/min | 16 req/sec average |
| Webhook | 120/min | 2 req/sec average |
| Development | unlimited | No restrictions |

## 🔍 Monitoring

To monitor rate limiting effectiveness:

```bash
# Count active rate limit files
echo "Active IPs: $(ls storage/cache/rate_limit_*.json 2>/dev/null | wc -l)"

# Check largest rate limit file
ls -lhS storage/cache/rate_limit_*.json | head -1

# View a specific IP's rate data
cat storage/cache/rate_limit_127_0_0_1.json
```

---

**Luminous Framework** - Simple rate limiting for stable APIs! ⚡
