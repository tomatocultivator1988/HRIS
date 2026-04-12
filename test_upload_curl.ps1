# Simple curl-based upload test
$baseUrl = "http://localhost/HRIS"

# Step 1: Login
Write-Host "Logging in..." -ForegroundColor Cyan
$loginResponse = curl.exe -s -X POST "$baseUrl/api/auth/login" `
    -H "Content-Type: application/json" `
    -d '{\"email\":\"kiancabalumcabalum@gmail.com\",\"password\":\"kiancabalum123T\"}' | ConvertFrom-Json

if ($loginResponse.success) {
    $token = $loginResponse.data.access_token
    $employeeId = $loginResponse.data.user.id
    Write-Host "Login successful! Employee ID: $employeeId" -ForegroundColor Green
    
    # Step 2: Upload
    Write-Host "`nUploading document..." -ForegroundColor Cyan
    $filePath = "C:\Users\JOPZ SSD PC1\Pictures\yumi.jpg"
    
    $uploadResponse = curl.exe -s -X POST "$baseUrl/api/employees/$employeeId/documents" `
        -H "Authorization: Bearer $token" `
        -F "file=@$filePath" `
        -F "document_type=Resume" `
        -F "notes=Test upload"
    
    Write-Host "Upload response:" -ForegroundColor Yellow
    Write-Host $uploadResponse
} else {
    Write-Host "Login failed!" -ForegroundColor Red
}
