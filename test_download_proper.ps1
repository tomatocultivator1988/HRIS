# Test document download with proper authentication

Write-Host "Step 1: Logging in..." -ForegroundColor Cyan

$loginUrl = "http://localhost/HRIS/api/auth/login"
$loginBody = @{
    email = "kiancabalumcabalum@gmail.com"
    password = "password"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri $loginUrl -Method Post -Body $loginBody -ContentType "application/json"

if ($loginResponse.success) {
    Write-Host "✓ Login successful!" -ForegroundColor Green
    $token = $loginResponse.data.token
    $employeeId = $loginResponse.data.user.id
} else {
    Write-Host "✗ Login failed" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 2: Fetching documents..." -ForegroundColor Cyan

$listUrl = "http://localhost/HRIS/api/employees/$employeeId/documents"
$headers = @{
    Authorization = "Bearer $token"
}

$listResponse = Invoke-RestMethod -Uri $listUrl -Method Get -Headers $headers

if ($listResponse.success -and $listResponse.data.documents.Count -gt 0) {
    $document = $listResponse.data.documents[0]
    $documentId = $document.id
    $fileName = $document.file_name
    
    Write-Host "✓ Found document: $fileName (ID: $documentId)" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "Step 3: Downloading document..." -ForegroundColor Cyan
    
    $downloadUrl = "http://localhost/HRIS/api/employees/$employeeId/documents/$documentId/download"
    $outputPath = "test_downloaded_$fileName"
    
    # Use Invoke-WebRequest to get full response details
    $response = Invoke-WebRequest -Uri $downloadUrl -Method Get -Headers $headers -OutFile $outputPath
    
    Write-Host "✓ Download completed!" -ForegroundColor Green
    Write-Host "  Status Code: $($response.StatusCode)" -ForegroundColor Gray
    Write-Host "  Content-Type: $($response.Headers.'Content-Type')" -ForegroundColor Gray
    
    if (Test-Path $outputPath) {
        $fileInfo = Get-Item $outputPath
        Write-Host "  Downloaded file: $($fileInfo.Name)" -ForegroundColor Gray
        Write-Host "  File size: $($fileInfo.Length) bytes" -ForegroundColor Gray
        Write-Host "  Extension: $($fileInfo.Extension)" -ForegroundColor Gray
        
        if ($fileInfo.Extension -match '\.(jpg|jpeg|png|pdf|doc|docx)$') {
            Write-Host "✓ File has correct extension!" -ForegroundColor Green
        } else {
            Write-Host "✗ Warning: Unexpected extension: $($fileInfo.Extension)" -ForegroundColor Yellow
        }
    }
} else {
    Write-Host "✗ No documents found" -ForegroundColor Red
}
