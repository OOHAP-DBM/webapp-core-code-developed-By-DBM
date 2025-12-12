# API Rate Limiting - Quick Reference Card

## 🚀 Quick Start

All rate limiting is **automatically applied** to API routes. No additional code needed in controllers.

---

## 📊 Rate Limits at a Glance

### Authentication Endpoints

| Endpoint | Limit | Time Window | Identifier |
|----------|-------|-------------|------------|
| `POST /auth/login` | 5 | per minute | IP Address |
| `POST /auth/register` | 3 | per hour | IP Address |
| `POST /auth/otp/send` | 3 | per 5 min | Phone/Email |
| `POST /auth/otp/verify` | 3 | per 5 min | Phone/Email |

### Media Uploads (`POST /media/upload`)

| Role | Requests/Minute |
|------|-----------------|
| Admin/Staff | 100 |
| Vendor | 30 |
| Customer | 10 |
| Guest | ❌ Not Allowed |

### Search Endpoints (`GET /search/*`)

| Role | Requests/Minute |
|------|-----------------|
| Admin/Staff | 100 |
| Vendor | 50 |
| Customer | 30 |
| Guest | 10 |

### Payment Operations (`POST /payments/*`)

| Endpoint | Limit | Role |
|----------|-------|------|
| `/payments/create-order` | 10/min | Customer |
| `/payments/verify` | 10/min | Customer |
| `/payments/refund` | ♾️ | Admin |

### Booking Operations (`/bookings/*`)

| Role | Limit | Endpoints |
|------|-------|-----------|
| Customer | 10/min | All booking operations |
| Vendor | 120/min | Vendor-specific endpoints |
| Staff | 300/min | POD uploads, mounting |
| Admin | 300/min | All admin operations |

---

## 🔑 Environment Variables

Copy these to your `.env` file:

```env
# Core Rate Limits
RATE_LIMIT_DEFAULT=60
RATE_LIMIT_LOGIN=5
RATE_LIMIT_REGISTER_HOUR=3
RATE_LIMIT_OTP_IDENTIFIER=3

# Upload Limits by Role
RATE_LIMIT_UPLOAD_ADMIN=100
RATE_LIMIT_UPLOAD_VENDOR=30
RATE_LIMIT_UPLOAD_CUSTOMER=10

# Search Limits by Role
RATE_LIMIT_SEARCH_ADMIN=100
RATE_LIMIT_SEARCH_VENDOR=50
RATE_LIMIT_SEARCH_CUSTOMER=30
RATE_LIMIT_SEARCH_GUEST=10

# Authenticated API Limits
RATE_LIMIT_AUTH_ADMIN=300
RATE_LIMIT_AUTH_VENDOR=120
RATE_LIMIT_AUTH_CUSTOMER=60

# Critical Operations
RATE_LIMIT_CRITICAL=10
RATE_LIMIT_WEBHOOKS=100
```

---

## 🛡️ Applying Rate Limits to Routes

### Method 1: Specific Rate Limiter
```php
// routes/api_v1/custom.php
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/sensitive-action', [Controller::class, 'action']);
});
```

### Method 2: Multiple Middlewares
```php
Route::middleware(['auth:sanctum', 'throttle:critical'])->group(function () {
    Route::post('/payment', [PaymentController::class, 'process']);
});
```

### Available Rate Limiters
- `throttle:api` - Default (60/min)
- `throttle:auth` - Authentication (5/min)
- `throttle:otp` - OTP requests (3 per 5min)
- `throttle:register` - Registration (3/hour)
- `throttle:uploads` - File uploads (role-based)
- `throttle:search` - Search queries (role-based)
- `throttle:authenticated` - General auth endpoints (role-based)
- `throttle:critical` - Payments/bookings (10/min)
- `throttle:webhooks` - External callbacks (100/min)

---

## 📱 Mobile App: Handle 429 Responses

### Basic Handler (Dart/Flutter)
```dart
Future<http.Response> apiCall(String url, Map data) async {
  final response = await http.post(url, body: jsonEncode(data));
  
  if (response.statusCode == 429) {
    final json = jsonDecode(response.body);
    final retryAfter = json['retry_after'] ?? 60;
    
    // Show user message
    showSnackBar('Too many requests. Wait ${retryAfter}s');
    
    // Wait and retry
    await Future.delayed(Duration(seconds: retryAfter));
    return apiCall(url, data);
  }
  
  return response;
}
```

### With Exponential Backoff
```dart
Future<http.Response> apiCallWithBackoff(String url, Map data) async {
  for (int attempt = 0; attempt < 3; attempt++) {
    final response = await http.post(url, body: jsonEncode(data));
    
    if (response.statusCode != 429) {
      return response;
    }
    
    final waitTime = pow(2, attempt).toInt(); // 1s, 2s, 4s
    await Future.delayed(Duration(seconds: waitTime));
  }
  
  throw Exception('Max retries exceeded');
}
```

---

## 🔍 Testing Rate Limits

### Test Login Limit (5/min)
```bash
for ($i=1; $i -le 6; $i++) {
    curl -X POST http://localhost:8000/api/v1/auth/login `
        -H "Content-Type: application/json" `
        -d '{"email":"test@test.com","password":"wrong"}' `
        -w "`nStatus: %{http_code}`n"
}
```

### Test OTP Limit (3 per 5min)
```bash
for ($i=1; $i -le 4; $i++) {
    curl -X POST http://localhost:8000/api/v1/auth/otp/send `
        -H "Content-Type: application/json" `
        -d '{"phone":"+919999999999"}' `
        -w "`nStatus: %{http_code}`n"
}
```

### Run Automated Tests
```bash
php artisan test --filter RateLimitTest
```

---

## 🔧 Common Tasks

### Increase Customer Upload Limit
**File:** `.env`
```env
RATE_LIMIT_UPLOAD_CUSTOMER=20  # Changed from 10
```

### Whitelist an IP
**File:** `config/ratelimit.php`
```php
'whitelist' => [
    '127.0.0.1',
    '192.168.1.100',  // Add your IP
],
```

### Disable Rate Limiting (Local Dev Only)
**File:** `app/Providers/AppServiceProvider.php`
```php
RateLimiter::for('api', function (Request $request) {
    if (app()->environment('local')) {
        return Limit::none();  // No limits in local
    }
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

### View Rate Limit Logs
```bash
tail -f storage/logs/laravel.log | grep "Rate Limit"
```

---

## ⚠️ Troubleshooting

### Issue: Getting 429 on every request
**Cause:** Cache key collision
**Fix:**
```bash
php artisan cache:clear
php artisan config:clear
```

### Issue: Rate limit not applied
**Cause:** Middleware not in route
**Fix:** Check route file for `throttle:limiter_name`

### Issue: Different limits for same user
**Cause:** Multiple tokens/sessions
**Fix:** Use consistent identifier (user ID preferred)

---

## 📈 Monitoring Dashboard

### View Violations in Last Hour
```bash
php artisan tinker
```
```php
use Illuminate\Support\Facades\Cache;
// Check rate limit key
Cache::get('rate_limit:login:127.0.0.1');
```

### Clear Specific User's Rate Limit
```php
RateLimiter::clear('uploads:' . $userId);
```

---

## 🎯 Best Practices

1. **Always handle 429 in mobile app**
2. **Implement exponential backoff**
3. **Cache responses when possible**
4. **Batch operations to reduce requests**
5. **Use pagination efficiently**
6. **Monitor logs weekly**
7. **Adjust limits based on real usage**

---

## 📞 Support

- 📖 Full Docs: `docs/API_RATE_LIMITING.md`
- 🧪 Tests: `tests/Feature/Api/RateLimitTest.php`
- ⚙️ Config: `config/ratelimit.php`
- 🔧 Code: `app/Providers/AppServiceProvider.php`

---

**Version:** 1.0.0  
**Last Updated:** December 12, 2025  
**Status:** ✅ Production Ready
