# Laravel API Role - Testing Guide

Server running at: http://127.0.0.1:8000

## 1. Register User

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

Expected Response:
```json
{
  "message": "User registered successfully",
  "user": { "id": 6, "name": "John Doe", "email": "john@example.com", "role": "user", "..." },
  "token": "token_string_here"
}
```

## 2. Login User

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

Expected Response:
```json
{
  "message": "Login successful",
  "user": { "id": 1, "name": "Admin User", "email": "admin@example.com", "role": "admin", "..." },
  "token": "token_string_here"
}
```

## 3. Get Current User (Authenticated)

```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 4. List All Users (Admin Only)

```bash
curl -X GET http://127.0.0.1:8000/api/users \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

Will return 403 Forbidden if user is not admin.

## 5. Get Specific User

```bash
curl -X GET http://127.0.0.1:8000/api/users/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 6. Update User

```bash
curl -X PUT http://127.0.0.1:8000/api/users/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "name": "Updated Name",
    "email": "updated@example.com"
  }'
```

## 7. Delete User (Admin Only)

```bash
curl -X DELETE http://127.0.0.1:8000/api/users/1 \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

## 8. Logout

```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## Test Users Created

- **Admin User**
  - Email: admin@example.com
  - Password: password
  - Role: admin

- **Test User**
  - Email: test@example.com
  - Password: password
  - Role: user

- **Additional Users** (3 more regular users with auto-generated credentials)

---

## API Features Implemented

1. ✅ User Registration with default 'user' role
2. ✅ User Login with token generation
3. ✅ User Logout (revokes token)
4. ✅ Get Current User (authenticated)
5. ✅ User CRUD Operations
6. ✅ Role-based Access Control (admin-only endpoints)
7. ✅ Sanctum Token Authentication
8. ✅ Role Middleware for authorization
