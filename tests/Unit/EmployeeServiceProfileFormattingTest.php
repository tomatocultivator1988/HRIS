<?php

require_once __DIR__ . '/../../src/autoload.php';

use Services\EmployeeService;

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' | Expected: ' . var_export($expected, true) . ' | Actual: ' . var_export($actual, true));
    }
}

function runEmployeeServiceProfileFormattingTests(): void
{
    $reflection = new ReflectionClass(EmployeeService::class);
    $service = $reflection->newInstanceWithoutConstructor();
    $formatMethod = $reflection->getMethod('formatEmployeeData');
    $formatMethod->setAccessible(true);

    $rawEmployee = [
        'id' => 'emp-123',
        'employee_id' => 'EMP-123',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'work_email' => 'jane@company.com',
        'mobile_number' => '',
        'department' => 'Engineering',
        'position' => 'Developer',
        'employment_status' => 'Regular',
        'date_hired' => new DateTime('2024-01-15 00:00:00', new DateTimeZone('Asia/Manila')),
        'manager_id' => null,
        'is_active' => true,
        'created_at' => new DateTime('2024-01-15 10:30:00', new DateTimeZone('UTC')),
        'updated_at' => ['date' => '2024-02-01 08:00:00']
    ];

    $formatted = $formatMethod->invoke($service, $rawEmployee);

    assertSameValue('2024-01-15', $formatted['date_hired'], 'date_hired should be normalized to Y-m-d');
    assertSameValue(null, $formatted['mobile_number'], 'empty string fields should be normalized to null');
    assertSameValue('2024-01-15 10:30:00', $formatted['created_at'], 'created_at should be normalized with time');
    assertSameValue('2024-02-01 08:00:00', $formatted['updated_at'], 'updated_at array payload should be normalized with time');
    assertSameValue('Jane Doe', $formatted['full_name'], 'full_name should be generated correctly');
}

try {
    runEmployeeServiceProfileFormattingTests();
    echo "EmployeeServiceProfileFormattingTest: PASS\n";
} catch (Throwable $exception) {
    echo "EmployeeServiceProfileFormattingTest: FAIL\n";
    echo $exception->getMessage() . "\n";
    exit(1);
}
