# Complete test script: Login and upload document
$baseUrl = "http://localhost/HRIS"
$email = "kiancabalumcabalum@gmail.com"
$password = "kiancabalum123T"

Write-Host "`nStep 1: Logging in..." -ForegroundColor Cyan

# Login to get JWT token
$loginUrl = "$baseUrl/api/auth/login"
$loginBody = @{
    email = $email
    password = $password
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri $loginUrl -Method Post -Body $loginBody -ContentType "application/json"
    
    if ($loginResponse.success -and $loginResponse.data.access_token) {
        $token = $loginResponse.data.access_token
        $employeeId = $loginResponse.data.user.id
        Write-Host "✓ Login successful!" -ForegroundColor Green
        Write-Host "  Employee ID: $employeeId" -ForegroundColor Gray
    } else {
        Write-Host "✗ Login failed: $($loginResponse.message)" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "✗ Login error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host "`nStep 2: Uploading document..." -ForegroundColor Cyan

# Use the specific file path
$testFile = "C:\Users\JOPZ SSD PC1\Pictures\yumi.jpg"

if (-not (Test-Path $testFile)) {
    Write-Host "  File not found: $testFile" -ForegroundColor Red
    exit 1
}

Write-Host "  Using file: yumi.jpg" -ForegroundColor Gray

# Upload document
$uploadUrl = "$baseUrl/api/employees/$employeeId/documents"
$form = @{
    file = Get-Item -Path $testFile
    document_type = "Resume"
    notes = "Test upload from PowerShell script"
}

$headers = @{
    "Authorization" = "Bearer $token"
}

try {
    $uploadResponse = Invoke-RestMethod -Uri $uploadUrl -Method Post -Form $form -Headers $headers
    
    if ($uploadResponse.success) {
        Write-Host "✓ Upload successful!" -ForegroundColor Green
        Write-Host "`nDocument Details:" -ForegroundColor Cyan
        Write-Host "  ID: $($uploadResponse.data.document.id)" -ForegroundColor Gray
        Write-Host "  Type: $($uploadResponse.data.document.document_type)" -ForegroundColor Gray
        Write-Host "  Filename: $($uploadResponse.data.document.file_name)" -ForegroundColor Gray
        Write-Host "  Size: $($uploadResponse.data.document.file_size) bytes" -ForegroundColor Gray
    } else {
        Write-Host "✗ Upload failed: $($uploadResponse.message)" -ForegroundColor Red
        if ($uploadResponse.errors) {
            Write-Host "  Errors:" -ForegroundColor Red
            $uploadResponse.errors.PSObject.Properties | ForEach-Object {
                Write-Host "    - $($_.Name): $($_.Value)" -ForegroundColor Red
            }
        }
    }
} catch {
    Write-Host "✗ Upload error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.ErrorDetails.Message) {
        Write-Host "  Details: $($_.ErrorDetails.Message)" -ForegroundColor Red
    }
}

Write-Host "`nTest complete!" -ForegroundColor Cyan
