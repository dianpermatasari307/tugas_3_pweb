# Laravel API Role - Complete Implementation

## Project Overview

Ini adalah implementasi lengkap dari tutorial **Laravel API Role** dengan fitur:
- ✅ User Authentication (Register, Login, Logout)
- ✅ Role-Based Authorization (Admin vs User)
- ✅ Todo Management dengan ownership
- ✅ Sanctum Token Authentication
- ✅ User Profile Management
- ✅ Admin Dashboard untuk manage users & todos

---

## Database Schema

### Users Table
```
id (PK)
name
email (UNIQUE)
password (hashed)
role (user|admin)
created_at
updated_at
```

### Todos Table
```
id (PK)
user_id (FK → users)
title
description (nullable)
completed (boolean)
created_at
updated_at
```

### Personal Access Tokens Table
```
id (PK)
tokenable_id
tokenable_type
name
token (UNIQUE)
abilities (nullable)
last_used_at (nullable)
expires_at (nullable)
created_at
updated_at
```

---

## API Endpoints Summary

### Authentication Routes (Public)
```
POST   /api/register                    - Register new user
POST   /api/login                       - Login user
```

### User Routes (Protected)
```
GET    /api/users                       - Get own profile (all authenticated users)
PUT    /api/users/{id}                  - Update own profile
DELETE /api/users/{id}                  - Delete own account
```

### Todo Routes (Protected)
```
GET    /api/todos                       - List own todos
POST   /api/todos                       - Create new todo
GET    /api/todos/{id}                  - Get specific todo
PUT    /api/todos/{id}                  - Update todo
DELETE /api/todos/{id}                  - Delete todo
```

### Admin Routes (Admin Only)
```
POST   /api/logout                      - Logout (all users)
GET    /api/me                          - Get current user data

Admin User Management:
GET    /api/admin/users                 - List all users with todos
GET    /api/admin/users/{id}            - Get specific user with todos
POST   /api/admin/users/{id}/assign-role - Change user role

Admin Todo Management:
GET    /api/admin/todos                 - View all todos
GET    /api/admin/todos/{id}            - View specific todo
```

---

## Architecture

### Controllers
- **AuthController** - Handle authentication (register, login, logout, me)
- **UserController** - Handle user profile & admin user management
- **TodoController** - Handle CRUD todos with authorization

### Models
- **User** - User model dengan HasApiTokens, HasMany todos
- **Todo** - Todo model dengan BelongsTo user

### Middleware
- **CheckRole** - Verify user has required role(s)
- **auth:sanctum** - Verify API token

### Traits
- **HasApiTokens** - Sanctum trait for API token generation

---

## Authorization Logic

### User Profile Access
- ✅ User dapat melihat & update profile mereka sendiri
- ❌ User tidak bisa melihat profile user lain
- ✅ Admin dapat melihat semua user profiles

### Todo Access
- ✅ User dapat CRUD todos mereka sendiri
- ❌ User tidak bisa akses todos user lain
- ✅ Admin dapat melihat & manage semua todos

### Admin Functions
- ✅ Assign/change user roles
- ✅ View semua users dengan todos mereka
- ✅ View semua todos di sistem
- ✅ Dapat mengakses admin endpoints

---

## Request/Response Format

### Success Response
```json
{
  "message": "Operation successful",
  "data": { /* resource data */ }
}
```

### Error Response - Unauthorized (401)
```json
{
  "message": "Unauthorized"
}
```

### Error Response - Forbidden (403)
```json
{
  "message": "Forbidden - You do not have access to this resource"
}
```

### Error Response - Validation (422)
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Setup & Deployment

### Prerequisites
- PHP 8.2+
- Laravel 12
- SQLite or MySQL
- Composer

### Installation Steps
```bash
# 1. Clone or setup project
cd "c:\latihan laravel 3\laravel_api_role"

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Run migrations & seeders
php artisan migrate --seed

# 5. Start development server
php artisan serve
```

### Test Data Created
- **Admin User**: admin@example.com / password
- **Test User**: test@example.com / password
- **Additional Users**: 3 more users
- **Todos**: Admin & test user memiliki beberapa todos

---

## Security Features

1. **Password Hashing** - Bcrypt hashing untuk semua passwords
2. **Token Authentication** - Sanctum API tokens
3. **CORS Protection** - Middleware untuk cross-origin requests
4. **Role-Based Access** - Custom middleware untuk role checking
5. **Email Validation** - Unique email constraint
6. **Token Expiration** - Optional token expiry settings
7. **Request Validation** - Laravel validation rules

---

## Testing Guide

Lihat file `TESTING_GUIDE.md` untuk:
- Complete API endpoint documentation
- cURL examples untuk setiap endpoint
- Authorization matrix
- Testing checklist

---

## File Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   └── TodoController.php
│   └── Middleware/
│       └── CheckRole.php
├── Models/
│   ├── User.php
│   └── Todo.php
│
database/
├── migrations/
│   ├── 2025_11_29_021837_add_role_to_users_table.php
│   ├── 2025_11_29_022000_create_personal_access_tokens_table.php
│   └── 2025_11_29_022929_create_todos_table.php
├── seeders/
│   ├── DatabaseSeeder.php
│   └── TodoSeeder.php
│
routes/
└── api.php

bootstrap/
└── app.php (middleware registration)
```

---

## Strategi Teknis yang Diimplementasikan

✅ **Tambahkan kolom `role` di migrasi baru**
  - Migration: `add_role_to_users_table.php`
  - Default value: 'user'

✅ **Penetapkan peran default saat mendaftar**
  - AuthController register method set role='user'

✅ **Membuat middleware `role:admin`**
  - CheckRole middleware dengan parameter roles
  - Registered di bootstrap/app.php

✅ **Membuat grup rute khusus admin**
  - routes/api.php dengan middleware('role:admin') group
  - Endpoints: /api/admin/users, /api/admin/todos

---

## Performance Considerations

- ✅ Use eager loading (with('todos')) untuk menghindari N+1 queries
- ✅ Indexed columns: id, user_id, email
- ✅ Token-based auth lebih scalable dari session
- ✅ API responses minimal dan efficient

---

## Future Enhancements

1. **Pagination** - Add pagination untuk list endpoints
2. **Filtering** - Filter todos by status, user
3. **Sorting** - Sort by date, name, status
4. **Rate Limiting** - Throttle requests per user
5. **Logging** - Log all admin actions
6. **Audit Trail** - Track changes to users & todos
7. **Notifications** - Notify on todo assignment
8. **Two Factor Auth** - Add 2FA support
9. **API Documentation** - Swagger/OpenAPI
10. **Unit Tests** - PHPUnit test suite

---

## Support & Documentation

- Laravel Docs: https://laravel.com/docs
- Sanctum Docs: https://laravel.com/docs/sanctum
- See `API_TESTING.md` for basic examples
- See `TESTING_GUIDE.md` for complete testing guide
- See `COMPLETION_REPORT.md` for initial implementation

---

## License

MIT License - Feel free to use this project as reference or base for your applications.
