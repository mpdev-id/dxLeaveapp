# Security Implementation Guide

## 🔒 Security Features Implemented

### 1. **Security Headers Middleware**
File: `app/Http/Middleware/SecurityHeaders.php`

**Headers Added:**
- `X-Frame-Options: SAMEORIGIN` - Prevents clickjacking attacks
- `X-Content-Type-Options: nosniff` - Prevents MIME type sniffing
- `X-XSS-Protection: 1; mode=block` - Enables XSS protection
- `Referrer-Policy: strict-origin-when-cross-origin` - Controls referrer information
- `Content-Security-Policy` - Restricts resource loading
- `Permissions-Policy` - Controls browser features

**Applied to:** All routes globally via `bootstrap/app.php`

---

### 2. **Rate Limiting**

**Authentication Routes** (10 requests per minute):
- `/api/register`
- `/api/login`
- `/api/forgot-password`
- `/api/reset-password`

**Authenticated User Routes** (60 requests per minute):
- `/api/user`
- `/api/leave-requests/*`
- All user-level endpoints

**Admin Routes** (120 requests per minute):
- `/api/admin/master/*`
- `/api/admin/dashboard/*`

**Why these limits?**
- Auth routes: Lower limit to prevent brute force attacks
- User routes: Standard limit for normal usage
- Admin routes: Higher limit for dashboard/management operations

---

### 3. **Existing Security Features**

✅ **Laravel Sanctum** - API token authentication  
✅ **Spatie Permission** - Role-based access control  
✅ **CORS Middleware** - Cross-origin request protection  
✅ **Input Validation** - All controllers validate input  
✅ **CSRF Protection** - Laravel's built-in CSRF for web routes

---

## 📋 Testing the Implementation

### Test Rate Limiting:
```bash
# Test login rate limit (should block after 10 requests in 1 minute)
for i in {1..15}; do
  curl -X POST http://localhost/api/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"wrong"}'
  echo "Request $i"
done
```

### Test Security Headers:
```bash
# Check headers in response
curl -I http://localhost/api/user
```

You should see headers like:
```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
```

---

## 🚀 Production Recommendations

### 1. **Environment Variables**
Add to `.env`:
```env
# Security
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Session Security
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# CORS (adjust to your frontend domain)
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

### 2. **HTTPS Configuration**
- Use SSL certificate (Let's Encrypt recommended)
- Force HTTPS in production
- Update `Content-Security-Policy` to use `https:` only

### 3. **Database Security**
- Use strong database passwords
- Limit database user permissions
- Enable SSL for database connections

### 4. **File Permissions**
```bash
# Set proper permissions
chmod -R 755 storage bootstrap/cache
chmod -R 644 .env
```

### 5. **Logging & Monitoring**
- Monitor failed login attempts
- Log rate limit violations
- Set up alerts for suspicious activity

---

## 🔧 Customization

### Adjust Rate Limits:
Edit `routes/api.php`:
```php
// Change from 60 to your desired limit
Route::middleware(['auth:sanctum', 'throttle:100,1'])
```

### Modify Security Headers:
Edit `app/Http/Middleware/SecurityHeaders.php` to adjust CSP or other headers.

### Add IP Whitelisting (Optional):
Create a new middleware for admin routes to allow only specific IPs.

---

## ⚠️ Important Notes

1. **Rate limiting uses cache** - Make sure cache is configured properly
2. **CSP may need adjustment** - If you use external CDNs, update the policy
3. **Test thoroughly** - Test all functionality after implementing
4. **Monitor logs** - Check `storage/logs/laravel.log` for rate limit hits

---

## 📚 Additional Security Measures

### Consider implementing:
- [ ] Two-Factor Authentication (2FA)
- [ ] IP-based access control for admin panel
- [ ] Audit logging for sensitive operations
- [ ] Regular security updates
- [ ] Automated backup system
- [ ] Web Application Firewall (WAF)

---

## 🆘 Troubleshooting

**Rate limit not working?**
- Check cache configuration (`CACHE_STORE` in `.env`)
- Clear cache: `php artisan cache:clear`

**Headers not appearing?**
- Clear config cache: `php artisan config:clear`
- Check middleware is registered in `bootstrap/app.php`

**Too restrictive CSP?**
- Check browser console for CSP violations
- Adjust policy in `SecurityHeaders.php`

---

Generated: 2025-11-23
