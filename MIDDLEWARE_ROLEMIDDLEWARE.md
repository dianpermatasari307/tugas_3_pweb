# 🔐 RoleMiddleware - Advanced Authorization

## Overview

`RoleMiddleware` adalah middleware Laravel yang lebih advanced untuk mengotomasi pengecekan role pada route. Middleware ini memungkinkan multiple role parameters dengan syntax yang fleksibel.

## Perbandingan: CheckRole vs RoleMiddleware

### CheckRole (Legacy)
```php
// Hardcoded dalam controller
if ($user->role !== 'admin') {
    return response()->json(['message' => 'Forbidden'], 403);
}
```

### RoleMiddleware (New - Recommended)
```php
// Deklaratif pada route
Route::middleware('role.check:admin')->group(function () {
    // admin-only routes
});
```

---

## Implementasi

### 1. Middleware File

**Location:** `app/Http/Middleware/RoleMiddleware.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$requiredRoles
     */
    public function handle(Request $request, Closure $next, ...$requiredRoles): Response
    {
        $user = $request->user(); // user yang login via sanctum

        if (!$user || !in_array($user->role, $requiredRoles)) {
            return response()->json([
                'message' => 'Forbidden: insufficient role'
            ], 403);
        }

        return $next($request);
    }
}
```

### 2. Registrasi di Bootstrap

**Location:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'role.check' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

### 3. Penggunaan di Routes

**Location:** `routes/api.php`

```php
// Single role
Route::middleware('role.check:admin')->group(function () {
    // admin-only endpoints
});

// Multiple roles (user ATAU admin)
Route::middleware('role.check:admin,moderator')->group(function () {
    // admin and moderator can access
});
```

---

## Cara Kerja

### Flow Diagram

```
Request ke /admin/users
    ↓
Middleware chain:
├─ 'auth:sanctum' → Check token valid
│
└─ 'role.check:admin' → RoleMiddleware::handle()
                        ├─ Get current user
                        ├─ Check user role
                        ├─ Check if role in ['admin']
                        │
                        ├─ If YES → Continue
                        │           ↓
                        │           Controller
                        │           ↓
                        │           Response
                        │
                        └─ If NO → Return 403 Forbidden
                                   ↓
                                   JSON Error Response
```

### Parameter Explanation

```php
public function handle(Request $request, Closure $next, ...$requiredRoles): Response
{
    // $request     : Current HTTP request dengan user() method
    // $next        : Callback ke next middleware / controller
    // $requiredRoles : Variadic parameter, bisa ['admin'] atau ['admin', 'moderator']
}
```

---

## Usage Examples

### Example 1: Single Role (Admin Only)

```php
Route::middleware('role.check:admin')->group(function () {
    Route::post('/admin/create-user', [UserController::class, 'create']);
    Route::delete('/admin/delete-user/{id}', [UserController::class, 'destroy']);
});
```

**Test:**
```bash
# As Admin - Should succeed (200)
curl -X POST http://127.0.0.1:8000/admin/create-user \
  -H "Authorization: Bearer {admin_token}"

# As Regular User - Should fail (403)
curl -X POST http://127.0.0.1:8000/admin/create-user \
  -H "Authorization: Bearer {user_token}"
```

### Example 2: Multiple Roles

```php
Route::middleware('role.check:admin,moderator')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/users', [DashboardController::class, 'users']);
});
```

**Test:**
```bash
# As Admin - Should succeed
curl -X GET http://127.0.0.1:8000/dashboard/stats \
  -H "Authorization: Bearer {admin_token}"

# As Moderator - Should succeed
curl -X GET http://127.0.0.1:8000/dashboard/stats \
  -H "Authorization: Bearer {moderator_token}"

# As Regular User - Should fail (403)
curl -X GET http://127.0.0.1:8000/dashboard/stats \
  -H "Authorization: Bearer {user_token}"
```

### Example 3: Mixed Middleware

```php
Route::middleware(['auth:sanctum', 'role.check:admin'])->group(function () {
    Route::apiResource('admin/roles', RoleController::class);
});
```

---

## Error Handling

### 403 Forbidden Response

```json
{
  "message": "Forbidden: insufficient role"
}
```

### 401 Unauthorized Response

Jika user tidak authenticated (token invalid/expired):

```json
{
  "message": "Unauthenticated"
}
```

---

## Authorization Logic

### Flowchart

```
┌─────────────────────────────────┐
│ Request dengan role.check:admin │
└──────────────┬──────────────────┘
               ↓
    ┌──────────────────────┐
    │ Get user dari token  │
    └──────────────┬───────┘
                   ↓
        ┌──────────────────────┐
        │ User exists?         │
        └──────┬──────────┬────┘
             NO│          │YES
               ↓          ↓
            403        ┌────────────────────┐
                       │ User role in       │
                       │ required roles?    │
                       └───┬────────────┬───┘
                         NO│           │YES
                           ↓           ↓
                         403         ✅ Continue
```

---

## Advanced Scenarios

### 1. Multiple Roles dengan Kondisi

```php
// Super admin mendapat akses ke semua route
Route::middleware('role.check:super-admin,admin')->group(function () {
    Route::prefix('/admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
    });
});
```

### 2. Nested Role Protection

```php
Route::middleware('auth:sanctum')->group(function () {
    // Level 1: User routes (all authenticated users)
    Route::apiResource('todos', TodoController::class);
    
    // Level 2: Moderator routes
    Route::middleware('role.check:moderator')->group(function () {
        Route::get('/moderation/reports', [ModerationController::class, 'reports']);
    });
    
    // Level 3: Admin routes
    Route::middleware('role.check:admin')->group(function () {
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
    });
});
```

### 3. Conditional Role Access

```php
// Kontroiler dapat custom check jika diperlukan
class AdminController extends Controller
{
    public function updateUser(Request $request, $id)
    {
        // Middleware sudah check role = admin
        // Tapi kita bisa tambah custom logic
        
        if ($request->user()->role === 'admin') {
            // Additional admin-only logic
        }
        
        // Update user
    }
}
```

---

## Comparison Table

| Feature | CheckRole | RoleMiddleware |
|---------|-----------|----------------|
| Definition | Hardcoded | Route middleware |
| Multiple Roles | ❌ Limited | ✅ Variadic |
| DRY Principle | ❌ Repeated | ✅ Once in route |
| Readability | ❌ Mixed in logic | ✅ Clear & explicit |
| Maintenance | ❌ Scattered | ✅ Centralized |
| Performance | ✅ Direct check | ✅ Middleware overhead negligible |

---

## Testing

### Test Scenario 1: Admin Access

```bash
# 1. Login as admin
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq -r '.access_token')

# 2. Access admin endpoint (should succeed)
curl -X GET http://127.0.0.1:8000/api/admin/users \
  -H "Authorization: Bearer $TOKEN"

# Expected: 200 OK with users list
```

### Test Scenario 2: User Trying Admin Access

```bash
# 1. Login as regular user
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' \
  | jq -r '.access_token')

# 2. Try to access admin endpoint (should fail)
curl -X GET http://127.0.0.1:8000/api/admin/users \
  -H "Authorization: Bearer $TOKEN"

# Expected: 403 Forbidden with error message
```

### Test Scenario 3: Invalid Token

```bash
curl -X GET http://127.0.0.1:8000/api/admin/users \
  -H "Authorization: Bearer invalid-token-123"

# Expected: 401 Unauthenticated
```

---

## Best Practices

### ✅ DO

```php
// ✅ Clear route grouping
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role.check:admin')->group(function () {
        Route::apiResource('users', UserController::class);
    });
});

// ✅ Meaningful role names
'role.check:manager,supervisor' // Clear intent

// ✅ Consistent ordering
// 1. auth:sanctum (authentication)
// 2. role.check:* (authorization)
// 3. custom middleware
```

### ❌ DON'T

```php
// ❌ Avoid hardcoding in controller
public function destroy($id) {
    if ($this->user->role !== 'admin') {
        return 403;
    }
}

// ❌ Avoid inconsistent role names
'role.check:adm' // Confusing abbreviation

// ❌ Avoid mixing middleware order
Route::middleware('role.check:admin', 'auth:sanctum') // Wrong order
```

---

## Troubleshooting

### Issue 1: "Class not found" Error

```
Error: Class App\Http\Middleware\RoleMiddleware not found
```

**Solution:**
- Pastikan file ada di `app/Http/Middleware/RoleMiddleware.php`
- Run `composer dump-autoload`

### Issue 2: 403 Ketika Should 200

```
Seharusnya user admin bisa akses, tapi dapat 403
```

**Solution:**
- Verify token user sudah login
- Check user.role = 'admin' via Tinker:
  ```php
  User::find(1)->role // should be 'admin'
  ```

### Issue 3: Middleware Not Being Called

```
Route tidak di-protect, semua bisa akses
```

**Solution:**
- Check `bootstrap/app.php` middleware registration
- Verify route syntax `middleware('role.check:admin')`

---

## Summary

| Aspek | Detail |
|-------|--------|
| **Purpose** | Advanced role-based authorization middleware |
| **Usage** | `Route::middleware('role.check:role1,role2')` |
| **Variadic** | ✅ Multiple roles supported |
| **Performance** | ✅ Minimal overhead |
| **Maintainability** | ✅ Centralized in routes |
| **Security** | ✅ Prevents unauthorized access |

---

**Next Steps:**
1. ✅ Created RoleMiddleware class
2. ✅ Registered in bootstrap/app.php
3. ✅ Updated routes/api.php
4. ➡️ Test all endpoints with role protection
5. ➡️ Create advanced authorization scenarios

