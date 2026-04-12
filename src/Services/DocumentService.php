<?php

namespace Services;

use Models\EmployeeDocument;

/**
 * DocumentService
 * 
 * Handles business logic for employee 201 files management including
 * file validation, storage quota checking, and file operations.
 * 
 * @package App\Services
 */
class DocumentService
{
    // File size limits
    const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    const MAX_STORAGE_PER_EMPLOYEE = 50 * 1024 * 1024; // 50MB
    
    // Allowed file types
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    // Storage configuration
    const STORAGE_BUCKET = 'employee-documents';
    const SUPABASE_STORAGE_URL = 'https://xtfekjcusnnadfgcrzht.supabase.co/storage/v1';
    
    private EmployeeDocument $employeeDocument;
    private array $supabaseConfig;

    /**
     * Constructor
     *
     * @param EmployeeDocument $employeeDocument Employee document model
     */
    public function __construct(EmployeeDocument $employeeDocument)
    {
        $this->employeeDocument = $employeeDocument;
        $this->supabaseConfig = require dirname(__DIR__, 2) . '/config/supabase.php';
    }

    /**
     * Validate uploaded file
     *
     * @param array $file Uploaded file from $_FILES
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validateFile(array $file): array
    {
        $errors = [];

        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors['file'] = 'No file was uploaded';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $errors['file'] = 'File size exceeds 10MB limit';
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            $errors['file'] = 'Only PDF, JPG, PNG, DOC, DOCX files are allowed';
        }

        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            $errors['file'] = 'Invalid file type detected';
        }

        // Verify MIME type matches extension
        $expectedMimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        if (isset($expectedMimeTypes[$extension]) && $mimeType !== $expectedMimeTypes[$extension]) {
            $errors['file'] = 'File type does not match extension';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if employee has enough storage quota
     *
     * @param string $employeeId Employee UUID
     * @param int $newFileSize Size of new file in bytes
     * @return bool True if within quota, false otherwise
     */
    public function checkStorageQuota(string $employeeId, int $newFileSize): bool
    {
        $currentUsage = $this->calculateStorageUsed($employeeId);
        return ($currentUsage + $newFileSize) <= self::MAX_STORAGE_PER_EMPLOYEE;
    }

    /**
     * Calculate total storage used by employee
     *
     * @param string $employeeId Employee UUID
     * @return int Total bytes used
     */
    public function calculateStorageUsed(string $employeeId): int
    {
        return $this->employeeDocument->getTotalSize($employeeId);
    }

    /**
     * Get storage statistics for employee
     *
     * @param string $employeeId Employee UUID
     * @return array Storage statistics
     */
    public function getStorageStats(string $employeeId): array
    {
        $totalSize = $this->calculateStorageUsed($employeeId);
        $totalCount = $this->employeeDocument->getDocumentCount($employeeId);
        $storageLimit = self::MAX_STORAGE_PER_EMPLOYEE;
        $storageUsedPercentage = $storageLimit > 0 ? ($totalSize / $storageLimit) * 100 : 0;

        return [
            'total_size' => $totalSize,
            'total_count' => $totalCount,
            'storage_limit' => $storageLimit,
            'storage_used_percentage' => round($storageUsedPercentage, 2)
        ];
    }

    /**
     * Get allowed file extensions
     *
     * @return array Allowed extensions
     */
    public function getAllowedExtensions(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }

    /**
     * Get allowed MIME types
     *
     * @return array Allowed MIME types
     */
    public function getAllowedMimeTypes(): array
    {
        return self::ALLOWED_MIME_TYPES;
    }

    /**
     * Get maximum file size
     *
     * @return int Maximum file size in bytes
     */
    public function getMaxFileSize(): int
    {
        return self::MAX_FILE_SIZE;
    }

    /**
     * Get maximum storage per employee
     *
     * @return int Maximum storage in bytes
     */
    public function getMaxStoragePerEmployee(): int
    {
        return self::MAX_STORAGE_PER_EMPLOYEE;
    }

    /**
     * Format file size for display
     *
     * @param int $bytes File size in bytes
     * @return string Formatted file size
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        } else {
            return round($bytes / (1024 * 1024), 1) . ' MB';
        }
    }

    /**
     * Generate unique filename for storage
     *
     * @param string $employeeId Employee UUID
     * @param string $originalName Original filename
     * @return string Generated filename
     */
    public function generateFileName(string $employeeId, string $originalName): string
    {
        $timestamp = time();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $sanitizedName = $this->sanitizeFileName(pathinfo($originalName, PATHINFO_FILENAME));
        
        return "{$employeeId}_{$timestamp}_{$sanitizedName}.{$extension}";
    }

    /**
     * Sanitize filename by removing special characters
     *
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     */
    public function sanitizeFileName(string $filename): string
    {
        // Remove special characters and replace spaces with underscores
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        
        // Replace multiple underscores with single underscore
        $sanitized = preg_replace('/_+/', '_', $sanitized);
        
        // Trim underscores from start and end
        $sanitized = trim($sanitized, '_');
        
        // Limit length to 100 characters
        if (strlen($sanitized) > 100) {
            $sanitized = substr($sanitized, 0, 100);
        }
        
        return $sanitized ?: 'document';
    }

    /**
     * Store uploaded file to Supabase Storage
     *
     * @param array $file Uploaded file from $_FILES
     * @param string $employeeId Employee UUID
     * @return array File data with path and metadata
     * @throws \Exception If file storage fails
     */
    public function storeFile(array $file, string $employeeId): array
    {
        try {
            // Generate unique filename
            $fileName = $this->generateFileName($employeeId, $file['name']);
            
            // Storage path in bucket: {employeeId}/{filename}
            $storagePath = $employeeId . '/' . $fileName;
            
            // Read file content
            $fileContent = file_get_contents($file['tmp_name']);
            if ($fileContent === false) {
                throw new \Exception('Failed to read uploaded file');
            }
            
            // Upload to Supabase Storage
            $uploadUrl = self::SUPABASE_STORAGE_URL . '/object/' . self::STORAGE_BUCKET . '/' . $storagePath;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $uploadUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $fileContent,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->supabaseConfig['service_key'],
                    'apikey: ' . $this->supabaseConfig['service_key'],
                    'Content-Type: ' . $file['type'],
                    'x-upsert: false'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error || $httpCode < 200 || $httpCode >= 300) {
                throw new \Exception('Failed to upload file to Supabase Storage: ' . ($error ?: $response));
            }
            
            return [
                'path' => $storagePath,
                'filename' => $fileName,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'mime_type' => $file['type']
            ];
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete file from Supabase Storage
     *
     * @param string $filePath Path to file in bucket
     * @return bool True if deleted successfully
     */
    public function deleteFile(string $filePath): bool
    {
        try {
            $deleteUrl = self::SUPABASE_STORAGE_URL . '/object/' . self::STORAGE_BUCKET . '/' . $filePath;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $deleteUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->supabaseConfig['service_key'],
                    'apikey: ' . $this->supabaseConfig['service_key']
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode >= 200 && $httpCode < 300;
        } catch (\Exception $e) {
            error_log("Failed to delete file {$filePath}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get public URL for a file in Supabase Storage
     *
     * @param string $filePath File path in bucket
     * @return string Public URL
     */
    public function getStoragePath(string $filePath): string
    {
        return self::SUPABASE_STORAGE_URL . '/object/public/' . self::STORAGE_BUCKET . '/' . $filePath;
    }

    /**
     * Check if file exists in Supabase Storage
     *
     * @param string $filePath Path to file in bucket
     * @return bool True if file exists
     */
    public function fileExists(string $filePath): bool
    {
        try {
            $url = self::SUPABASE_STORAGE_URL . '/object/' . self::STORAGE_BUCKET . '/' . $filePath;
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_NOBODY => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->supabaseConfig['service_key'],
                    'apikey: ' . $this->supabaseConfig['service_key']
                ]
            ]);
            
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
}
