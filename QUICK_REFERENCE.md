# Quick API Reference

## Server
```
http://127.0.0.1:8000
```

## Test Credentials
```
Admin:
  Email: admin@example.com
  Password: password
  Token: [get from login response]

User:
  Email: test@example.com
  Password: password
  Token: [get from login response]
```

---

## Quick Test Commands

### 1. Get Admin Token
```powershell
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/login" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"email":"admin@example.com","password":"password"}'
  
$data = $response.Content | ConvertFrom-Json
$adminToken = $data.token
Write-Output "Admin Token: $adminToken"
```

### 2. List All Users (Admin)
```powershell
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/admin/users" `
  -Method GET `
  -Headers @{"Authorization" = "Bearer $adminToken"}
  
$response.Content | ConvertFrom-Json | ConvertTo-Json
```

### 3. Create Todo (User)
```powershell
$userResponse = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/login" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"email":"test@example.com","password":"password"}'
  
$userData = $userResponse.Content | ConvertFrom-Json
$userToken = $userData.token

$todo = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/todos" `
  -Method POST `
  -ContentType "application/json" `
  -Headers @{"Authorization" = "Bearer $userToken"} `
  -Body '{"title":"My Todo","description":"Test todo"}'
  
$todo.Content | ConvertFrom-Json | ConvertTo-Json
```

### 4. Get All Todos (User)
```powershell
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/todos" `
  -Method GET `
  -Headers @{"Authorization" = "Bearer $userToken"}
  
$response.Content | ConvertFrom-Json | ConvertTo-Json
```

### 5. View All Todos (Admin)
```powershell
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/admin/todos" `
  -Method GET `
  -Headers @{"Authorization" = "Bearer $adminToken"}
  
$response.Content | ConvertFrom-Json | ConvertTo-Json
```

---

## Endpoint Cheat Sheet

| Method | Endpoint | Auth | Role | Purpose |
|--------|----------|------|------|---------|
| POST | /api/register | - | - | Register |
| POST | /api/login | - | - | Login |
| POST | /api/logout | ✅ | - | Logout |
| GET | /api/me | ✅ | - | Get me |
| GET | /api/users | ✅ | - | My profile |
| PUT | /api/users/{id} | ✅ | - | Update me |
| DELETE | /api/users/{id} | ✅ | - | Delete me |
| GET | /api/admin/users | ✅ | Admin | List all |
| GET | /api/admin/users/{id} | ✅ | Admin | View user |
| POST | /api/admin/users/{id}/assign-role | ✅ | Admin | Assign role |
| GET | /api/todos | ✅ | - | My todos |
| POST | /api/todos | ✅ | - | Create |
| GET | /api/todos/{id} | ✅ | - | Get |
| PUT | /api/todos/{id} | ✅ | - | Update |
| DELETE | /api/todos/{id} | ✅ | - | Delete |
| GET | /api/admin/todos | ✅ | Admin | All todos |
| GET | /api/admin/todos/{id} | ✅ | Admin | Get todo |

---

## Common Issues & Solutions

### Issue: "Unauthorized" on protected routes
**Solution:** Make sure you include the token in Authorization header
```
Authorization: Bearer YOUR_TOKEN_HERE
```

### Issue: "Token not found"
**Solution:** Login first to get token
```powershell
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/login" `
  -Method POST `
  -ContentType "application/json" `
  -Body '{"email":"test@example.com","password":"password"}'
```

### Issue: "Forbidden" on admin endpoints
**Solution:** You need admin role. Try with admin account or ask admin to promote you

### Issue: "Unauthorized" when updating other user's todo
**Solution:** Users can only update/delete their own todos. Admin can modify any.

### Issue: Server not running
**Solution:** Start server:
```bash
cd "c:\latihan laravel 3\laravel_api_role"
php artisan serve
```

---

## Notes
- All requests use JSON format
- Token expires automatically based on sanctum config
- Passwords must be 8+ characters
- Emails must be unique
- Default role on register: 'user'
- Admin can change user roles anytime
