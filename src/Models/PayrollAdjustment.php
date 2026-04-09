<?php

namespace Models;

use Core\Model;
use Core\ValidationResult;

class PayrollAdjustment extends Model
{
    protected string $table = 'payroll_adjustments';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'payroll_line_item_id',
        'adjustment_type',
        'category',
        'amount',
        'reason',
        'created_by'
    ];

    protected array $casts = [
        'amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getByLineItem(string $lineItemId): array
    {
        try {
            return $this->where(['payroll_line_item_id' => $lineItemId])->orderBy('created_at', 'DESC')->get();
        } catch (\Exception $e) {
            $this->handleDatabaseError($e, 'getByLineItem', ['payroll_line_item_id' => $lineItemId]);
            return [];
        }
    }

    protected function validate(array $data, $id = null): ValidationResult
    {
        $errors = [];

        if ($id === null) {
            foreach (['payroll_line_item_id', 'adjustment_type', 'category', 'amount', 'reason'] as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }
        }

        if (isset($data['adjustment_type'])) {
            $validTypes = ['Earning', 'Deduction'];
            if (!in_array($data['adjustment_type'], $validTypes, true)) {
                $errors['adjustment_type'] = 'Invalid adjustment type. Must be one of: ' . implode(', ', $validTypes);
            }
        }

        if (isset($data['amount']) && (!is_numeric($data['amount']) || (float) $data['amount'] < 0)) {
            $errors['amount'] = 'Amount must be a non-negative number';
        }

        return new ValidationResult(empty($errors), $errors, $data);
    }
}
