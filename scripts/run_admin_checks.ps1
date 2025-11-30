# PowerShell script: run_admin_checks.ps1
# Purpose: Login as admin and call /api/admin/users and /api/admin/todos
# Usage: Open powershell, cd to project root, then: .\scripts\run_admin_checks.ps1

param(
    [string]$Host = 'http://127.0.0.1:8000'
)

Write-Output "Running Admin checks against $Host"

$loginUrl = "$Host/api/login"
$usersUrl = "$Host/api/admin/users"
$todosUrl = "$Host/api/admin/todos"

$headers = @{ 'Content-Type' = 'application/json' }
$body = @{ email = 'admin@example.com'; password = 'password' } | ConvertTo-Json

try {
    Write-Output "Logging in as admin..."
    $resp = Invoke-RestMethod -Uri $loginUrl -Method POST -Headers $headers -Body $body -UseBasicParsing
    $token = $resp.access_token
    if (-not $token) { throw "No token returned" }
    Write-Output "Login OK. Token length: $($token.Length)"
} catch {
    Write-Error "Login failed: $($_.Exception.Message)"
    exit 2
}

# Prepare auth header
$authHeader = @{ Authorization = "Bearer $token"; Accept = 'application/json' }

function Call-Get {
    param($url)
    Write-Output "\nGET $url"
    try {
        $result = Invoke-RestMethod -Uri $url -Method GET -Headers $authHeader -UseBasicParsing -TimeoutSec 30
        Write-Output "Status: 200 OK"
        Write-Output (ConvertTo-Json $result -Depth 4)
    } catch {
        $err = $_.Exception.Response
        if ($err) {
            $status = $err.StatusCode.Value__
            $body = (New-Object System.IO.StreamReader($err.GetResponseStream())).ReadToEnd()
            Write-Output "Status: $status"
            Write-Output "Body: $body"
        } else {
            Write-Error "Request failed: $($_.Exception.Message)"
        }
    }
}

# Call endpoints
Call-Get -url $usersUrl
Call-Get -url $todosUrl

Write-Output "\nDone."
