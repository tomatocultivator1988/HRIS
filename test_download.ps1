# Test document download functionality

Write-Host "Step 1: Logging in..." -ForegroundColor Cyan

$loginUrl = "http://localhost/HRIS/api/auth/login"
$loginBody = @{
    email = "kiancabalumcabalum@gmail.com"
    password = "password"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri $loginUrl -Method Post -Body $loginBody -ContentType "application/json"
    
    if ($loginResponse.success) {
        Write-Host "✓ Login successful!" -ForegroundColor Green
        $token = $loginResponse.data.token
        $employeeId = $loginResponse.data.user.id
        Write-Host "  Employee ID: $employeeId" -ForegroundColor Gray
    } else {
        Write-Host "✗ Login failed: $($loginResponse.message)" -ForegroundColor Red
        exit 1
    }
} catch {
    Write-Host "✗ Login error: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 2: Fetching documents list..." -ForegroundColor Cyan

$listUrl = "http://localhost/HRIS/api/employees/$employeeId/documents"
$headers = @{
    Authorization = "Bearer $token"
}

try {
    $listResponse = Invoke-RestMethod -Uri $listUrl -Method Get -Headers $headers
    
    if ($listResponse.success -and $listResponse.data.documents.Count -gt 0) {
        Write-Host "✓ Found $($listResponse.data.documents.Count) documents" -ForegroundColor Green
        
        # Get the first document
        $document = $listResponse.data.documents[0]
        $documentId = $document.id
        $fileName = $document.file_name
        
        Write-Host "  Document ID: $documentId" -ForegroundColor Gray
        Write-Host "  File Name: $fileName" -ForegroundColor Gray
        Write-Host "  File Size: $($document.file_size) bytes" -ForegroundColor Gray
        
        Write-Host ""
        Write-Host "Step 3: Downloading document..." -ForegroundColor Cyan
        
        $downloadUrl = "http://localhost/HRIS/api/employees/$employeeId/documents/$documentId/download"
        $outputPath = "downloaded_$fileName"
        
        # Download the file
        Invoke-WebRequest -Uri $downloadUrl -Method Get -Headers $headers -OutFile $outputPath
        
        if (Test-Path $outputPath) {
            $fileInfo = Get-Item $outputPath
            Write-Host "✓ Download successful!" -ForegroundColor Green
            Write-Host "  Saved to: $outputPath" -ForegroundColor Gray
            Write-Host "  File size: $($fileInfo.Length) bytes" -ForegroundColor Gray
            Write-Host "  File type: $($fileInfo.Extension)" -ForegroundColor Gray
            
            # Check if it's the correct file type (not .htm)
            if ($fileInfo.Extension -eq ".jpg" -or $fileInfo.Extension -eq ".jpeg") {
                Write-Host "✓ File downloaded with correct extension!" -ForegroundColor Green
            } else {
                Write-Host "✗ Warning: File has unexpected extension: $($fileInfo.Extension)" -ForegroundColor Yellow
            }
        } else {
            Write-Host "✗ Download failed: File not found" -ForegroundColor Red
        }
        
    } else {
        Write-Host "✗ No documents found" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Test complete!" -ForegroundColor Cyan
