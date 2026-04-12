# Test document download

$loginUrl = "http://localhost/HRIS/api/auth/login"
$loginBody = @{
    email = "kiancabalumcabalum@gmail.com"
    password = "password"
} | ConvertTo-Json

$loginResponse = Invoke-RestMethod -Uri $loginUrl -Method Post -Body $loginBody -ContentType "application/json"
$token = $loginResponse.data.token
$employeeId = $loginResponse.data.user.id

Write-Host "Logged in as employee: $employeeId"

$listUrl = "http://localhost/HRIS/api/employees/$employeeId/documents"
$headers = @{ Authorization = "Bearer $token" }

$listResponse = Invoke-RestMethod -Uri $listUrl -Method Get -Headers $headers
$document = $listResponse.data.documents[0]
$documentId = $document.id
$fileName = $document.file_name

Write-Host "Downloading: $fileName (ID: $documentId)"

$downloadUrl = "http://localhost/HRIS/api/employees/$employeeId/documents/$documentId/download"
$outputPath = "downloaded_$fileName"

Invoke-WebRequest -Uri $downloadUrl -Method Get -Headers $headers -OutFile $outputPath

$fileInfo = Get-Item $outputPath
Write-Host "Downloaded: $($fileInfo.Name) - Size: $($fileInfo.Length) bytes - Extension: $($fileInfo.Extension)"
