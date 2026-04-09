<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

class PayrollPeriod extends Model
{
    protected string $table = 'payroll_periods';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'code',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'created_by',
        'finalized_by',
        'finalized_at',
        'paid_by',
        'paid_at'
    ];

    protected array $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'finalized_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    public function getByStatus(string $status): array
    {
        try {
            return $this->where(['status' => $status])->orderBy('start_date', 'DESC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByStatus', ['status' => $status]);
            return [];
        }
    }

    public function findOverlappingPeriod(string $startDate, string $endDate, ?string $excludeId = null): ?array
    {
        try {
            $periods = $this->all();
            foreach ($periods as $period) {
                if ($excludeId && ($period['id'] ?? null) === $excludeId) {
                    continue;
                }
                if (($period['start_date'] ?? '') <= $endDate && ($period['end_date'] ?? '') >= $startDate) {
                    return $period;
                }
            }
            return null;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'findOverlappingPeriod', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'exclude_id' => $excludeId
            ]);
            return null;
        }
    }

    public function getCurrentDraft(?string $date = null): ?array
    {
        try {
            $targetDate = $date ?? date('Y-m-d');
            $draftPeriods = $this->where(['status' => 'Draft'])->orderBy('start_date', 'DESC')->get();
            foreach ($draftPeriods as $period) {
                if (($period['start_date'] ?? '') <= $targetDate && ($period['end_date'] ?? '') >= $targetDate) {
                    return $period;
                }
            }
            return $draftPeriods[0] ?? null;
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getCurrentDraft', ['date' => $date]);
            return null;
        }
    }

    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];

        if ($id === null) {
            $requiredFields = ['code', 'start_date', 'end_date', 'pay_date'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }

        if (isset($data['code']) && mb_strlen($data['code']) > 20) {
            $errors['code'] = 'Code must not exceed 20 characters';
        }

        foreach (['start_date', 'end_date', 'pay_date'] as $dateField) {
            if (isset($data[$dateField]) && !empty($data[$dateField])) {
                $dateObj = \DateTime::createFromFormat('Y-m-d', $data[$dateField]);
                if (!$dateObj || $dateObj->format('Y-m-d') !== $data[$dateField]) {
                    $errors[$dateField] = ucfirst(str_replace('_', ' ', $dateField)) . ' must be in Y-m-d format';
                }
            }
        }

        if (
            isset($data['start_date'], $data['end_date']) &&
            empty($errors['start_date']) &&
            empty($errors['end_date']) &&
            $data['start_date'] > $data['end_date']
        ) {
            $errors['end_date'] = 'End date must be on or after start date';
        }

        if (
            isset($data['pay_date'], $data['end_date']) &&
            empty($errors['pay_date']) &&
            empty($errors['end_date']) &&
            $data['pay_date'] < $data['end_date']
        ) {
            $errors['pay_date'] = 'Pay date must be on or after end date';
        }

        if (isset($data['status'])) {
            $validStatuses = ['Draft', 'Processing', 'Finalized', 'Paid', 'Cancelled'];
            if (!in_array($data['status'], $validStatuses, true)) {
                $errors['status'] = 'Invalid status. Must be one of: ' . implode(', ', $validStatuses);
            }
        }

        return new ValidationResult(empty($errors), $errors, $data);
    }
}
