<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

class PayrollLineItem extends Model
{
    protected string $table = 'payroll_line_items';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'payroll_run_id',
        'employee_id',
        'attendance_days',
        'attendance_hours',
        'late_minutes',
        'undertime_minutes',
        'overtime_hours',
        'basic_pay',
        'overtime_pay',
        'leave_pay',
        'allowance_total',
        'adjustment_earnings',
        'gross_pay',
        'tax_amount',
        'sss_amount',
        'philhealth_amount',
        'pagibig_amount',
        'loan_deductions',
        'adjustment_deductions',
        'total_deductions',
        'net_pay',
        'payment_status',
        'payment_reference',
        'remarks'
    ];

    protected array $casts = [
        'attendance_days' => 'float',
        'attendance_hours' => 'float',
        'late_minutes' => 'integer',
        'undertime_minutes' => 'integer',
        'overtime_hours' => 'float',
        'basic_pay' => 'float',
        'overtime_pay' => 'float',
        'leave_pay' => 'float',
        'allowance_total' => 'float',
        'adjustment_earnings' => 'float',
        'gross_pay' => 'float',
        'tax_amount' => 'float',
        'sss_amount' => 'float',
        'philhealth_amount' => 'float',
        'pagibig_amount' => 'float',
        'loan_deductions' => 'float',
        'adjustment_deductions' => 'float',
        'total_deductions' => 'float',
        'net_pay' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getByRun(string $runId): array
    {
        try {
            return $this->where(['payroll_run_id' => $runId])->orderBy('employee_id', 'ASC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByRun', ['payroll_run_id' => $runId]);
            return [];
        }
    }

    public function upsertLineItem(string $runId, string $employeeId, array $payload): array
    {
        $existing = $this->where([
            'payroll_run_id' => $runId,
            'employee_id' => $employeeId
        ])->first();

        if ($existing) {
            $this->update($existing['id'], $payload);
            return $this->find($existing['id']) ?? $existing;
        }

        $payload['payroll_run_id'] = $runId;
        $payload['employee_id'] = $employeeId;
        return $this->create($payload);
    }

    public function getPayslipsByEmployee(string $employeeId, int $limit = 20, int $offset = 0): array
    {
        try {
            return $this->where(['employee_id' => $employeeId])->orderBy('created_at', 'DESC')->limit($limit, $offset)->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getPayslipsByEmployee', [
                'employee_id' => $employeeId,
                'limit' => $limit,
                'offset' => $offset
            ]);
            return [];
        }
    }

    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];

        if ($id === null) {
            foreach (['payroll_run_id', 'employee_id'] as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }

        if (isset($data['payment_status'])) {
            $validPaymentStatuses = ['Unpaid', 'Paid', 'Hold'];
            if (!in_array($data['payment_status'], $validPaymentStatuses, true)) {
                $errors['payment_status'] = 'Invalid payment status. Must be one of: ' . implode(', ', $validPaymentStatuses);
            }
        }

        $numericFields = [
            'attendance_days',
            'attendance_hours',
            'late_minutes',
            'undertime_minutes',
            'overtime_hours',
            'basic_pay',
            'overtime_pay',
            'leave_pay',
            'allowance_total',
            'adjustment_earnings',
            'gross_pay',
            'tax_amount',
            'sss_amount',
            'philhealth_amount',
            'pagibig_amount',
            'loan_deductions',
            'adjustment_deductions',
            'total_deductions',
            'net_pay'
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
