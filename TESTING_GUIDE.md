# API Testing Guide - Role-Based Authorization

Server: http://127.0.0.1:8000

## Test Accounts

```
Admin Account:
- Email: admin@example.com
- Password: password
- Role: admin

User Account:
- Email: test@example.com
- Password: password
- Role: user
```

---

## 1. AUTHENTICATION ENDPOINTS

### 1.1 Register New User
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

**Response (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 6,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "created_at": "2025-11-29T..."
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

---

### 1.2 Login
```bash
# Admin Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'

# User Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password"
  }'
```

**Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin",
    "created_at": "2025-11-29T..."
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

---

### 1.3 Logout
```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### 1.4 Get Current User (Me)
```bash
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "Get current authenticated user",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  }
}
```

---

## 2. USER MANAGEMENT ENDPOINTS (All Users)

### 2.1 Get Own Profile
```bash
curl -X GET http://127.0.0.1:8000/api/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "User profile retrieved successfully",
  "user": {
    "id": 2,
    "name": "Test User",
    "email": "test@example.com",
    "role": "user"
  }
}
```

---

### 2.2 Update Own Profile
```bash
curl -X PUT http://127.0.0.1:8000/api/users/2 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer USER_TOKEN_HERE" \
  -d '{
    "name": "Updated Name",
    "email": "newemail@example.com",
    "password": "newpassword123"
  }'
```

**Response (200):**
```json
{
  "message": "User updated successfully",
  "user": {
    "id": 2,
    "name": "Updated Name",
    "email": "newemail@example.com",
    "role": "user"
  }
}
```

**Error (403) - Trying to update other user:**
```json
{
  "message": "Unauthorized - You can only update your own profile"
}
```

---

### 2.3 Delete Own Account
```bash
curl -X DELETE http://127.0.0.1:8000/api/users/2 \
  -H "Authorization: Bearer USER_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "User account deleted successfully"
}
```

---

## 3. ADMIN-ONLY USER MANAGEMENT

### 3.1 List All Users (Admin Only)
```bash
curl -X GET http://127.0.0.1:8000/api/admin/users \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "All users retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "admin",
      "todos": [...]
    },
    {
      "id": 2,
      "name": "Test User",
      "email": "test@example.com",
      "role": "user",
      "todos": [...]
    }
  ]
}
```

**Error (403) - Regular User:**
```json
{
  "message": "Forbidden - You do not have access to this resource"
}
```

---

### 3.2 Get Specific User (Admin Only)
```bash
curl -X GET http://127.0.0.1:8000/api/admin/users/2 \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "User retrieved successfully",
  "user": {
    "id": 2,
    "name": "Test User",
    "email": "test@example.com",
    "role": "user",
    "todos": [
      {
        "id": 4,
        "user_id": 2,
        "title": "Learn Laravel basics",
        "description": "Study Laravel fundamentals",
        "completed": false
      }
    ]
  }
}
```

---

### 3.3 Assign Role to User (Admin Only)
```bash
curl -X POST http://127.0.0.1:8000/api/admin/users/2/assign-role \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE" \
  -d '{
    "role": "admin"
  }'
```

**Response (200):**
```json
{
  "message": "Role assigned successfully",
  "user": {
    "id": 2,
    "name": "Test User",
    "email": "test@example.com",
    "role": "admin"
  }
}
```

---

## 4. TODO ENDPOINTS (User Can CRUD Own Todos)

### 4.1 Create Todo
```bash
curl -X POST http://127.0.0.1:8000/api/todos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "title": "Buy groceries",
    "description": "Milk, eggs, bread"
  }'
```

**Response (201):**
```json
{
  "message": "Todo created successfully",
  "data": {
    "user_id": 2,
    "title": "Buy groceries",
    "description": "Milk, eggs, bread",
    "completed": false,
    "id": 7,
    "created_at": "2025-11-29T..."
  }
}
```

---

### 4.2 List User's Todos
```bash
curl -X GET http://127.0.0.1:8000/api/todos \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "Todos retrieved successfully",
  "data": [
    {
      "id": 4,
      "user_id": 2,
      "title": "Learn Laravel basics",
      "description": "Study Laravel fundamentals",
      "completed": false,
      "created_at": "2025-11-29T..."
    },
    {
      "id": 5,
      "user_id": 2,
      "title": "Build a simple project",
      "description": "Create a simple todo app",
      "completed": false,
      "created_at": "2025-11-29T..."
    }
  ]
}
```

---

### 4.3 Get Single Todo
```bash
curl -X GET http://127.0.0.1:8000/api/todos/4 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "Todo retrieved successfully",
  "data": {
    "id": 4,
    "user_id": 2,
    "title": "Learn Laravel basics",
    "description": "Study Laravel fundamentals",
    "completed": false
  }
}
```

**Error (403) - Access other user's todo:**
```json
{
  "message": "Unauthorized"
}
```

---

### 4.4 Update Todo
```bash
curl -X PUT http://127.0.0.1:8000/api/todos/4 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "title": "Learn Laravel advanced",
    "completed": true
  }'
```

**Response (200):**
```json
{
  "message": "Todo updated successfully",
  "data": {
    "id": 4,
    "user_id": 2,
    "title": "Learn Laravel advanced",
    "description": "Study Laravel fundamentals",
    "completed": true,
    "updated_at": "2025-11-29T..."
  }
}
```

---

### 4.5 Delete Todo
```bash
curl -X DELETE http://127.0.0.1:8000/api/todos/4 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "Todo deleted successfully"
}
```

---

## 5. ADMIN-ONLY TODO MANAGEMENT

### 5.1 View All Todos (Admin Only)
```bash
curl -X GET http://127.0.0.1:8000/api/admin/todos \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "Todos retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "title": "Setup project",
      "description": "Initialize the Laravel API project",
      "completed": true,
      "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "role": "admin"
      }
    },
    {
      "id": 4,
      "user_id": 2,
      "title": "Learn Laravel basics",
      "description": "Study Laravel fundamentals",
      "completed": false,
      "user": {
        "id": 2,
        "name": "Test User",
        "email": "test@example.com",
        "role": "user"
      }
    }
  ]
}
```

---

### 5.2 View Specific Todo (Admin Only)
```bash
curl -X GET http://127.0.0.1:8000/api/admin/todos/1 \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

**Response (200):**
```json
{
  "message": "Todo retrieved successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "title": "Setup project",
    "description": "Initialize the Laravel API project",
    "completed": true
  }
}
```

---

## Authorization Matrix

| Feature | Public | User | Admin |
|---------|--------|------|-------|
| Register | ✅ | ✅ | ✅ |
| Login | ✅ | ✅ | ✅ |
| Get Own Profile | ❌ | ✅ | ✅ |
| Update Own Profile | ❌ | ✅ | ✅ |
| Delete Own Account | ❌ | ✅ | ✅ |
| CRUD Own Todos | ❌ | ✅ | ✅ |
| View All Users | ❌ | ❌ | ✅ |
| Assign Role | ❌ | ❌ | ✅ |
| View All Todos | ❌ | ❌ | ✅ |
| Manage Any Todo | ❌ | ❌ | ✅ |

---

## Testing Checklist

- [ ] Register as new user
- [ ] Login with both admin and user accounts
- [ ] Get current user profile
- [ ] Update own profile
- [ ] Create a todo
- [ ] List own todos
- [ ] Update own todo
- [ ] Mark todo as completed
- [ ] Delete own todo
- [ ] Try to access other user's todo (should fail)
- [ ] Login as admin
- [ ] List all users
- [ ] View specific user with todos
- [ ] Assign role to user
- [ ] View all todos as admin
- [ ] Try admin action as regular user (should fail)
- [ ] Logout
