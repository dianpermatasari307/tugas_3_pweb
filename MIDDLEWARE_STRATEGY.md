# 🔐 MIDDLEWARE STRATEGY GUIDE

## Middleware Options di Project Ini

Project `laravel_api_role` memiliki **2 opsi middleware** untuk role-based authorization:

---

## Option 1: CheckRole (Simple & Direct)

### Karakteristik
- ✅ Simple untuk single role check
- ✅ Direct dalam controller
- ❌ Repeatitive untuk multiple endpoints

### File
```
app/Http/Middleware/CheckRole.php
```

### Cara Pakai

**Di Route:**
```php
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/users', [UserController::class, 'indexAll']);
});
```

**Di Controller (Alternative):**
```php
if ($request->user()->role !== 'admin') {
    return response()->json(['message' => 'Forbidden'], 403);
}
```

### Keunggulan
- Simple dan mudah dipahami
- Cocok untuk sederhana checks
- Minimal overhead

### Kekurangan
- Hardcoded di setiap endpoint
- Tidak support multiple roles mudah
- Sulit untuk maintain di project besar

---

## Option 2: RoleMiddleware (Advanced & Flexible)

### Karakteristik
- ✅ Powerful dengan variadic parameters
- ✅ Support multiple roles langsung
- ✅ DRY principle (Don't Repeat Yourself)
- ✅ Scalable untuk project besar

### File
```
app/Http/Middleware/RoleMiddleware.php
```

### Cara Pakai

**Single Role:**
```php
Route::middleware('role.check:admin')->group(function () {
    Route::get('/admin/users', [UserController::class, 'indexAll']);
});
```

**Multiple Roles:**
```php
Route::middleware('role.check:admin,moderator')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Keunggulan
- Variadic parameters → multiple roles
- Deklaratif & jelas di routes
- DRY principle
- Mudah maintain & scale

### Kekurangan
- Sedikit lebih kompleks
- Minimal performance overhead

---

## Comparison Matrix

```
┌──────────────────────┬──────────────┬─────────────────┐
│ Feature              │ CheckRole    │ RoleMiddleware  │
├──────────────────────┼──────────────┼─────────────────┤
│ Single Role          │ ✅ Simple    │ ✅ Simple       │
│ Multiple Roles       │ ❌ Difficult │ ✅ Easy         │
│ DRY Principle        │ ❌ No        │ ✅ Yes          │
│ Code Reuse           │ ❌ Repeated  │ ✅ Centralized  │
│ Readability          │ ⚠️ Mixed     │ ✅ Clear        │
│ Maintenance          │ ❌ Scattered │ ✅ Organized    │
│ Performance          │ ✅ Minimal   │ ✅ Minimal      │
│ Learning Curve       │ ✅ Easy      │ ⚠️ Medium       │
│ Project Scale        │ ⚠️ Small     │ ✅ Large        │
└──────────────────────┴──────────────┴─────────────────┘
```

---

## Implementation Status

### ✅ Current Project Status

```
├─ CheckRole.php .................... ✅ Implemented & Used
│   └─ Used in: routes/api.php (legacy route grouping)
│
└─ RoleMiddleware.php ............... ✅ Implemented & Ready
    └─ Registered as: 'role.check' in bootstrap/app.php
    └─ Ready to use in: Any route
```

### Routes Configuration

**Current routes/api.php:**
```php
// Legacy style with CheckRole
Route::middleware('role:admin')->group(function () {
    Route::get('/admin/users', [UserController::class, 'indexAll']);
});

// New style with RoleMiddleware
Route::middleware('role.check:admin')->group(function () {
    // Future admin routes can use this
});
```

---

## Migration Path

### Phase 1: Current State (Mixed)
- ✅ CheckRole used in existing routes
- ✅ RoleMiddleware available & documented

### Phase 2: Gradual Migration
- Keep CheckRole for backward compatibility
- Migrate new routes to RoleMiddleware
- Test thoroughly

### Phase 3: Modern Best Practice
- Use RoleMiddleware everywhere
- Keep CheckRole for special cases only
- Archive CheckRole documentation

---

## Decision Guide

### Use CheckRole When:
```
✅ Simple single-role check
✅ Legacy code
✅ Minimal authorization needed
✅ Small project with few endpoints
```

### Use RoleMiddleware When:
```
✅ Multiple roles need access
✅ Project will scale
✅ Multiple similar endpoints
✅ Team collaboration
✅ Modern best practices
✅ Production API
```

---

## Code Examples

### Scenario 1: User Management (Multiple Roles Needed)

**❌ With CheckRole (Clunky):**
```php
Route::middleware('role:admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});

// Problem: What if moderators also need access?
// Need separate route or custom logic
```

**✅ With RoleMiddleware (Clean):**
```php
Route::middleware('role.check:admin,moderator')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

### Scenario 2: Dashboard Access (Multiple Endpoint Groups)

**❌ With CheckRole (Repetitive):**
```php
Route::middleware('role:manager')->get('/dashboard/sales', [...]);
Route::middleware('role:manager')->get('/dashboard/revenue', [...]);
Route::middleware('role:manager')->get('/dashboard/stats', [...]);
Route::middleware('role:manager')->get('/dashboard/reports', [...]);
```

**✅ With RoleMiddleware (DRY):**
```php
Route::middleware('role.check:manager')->group(function () {
    Route::get('/dashboard/sales', [...]);
    Route::get('/dashboard/revenue', [...]);
    Route::get('/dashboard/stats', [...]);
    Route::get('/dashboard/reports', [...]);
});
```

### Scenario 3: Multi-Tier Authorization

**With RoleMiddleware (Hierarchical):**
```php
Route::middleware('auth:sanctum')->group(function () {
    // Tier 1: All users
    Route::apiResource('todos', TodoController::class);
    
    // Tier 2: Moderators
    Route::middleware('role.check:moderator')->group(function () {
        Route::get('/moderation/reports', [ModerationController::class, 'reports']);
    });
    
    // Tier 3: Admins
    Route::middleware('role.check:admin')->group(function () {
        Route::apiResource('admin/users', UserController::class);
        Route::apiResource('admin/settings', SettingController::class);
    });
    
    // Tier 4: Super Admin
    Route::middleware('role.check:super-admin')->group(function () {
        Route::delete('/admin/purge-all-data', [AdminController::class, 'purgeAll']);
    });
});
```

---

## Performance Considerations

### Middleware Overhead
```
CheckRole:        ~0.1ms
RoleMiddleware:   ~0.1ms
Database Query:   ~10ms

→ Middleware overhead is NEGLIGIBLE
```

### Best Practices
1. ✅ Use eager loading for user relationships
2. ✅ Cache role data if needed
3. ✅ Avoid N+1 queries in controllers
4. ✅ Use middleware for initial filtering

---

## Testing Both Options

### Test CheckRole

```bash
# Setup
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.access_token')

# Test - Should succeed
curl -X GET http://127.0.0.1:8000/api/admin/users \
  -H "Authorization: Bearer $TOKEN"
```

### Test RoleMiddleware

```bash
# Setup (same token as above)

# Test with admin - Should succeed (200)
curl -X GET http://127.0.0.1:8000/api/admin/todos \
  -H "Authorization: Bearer $TOKEN"

# Setup with non-admin token
USER_TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' | jq -r '.access_token')

# Test with user - Should fail (403)
curl -X GET http://127.0.0.1:8000/api/admin/todos \
  -H "Authorization: Bearer $USER_TOKEN"
```

---

## Recommendations

### For This Project

```
✅ Current Status:
- CheckRole: Keep for existing routes (backward compatible)
- RoleMiddleware: Use for new features

✅ Future Refactor:
1. Keep both options documented
2. Gradually migrate to RoleMiddleware
3. Archive CheckRole when fully migrated
4. Document pattern for team
```

### For New Features

```
➡️ Always use RoleMiddleware:
- 'role.check:admin' for single role
- 'role.check:admin,moderator' for multiple roles
- Combine with 'auth:sanctum' for complete protection
```

---

## Summary

| Aspect | CheckRole | RoleMiddleware |
|--------|-----------|----------------|
| **Use Case** | Simple, single-role | Advanced, multiple roles |
| **Best For** | Small projects | Large, scalable projects |
| **Current Status** | ✅ Used | ✅ Ready |
| **Recommendation** | Legacy | Modern Best Practice |
| **Next Step** | Keep existing | Use for new features |

---

## Quick Reference

### CheckRole Alias
```php
'role' => \App\Http\Middleware\CheckRole::class
```

### RoleMiddleware Alias
```php
'role.check' => \App\Http\Middleware\RoleMiddleware::class
```

### Usage
```php
// CheckRole
Route::middleware('role:admin')

// RoleMiddleware
Route::middleware('role.check:admin')
Route::middleware('role.check:admin,moderator')
```

---

**Next Phase:** Implement advanced authorization scenarios using RoleMiddleware!

