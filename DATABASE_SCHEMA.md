# Database Schema & Relations

## ERD (Entity Relationship Diagram)

```
┌─────────────────────────┐
│      users              │
├─────────────────────────┤
│ id (PK)                 │
│ name                    │
│ email (UNIQUE)          │
│ password                │
│ role (user | admin)     │◄──────────┐
│ email_verified_at       │           │
│ remember_token          │           │ 1:N
│ created_at              │           │
│ updated_at              │           │
└─────────────────────────┘           │
                                      │
                            ┌─────────────────────────┐
                            │      todos              │
                            ├─────────────────────────┤
                            │ id (PK)                 │
                            │ user_id (FK)───────────┘
                            │ title                   │
                            │ description             │
                            │ completed               │
                            │ created_at              │
                            │ updated_at              │
                            └─────────────────────────┘

┌──────────────────────────────────────────┐
│   personal_access_tokens (Sanctum)       │
├──────────────────────────────────────────┤
│ id (PK)                                  │
│ tokenable_id (FK → users.id)             │
│ tokenable_type = 'App\Models\User'       │
│ name                                     │
│ token (UNIQUE, hashed)                   │
│ abilities (JSON nullable)                │
│ last_used_at                             │
│ expires_at (nullable)                    │
│ created_at                               │
│ updated_at                               │
└──────────────────────────────────────────┘
```

---

## Tables Detailed

### users Table
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    role VARCHAR(255) DEFAULT 'user',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
```

### todos Table
```sql
CREATE TABLE todos (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NULL,
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_todos_user_id ON todos(user_id);
CREATE INDEX idx_todos_completed ON todos(completed);
```

### personal_access_tokens Table
```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    tokenable_type VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(80) UNIQUE NOT NULL,
    abilities LONGTEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Indexes
CREATE INDEX idx_tokens_tokenable ON personal_access_tokens(tokenable_type, tokenable_id);
CREATE INDEX idx_tokens_token ON personal_access_tokens(token);
```

---

## Model Relationships

### User Model Relations
```php
class User extends Authenticatable {
    /**
     * User memiliki banyak todos
     * Relationship: One-to-Many
     */
    public function todos() {
        return $this->hasMany(Todo::class);
    }
    
    /**
     * User memiliki banyak API tokens (dari Sanctum)
     * Inherited dari HasApiTokens trait
     */
    public function tokens() {
        return $this->morphMany(PersonalAccessToken::class, 'tokenable');
    }
}
```

### Todo Model Relations
```php
class Todo extends Model {
    /**
     * Todo milik satu user
     * Relationship: Many-to-One
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

---

## Data Flow Examples

### Example 1: Create Todo
```
Client sends POST /api/todos with auth token
    ↓
Token validated → Find user from token
    ↓
Create Todo with user_id = authenticated user's id
    ↓
Todo inserted into database with:
  - user_id: 2 (authenticated user)
  - title: "My Todo"
  - description: "Buy milk"
  - completed: false
    ↓
Return created todo with 201 status
```

### Example 2: List User's Todos
```
Client sends GET /api/todos with auth token
    ↓
Token validated → Get user from token (e.g., id=2)
    ↓
If user role = 'admin':
  - Return: SELECT * FROM todos
Else:
  - Return: SELECT * FROM todos WHERE user_id = 2
    ↓
Return todos collection with 200 status
```

### Example 3: Update Todo
```
Client sends PUT /api/todos/4 with auth token
    ↓
Token validated → Get user from token (e.g., id=2)
    ↓
Find todo with id = 4
    ↓
Check authorization:
  - If user role = 'admin' OR todo.user_id = user.id:
    → Update allowed
  - Else:
    → Return 403 Forbidden
    ↓
If allowed: UPDATE todos SET ... WHERE id = 4
    ↓
Return updated todo with 200 status
```

---

## Query Examples

### Get User with All Todos
```php
$user = User::with('todos')->find(1);
// SQL: SELECT * FROM users WHERE id = 1
// SQL: SELECT * FROM todos WHERE user_id = 1
```

### Get Todo with Owner Info
```php
$todo = Todo::with('user')->find(1);
// SQL: SELECT * FROM todos WHERE id = 1
// SQL: SELECT * FROM users WHERE id = [user_id from todo]
```

### Get All Users with Their Todos (Admin)
```php
$users = User::with('todos')->get();
// SQL: SELECT * FROM users
// SQL: SELECT * FROM todos WHERE user_id IN (1,2,3,4,5)
```

### Get Completed Todos by User
```php
$completedTodos = Todo::where('user_id', 2)
                        ->where('completed', true)
                        ->get();
// SQL: SELECT * FROM todos WHERE user_id = 2 AND completed = 1
```

---

## Constraints & Integrity

### Foreign Key Constraint
```
Constraint: todos.user_id → users.id
Type: ON DELETE CASCADE
Effect: When user deleted, all their todos are also deleted
```

### Unique Constraints
```
users.email - Must be unique (no duplicate emails)
personal_access_tokens.token - Must be unique (each token unique)
```

### Default Values
```
users.role - DEFAULT 'user' (new users are regular users)
todos.completed - DEFAULT FALSE (todos start incomplete)
```

---

## Sample Data Structure

### User Record
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "$2y$12$...",
  "role": "admin",
  "email_verified_at": "2025-11-29T02:27:40Z",
  "remember_token": "abcd1234",
  "created_at": "2025-11-29T02:27:40Z",
  "updated_at": "2025-11-29T02:27:40Z"
}
```

### Todo Record
```json
{
  "id": 1,
  "user_id": 1,
  "title": "Setup project",
  "description": "Initialize the Laravel API project",
  "completed": true,
  "created_at": "2025-11-29T02:32:34Z",
  "updated_at": "2025-11-29T02:32:34Z"
}
```

### Access Token Record
```json
{
  "id": 1,
  "tokenable_type": "App\\Models\\User",
  "tokenable_id": 1,
  "name": "auth_token",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "abilities": ["*"],
  "last_used_at": "2025-11-29T10:30:00Z",
  "expires_at": null,
  "created_at": "2025-11-29T10:30:00Z",
  "updated_at": "2025-11-29T10:30:00Z"
}
```

---

## Migration Timeline

```
2025-11-29 02:27:00 - Create users table
2025-11-29 02:27:00 - Create cache table
2025-11-29 02:27:00 - Create jobs table
2025-11-29 02:21:00 - Add role column to users
2025-11-29 02:22:00 - Create personal_access_tokens table
2025-11-29 02:29:00 - Create todos table

Status: All migrations completed ✅
```

---

## Database Optimization

### Indexes Created
```sql
-- For quick email lookup during login
CREATE INDEX idx_users_email ON users(email);

-- For role-based filtering
CREATE INDEX idx_users_role ON users(role);

-- For user's todo queries
CREATE INDEX idx_todos_user_id ON todos(user_id);

-- For completed status filtering
CREATE INDEX idx_todos_completed ON todos(completed);

-- For token lookups (Sanctum)
CREATE INDEX idx_tokens_token ON personal_access_tokens(token);
```

### Query Performance Tips
1. Always use `with()` for eager loading relations
2. Filter by `user_id` when getting todos (uses index)
3. Use `select()` to only get needed columns
4. Paginate large result sets
5. Use `whereIn()` instead of multiple `where()` for role checks

---

## Foreign Key Behavior

### Cascade Delete
```
When user is deleted:
- All their todos are automatically deleted
- All their access tokens are revoked
- No orphaned records remain

Example:
DELETE FROM users WHERE id = 5;
-- Triggers automatic deletion of:
-- - All todos where user_id = 5
-- - All tokens where tokenable_id = 5
```

---

## Current Database State

### Active Records
- Users: 5 total
  - 1 admin user
  - 4 regular users
  
- Todos: 6 total
  - Admin: 3 todos
  - Test user: 3 todos
  - Other users: 0 todos
  
- Personal Access Tokens: Multiple (generated during login)

---

## Database Maintenance

### Backup
```bash
# Export database
php artisan db:seed
# Or backup SQLite file directly
cp database/database.sqlite database/database.sqlite.backup
```

### Restore
```bash
# Reset database
php artisan migrate:reset

# Re-run migrations
php artisan migrate

# Re-seed data
php artisan db:seed
```

### Clear Old Tokens
```php
// Remove expired tokens
PersonalAccessToken::where('expires_at', '<', now())->delete();

// Remove all tokens for a user
$user->tokens()->delete();
```

---

**Database Design Summary:**
✅ Normalized schema with proper relationships
✅ Cascading deletes for data integrity
✅ Efficient indexes for common queries
✅ Foreign key constraints enforced
✅ Ready for production use
