# Mobile App API Rate Limiter

## Overview
Comprehensive rate limiting system for OOHAPP mobile APIs to prevent abuse, protect resources, and ensure fair usage across different user roles.

## Rate Limit Configuration

### 1. Authentication Endpoints (`throttle:auth`)
**Purpose:** Prevent brute force attacks on login endpoints

- **Limit:** 5 requests per minute per IP
- **Applied to:**
  - `POST /api/v1/auth/login`
- **Response:** 429 with retry_after header
- **Message:** "Too many login attempts. Please try again later."

### 2. OTP Endpoints (`throttle:otp`)
**Purpose:** Prevent SMS/Email flooding and abuse

- **Limit:** 
  - 3 OTP requests per 5 minutes per phone/email
  - 10 OTP requests per 5 minutes per IP (secondary limit)
- **Applied to:**
  - `POST /api/v1/auth/otp/send`
  - `POST /api/v1/auth/otp/verify`
- **Response:** 429 with retry_after header (300 seconds)
- **Message:** "Too many OTP requests. Please wait before requesting again."

### 3. Registration Endpoint (`throttle:register`)
**Purpose:** Prevent spam account creation

- **Limit:**
  - 3 registrations per hour per IP
  - 1 registration per day per email/phone
- **Applied to:**
  - `POST /api/v1/auth/register`
- **Response:** 429 with retry_after header (3600 seconds)
- **Message:** "Too many registration attempts. Please try again later."

### 4. Image/Media Uploads (`throttle:uploads`)
**Purpose:** Prevent storage abuse and server overload

Role-based limits:
- **Admin/Staff:** 100 uploads per minute
- **Vendor:** 30 uploads per minute
- **Customer:** 10 uploads per minute
- **Guest:** No uploads allowed

**Applied to:**
- `POST /api/v1/media/upload`

### 5. Search Endpoints (`throttle:search`)
**Purpose:** Prevent data scraping and excessive queries

Role-based limits:
- **Admin/Staff:** 100 requests per minute
- **Vendor:** 50 requests per minute
- **Customer:** 30 requests per minute
- **Guest:** 10 requests per minute

**Applied to:**
- `GET /api/v1/search/*`
- `POST /api/v1/search/advanced`

### 6. Critical Operations (`throttle:critical`)
**Purpose:** Conservative limits for payment and booking operations

- **Limit:** 10 requests per minute per user
- **Applied to:**
  - `POST /api/v1/payments/create-order`
  - `POST /api/v1/payments/verify`
  - All booking endpoints for customers
- **Response:** 429 with retry_after header
- **Message:** "Too many requests. Please slow down."

### 7. Authenticated API (`throttle:authenticated`)
**Purpose:** General rate limiting for authenticated endpoints

Role-based limits:
- **Admin/Staff:** 300 requests per minute
- **Vendor:** 120 requests per minute
- **Customer:** 60 requests per minute
- **Default:** 30 requests per minute

**Applied to:**
- Most authenticated endpoints not covered by more specific limiters

### 8. Webhooks (`throttle:webhooks`)
**Purpose:** Allow high volume from trusted external services

- **Limit:** 100 requests per minute per IP
- **Applied to:**
  - `POST /webhooks/razorpay`
  - `POST /api/v1/payments/webhook/razorpay`

### 9. Default API (`throttle:api`)
**Purpose:** Baseline rate limiting for all API endpoints

- **Limit:** 60 requests per minute
- **Identification:** By user ID (if authenticated) or IP address
- **Applied to:** All `/api/v1/*` endpoints as fallback

## Implementation Details

### Rate Limiter Configuration
Location: `app/Providers/AppServiceProvider.php`

```php
protected function configureRateLimiting(): void
{
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
    
    // ... other rate limiters
}
```

### Route Application

#### Authentication Routes
```php
// routes/api_v1/auth.php
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['throttle:otp'])->group(function () {
    Route::post('/otp/send', [AuthController::class, 'sendOTP']);
    Route::post('/otp/verify', [AuthController::class, 'verifyOTP']);
});
```

#### Media Upload Routes
```php
// routes/api_v1/media.php
Route::middleware(['auth:sanctum', 'throttle:uploads'])->group(function () {
    Route::post('/upload', [MediaController::class, 'upload']);
});
```

## Rate Limit Headers

All API responses include rate limit information:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
Retry-After: 42 (only on 429 responses)
```

## 429 Response Format

When rate limit is exceeded:

```json
{
    "message": "Too many requests. Please slow down.",
    "retry_after": 60
}
```

## Monitoring & Logging

Rate limit violations are automatically logged:

```php
Log::warning('API Rate Limit Exceeded', [
    'ip' => '192.168.1.1',
    'user_id' => 123,
    'user_role' => 'customer',
    'path' => 'api/v1/media/upload',
    'method' => 'POST',
    'user_agent' => 'OOHAPP-Mobile/1.0',
    'timestamp' => '2025-12-12T10:30:00+00:00'
]);
```

## Testing Rate Limits

### Test Login Rate Limit
```bash
# Make 6 rapid login requests (limit is 5/min)
for i in {1..6}; do
  curl -X POST http://localhost:8000/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"wrong"}' \
    -w "\nStatus: %{http_code}\n"
done
```

### Test OTP Rate Limit
```bash
# Make 4 OTP requests (limit is 3 per 5 minutes)
for i in {1..4}; do
  curl -X POST http://localhost:8000/api/v1/auth/otp/send \
    -H "Content-Type: application/json" \
    -d '{"phone":"+919876543210"}' \
    -w "\nStatus: %{http_code}\n"
done
```

### Test Upload Rate Limit (as Customer)
```bash
# Make 11 upload requests (customer limit is 10/min)
TOKEN="your_customer_token_here"
for i in {1..11}; do
  curl -X POST http://localhost:8000/api/v1/media/upload \
    -H "Authorization: Bearer $TOKEN" \
    -F "file=@test.jpg" \
    -w "\nStatus: %{http_code}\n"
done
```

## Best Practices

### For Mobile App Developers

1. **Handle 429 Responses**
   ```dart
   if (response.statusCode == 429) {
     int retryAfter = int.parse(response.headers['retry-after'] ?? '60');
     await Future.delayed(Duration(seconds: retryAfter));
     // Retry the request
   }
   ```

2. **Implement Exponential Backoff**
   ```dart
   int attempt = 0;
   while (attempt < maxAttempts) {
     try {
       var response = await apiCall();
       return response;
     } catch (e) {
       if (e.statusCode == 429) {
         await Future.delayed(Duration(seconds: pow(2, attempt).toInt()));
         attempt++;
       }
     }
   }
   ```

3. **Cache Responses**
   - Cache search results, hoarding lists
   - Reduce unnecessary API calls

4. **Batch Operations**
   - Upload multiple images in single request when possible
   - Use pagination efficiently

### For Backend Team

1. **Monitor Rate Limit Logs**
   ```bash
   tail -f storage/logs/laravel.log | grep "Rate Limit Exceeded"
   ```

2. **Adjust Limits Based on Usage**
   - Review logs weekly
   - Increase limits for legitimate high-usage users
   - Decrease limits if abuse detected

3. **Whitelist Trusted IPs**
   ```php
   RateLimiter::for('api', function (Request $request) {
       if (in_array($request->ip(), config('ratelimit.whitelist'))) {
           return Limit::none();
       }
       return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
   });
   ```

## Security Considerations

1. **DDoS Protection:** Rate limits provide first line of defense against DDoS attacks
2. **Credential Stuffing:** Auth rate limits prevent automated credential stuffing attacks
3. **Data Scraping:** Search rate limits prevent competitors from scraping hoarding data
4. **Resource Exhaustion:** Upload limits prevent storage/bandwidth exhaustion
5. **SMS Abuse:** OTP rate limits prevent SMS gateway abuse and costs

## Future Enhancements

1. **Dynamic Rate Limiting:** Adjust limits based on server load
2. **User-Specific Overrides:** Allow admins to set custom limits for specific users
3. **Geographic Rate Limiting:** Different limits for different regions
4. **Time-Based Limits:** Lower limits during peak hours
5. **Cost-Based Rate Limiting:** Limit expensive operations (e.g., AI features) more strictly

## Configuration

To modify rate limits, edit `app/Providers/AppServiceProvider.php`:

```php
protected function configureRateLimiting(): void
{
    // Increase customer upload limit to 20/min
    RateLimiter::for('uploads', function (Request $request) {
        $user = $request->user();
        return match ($user->role) {
            'customer' => Limit::perMinute(20)->by($user->id), // Changed from 10
            // ... other roles
        };
    });
}
```

## Troubleshooting

### Issue: Legitimate users hitting rate limits
**Solution:** 
- Check logs to identify the user
- Increase role-based limit
- Or add user to whitelist

### Issue: Rate limit not working
**Solution:**
- Clear cache: `php artisan cache:clear`
- Verify middleware is applied to route
- Check rate limiter is defined in AppServiceProvider

### Issue: Different limits for same user
**Solution:**
- Ensure consistent rate limiter naming
- Check if user has multiple sessions/tokens
- Verify rate limiter uses correct identifier (user ID vs IP)

## Related Files

- `app/Providers/AppServiceProvider.php` - Rate limiter definitions
- `app/Http/Middleware/ApiRateLimitMiddleware.php` - Custom rate limit middleware
- `routes/api.php` - Main API routes with rate limiting
- `routes/api_v1/*.php` - Module-specific routes with rate limiting

## Support

For rate limit issues or adjustments, contact the backend team or create a ticket in the project management system.
