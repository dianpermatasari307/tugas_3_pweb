# Setup Guide - Langkah Demi Langkah

**Panduan lengkap untuk setup dan menjalankan Laravel API Role project**

---

## 📋 Prerequisites

Sebelum memulai, pastikan Anda memiliki:

### Software Requirements
- [ ] PHP 8.2+ (`php -v`)
- [ ] Composer (`composer -v`)
- [ ] Git (optional)
- [ ] Text Editor/IDE (VSCode, PHPStorm, etc)
- [ ] cURL atau Postman (untuk testing API)

### Verifikasi Installation
```bash
# Check PHP version
php -v

# Check Composer version
composer -v

# Check PHP extensions
php -m | find "sqlite"  # Untuk SQLite
php -m | find "mysqli"  # Untuk MySQL
```

---

## 🚀 Setup Steps

### Step 1: Navigate to Project Directory
```bash
cd "c:\latihan laravel 3\laravel_api_role"
```

**Verify:**
```bash
# You should see these files
ls -la
# Should show: artisan, composer.json, .env, etc
```

---

### Step 2: Install Dependencies
```bash
composer install
```

**What happens:**
- Downloads ~300+ packages
- Creates vendor/ directory
- Generates autoload files
- Takes 2-5 minutes

**Verify:**
```bash
# Check if vendor directory exists
ls vendor
# Should contain: laravel, symfony, monolog, etc
```

---

### Step 3: Environment Setup
```bash
# If .env doesn't exist, copy from example
cp .env.example .env

# Generate application key (already done, but verify)
php artisan key:generate
```

**Check .env file contains:**
```
APP_NAME=Laravel
APP_KEY=base64:HBYB1L3AowaL8EsifFyImgLZ1ANxARgLDF4ONoA0dfg=
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
DB_CONNECTION=sqlite
```

---

### Step 4: Database Setup

#### Option A: SQLite (Default)
```bash
# Database file should be created automatically
# Check if exists
ls database/database.sqlite

# If not exists, create it
touch database/database.sqlite
```

#### Option B: MySQL
```bash
# Update .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel_api_role
# DB_USERNAME=root
# DB_PASSWORD=

# Create database
# MySQL: CREATE DATABASE laravel_api_role;
```

---

### Step 5: Run Migrations
```bash
php artisan migrate
```

**Output expected:**
```
INFO  Running migrations.

  0001_01_01_000000_create_users_table ................... ✓
  0001_01_01_000001_create_cache_table ................... ✓
  0001_01_01_000002_create_jobs_table .................... ✓
  2025_11_29_021837_add_role_to_users_table .............. ✓
  2025_11_29_022000_create_personal_access_tokens_table .. ✓
  2025_11_29_022929_create_todos_table ................... ✓
```

**Verify:**
```bash
php artisan tinker
> Schema::getTables()
# Should show: users, todos, personal_access_tokens, etc
> exit
```

---

### Step 6: Seed Database
```bash
php artisan db:seed
```

**Output expected:**
```
INFO  Seeding database.

# Data created successfully
```

**Verify:**
```bash
php artisan tinker
> User::count()
# Should return: 5
> Todo::count()
# Should return: 6
> exit
```

---

### Step 7: Verify Installation
```bash
# Check all files are in place
php artisan --version
# Should show: Laravel Framework 12.40.2

# Check database
php artisan tinker
> User::all()
> Todo::all()
> exit
```

---

### Step 8: Start Development Server
```bash
php artisan serve
```

**Output expected:**
```
Starting Laravel development server: http://127.0.0.1:8000
[timestamp] INFO Server running on [http://127.0.0.1:8000]
```

**Server is now running! ✅**

---

## ✅ Verification Checklist

### Installation Complete
- [ ] Project directory accessible
- [ ] Dependencies installed (vendor folder exists)
- [ ] .env file configured
- [ ] Database migrations completed
- [ ] Seed data created
- [ ] Development server running

### Database Verification
- [ ] 5 users created
- [ ] 6 todos created
- [ ] Tables visible in database

### Server Verification
- [ ] Server running on http://127.0.0.1:8000
- [ ] No errors in console
- [ ] Port 8000 is free

---

## 🧪 First Test

### Test 1: Login as Admin
```bash
# Open another terminal
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

**Expected Response:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "role": "admin"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI..."
}
```

### Test 2: Get Current User
```bash
# Replace YOUR_TOKEN with token from login response
curl -X GET http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
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

### Test 3: List Todos
```bash
curl -X GET http://127.0.0.1:8000/api/todos \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "message": "Todos retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "title": "Setup project",
      "completed": true
    },
    ...
  ]
}
```

---

## 🔧 Common Setup Issues

### Issue 1: Composer Command Not Found
```
Error: 'composer' is not recognized
```

**Solution:**
- Install Composer from https://getcomposer.org/download/
- Add to PATH environment variable
- Restart terminal/IDE

### Issue 2: PHP Version Error
```
Error: requires php: ^8.2
```

**Solution:**
- Update PHP to version 8.2 or higher
- Verify: `php -v`

### Issue 3: Database Error
```
Error: database.sqlite not writable
```

**Solution:**
```bash
# Check permissions
ls -la database/

# Create if missing
touch database/database.sqlite

# Fix permissions (Windows)
# Right-click folder → Properties → Security → Edit
```

### Issue 4: Port 8000 Already in Use
```
Error: The server is already running or address is in use
```

**Solution:**
```bash
# Use different port
php artisan serve --port=8001

# Or kill process using port 8000
lsof -ti:8000 | xargs kill -9  # Mac/Linux
netstat -ano | findstr :8000   # Windows
```

### Issue 5: Token Validation Error
```
Error: Unauthenticated
```

**Solution:**
- Make sure you're using token from login response
- Token should be in Authorization header: `Bearer TOKEN`
- Token may have expired

### Issue 6: "File .env" Not Found
```
Error: The file .env does not exist
```

**Solution:**
```bash
# Copy from example
cp .env.example .env

# Or create new
php artisan env:set APP_KEY base64:HBYB...
```

---

## 📚 Next Steps After Setup

### 1. Read Documentation
```bash
# Start with
cat INDEX.md
cat FINAL_STATUS.md
cat QUICK_REFERENCE.md
```

### 2. Test All Endpoints
```bash
# See TESTING_GUIDE.md for all examples
cat TESTING_GUIDE.md
```

### 3. Explore Code
```bash
# Check controllers
ls -la app/Http/Controllers/Api/

# Check models
ls -la app/Models/

# Check routes
cat routes/api.php
```

### 4. Database Exploration
```bash
# Open Tinker
php artisan tinker

# Explore data
> User::with('todos')->get()
> Todo::with('user')->get()
> User::find(1)->tokens()->count()

# Exit
> exit
```

---

## 🎓 Learning Resources

### Official Documentation
- Laravel: https://laravel.com/docs/12.x
- Sanctum: https://laravel.com/docs/12.x/sanctum
- Eloquent: https://laravel.com/docs/12.x/eloquent

### Project Documentation
- `INDEX.md` - Main navigation
- `IMPLEMENTATION_GUIDE.md` - Architecture
- `TESTING_GUIDE.md` - API testing
- `DATABASE_SCHEMA.md` - Database design

### Video Tutorials
- Laravel Basics: https://laravel.com/learn
- API Development: Search on YouTube/Laracasts

---

## 🚀 Development Workflow

### Daily Development
```bash
# 1. Navigate to project
cd "c:\latihan laravel 3\laravel_api_role"

# 2. Start server
php artisan serve

# 3. Open in browser/API client
# http://127.0.0.1:8000

# 4. Make changes to code
# Server auto-reloads

# 5. Stop server (Ctrl+C)
```

### Testing Workflow
```bash
# 1. Start server
php artisan serve

# 2. In another terminal, run tests
curl -X GET http://127.0.0.1:8000/api/todos \
  -H "Authorization: Bearer TOKEN"

# 3. Check responses
```

### Database Reset
```bash
# Reset all data and reseed
php artisan migrate:reset
php artisan migrate --seed

# Or fresh install
php artisan migrate:fresh --seed
```

---

## 📊 Project Structure Overview

```
laravel_api_role/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   └── TodoController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   └── Models/
│       ├── User.php
│       └── Todo.php
│
├── database/
│   ├── database.sqlite (your database file)
│   ├── migrations/
│   └── seeders/
│
├── routes/
│   └── api.php
│
├── bootstrap/
│   └── app.php
│
├── vendor/ (created by composer)
│
├── .env (created from .env.example)
├── composer.json
├── artisan
└── *.md (documentation files)
```

---

## ✨ You're Ready!

After completing this setup guide:

✅ Project is fully installed  
✅ Database is seeded with test data  
✅ Development server is running  
✅ API is ready for testing  
✅ Documentation is available  

### What to do next:
1. Read `FINAL_STATUS.md` for overview
2. Read `QUICK_REFERENCE.md` for quick lookup
3. Test endpoints using `TESTING_GUIDE.md`
4. Explore code in `app/` directory
5. Modify and extend as needed

---

## 🎉 Setup Complete!

**Congratulations!** Your Laravel API Role project is now ready for development.

For questions or issues, check:
- `QUICK_REFERENCE.md` → Common Issues section
- `TESTING_GUIDE.md` → Endpoint examples
- `IMPLEMENTATION_GUIDE.md` → Architecture details

Happy coding! 🚀

---

**Setup Guide Generated:** November 29, 2025  
**Laravel Version:** 12.40.2  
**Estimated Setup Time:** 10-15 minutes  
**Difficulty Level:** Beginner-Friendly ✅
