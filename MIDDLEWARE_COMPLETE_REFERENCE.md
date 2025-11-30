# 🔐 COMPLETE MIDDLEWARE REFERENCE

## Table of Contents

1. [Middleware Overview](#middleware-overview)
2. [CheckRole Middleware](#checkrole-middleware)
3. [RoleMiddleware Middleware](#rolemiddleware-middleware)
4. [Authorization Flow](#authorization-flow)
5. [Implementation Examples](#implementation-examples)
6. [Testing Guide](#testing-guide)
7. [Troubleshooting](#troubleshooting)

---

## Middleware Overview

### What is Middleware?

Middleware adalah layer yang memproses request sebelum sampai ke controller:

```
┌─────────────┐
│   Request   │
└──────┬──────┘
       ↓
   [Middleware Chain]
   ├─ 1. auth:sanctum
   ├─ 2. role (or role.check)
   ├─ 3. custom middleware
   └─ ...
       ↓
   ┌──────────────┐
   │   Controller │
   └──────┬───────┘
          ↓
      [Response]
```

### Middleware dalam Project

Project ini memiliki 2 authorization middleware:

| Middleware | Alias | File | Purpose |
|-----------|-------|------|---------|
| CheckRole | `role` | `app/Http/Middleware/CheckRole.php` | Simple role check |
| RoleMiddleware | `role.check` | `app/Http/Middleware/RoleMiddleware.php` | Advanced role check |

---

## CheckRole Middleware

### Location
```
app/Http/Middleware/CheckRole.php
```

### Source Code
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $requiredRole)
    {
        if (!$request->user() || $request->user()->role !== $requiredRole) {
            return response()->json([
                'message' => 'Forbidden: insufficient role'
            ], 403);
        }

        return $next($request);
    }
}
```

### How It Works

1. Get current user dari request
2. Check jika user memiliki role yang diperlukan
3. Jika ya → lanjut ke next middleware/controller
4. Jika tidak → return 403 Forbidden

### Usage

```php
// In routes/api.php
Route::middleware('auth:sanctum', 'role:admin')->group(function () {
    Route::get('/admin/users', [UserController::class, 'indexAll']);
});
```

### Key Points

- ✅ Single role parameter only
- ✅ Simple & straightforward
- ❌ Tidak flexible untuk multiple roles
- ✅ Backward compatible

### Examples

**Example 1: Admin Only**
```php
Route::middleware('auth:sanctum', 'role:admin')->group(function () {
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
});
```

**Example 2: Manager Only**
```php
Route::middleware('auth:sanctum', 'role:manager')->group(function () {
    Route::get('/manager/reports', [ManagerController::class, 'reports']);
});
```

---

## RoleMiddleware Middleware

### Location
```
app/Http/Middleware/RoleMiddleware.php
```

### Source Code
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$requiredRoles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $requiredRoles)) {
            return response()->json([
                'message' => 'Forbidden: insufficient role'
            ], 403);
        }

        return $next($request);
    }
}
```

### How It Works

1. Get current user dari request
2. Convert `...$requiredRoles` parameter menjadi array
3. Check jika user role ada dalam required roles array
4. Jika ya → lanjut ke next middleware/controller
5. Jika tidak → return 403 Forbidden

### Usage

```php
// Single role
Route::middleware('auth:sanctum', 'role.check:admin')->group(function () {
    Route::get('/admin/dashboard', [...]);
});

// Multiple roles
Route::middleware('auth:sanctum', 'role.check:admin,moderator')->group(function () {
    Route::get('/dashboard', [...]);
});
```

### Key Points

- ✅ Variadic parameters → multiple roles
- ✅ Flexible & scalable
- ✅ DRY principle
- ✅ Modern best practice

### Examples

**Example 1: Admin or Moderator**
```php
Route::middleware('auth:sanctum', 'role.check:admin,moderator')->group(function () {
    Route::get('/moderation/reports', [ModController::class, 'reports']);
});
```

**Example 2: Manager or Director**
```php
Route::middleware('auth:sanctum', 'role.check:manager,director')->group(function () {
    Route::post('/budget/approve', [BudgetController::class, 'approve']);
});
```

**Example 3: Any leadership role**
```php
Route::middleware('auth:sanctum', 'role.check:admin,manager,supervisor')->group(function () {
    Route::get('/reports/analytics', [AnalyticsController::class, 'index']);
});
```

---

## Authorization Flow

### Complete Request Flow

```
┌────────────────────────────────────────────────────────────┐
│ HTTP Request (e.g., GET /api/admin/users)                │
└────────────────────┬─────────────────────────────────────┘
                     ↓
            ┌─────────────────────┐
            │ Route Matching      │
            │ (find matching route)
            └────────┬────────────┘
                     ↓
       ┌─────────────────────────────┐
       │ Middleware Chain Execution  │
       │                             │
       │ 1. auth:sanctum middleware  │
       │    ├─ Valid token?          │
       │    └─ If NO → 401           │
       │                             │
       │ 2. role.check:admin         │
       │    ├─ User exists?          │
       │    ├─ Role = admin?         │
       │    └─ If NO → 403           │
       │                             │
       │ 3. [Other middleware]       │
       └────────┬────────────────────┘
                ↓
       ┌─────────────────────┐
       │ Controller Action   │
       │ (business logic)    │
       └────────┬────────────┘
                ↓
       ┌─────────────────────┐
       │ Response to Client  │
       │ (success or error)  │
       └─────────────────────┘
```

### Authorization Decision Tree

```
    Start Request
        ↓
    ┌─ Token valid?
    │
    NO → Return 401 Unauthorized
    │
    YES
    │
    ├─ Get User from Token
    │
    ├─ User Role = required role?
    │
    NO → Return 403 Forbidden
    │
    YES
    │
    ├─ Proceed to Controller
    │
    ├─ Execute Business Logic
    │
    └─ Return 200 OK + Response Body
```

### Multiple Roles Decision Tree

```
Input: role.check:admin,moderator,editor

    User Login?
        ↓
    NO → 401 Unauthorized
        ↓
    YES
        ↓
    Check if user.role in ['admin', 'moderator', 'editor']
        ↓
    YES (matched) → Proceed to Controller ✓
    NO (not matched) → 403 Forbidden ✗
```

---

## Implementation Examples

### Example 1: Basic API Authorization

**Setup:**
```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // User routes (all authenticated users)
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'update']);
    
    // Admin routes (admin only)
    Route::middleware('role.check:admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
    });
});
```

**Test:**
```bash
# 1. Admin can access own profile
TOKEN=$(curl -s -X POST /api/login \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.access_token')
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8000/api/profile

# 2. Admin can access admin endpoints
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8000/api/admin/users

# 3. Regular user can access own profile
USER_TOKEN=$(curl -s -X POST /api/login \
  -d '{"email":"user@example.com","password":"password"}' | jq -r '.access_token')
curl -H "Authorization: Bearer $USER_TOKEN" http://127.0.0.1:8000/api/profile

# 4. Regular user CANNOT access admin endpoints (403)
curl -H "Authorization: Bearer $USER_TOKEN" http://127.0.0.1:8000/api/admin/users
```

### Example 2: Multi-Level Authorization

**Setup:**
```php
// Tier structure: user < moderator < admin

Route::middleware('auth:sanctum')->group(function () {
    // Level 1: All authenticated users
    Route::get('/my-data', [UserController::class, 'myData']);
    
    // Level 2: Moderators and above
    Route::middleware('role.check:moderator,admin')->group(function () {
        Route::get('/reports/content', [ReportController::class, 'content']);
        Route::post('/moderation/approve', [ModerationController::class, 'approve']);
    });
    
    // Level 3: Admin only
    Route::middleware('role.check:admin')->group(function () {
        Route::get('/admin/system-health', [AdminController::class, 'systemHealth']);
        Route::delete('/admin/purge-data', [AdminController::class, 'purge']);
    });
});
```

**Access Matrix:**
```
                    User | Moderator | Admin
/my-data            ✅   |    ✅     |  ✅
/reports/content    ❌   |    ✅     |  ✅
/moderation/approve ❌   |    ✅     |  ✅
/admin/system-health❌   |    ❌     |  ✅
/admin/purge-data   ❌   |    ❌     |  ✅
```

### Example 3: Role-Based Features

**Setup:**
```php
Route::middleware('auth:sanctum')->group(function () {
    // Feature: Reports (managers and above)
    Route::middleware('role.check:manager,director,admin')->group(function () {
        Route::apiResource('reports', ReportController::class);
    });
    
    // Feature: HR (HR staff only)
    Route::middleware('role.check:hr,admin')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
        Route::post('/payroll/process', [PayrollController::class, 'process']);
    });
    
    // Feature: Finance (Finance and CFO)
    Route::middleware('role.check:finance,cfo,admin')->group(function () {
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('/budgets/approve', [BudgetController::class, 'approve']);
    });
});
```

---

## Testing Guide

### Setup for Testing

```bash
# 1. Start Laravel server
cd "c:\latihan laravel 3\laravel_api_role"
php artisan serve

# 2. Get various tokens
ADMIN_TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.access_token')

USER_TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' | jq -r '.access_token')
```

### Test 1: CheckRole Middleware

```bash
# ✅ Admin accessing admin endpoint (success)
curl -H "Authorization: Bearer $ADMIN_TOKEN" \
  http://127.0.0.1:8000/api/admin/users
# Expected: 200 OK

# ❌ User accessing admin endpoint (fail)
curl -H "Authorization: Bearer $USER_TOKEN" \
  http://127.0.0.1:8000/api/admin/users
# Expected: 403 Forbidden
```

### Test 2: RoleMiddleware Middleware

```bash
# ✅ Multiple roles - admin accessing (success)
curl -H "Authorization: Bearer $ADMIN_TOKEN" \
  http://127.0.0.1:8000/api/admin/todos
# Expected: 200 OK

# ✅ Multiple roles - user accessing (if moderator role exists)
# (Set up moderator token first)
# Expected: 200 OK if role matches

# ❌ Non-matching role (fail)
curl -H "Authorization: Bearer $USER_TOKEN" \
  http://127.0.0.1:8000/api/admin/todos
# Expected: 403 Forbidden
```

### Test 3: Invalid Token

```bash
# ❌ Invalid token
curl -H "Authorization: Bearer invalid-token-xyz" \
  http://127.0.0.1:8000/api/admin/users
# Expected: 401 Unauthorized
```

### Test 4: No Token

```bash
# ❌ No token provided
curl http://127.0.0.1:8000/api/admin/users
# Expected: 401 Unauthorized
```

---

## Troubleshooting

### Issue 1: 403 Forbidden When Should Be Allowed

**Problem:**
```
Admin user gets 403 when accessing admin endpoint
```

**Diagnosis:**
```php
// Check user role in Tinker
php artisan tinker
User::where('email', 'admin@example.com')->first()->role
// Should output: 'admin'

// Check middleware registered
// Run: grep -n "role" bootstrap/app.php
```

**Solution:**
```php
// In bootstrap/app.php, verify:
$middleware->alias([
    'role' => \App\Http\Middleware\CheckRole::class,
    'role.check' => \App\Http\Middleware\RoleMiddleware::class,
]);

// Run
php artisan config:cache
```

### Issue 2: Middleware Not Being Applied

**Problem:**
```
All users can access admin endpoints (no 403)
```

**Diagnosis:**
```php
// Check routes
php artisan route:list | grep admin

// Should show middleware column with 'role.check:admin'
// If empty → middleware not applied
```

**Solution:**
```php
// Verify route syntax
// ❌ Wrong
Route::get('/admin/users', [UserController::class, 'indexAll']);

// ✅ Correct
Route::middleware('auth:sanctum', 'role.check:admin')
  ->get('/admin/users', [UserController::class, 'indexAll']);
```

### Issue 3: "Class not found" Error

**Problem:**
```
Error: Class App\Http\Middleware\RoleMiddleware not found
```

**Solution:**
```bash
# 1. Verify file exists
ls app/Http/Middleware/RoleMiddleware.php

# 2. Dump autoloader
composer dump-autoload

# 3. Clear config cache
php artisan config:cache
```

### Issue 4: Token Issues

**Problem:**
```
Even with valid token getting 401 Unauthorized
```

**Diagnosis:**
```php
// 1. Check Sanctum is installed
php artisan tinker
Schema::hasTable('personal_access_tokens')
// Should return: true

// 2. Verify token in database
PersonalAccessToken::where('id', 1)->first()

// 3. Check token expiry
PersonalAccessToken::where('id', 1)->first()->created_at
```

**Solution:**
```bash
# 1. Re-migrate database
php artisan migrate:reset
php artisan migrate --seed

# 2. Re-install Sanctum
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

---

## Best Practices

### ✅ DO

```php
// ✅ Chain middleware properly
Route::middleware(['auth:sanctum', 'role.check:admin'])
  ->group(function () {
    Route::apiResource('admin/users', UserController::class);
  });

// ✅ Use meaningful role names
'role.check:manager,director'

// ✅ Group related endpoints
Route::prefix('admin')
  ->middleware('role.check:admin')
  ->group(function () {
    // All admin endpoints here
  });

// ✅ Document authorization requirements
/**
 * List all users
 * Requires: admin role
 * Response: 200 OK | 403 Forbidden | 401 Unauthorized
 */
public function indexAll() { ... }
```

### ❌ DON'T

```php
// ❌ Hardcode checks in controller
public function destroy($id) {
    if ($request->user()->role !== 'admin') {
        return 403;
    }
}

// ❌ Mix different middleware aliases
Route::middleware('role:admin', 'role.check:moderator')

// ❌ Forget auth:sanctum
Route::middleware('role.check:admin') // Missing auth:sanctum!

// ❌ Use inconsistent role names
Route::middleware('role.check:adm') // Confusing abbreviation
```

---

## Quick Reference

### Middleware Comparison

| Feature | CheckRole | RoleMiddleware |
|---------|-----------|----------------|
| Single role | ✅ | ✅ |
| Multiple roles | ❌ | ✅ |
| Variadic params | ❌ | ✅ |
| DRY principle | ❌ | ✅ |
| Best practice | ⚠️ Legacy | ✅ Modern |

### Aliases in bootstrap/app.php

```php
'role' => \App\Http\Middleware\CheckRole::class,
'role.check' => \App\Http\Middleware\RoleMiddleware::class,
```

### Usage Templates

```php
// Single role
Route::middleware('role.check:admin')

// Multiple roles
Route::middleware('role.check:admin,moderator,manager')

// With auth
Route::middleware(['auth:sanctum', 'role.check:admin'])
```

---

## Summary

### Key Takeaways

1. **CheckRole** - Simple, single-role, legacy
2. **RoleMiddleware** - Advanced, multiple-role, modern
3. Always use `auth:sanctum` first
4. RoleMiddleware is recommended for new features
5. Test all authorization scenarios thoroughly

### Next Steps

1. ✅ Understand both middleware options
2. ✅ Know when to use each
3. ➡️ Implement role-based features
4. ➡️ Test all scenarios
5. ➡️ Document your authorization matrix

---

**Reference:** For detailed implementation, see:
- `MIDDLEWARE_ROLEMIDDLEWARE.md` - RoleMiddleware details
- `MIDDLEWARE_STRATEGY.md` - Strategy & decision guide
- Route examples in `routes/api.php`

