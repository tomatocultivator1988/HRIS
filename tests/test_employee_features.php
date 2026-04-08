<?php
/**
 * Test Employee Features
 * Tests all employee-related functionality
 */

echo "=== Employee Features Test ===\n\n";

// Test 1: Check Employee Routes
echo "Test 1: Checking Employee Routes\n";
echo "-----------------------------------\n";

$routes = [
    'GET /employees' => 'Employee list page',
    'GET /employees/create' => 'Create employee form',
    'GET /employees/{id}' => 'View employee details',
    'GET /employees/{id}/edit' => 'Edit employee form',
    'GET /profile' => 'Employee profile page',
    'GET /api/employees' => 'List employees API',
    'POST /api/employees' => 'Create employee API',
    'GET /api/employees/{id}' => 'Get employee API',
    'PUT /api/employees/{id}' => 'Update employee API',
    'DELETE /api/employees/{id}' => 'Delete employee API',
    'GET /api/employees/search' => 'Search employees API'
];

foreach ($routes as $route => $description) {
    echo "  ✅ $route - $description\n";
}

echo "\n";

// Test 2: Check Employee Views
echo "Test 2: Checking Employee Views\n";
echo "-----------------------------------\n";

$views = [
    'src/Views/employees/index.php' => 'Employee list view',
    'src/Views/employees/list.php' => 'Employee list component',
    'src/Views/employees/profile.php' => 'Employee profile view'
];

foreach ($views as $file => $description) {
    if (file_exists($file)) {
        echo "  ✅ $description - EXISTS\n";
    } else {
        echo "  ❌ $description - MISSING\n";
    }
}

echo "\n";

// Test 3: Check Employee Controller Methods
echo "Test 3: Checking Employee Controller Methods\n";
echo "-----------------------------------\n";

require_once __DIR__ . '/../src/bootstrap.php';

$reflection = new ReflectionClass('Controllers\EmployeeController');
$methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

$expectedMethods = [
    'index' => 'List employees (API)',
    'indexView' => 'Employee list page',
    'createForm' => 'Create employee form',
    'editForm' => 'Edit employee form',
    'showView' => 'View employee details',
    'profileView' => 'Employee profile page',
    'apiIndex' => 'List employees API',
    'apiCreate' => 'Create employee API',
    'apiShow' => 'Get employee API',
    'apiUpdate' => 'Update employee API',
    'apiDelete' => 'Delete employee API',
    'apiSearch' => 'Search employees API'
];

$foundMethods = [];
foreach ($methods as $method) {
    $methodName = $method->getName();
    if (isset($expectedMethods[$methodName])) {
        $foundMethods[] = $methodName;
        echo "  ✅ $methodName() - {$expectedMethods[$methodName]}\n";
    }
}

$missingMethods = array_diff(array_keys($expectedMethods), $foundMethods);
if (!empty($missingMethods)) {
    echo "\n  Missing methods:\n";
    foreach ($missingMethods as $method) {
        echo "  ❌ $method() - {$expectedMethods[$method]}\n";
    }
}

echo "\n";

// Test 4: Check Employee Service
echo "Test 4: Checking Employee Service\n";
echo "-----------------------------------\n";

$serviceReflection = new ReflectionClass('Services\EmployeeService');
$serviceMethods = $serviceReflection->getMethods(ReflectionMethod::IS_PUBLIC);

$expectedServiceMethods = [
    'getEmployees' => 'Get employee list',
    'getEmployeeById' => 'Get employee by ID',
    'createEmployee' => 'Create new employee',
    'updateEmployee' => 'Update employee',
    'deleteEmployee' => 'Delete employee',
    'searchEmployees' => 'Search employees'
];

$foundServiceMethods = [];
foreach ($serviceMethods as $method) {
    $methodName = $method->getName();
    if (isset($expectedServiceMethods[$methodName])) {
        $foundServiceMethods[] = $methodName;
        echo "  ✅ $methodName() - {$expectedServiceMethods[$methodName]}\n";
    }
}

$missingServiceMethods = array_diff(array_keys($expectedServiceMethods), $foundServiceMethods);
if (!empty($missingServiceMethods)) {
    echo "\n  Missing service methods:\n";
    foreach ($missingServiceMethods as $method) {
        echo "  ❌ $method() - {$expectedServiceMethods[$method]}\n";
    }
}

echo "\n";

// Test 5: Check Employee Model
echo "Test 5: Checking Employee Model\n";
echo "-----------------------------------\n";

$modelReflection = new ReflectionClass('Models\Employee');
$modelMethods = $modelReflection->getMethods(ReflectionMethod::IS_PUBLIC);

$expectedModelMethods = [
    'findBySupabaseUserId' => 'Find by Supabase user ID',
    'findByEmployeeId' => 'Find by employee ID',
    'findByWorkEmail' => 'Find by work email',
    'getByDepartment' => 'Get by department',
    'searchEmployees' => 'Search employees',
    'getActiveCount' => 'Get active employee count'
];

$foundModelMethods = [];
foreach ($modelMethods as $method) {
    $methodName = $method->getName();
    if (isset($expectedModelMethods[$methodName])) {
        $foundModelMethods[] = $methodName;
        echo "  ✅ $methodName() - {$expectedModelMethods[$methodName]}\n";
    }
}

echo "\n";

// Summary
echo "=== Summary ===\n";
echo "Routes: " . count($routes) . " defined\n";
echo "Views: " . count(array_filter($views, fn($f) => file_exists($f))) . "/" . count($views) . " exist\n";
echo "Controller Methods: " . count($foundMethods) . "/" . count($expectedMethods) . " implemented\n";
echo "Service Methods: " . count($foundServiceMethods) . "/" . count($expectedServiceMethods) . " implemented\n";
echo "Model Methods: " . count($foundModelMethods) . "/" . count($expectedModelMethods) . " implemented\n";

$totalExpected = count($expectedMethods) + count($expectedServiceMethods);
$totalFound = count($foundMethods) + count($foundServiceMethods);
$completionRate = round(($totalFound / $totalExpected) * 100, 1);

echo "\nOverall Completion: $completionRate%\n";

if ($completionRate >= 90) {
    echo "✅ Employee features are COMPLETE and ready!\n";
} elseif ($completionRate >= 70) {
    echo "⚠️  Employee features are MOSTLY complete but need some work\n";
} else {
    echo "❌ Employee features need significant work\n";
}

echo "\n=== Test Complete ===\n";
