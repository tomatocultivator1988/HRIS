<?php

namespace Services;

use Core\BusinessLogicException;
use Core\NotFoundException;
use Core\ValidationException;
use Models\Employee;
use Models\PositionSalary;

class CompensationService
{
    private Employee $employeeModel;
    private PositionSalary $positionSalaryModel;

    public function __construct(
        ?Employee $employeeModel = null,
        ?PositionSalary $positionSalaryModel = null
    ) {
        $container = \Core\Container::getInstance();
        $this->employeeModel = $employeeModel ?? $container->resolve(Employee::class);
        $this->positionSalaryModel = $positionSalaryModel ?? $container->resolve(PositionSalary::class);
    }

    public function listAllPositions(): array
    {
        // Get all positions that have salary records
        $positionSalaries = $this->positionSalaryModel->getAllActive();
        
        // Get all unique positions from employees
        $employees = $this->employeeModel->where(['is_active' => true])->get();
        $employeePositions = [];
        
        foreach ($employees as $emp) {
            $position = trim((string) ($emp['position'] ?? ''));
            $department = trim((string) ($emp['department'] ?? ''));
            
            if ($position !== '' && !isset($employeePositions[$position])) {
                $employeePositions[$position] = [
                    'position' => $position,
                    'department' => $department,
                    'has_salary' => false
                ];
            }
        }
        
        // Merge: mark which positions have salaries set
        foreach ($positionSalaries as $salary) {
            $position = (string) ($salary['position'] ?? '');
            if (isset($employeePositions[$position])) {
                $employeePositions[$position] = array_merge($employeePositions[$position], $salary);
                $employeePositions[$position]['has_salary'] = true;
            }
        }
        
        // Return all positions (with or without salary data)
        return array_values($employeePositions);
    }

    public function getPositionSalary(string $position): array
    {
        $salary = $this->positionSalaryModel->getByPosition($position);
        
        // If no salary record exists, get department from employees
        if (!$salary) {
            $employees = $this->employeeModel->where(['position' => $position, 'is_active' => true])->get();
            $department = count($employees) > 0 ? ($employees[0]['department'] ?? null) : null;
            
            return [
                'id' => null,
                'position' => $position,
                'department' => $department,
                'payroll_type' => 'Monthly',
                'base_salary' => 0,
                'daily_rate' => 0,
                'hourly_rate' => 0,
                'sss_employee_share' => 0,
                'philhealth_employee_share' => 0,
                'pagibig_employee_share' => 0,
                'tax_value' => 0,
                'standard_work_hours_per_day' => 8.00,
                'is_active' => true,
                'has_salary' => false
            ];
        }

        $salary['has_salary'] = true;
        return $salary;
    }

    public function createPositionSalary(array $data): array
    {
        $this->validatePositionSalaryData($data);

        $position = (string) ($data['position'] ?? '');
        
        // Check if position salary already exists
        $existing = $this->positionSalaryModel->getByPosition($position);
        if ($existing) {
            throw new BusinessLogicException('Salary for this position already exists');
        }

        $salaryData = [
            'position' => $position,
            'department' => $data['department'] ?? null,
            'payroll_type' => $data['payroll_type'] ?? 'Monthly',
            'base_salary' => (float) ($data['base_salary'] ?? 0),
            'daily_rate' => (float) ($data['daily_rate'] ?? 0),
            'hourly_rate' => (float) ($data['hourly_rate'] ?? 0),
            'sss_employee_share' => (float) ($data['sss_employee_share'] ?? 0),
            'philhealth_employee_share' => (float) ($data['philhealth_employee_share'] ?? 0),
            'pagibig_employee_share' => (float) ($data['pagibig_employee_share'] ?? 0),
            'tax_value' => (float) ($data['tax_value'] ?? 0),
            'standard_work_hours_per_day' => (float) ($data['standard_work_hours_per_day'] ?? 8.00),
            'is_active' => true
        ];

        return $this->positionSalaryModel->create($salaryData);
    }

    public function updatePositionSalary(string $positionSalaryId, array $data): array
    {
        $salary = $this->positionSalaryModel->find($positionSalaryId);
        if (!$salary) {
            throw new NotFoundException('Position salary record not found');
        }

        $this->validatePositionSalaryData($data, $positionSalaryId);

        $updateData = [
            'payroll_type' => $data['payroll_type'] ?? $salary['payroll_type'],
            'base_salary' => (float) ($data['base_salary'] ?? $salary['base_salary']),
            'daily_rate' => (float) ($data['daily_rate'] ?? $salary['daily_rate']),
            'hourly_rate' => (float) ($data['hourly_rate'] ?? $salary['hourly_rate']),
            'sss_employee_share' => (float) ($data['sss_employee_share'] ?? $salary['sss_employee_share']),
            'philhealth_employee_share' => (float) ($data['philhealth_employee_share'] ?? $salary['philhealth_employee_share']),
            'pagibig_employee_share' => (float) ($data['pagibig_employee_share'] ?? $salary['pagibig_employee_share']),
            'tax_value' => (float) ($data['tax_value'] ?? $salary['tax_value']),
            'standard_work_hours_per_day' => (float) ($data['standard_work_hours_per_day'] ?? $salary['standard_work_hours_per_day']),
            'department' => $data['department'] ?? $salary['department']
        ];

        $this->positionSalaryModel->update($positionSalaryId, $updateData);
        return $this->positionSalaryModel->find($positionSalaryId) ?? $salary;
    }

    private function validatePositionSalaryData(array $data, ?string $positionSalaryId = null): void
    {
        $errors = [];

        if ($positionSalaryId === null && empty($data['position'])) {
            $errors['position'] = 'Position is required';
        }

        if (isset($data['payroll_type']) && !in_array($data['payroll_type'], ['Monthly', 'Daily', 'Hourly'], true)) {
            $errors['payroll_type'] = 'Invalid payroll type. Must be Monthly, Daily, or Hourly';
        }

        $numericFields = ['base_salary', 'daily_rate', 'hourly_rate', 'sss_employee_share', 'philhealth_employee_share', 'pagibig_employee_share', 'tax_value', 'standard_work_hours_per_day'];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && (!is_numeric($data[$field]) || (float) $data[$field] < 0)) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a non-negative number';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    // Legacy methods for backward compatibility (deprecated)
    public function listEmployeesWithCompensation(): array
    {
        $employees = $this->employeeModel->where(['is_active' => true])->get();
        
        foreach ($employees as &$employee) {
            $position = (string) ($employee['position'] ?? '');
            if ($position !== '') {
                $salary = $this->positionSalaryModel->getByPosition($position);
                $employee['compensation'] = $salary;
            }
        }
        unset($employee);

        return $employees;
    }

    public function getActiveCompensation(string $employeeId): array
    {
        $employee = $this->employeeModel->find($employeeId);
        if (!$employee) {
            throw new NotFoundException('Employee not found');
        }

        $position = (string) ($employee['position'] ?? '');
        if ($position === '') {
            return [
                'id' => null,
                'employee_id' => $employeeId,
                'payroll_type' => 'Monthly',
                'base_salary' => 0,
                'daily_rate' => 0,
                'hourly_rate' => 0,
                'sss_employee_share' => 0,
                'philhealth_employee_share' => 0,
                'pagibig_employee_share' => 0,
                'tax_value' => 0,
                'effective_start_date' => date('Y-m-d'),
                'effective_end_date' => null,
                'is_active' => true
            ];
        }

        $salary = $this->positionSalaryModel->getByPosition($position);
        if (!$salary) {
            return [
                'id' => null,
                'employee_id' => $employeeId,
                'payroll_type' => 'Monthly',
                'base_salary' => 0,
                'daily_rate' => 0,
                'hourly_rate' => 0,
                'sss_employee_share' => 0,
                'philhealth_employee_share' => 0,
                'pagibig_employee_share' => 0,
                'tax_value' => 0,
                'effective_start_date' => date('Y-m-d'),
                'effective_end_date' => null,
                'is_active' => true
            ];
        }

        return $salary;
    }

    public function createCompensation(array $data): array
    {
        throw new BusinessLogicException('Per-employee compensation is deprecated. Please use position-based salaries instead.');
    }

    public function updateCompensation(string $compensationId, array $data): array
    {
        throw new BusinessLogicException('Per-employee compensation is deprecated. Please use position-based salaries instead.');
    }

    private function validateCompensationData(array $data, ?string $compensationId = null): void
    {
        $errors = [];

        if ($compensationId === null && empty($data['employee_id'])) {
            $errors['employee_id'] = 'Employee ID is required';
        }

        if (isset($data['payroll_type']) && !in_array($data['payroll_type'], ['Monthly', 'Daily', 'Hourly'], true)) {
            $errors['payroll_type'] = 'Invalid payroll type. Must be Monthly, Daily, or Hourly';
        }

        $numericFields = ['base_salary', 'daily_rate', 'hourly_rate', 'sss_employee_share', 'philhealth_employee_share', 'pagibig_employee_share', 'tax_value'];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && (!is_numeric($data[$field]) || (float) $data[$field] < 0)) {
                $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a non-negative number';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }
}
