<?php
/**
 * Test Attendance Features
 * Comprehensive test of attendance module
 */

echo "=== Attendance Features Test ===\n\n";

// Test 1: Check Attendance Routes
echo "Test 1: Checking Attendance Routes\n";
echo "-----------------------------------\n";

$routes = [
    'GET /attendance' => 'Attendance page',
    'GET /api/attendance' => 'List attendance records API',
    'POST /api/attendance/clock-in' => 'Clock in API',
    'POST /api/attendance/clock-out' => 'Clock out API',
    'GET /api/attendance/today' => 'Today\'s attendance API',
    'GET /api/attendance/employee/{id}' => 'Employee attendance history API',
    'GET /api/attendance/report' => 'Attendance report API'
];

foreach ($routes as $route => $description) {
    echo "  📋 $route - $description\n";
}

echo "\n";

// Test 2: Check Attendance Files
echo "Test 2: Checking Attendance Files\n";
echo "-----------------------------------\n";

$files = [
    'src/Controllers/AttendanceController.php' => 'Attendance Controller',
    'src/Services/AttendanceService.php' => 'Attendance Service',
    'src/Models/Attendance.php' => 'Attendance Model',
    'src/Views/attendance/index.php' => 'Attendance View'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "  ✅ $description - EXISTS\n";
    } else {
        echo "  ❌ $description - MISSING\n";
    }
}

echo "\n";

// Test 3: Check Attendance Controller Methods
echo "Test 3: Checking Attendance Controller Methods\n";
echo "-----------------------------------\n";

require_once __DIR__ . '/../src/bootstrap.php';

try {
    $reflection = new ReflectionClass('Controllers\AttendanceController');
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    $expectedMethods = [
        'index' => 'List attendance records',
        'indexView' => 'Attendance page view',
        'clockIn' => 'Clock in',
        'clockOut' => 'Clock out',
        'getTodayAttendance' => 'Get today\'s attendance',
        'getEmployeeAttendance' => 'Get employee attendance history',
        'getAttendanceReport' => 'Get attendance report'
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
} catch (Exception $e) {
    echo "  ❌ Error loading AttendanceController: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Check Attendance Service
echo "Test 4: Checking Attendance Service\n";
echo "-----------------------------------\n";

try {
    $serviceReflection = new ReflectionClass('Services\AttendanceService');
    $serviceMethods = $serviceReflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    $expectedServiceMethods = [
        'clockIn' => 'Clock in employee',
        'clockOut' => 'Clock out employee',
        'getTodayAttendance' => 'Get today\'s attendance',
        'getEmployeeAttendance' => 'Get employee attendance history',
        'getAttendanceByDateRange' => 'Get attendance by date range',
        'getAttendanceReport' => 'Generate attendance report',
        'checkIfClockedIn' => 'Check if employee is clocked in'
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
} catch (Exception $e) {
    echo "  ❌ Error loading AttendanceService: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Check Attendance Model
echo "Test 5: Checking Attendance Model\n";
echo "-----------------------------------\n";

try {
    $modelReflection = new ReflectionClass('Models\Attendance');
    $modelMethods = $modelReflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    $expectedModelMethods = [
        'findByEmployeeAndDate' => 'Find by employee and date',
        'getTodayAttendance' => 'Get today\'s attendance',
        'getByDateRange' => 'Get by date range',
        'getEmployeeAttendance' => 'Get employee attendance'
    ];
    
    $foundModelMethods = [];
    foreach ($modelMethods as $method) {
        $methodName = $method->getName();
        if (isset($expectedModelMethods[$methodName])) {
            $foundModelMethods[] = $methodName;
            echo "  ✅ $methodName() - {$expectedModelMethods[$methodName]}\n";
        }
    }
    
    $missingModelMethods = array_diff(array_keys($expectedModelMethods), $foundModelMethods);
    if (!empty($missingModelMethods)) {
        echo "\n  Missing model methods:\n";
        foreach ($missingModelMethods as $method) {
            echo "  ❌ $method() - {$expectedModelMethods[$method]}\n";
        }
    }
} catch (Exception $e) {
    echo "  ❌ Error loading Attendance Model: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "=== Summary ===\n";
$filesExist = count(array_filter($files, fn($f) => file_exists($f)));
echo "Files: $filesExist/" . count($files) . " exist\n";

if (isset($foundMethods)) {
    echo "Controller Methods: " . count($foundMethods) . "/" . count($expectedMethods) . " implemented\n";
}

if (isset($foundServiceMethods)) {
    echo "Service Methods: " . count($foundServiceMethods) . "/" . count($expectedServiceMethods) . " implemented\n";
}

if (isset($foundModelMethods)) {
    echo "Model Methods: " . count($foundModelMethods) . "/" . count($expectedModelMethods) . " implemented\n";
}

// Calculate completion
$totalExpected = 0;
$totalFound = 0;

if (isset($expectedMethods)) {
    $totalExpected += count($expectedMethods);
    $totalFound += count($foundMethods);
}

if (isset($expectedServiceMethods)) {
    $totalExpected += count($expectedServiceMethods);
    $totalFound += count($foundServiceMethods);
}

if (isset($expectedModelMethods)) {
    $totalExpected += count($expectedModelMethods);
    $totalFound += count($foundModelMethods);
}

if ($totalExpected > 0) {
    $completionRate = round(($totalFound / $totalExpected) * 100, 1);
    echo "\nOverall Completion: $completionRate%\n";
    
    if ($completionRate >= 90) {
        echo "✅ Attendance features are COMPLETE and ready!\n";
    } elseif ($completionRate >= 70) {
        echo "⚠️  Attendance features are MOSTLY complete but need some work\n";
    } else {
        echo "❌ Attendance features need significant work\n";
    }
} else {
    echo "\n❌ Unable to calculate completion rate\n";
}

echo "\n=== Test Complete ===\n";
