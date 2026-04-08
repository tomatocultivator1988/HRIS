<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;
use Core\Container;
use Services\AuthService;

/**
 * User Model - Represents system users (admins and employees)
 * 
 * This model handles user authentication, role management, and user data operations.
 * Works with both admin and employee tables in Supabase.
 */
class User extends Model
{
    protected string $table = 'admins'; // Default table, but we query both admins and employees
    protected string $primaryKey = 'id';
    
    protected array $fillable = [
        'username',
        'email', 
        'name',
        'first_name',
        'last_name',
        'employee_id',
        'department',
        'position',
        'is_active',
        'last_login'
    ];
    
    protected array $guarded = [
        'id',
        'supabase_user_id',
        'created_at',
        'updated_at'
    ];
    
    protected array $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime'
    ];
    
    private array $attributes = [];
    
    /**
     * Find user by Supabase user ID across both admin and employee tables
     *
     * @param string $supabaseUserId Supabase user ID
     * @return array|null User data with role information
     */
    public function findBySupabaseUserId(string $supabaseUserId): ?array
    {
        // Check admins table first
        $adminUser = $this->db->select('admins', [
            'supabase_user_id' => $supabaseUserId,
            'is_active' => true
        ]);
        
        if (!empty($adminUser)) {
            $user = $adminUser[0];
            $user['role'] = 'admin';
            $user['user_type'] = 'admin';
            return $user;
        }
        
        // Check employees table
        $employeeUser = $this->db->select('employees', [
            'supabase_user_id' => $supabaseUserId,
            'is_active' => true
        ]);
        
        if (!empty($employeeUser)) {
            $user = $employeeUser[0];
            $user['role'] = 'employee';
            $user['user_type'] = 'employee';
            return $user;
        }
        
        return null;
    }
    
    /**
     * Find user by email across both admin and employee tables
     *
     * @param string $email Email address
     * @return array|null User data with role information
     */
    public function findByEmail(string $email): ?array
    {
        // Check admins table first
        $adminUser = $this->db->select('admins', [
            'email' => $email,
            'is_active' => true
        ]);
        
        if (!empty($adminUser)) {
            $user = $adminUser[0];
            $user['role'] = 'admin';
            $user['user_type'] = 'admin';
            return $user;
        }
        
        // Check employees table
        $employeeUser = $this->db->select('employees', [
            'work_email' => $email,
            'is_active' => true
        ]);
        
        if (!empty($employeeUser)) {
            $user = $employeeUser[0];
            $user['role'] = 'employee';
            $user['user_type'] = 'employee';
            $user['email'] = $user['work_email']; // Normalize email field
            return $user;
        }
        
        return null;
    }
    
    /**
     * Find user by ID and role
     *
     * @param int $id User ID
     * @param string $role User role (admin or employee)
     * @return array|null User data
     */
    public function findByIdAndRole(int $id, string $role): ?array
    {
        $table = $role === 'admin' ? 'admins' : 'employees';
        
        $user = $this->db->select($table, [
            'id' => $id,
            'is_active' => true
        ]);
        
        if (!empty($user)) {
            $userData = $user[0];
            $userData['role'] = $role;
            $userData['user_type'] = $role;
            
            // Normalize email field for employees
            if ($role === 'employee' && isset($userData['work_email'])) {
                $userData['email'] = $userData['work_email'];
            }
            
            return $userData;
        }
        
        return null;
    }
    
    /**
     * Get user's full name based on role
     *
     * @param array $userData User data array
     * @return string Formatted full name
     */
    public function getFullName(array $userData): string
    {
        if ($userData['role'] === 'admin') {
            return $userData['name'] ?? 'Admin User';
        }
        
        $firstName = $userData['first_name'] ?? '';
        $lastName = $userData['last_name'] ?? '';
        
        return trim($firstName . ' ' . $lastName) ?: 'Employee';
    }
    
    /**
     * Check if user has specific role
     *
     * @param array $userData User data array
     * @param string $role Role to check
     * @return bool True if user has role
     */
    public function hasRole(array $userData, string $role): bool
    {
        return ($userData['role'] ?? '') === $role;
    }
    
    /**
     * Check if user is active
     *
     * @param array $userData User data array
     * @return bool True if active
     */
    public function isActive(array $userData): bool
    {
        return (bool) ($userData['is_active'] ?? true);
    }
    
    /**
     * Update last login timestamp
     *
     * @param int $userId User ID
     * @param string $role User role (admin or employee)
     * @return bool True if updated successfully
     */
    public function updateLastLogin(int $userId, string $role): bool
    {
        $table = $role === 'admin' ? 'admins' : 'employees';
        
        try {
            $affectedRows = $this->db->update($table, 
                ['last_login' => date('Y-m-d H:i:s')], 
                ['id' => $userId]
            );
            return $affectedRows > 0;
        } catch (\Exception $e) {
            error_log('User::updateLastLogin Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user permissions based on role
     *
     * @param array $userData User data array
     * @return array Array of permissions
     */
    public function getPermissions(array $userData): array
    {
        $role = $userData['role'] ?? 'employee';
        
        switch ($role) {
            case 'admin':
                return [
                    'view_dashboard',
                    'manage_employees',
                    'manage_attendance',
                    'manage_leave',
                    'view_reports',
                    'manage_announcements',
                    'manage_system'
                ];
                
            case 'employee':
                return [
                    'view_dashboard',
                    'view_profile',
                    'record_attendance',
                    'request_leave',
                    'view_announcements'
                ];
                
            default:
                return [];
        }
    }
    
    /**
     * Check if user has specific permission
     *
     * @param array $userData User data array
     * @param string $permission Permission to check
     * @return bool True if user has permission
     */
    public function hasPermission(array $userData, string $permission): bool
    {
        $permissions = $this->getPermissions($userData);
        return in_array($permission, $permissions);
    }
    
    /**
     * Validate user data
     *
     * @param array $data User data to validate
     * @param mixed $id User ID for updates (null for create)
     * @return ValidationResult Validation result
     */
    protected function validate(array $data, $id = null): ValidationResult
    {
        $rules = [];
        
        // Email validation
        if (isset($data['email'])) {
            $rules['email'] = ['required', 'email', 'max:255'];
        }
        
        // Name validation for admins
        if (isset($data['name'])) {
            $rules['name'] = ['required', 'min:2', 'max:100'];
        }
        
        // First name validation for employees
        if (isset($data['first_name'])) {
            $rules['first_name'] = ['required', 'min:2', 'max:50'];
        }
        
        // Last name validation for employees
        if (isset($data['last_name'])) {
            $rules['last_name'] = ['required', 'min:2', 'max:50'];
        }
        
        // Employee ID validation
        if (isset($data['employee_id'])) {
            $rules['employee_id'] = ['required', 'max:20'];
        }
        
        // Department validation
        if (isset($data['department'])) {
            $rules['department'] = ['max:100'];
        }
        
        // Position validation
        if (isset($data['position'])) {
            $rules['position'] = ['max:100'];
        }
        
        $validator = Container::getInstance()->resolve('Validator');
        $result = $validator->validate($data, $rules);
        
        if (!$result->isValid()) {
            return $result;
        }
        
        $sanitizedData = $result->getSanitizedData();
        
        // Additional validation for unique constraints would go here
        // but since we're working with Supabase auth, we rely on AuthService
        
        return new ValidationResult(true, [], $sanitizedData);
    }
    
    /**
     * Create User instance from database record
     *
     * @param array $attributes User attributes from database
     * @return User User instance
     */
    public static function fromArray(array $attributes): User
    {
        $user = new self(Container::getInstance()->resolve('DatabaseConnection'));
        $user->attributes = $attributes;
        return $user;
    }
    
    /**
     * Get user ID
     *
     * @return int|null User ID
     */
    public function getId(): ?int
    {
        return $this->attributes['id'] ?? null;
    }
    
    /**
     * Get email
     *
     * @return string|null Email
     */
    public function getEmail(): ?string
    {
        return $this->attributes['email'] ?? null;
    }
    
    /**
     * Get user role
     *
     * @return string|null User role
     */
    public function getRole(): ?string
    {
        return $this->attributes['role'] ?? null;
    }
    
    /**
     * Update force password change flag (employees only)
     *
     * @param int $userId User ID
     * @param bool $forceChange Force password change flag
     * @return bool True if updated successfully
     */
    public function updateForcePasswordChange(string $userId, bool $forceChange): bool
    {
        try {
            $affectedRows = $this->db->update('employees', 
                ['force_password_change' => $forceChange], 
                ['id' => $userId]
            );
            return $affectedRows > 0;
        } catch (\Exception $e) {
            error_log('User::updateForcePasswordChange Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update password changed timestamp
     *
     * @param int $userId User ID
     * @param string $role User role (admin or employee)
     * @return bool True if updated successfully
     */
    public function updatePasswordChangedAt(string $userId, string $role): bool
    {
        $table = $role === 'admin' ? 'admins' : 'employees';
        
        try {
            $affectedRows = $this->db->update($table, 
                ['password_changed_at' => date('Y-m-d H:i:s')], 
                ['id' => $userId]
            );
            return $affectedRows > 0;
        } catch (\Exception $e) {
            error_log('User::updatePasswordChangedAt Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if employee needs to change password
     *
     * @param int $userId User ID
     * @return bool True if password change is required
     */
    public function needsPasswordChange(int $userId): bool
    {
        try {
            $employee = $this->db->select('employees', ['id' => $userId]);
            
            if (empty($employee)) {
                return false;
            }
            
            return (bool) ($employee[0]['force_password_change'] ?? false);
        } catch (\Exception $e) {
            error_log('User::needsPasswordChange Error: ' . $e->getMessage());
            return false;
        }
    }
}
