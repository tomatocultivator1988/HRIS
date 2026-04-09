<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

class PositionSalary extends Model
{
    protected string $table = 'position_salaries';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'position',
        'department',
        'payroll_type',
        'base_salary',
        'daily_rate',
        'hourly_rate',
        'sss_employee_share',
        'philhealth_employee_share',
        'pagibig_employee_share',
        'tax_value',
        'standard_work_hours_per_day',
        'is_active'
    ];

    protected array $casts = [
        'base_salary' => 'float',
        'daily_rate' => 'float',
        'hourly_rate' => 'float',
        'sss_employee_share' => 'float',
        'philhealth_employee_share' => 'float',
        'pagibig_employee_share' => 'float',
        'tax_value' => 'float',
        'standard_work_hours_per_day' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getByPosition(string $position): ?array
    {
        try {
            return $this->where([
                'position' => $position,
                'is_active' => true
            ])->first();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByPosition', ['position' => $position]);
            return null;
        }
    }

    public function getAllActive(): array
    {
        try {
            return $this->where(['is_active' => true])->orderBy('position', 'ASC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getAllActive', []);
            return [];
        }
    }

    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];

        if ($id === null && empty($data['position'])) {
            $errors['position'] = 'Position is required';
        }

        if (isset($data['payroll_type'])) {
            $validPayrollTypes = ['Monthly', 'Daily', 'Hourly'];
            if (!in_array($data['payroll_type'], $validPayrollTypes, true)) {
                $errors['payroll_type'] = 'Invalid payroll type. Must be one of: ' . implode(', ', $validPayrollTypes);
            }
        }

        $numericFields = [
            'base_salary',
            'daily_rate',
            'hourly_rate',
            'sss_employee_share',
            'philhealth_employee_share',
            'pagibig_employee_share',
            'tax_value',
            'standard_work_hours_per_day'
        ];
        
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                if (!is_numeric($data[$field]) || (float) $data[$field] < 0) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' must be a non-negative number';
                }
            }
        }

        return new ValidationResult(empty($errors), $errors, $data);
    }
}
