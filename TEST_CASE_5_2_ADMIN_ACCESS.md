# Test Case 5.2 — Admin berhasil mengakses

Tujuan:
- Verifikasi bahwa pengguna dengan role `admin` berhasil mengakses endpoint:
  - `GET /api/admin/users`
  - `GET /api/admin/todos`

Persyaratan:
- Aplikasi berjalan di `http://127.0.0.1:8000`
- Terdapat akun admin: `admin@example.com` / `password`

Langkah (PowerShell):

1. Start server:

```powershell
cd "c:\latihan laravel 3\laravel_api_role"
php artisan serve --host=127.0.0.1 --port=8000
```

2. Jalankan script otomatis (direkomendasikan):

```powershell
cd "c:\latihan laravel 3\laravel_api_role"
.\scripts\run_admin_checks.ps1
```

Script akan:
- Login sebagai admin
- Memanggil `GET /api/admin/users` dan cetak hasil
- Memanggil `GET /api/admin/todos` dan cetak hasil

Langkah alternatif (manual) — PowerShell:

```powershell
# Login
$headers = @{ 'Content-Type' = 'application/json' }
$body = @{ email = 'admin@example.com'; password = 'password' } | ConvertTo-Json
$resp = Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/login' -Method POST -Headers $headers -Body $body
$token = $resp.access_token

# Get users
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/admin/users' -Headers @{ Authorization = "Bearer $token"; Accept = 'application/json' }

# Get todos
Invoke-RestMethod -Uri 'http://127.0.0.1:8000/api/admin/todos' -Headers @{ Authorization = "Bearer $token"; Accept = 'application/json' }
```

Langkah alternatif (curl):

```bash
# Login and extract token (requires jq)
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.access_token')

# Get users
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8000/api/admin/users

# Get todos
curl -H "Authorization: Bearer $TOKEN" http://127.0.0.1:8000/api/admin/todos
```

Hasil yang diharapkan:
- HTTP 200 OK untuk kedua endpoint
- `GET /api/admin/users` mengembalikan array user (id, name, email, role, created_at)
- `GET /api/admin/todos` mengembalikan array todos dengan relasi `user`

Jika mendapatkan `403`:
- Pastikan token milik user dengan role `admin`
- Periksa user role via Tinker:

```bash
php artisan tinker
>>> App\Models\User::where('email', 'admin@example.com')->first()->role
```

Jika mendapatkan `connection refused`:
- Pastikan `php artisan serve` berjalan dan mendengarkan port 8000.
- Jika menggunakan WSL/container, gunakan host 0.0.0.0 dan alamat IP container.

---

File terkait:
- `routes/api.php` (admin closure routes)
- `scripts/run_admin_checks.ps1` (PowerShell automation)
- `tests/Feature/AdminAccessTest.php` (PHPUnit feature tests)

