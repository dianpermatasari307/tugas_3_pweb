# Cara Menggunakan Thunder Client Collection

## 1. Import Collection ke VS Code

### Instalasi Thunder Client (jika belum)
- Buka VS Code
- Klik Extensions (icon kotak di sidebar kiri)
- Cari "Thunder Client"
- Klik Install (dari Ranga Vadhineni)

### Import File
1. Buka Thunder Client di VS Code (icon petir di sidebar)
2. Klik **Collections** → **Import**
3. Pilih file `thunder_collection.json` di project root
4. Collection akan ter-import dengan nama "Laravel API Role"

---

## 2. Setup: Replace Token Placeholders

Sebelum menjalankan request, Anda perlu mendapatkan token valid:

### Dapatkan Admin Token
1. Jalankan server: `php artisan serve --host=127.0.0.1 --port=8000`
2. Di Thunder Client, buka folder **Public** → **Login**
3. Ubah body email/password jika perlu (default: `admin@example.com` / `password`)
4. Klik **Send**
5. Copy token dari respons JSON
6. Ganti `YOUR_ADMIN_TOKEN_HERE` di folder **Admin Only** dengan token yang dikopi

### Dapatkan User Token (Optional)
1. Di **Public**, buat request baru atau gunakan **Login** dengan email user biasa: `user@example.com` / `password`
2. Klik **Send**
3. Copy token
4. Ganti `YOUR_TOKEN_HERE` di folder **Protected - User** dengan token

---

## 3. Struktur Collection

```
├── Public
│   ├── Register       → POST /api/register
│   └── Login          → POST /api/login
│
├── Protected - User
│   ├── Get Me         → GET /api/me
│   ├── Logout         → POST /api/logout
│   ├── Get My Todos   → GET /api/todos
│   ├── Create Todo    → POST /api/todos
│   ├── Get Todo       → GET /api/todos/{id}
│   ├── Update Todo    → PUT /api/todos/{id}
│   └── Delete Todo    → DELETE /api/todos/{id}
│
└── Admin Only
    ├── List All Users  → GET /api/admin/users (Kasus 5.1)
    └── List All Todos  → GET /api/admin/todos (Kasus 5.2)
```

---

## 4. Test Skenario Kasus 5.1 & 5.2

### Kasus 5.1: User biasa tidak bisa akses admin endpoint (403)
1. Login sebagai user biasa (email: `user@example.com`, password: `password`)
2. Copy token
3. Di folder **Admin Only**, edit request **List All Users**
4. Ubah Authorization header ke token user
5. Klik **Send** → Expect: **403 Forbidden** dengan message

### Kasus 5.2: Admin berhasil akses (200)
1. Login sebagai admin (email: `admin@example.com`, password: `password`)
2. Copy token
3. Di folder **Admin Only**, pastikan Authorization header pakai admin token
4. Klik **List All Users** → **Send** → Expect: **200 OK** dengan daftar user
5. Klik **List All Todos** → **Send** → Expect: **200 OK** dengan daftar todos

---

## 5. Tips

- **Simpan token di Variable**: Klik ⚙️ (Settings) di Thunder Client, edit `admin_token` dan `user_token`, gunakan di request dengan `{{admin_token}}`
- **Test Response**: Klik tab **Tests** di request untuk assert response (bisa automated)
- **Environment**: Buat environment berbeda untuk local/staging/production (ganti base_url)

---

## File Location

```
c:\latihan laravel 3\laravel_api_role\thunder_collection.json
```

Untuk re-import, gunakan menu **Collections** → **Import** lalu pilih file ini.

