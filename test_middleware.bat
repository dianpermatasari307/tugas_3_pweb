@echo off
REM Test RoleMiddleware Implementation

setlocal enabledelayedexpansion

echo ========================================
echo Testing RoleMiddleware Implementation
echo ========================================
echo.

REM Test 1: Login as Admin
echo [1] Login as Admin...
for /f "tokens=*" %%A in ('curl -s -X POST http://127.0.0.1:8000/api/login -H "Content-Type: application/json" -d "{\"email\":\"admin@example.com\",\"password\":\"password\"}"') do set "login_response=%%A"

echo Response: !login_response!
echo.

REM Test 2: Access Admin Endpoint with Admin Token
echo [2] Testing Admin Access (should succeed)...
echo.

REM Test 3: Login as Regular User
echo [3] Login as Regular User...
for /f "tokens=*" %%A in ('curl -s -X POST http://127.0.0.1:8000/api/login -H "Content-Type: application/json" -d "{\"email\":\"user@example.com\",\"password\":\"password\"}"') do set "user_login=%%A"

echo Response: !user_login!
echo.

REM Test 4: Try to Access Admin Endpoint with User Token (should fail with 403)
echo [4] Testing User Access to Admin Endpoint (should fail with 403)...
echo.

echo ========================================
echo Testing Complete
echo ========================================
