<?php

namespace Core;

/**
 * ValidationResult - Represents the result of a validation operation
 * 
 * This class encapsulates validation results including success status,
 * error messages, and sanitized data.
 */
class ValidationResult
{
    private bool $isValid;
    private array $errors;
    private array $sanitizedData;
    
    /**
     * Constructor
     *
     * @param bool $isValid Whether validation passed
     * @param array $errors Array of validation errors
     * @param array $sanitizedData Sanitized input data
     */
    public function __construct(bool $isValid, array $errors = [], array $sanitizedData = [])
    {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->sanitizedData = $sanitizedData;
    }
    
    /**
     * Check if validation passed
     *
     * @return bool True if valid, false otherwise
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }
    
    /**
     * Get validation errors
     *
     * @return array Array of error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get sanitized data
     *
     * @return array Sanitized input data
     */
    public function getSanitizedData(): array
    {
        return $this->sanitizedData;
    }
    
    /**
     * Check if there are any errors
     *
     * @return bool True if there are errors, false otherwise
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Get first error message
     *
     * @return string|null First error message or null if no errors
     */
    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }
        
        $firstError = reset($this->errors);
        return is_array($firstError) ? reset($firstError) : $firstError;
    }
    
    /**
     * Get errors for a specific field
     *
     * @param string $field Field name
     * @return array Array of errors for the field
     */
    public function getFieldErrors(string $field): array
    {
        if (!isset($this->errors[$field])) {
            return [];
        }
        
        $fieldErrors = $this->errors[$field];
        return is_array($fieldErrors) ? $fieldErrors : [$fieldErrors];
    }
    
    /**
     * Add an error
     *
     * @param string $field Field name
     * @param string $message Error message
     */
    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        if (!is_array($this->errors[$field])) {
            $this->errors[$field] = [$this->errors[$field]];
        }
        
        $this->errors[$field][] = $message;
        $this->isValid = false;
    }
    
    /**
     * Set sanitized data for a field
     *
     * @param string $field Field name
     * @param mixed $value Sanitized value
     */
    public function setSanitizedData(string $field, $value): void
    {
        $this->sanitizedData[$field] = $value;
    }
    
    /**
     * Merge with another validation result
     *
     * @param ValidationResult $other Other validation result
     * @return ValidationResult Merged result
     */
    public function merge(ValidationResult $other): ValidationResult
    {
        $mergedErrors = array_merge($this->errors, $other->getErrors());
        $mergedData = array_merge($this->sanitizedData, $other->getSanitizedData());
        $isValid = $this->isValid && $other->isValid();
        
        return new ValidationResult($isValid, $mergedErrors, $mergedData);
    }
    
    /**
     * Convert to array representation
     *
     * @return array Array representation of validation result
     */
    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'errors' => $this->errors,
            'sanitized_data' => $this->sanitizedData
        ];
    }
}