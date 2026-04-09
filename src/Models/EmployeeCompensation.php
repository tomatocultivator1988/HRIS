<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

class EmployeeCompensation extends Model
{
    protected string $table = 'employee_compensation';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'employee_id',
        'payroll_type',
        'base_salary',
        'daily_rate',
        'hourly_rate',
        'standard_work_hours_per_day',
        'tax_mode',
        'tax_value',
        'sss_employee_share',
        'philhealth_employee_share',
        'pagibig_employee_share',
        'effective_start_date',
        'effective_end_date',
        'is_active'
    ];

    protected array $casts = [
        'base_salary' => 'float',
        'daily_rate' => 'float',
        'hourly_rate' => 'float',
        'standard_work_hours_per_day' => 'float',
        'tax_value' => 'float',
        'sss_employee_share' => 'float',
        'philhealth_employee_share' => 'float',
        'pagibig_employee_share' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getActiveByEmployee(string $employeeId): ?array
    {
        try {
            $records = $this->where([
                'employee_id' => $employeeId,
                'is_active' => true
            ])->orderBy('effective_start_date', 'DESC')->get();

            return $records[0] ?? null;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getActiveByEmployee', ['employee_id' => $employeeId]);
            return null;
        }
    }

    public function getActiveByEmployeeAndDate(string $employeeId, string $date): ?array
    {
        try {
            $records = $this->where([
                'employee_id' => $employeeId,
                'is_active' => true
            ])->orderBy('effective_start_date', 'DESC')->get();

            foreach ($records as $record) {
                $start = $record['effective_start_date'] ?? '';
                $end = $record['effective_end_date'] ?? null;
                if ($start <= $date && ($end === null || $end === '' || $date <= $end)) {
                    return $record;
                }
            }

            return null;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getActiveByEmployeeAndDate', [
                'employee_id' => $employeeId,
                'date' => $date
            ]);
            return null;
        }
    }

    public function getByEmployee(string $employeeId): array
    {
        try {
            return $this->where(['employee_id' => $employeeId])->orderBy('effective_start_date', 'DESC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByEmployee', ['employee_id' => $employeeId]);
            return [];
        }
    }

    public function bulkUpsert(array $rows): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0
        ];

        foreach ($rows as $row) {
            try {
                if (!empty($row['id'])) {
                    $updated = $this->update($row['id'], $row);
                    if ($updated) {
                        $results['updated']++;
                    } else {
                        $results['failed']++;
                    }
                } else {
                    $this->create($row);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }

    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];

        if ($id === null) {
            $requiredFields = ['employee_id', 'payroll_type', 'effective_start_date'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }

        if (isset($data['payroll_type'])) {
            $validPayrollTypes = ['Monthly', 'Daily', 'Hourly'];
            if (!in_array($data['payroll_type'], $validPayrollTypes, true)) {
                $errors['payroll_type'] = 'Invalid payroll type. Must be one of: ' . implode(', ', $validPayrollTypes);
            }
        }

        if (isset($data['tax_mode'])) {
            $validTaxModes = ['Flat', 'Bracketed', 'Exempt'];
            if (!in_array($data['tax_mode'], $validTaxModes, true)) {
                $errors['tax_mode'] = 'Invalid tax mode. Must be one of: ' . implode(', ', $validTaxModes);
            }
        }

        foreach (['effective_start_date', 'effective_end_date'] as $dateField) {
            if (isset($data[$dateField]) && !empty($data[$dateField])) {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $data[$dateField]);
                if (!$dateObj || $dateObj->format('Y-m-d') !== $data[$dateField]) {
                    $errors[$dateField] = ucfirst(str_replace('_', ' ', $dateField)) . ' must be in Y-m-d format';
                }
            }
        }

        if (
            isset($data['effective_start_date'], $data['effective_end_date']) &&
            !empty($data['effective_end_date']) &&
            empty($errors['effective_start_date']) &&
            empty($errors['effective_end_date']) &&
            $data['effective_start_date'] > $data['effective_end_date']
        ) {
            $errors['effective_end_date'] = 'Effective end date must be on or after effective start date';
        }

        $numericFields = [
            'base_salary',
            'daily_rate',
            'hourly_rate',
            'standard_work_hours_per_day',
            'tax_value',
            'sss_employee_share',
            'philhealth_employee_share',
            'pagibig_employee_share'
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
