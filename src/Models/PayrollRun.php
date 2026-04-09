<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

class PayrollRun extends Model
{
    protected string $table = 'payroll_runs';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'payroll_period_id',
        'run_number',
        'status',
        'total_gross',
        'total_deductions',
        'total_net',
        'employee_count',
        'generated_by',
        'generated_at',
        'finalized_by',
        'finalized_at',
        'notes'
    ];

    protected array $casts = [
        'run_number' => 'integer',
        'total_gross' => 'float',
        'total_deductions' => 'float',
        'total_net' => 'float',
        'employee_count' => 'integer',
        'generated_at' => 'datetime',
        'finalized_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function createRun(array $data): array
    {
        return $this->create($data);
    }

    public function getByPeriod(string $periodId): array
    {
        try {
            return $this->where(['payroll_period_id' => $periodId])->orderBy('run_number', 'DESC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByPeriod', ['payroll_period_id' => $periodId]);
            return [];
        }
    }

    public function finalize(string $runId, string $actorId): bool
    {
        return $this->update($runId, [
            'status' => 'Finalized',
            'finalized_by' => $actorId,
            'finalized_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markPaid(string $runId, string $actorId): bool
    {
        return $this->update($runId, [
            'status' => 'Paid',
            'finalized_by' => $actorId,
            'finalized_at' => date('Y-m-d H:i:s')
        ]);
    }

    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];

        if ($id === null && empty($data['payroll_period_id'])) {
            $errors['payroll_period_id'] = 'Payroll period ID is required';
        }

        if (isset($data['status'])) {
            $validStatuses = ['Draft', 'Computed', 'Finalized', 'Approved', 'Paid', 'Reversed'];
            if (!in_array($data['status'], $validStatuses, true)) {
                $errors['status'] = 'Invalid status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }

        $numericFields = ['run_number', 'total_gross', 'total_deductions', 'total_net', 'employee_count'];
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
