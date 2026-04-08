<?php

/**
 * Unit Tests for User Model
 * 
 * Tests the User model functionality including user lookup,
 * role checking, and permission management.
 */

require_once dirname(__DIR__) . '/src/autoload.php';

use Models\User;
use Core\Container;
use Core\SupabaseConnection;

class UserModelTest
{
    private User $userModel;
    private Container $container;
    
    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->container->registerDefaultBindings();
        $this->userModel = $this->container->resolve(User::class);
    }
    
    /**
     * Test user role checking
     */
    public function testHasRole(): bool
    {
        $adminUser = [
            'id' => 1,
            'email' => 'admin@example.com',
            'role' => 'admin',
            'is_active' => true
        ];
        
        $employeeUser = [
            'id' => 2,
            'email' => 'employee@example.com',
            'role' => 'employee',
            'is_active' => true
        ];
        
        // Test admin role
        $hasAdminRole = $this->userModel->hasRole($adminUser, 'admin');
        $hasEmployeeRole = $this->userModel->hasRole($adminUser, 'employee');
        
        if (!$hasAdminRole || $hasEmployeeRole) {
            return false;
        }
        
        // Test employee role
        $hasEmployeeRole = $this->userModel->hasRole($employeeUser, 'employee');
        $hasAdminRole = $this->userModel->hasRole($employeeUser, 'admin');
        
        return $hasEmployeeRole && !$hasAdminRole;
    }
    
    /**
     * Test user active status checking
     */
    public function testIsActive(): bool
    {
        $activeUser = [
            'id' => 1,
            'email' => 'active@example.com',
            'is_active' => true
        ];
        
        $inactiveUser = [
            'id' => 2,
            'email' => 'inactive@example.com',
            'is_active' => false
        ];
        
        $isActiveTrue = $this->userModel->isActive($activeUser);
        $isActiveFalse = $this->userModel->isActive($inactiveUser);
        
        return $isActiveTrue && !$isActiveFalse;
    }
    
    /**
     * Test user permissions
     */
    public function testGetPermissions(): bool
    {
        $adminUser = [
            'id' => 1,
            'role' => 'admin'
        ];
        
        $employeeUser = [
            'id' => 2,
            'role' => 'employee'
        ];
        
        $adminPermissions = $this->userModel->getPermissions($adminUser);
        $employeePermissions = $this->userModel->getPermissions($employeeUser);
        
        // Admin should have more permissions than employee
        if (count($adminPermissions) <= count($employeePermissions)) {
            return false;
        }
        
        // Admin should have manage_employees permission
        if (!in_array('manage_employees', $adminPermissions)) {
            return false;
        }
        
        // Employee should not have manage_employees permission
        if (in_array('manage_employees', $employeePermissions)) {
            return false;
        }
        
        // Both should have view_dashboard permission
        return in_array('view_dashboard', $adminPermissions) && 
               in_array('view_dashboard', $employeePermissions);
    }
    
    /**
     * Test specific permission checking
     */
    public function testHasPermission(): bool
    {
        $adminUser = [
            'id' => 1,
            'role' => 'admin'
        ];
        
        $employeeUser = [
            'id' => 2,
            'role' => 'employee'
        ];
        
        // Admin should have manage_employees permission
        $adminHasManage = $this->userModel->hasPermission($adminUser, 'manage_employees');
        
        // Employee should not have manage_employees permission
        $employeeHasManage = $this->userModel->hasPermission($employeeUser, 'manage_employees');
        
        // Both should have view_dashboard permission
        $adminHasDashboard = $this->userModel->hasPermission($adminUser, 'view_dashboard');
        $employeeHasDashboard = $this->userModel->hasPermission($employeeUser, 'view_dashboard');
        
        return $adminHasManage && !$employeeHasManage && 
               $adminHasDashboard && $employeeHasDashboard;
    }
    
    /**
     * Test full name formatting
     */
    public function testGetFullName(): bool
    {
        $adminUser = [
            'role' => 'admin',
            'name' => 'John Admin'
        ];
        
        $employeeUser = [
            'role' => 'employee',
            'first_name' => 'Jane',
            'last_name' => 'Doe'
        ];
        
        $employeeUserIncomplete = [
            'role' => 'employee',
            'first_name' => 'Bob'
        ];
        
        $adminName = $this->userModel->getFullName($adminUser);
        $employeeName = $this->userModel->getFullName($employeeUser);
        $incompleteEmployeeName = $this->userModel->getFullName($employeeUserIncomplete);
        
        return $adminName === 'John Admin' && 
               $employeeName === 'Jane Doe' && 
               $incompleteEmployeeName === 'Bob';
    }
    
    /**
     * Run all tests
     */
    public function runTests(): void
    {
        echo "Running User Model Tests\n";
        echo "========================\n\n";
        
        $tests = [
            'testHasRole' => 'Role checking functionality',
            'testIsActive' => 'Active status checking',
            'testGetPermissions' => 'Permission retrieval',
            'testHasPermission' => 'Specific permission checking',
            'testGetFullName' => 'Full name formatting'
        ];
        
        $passed = 0;
        $total = count($tests);
        
        foreach ($tests as $method => $description) {
            try {
                $result = $this->$method();
                
                if ($result) {
                    echo "✓ {$description}\n";
                    $passed++;
                } else {
                    echo "✗ {$description}\n";
                }
            } catch (Exception $e) {
                echo "✗ {$description} - Error: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\nResults: {$passed}/{$total} tests passed\n";
        
        if ($passed === $total) {
            echo "✓ All User Model tests passed!\n";
        } else {
            echo "✗ Some tests failed. Please review the implementation.\n";
        }
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new UserModelTest();
    $test->runTests();
}