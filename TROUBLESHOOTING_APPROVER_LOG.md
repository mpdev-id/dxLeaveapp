# Troubleshooting: Approver Log Menu Not Showing

## Problem
User dengan role Super Admin, SL, SPV, dll tidak bisa melihat menu "Approver Log" meskipun memiliki permission `approve leave request`.

## Root Causes

### 1. **Permission Cache Issue**
Spatie Permission menggunakan cache. Setelah assign role/permission, cache perlu di-reset.

**Solution:**
```bash
php artisan permission:cache-reset
```

### 2. **User Not Properly Assigned Roles**
User mungkin belum di-assign role dengan benar.

**Check:**
```sql
SELECT u.name, r.name as role_name, p.name as permission_name
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id
LEFT JOIN roles r ON mhr.role_id = r.id
LEFT JOIN role_has_permissions rhp ON r.id = rhp.role_id
LEFT JOIN permissions p ON rhp.permission_id = p.id
WHERE u.email = 'your@email.com';
```

**Fix via Tinker:**
```bash
php artisan tinker

# Get user
$user = User::where('email', 'your@email.com')->first();

# Check current roles
$user->roles;

# Assign role if missing
$user->assignRole('Super Admin');
$user->assignRole('SL');

# Check permissions
$user->getAllPermissions();

# Check specific permission
$user->can('approve leave request'); // Should return true
```

### 3. **Browser Cache**
Browser mungkin cache halaman lama.

**Solution:**
- Hard refresh: `Ctrl + Shift + R` (Windows/Linux) atau `Cmd + Shift + R` (Mac)
- Clear browser cache
- Try incognito/private mode

### 4. **Multiple Roles Issue**
Jika user memiliki multiple roles (e.g., Member + Super Admin), Spatie akan check **ANY** role yang memiliki permission.

**How it works:**
```php
@can('approve leave request')
    // This will show if ANY of user's roles has this permission
@endcan
```

User dengan roles: `[Member, Super Admin]`
- Member: NO permission
- Super Admin: HAS permission
- Result: Menu WILL SHOW ✓

## Verification Steps

### Step 1: Clear All Caches
```bash
php artisan permission:cache-reset
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Step 2: Verify User Permissions
```bash
php artisan tinker

$user = User::find(1); // Your user ID
$user->getAllPermissions()->pluck('name');
// Should include 'approve leave request'

$user->can('approve leave request');
// Should return true
```

### Step 3: Check Blade Directive
The `@can` directive in `resources/views/template/member.blade.php`:
```blade
@can('approve leave request')
    <li><a href="{{ route('member.approver-log.index') }}">Approver Log</a></li>
@endcan
```

This is correct and will work for ANY user with the permission.

### Step 4: Re-seed if Necessary
If permissions are missing:
```bash
php artisan db:seed --class=RoleAndPermissionSeeder
php artisan permission:cache-reset
```

## Expected Behavior

### Roles with "approve leave request" permission:
- ✅ Super Admin
- ✅ SL
- ✅ SPV
- ✅ ASMEN
- ✅ TL
- ✅ Manager

### Roles WITHOUT the permission:
- ❌ Employee
- ❌ Member (if exists)

### User with Multiple Roles:
If user has `[Member, Super Admin]`:
- Menu WILL show because Super Admin has the permission
- Spatie checks if ANY role has the permission

## Quick Fix Commands

Run these in order:
```bash
# 1. Clear permission cache
php artisan permission:cache-reset

# 2. Clear all caches
php artisan optimize:clear

# 3. Verify in browser (hard refresh)
Ctrl + Shift + R
```

## Still Not Working?

### Debug in Tinker:
```bash
php artisan tinker

# Check user
$user = auth()->user(); // Or User::find(YOUR_ID)

# Check roles
$user->roles->pluck('name');

# Check permissions
$user->getAllPermissions()->pluck('name');

# Check specific permission
$user->can('approve leave request');

# If false, assign role:
$user->assignRole('Super Admin');

# Clear cache
\Artisan::call('permission:cache-reset');

# Check again
$user->can('approve leave request'); // Should be true now
```

## Prevention

### After Assigning Roles/Permissions:
Always run:
```bash
php artisan permission:cache-reset
```

### In Production:
Add to deployment script:
```bash
php artisan migrate --force
php artisan db:seed --class=RoleAndPermissionSeeder --force
php artisan permission:cache-reset
php artisan optimize
```
