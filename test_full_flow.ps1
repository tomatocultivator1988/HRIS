# Full test: Login, Upload, List, Download

Write-Host "=== STEP 1: LOGIN ===" -ForegroundColor Cyan
$loginUrl = "http://localhost/HRIS/api/auth/login"
$loginBody = @{
    email = "kiancabalumcabalum@gmail.com"
    password = "kiancabalum123T"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri $loginUrl -Method Post -Body $loginBody -ContentType "application/json"
$token = $loginResponse.data.access_token
$employeeId = $loginResponse.data.user.id
Write-Host "Logged in! Employee ID: $employeeId" -ForegroundColor Green

Write-Host ""
Write-Host "=== STEP 2: LIST DOCUMENTS ===" -ForegroundColor Cyan
$listUrl = "http://localhost/HRIS/api/employees/$employeeId/documents"
$headers = @{ Authorization = "Bearer $token" }

$listResponse = Invoke-RestMethod -Uri $listUrl -Method Get -Headers $headers
Write-Host "Found $($listResponse.data.documents.Count) documents" -ForegroundColor Green

if ($listResponse.data.documents.Count -gt 0) {
    $document = $listResponse.data.documents[0]
    $documentId = $document.id
    $fileName = $document.file_name
    
    Write-Host "  Document ID: $documentId"
    Write-Host "  File Name: $fileName"
    Write-Host "  MIME Type: $($document.mime_type)"
    
    Write-Host ""
    Write-Host "=== STEP 3: DOWNLOAD DOCUMENT ===" -ForegroundColor Cyan
    $downloadUrl = "http://localhost/HRIS/api/employees/$employeeId/documents/$documentId/download"
    $outputPath = "test_$fileName"
    
    Write-Host "Downloading from: $downloadUrl"
    Write-Host "Saving to: $outputPath"
    
    $webRequest = Invoke-WebRequest -Uri $downloadUrl -Method Get -Headers $headers -OutFile $outputPath
    
    Write-Host ""
    Write-Host "=== DOWNLOAD RESULT ===" -ForegroundColor Cyan
    Write-Host "Status Code: $($webRequest.StatusCode)" -ForegroundColor Green
    Write-Host "Content-Type: $($webRequest.Headers.'Content-Type')"
    
    if (Test-Path $outputPath) {
        $fileInfo = Get-Item $outputPath
        Write-Host ""
        Write-Host "=== FILE INFO ===" -ForegroundColor Cyan
        Write-Host "Downloaded file: $($fileInfo.Name)" -ForegroundColor Green
        Write-Host "File size: $($fileInfo.Length) bytes"
        Write-Host "Extension: $($fileInfo.Extension)"
        
        if ($fileInfo.Extension -eq ".jpg" -or $fileInfo.Extension -eq ".jpeg") {
            Write-Host ""
            Write-Host "SUCCESS! File downloaded with correct .jpg extension!" -ForegroundColor Green
        } else {
            Write-Host ""
            Write-Host "WARNING: File has unexpected extension: $($fileInfo.Extension)" -ForegroundColor Yellow
        }
    }
}
