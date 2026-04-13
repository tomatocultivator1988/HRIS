<?php

namespace Services;

use Core\BusinessLogicException;
use Core\NotFoundException;
use Core\ValidationException;
use Models\Attendance;
use Models\Employee;
use Models\EmployeeCompensation;
use Models\PayrollAdjustment;
use Models\PayrollLineItem;
use Models\PayrollPeriod;
use Models\PayrollRun;
use Models\PositionSalary;

class PayrollService
{
    private PayrollPeriod $periodModel;
    private EmployeeCompensation $compensationModel;
    private PositionSalary $positionSalaryModel;
    private PayrollRun $runModel;
    private PayrollLineItem $lineItemModel;
    private PayrollAdjustment $adjustmentModel;
    private Employee $employeeModel;
    private Attendance $attendanceModel;
    private float $overtimeMultiplier;

    public function __construct(
        ?PayrollPeriod $periodModel = null,
        ?EmployeeCompensation $compensationModel = null,
        ?PayrollRun $runModel = null,
        ?PayrollLineItem $lineItemModel = null,
        ?PayrollAdjustment $adjustmentModel = null,
        ?Employee $employeeModel = null,
        ?Attendance $attendanceModel = null,
        ?PositionSalary $positionSalaryModel = null
    ) {
        $container = \Core\Container::getInstance();
        $this->periodModel = $periodModel ?? $container->resolve(PayrollPeriod::class);
        $this->compensationModel = $compensationModel ?? $container->resolve(EmployeeCompensation::class);
        $this->positionSalaryModel = $positionSalaryModel ?? $container->resolve(PositionSalary::class);
        $this->runModel = $runModel ?? $container->resolve(PayrollRun::class);
        $this->lineItemModel = $lineItemModel ?? $container->resolve(PayrollLineItem::class);
        $this->adjustmentModel = $adjustmentModel ?? $container->resolve(PayrollAdjustment::class);
        $this->employeeModel = $employeeModel ?? $container->resolve(Employee::class);
        $this->attendanceModel = $attendanceModel ?? $container->resolve(Attendance::class);
        $this->overtimeMultiplier = (float) env('PAYROLL_OVERTIME_MULTIPLIER', 1.25);
    }

    public function createPayrollPeriod(array $data): array
    {
        $code = (string) ($data['code'] ?? '');
        return $this->withOperationLock('period:create:' . $code, function () use ($data): array {
            $required = ['code', 'start_date', 'end_date', 'pay_date'];
            $errors = [];

            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
                }
            }

            if (!empty($errors)) {
                throw new ValidationException('Validation failed', $errors);
            }

            $existing = $this->periodModel->where(['code' => $data['code']])->first();
            if ($existing) {
                throw new BusinessLogicException('Payroll period code already exists');
            }

            $overlap = $this->periodModel->findOverlappingPeriod($data['start_date'], $data['end_date']);
            if ($overlap) {
                throw new BusinessLogicException('Payroll period overlaps with an existing period');
            }

            $data['status'] = $data['status'] ?? 'Draft';

            return $this->periodModel->create($data);
        });
    }

    public function listPayrollPeriods(array $filters = []): array
    {
        $periods = $this->periodModel->all([], ['start_date' => 'DESC']);

        if (!empty($filters['status'])) {
            $periods = array_values(array_filter($periods, function (array $period) use ($filters): bool {
                return ($period['status'] ?? null) === $filters['status'];
            }));
        }

        if (!empty($filters['year'])) {
            $year = (string) $filters['year'];
            $periods = array_values(array_filter($periods, function (array $period) use ($year): bool {
                $startDate = (string) ($period['start_date'] ?? '');
                return strpos($startDate, $year . '-') === 0;
            }));
        }

        return [
            'periods' => $periods,
            'total' => count($periods)
        ];
    }

    public function generatePayrollRun(string $periodId, array $options = []): array
    {
        return $this->withOperationLock('generate:' . $periodId, function () use ($periodId, $options): array {
            // Start performance monitoring
            \Core\PerformanceMonitor::start('payroll_generation_total');
            
            $period = $this->periodModel->find($periodId);
            if (!$period) {
                throw new NotFoundException('Payroll period not found');
            }

            $periodStatus = (string) ($period['status'] ?? 'Draft');
            if (in_array($periodStatus, ['Finalized', 'Paid', 'Cancelled'], true)) {
                throw new BusinessLogicException('Cannot generate payroll run for finalized/paid/cancelled period');
            }

            $existingRuns = $this->runModel->getByPeriod($periodId);
            $runNumber = empty($existingRuns) ? 1 : ((int) ($existingRuns[0]['run_number'] ?? 0) + 1);

            $run = $this->runModel->createRun([
                'payroll_period_id' => $periodId,
                'run_number' => $runNumber,
                'status' => 'Draft',
                'generated_by' => $options['actor_id'] ?? null,
                'generated_at' => date('Y-m-d H:i:s'),
                'employee_count' => 0,
                'total_gross' => 0,
                'total_deductions' => 0,
                'total_net' => 0
            ]);

            if (!isset($run['id'])) {
                throw new BusinessLogicException('Failed to create payroll run');
            }

            try {
                $employees = $this->employeeModel->where(['is_active' => true])->get();
                if (!empty($options['employee_ids']) && is_array($options['employee_ids'])) {
                    $allowedIds = array_flip($options['employee_ids']);
                    $employees = array_values(array_filter($employees, function (array $employee) use ($allowedIds): bool {
                        $id = (string) ($employee['id'] ?? '');
                        return isset($allowedIds[$id]);
                    }));
                }

                $includeOvertime = !isset($options['include_overtime']) || (bool) $options['include_overtime'] === true;
                $periodStart = (string) ($period['start_date'] ?? '');
                $periodEnd = (string) ($period['end_date'] ?? '');
                $periodDays = max(1, $this->getDateDiffInDays($periodStart, $periodEnd));

                // ========================================
                // PERFORMANCE OPTIMIZATION: Batch Load All Data First
                // ========================================
                // OLD: Made 2-3 HTTP requests PER employee (200-300 total for 100 employees)
                // NEW: Make ~3 HTTP requests TOTAL regardless of employee count
                
                // 1. Batch load all position salaries (1 query)
                $allPositionSalaries = $this->positionSalaryModel->getAllActive();
                $positionSalaryMap = [];
                foreach ($allPositionSalaries as $salary) {
                    $positionSalaryMap[$salary['position']] = $salary;
                }
                
                // 2. Batch load all employee IDs for attendance query
                $employeeIds = array_map(function($emp) { return $emp['id']; }, $employees);
                
                // 3. Batch load ALL attendance records for the period (1 query)
                // Get all attendance records and filter in PHP
                $allAttendance = $this->attendanceModel->all();
                $attendanceByEmployee = [];
                foreach ($allAttendance as $record) {
                    $empId = $record['employee_id'];
                    $date = $record['date'];
                    
                    // Filter by employee IDs and date range
                    if (in_array($empId, $employeeIds) && $date >= $periodStart && $date <= $periodEnd) {
                        if (!isset($attendanceByEmployee[$empId])) {
                            $attendanceByEmployee[$empId] = [];
                        }
                        $attendanceByEmployee[$empId][] = $record;
                    }
                }
                
                // 4. Batch load employee compensations if needed (1 query)
                $allCompensations = $this->compensationModel->all();
                $compensationByEmployee = [];
                foreach ($allCompensations as $comp) {
                    $empId = $comp['employee_id'];
                    // Store active compensations
                    if (($comp['is_active'] ?? false) || 
                        (isset($comp['effective_date']) && $comp['effective_date'] <= $periodEnd)) {
                        $compensationByEmployee[$empId] = $comp;
                    }
                }

                $totals = [
                    'employee_count' => 0,
                    'total_gross' => 0.0,
                    'total_deductions' => 0.0,
                    'total_net' => 0.0
                ];

                $lineItems = [];

                // Now loop through employees using PRE-LOADED data (no queries in loop!)
                foreach ($employees as $employee) {
                    $employeeId = (string) ($employee['id'] ?? '');
                    if ($employeeId === '') {
                        continue;
                    }

                    // Get compensation from pre-loaded position salary map
                    $position = (string) ($employee['position'] ?? '');
                    $compensation = null;
                    
                    if ($position !== '' && isset($positionSalaryMap[$position])) {
                        $compensation = $positionSalaryMap[$position];
                    }
                    
                    // Fallback to pre-loaded employee compensation
                    if (!$compensation && isset($compensationByEmployee[$employeeId])) {
                        $compensation = $compensationByEmployee[$employeeId];
                    }
                    
                    if (!$compensation) {
                        continue;
                    }

                    // Get attendance from pre-loaded map
                    $attendance = $attendanceByEmployee[$employeeId] ?? [];
                    
                    $linePayload = $this->buildLineItemPayload($employeeId, $compensation, $attendance, $periodDays, $includeOvertime);
                    $lineItem = $this->lineItemModel->upsertLineItem($run['id'], $employeeId, $linePayload);

                    $totals['employee_count']++;
                    $totals['total_gross'] += (float) ($lineItem['gross_pay'] ?? 0);
                    $totals['total_deductions'] += (float) ($lineItem['total_deductions'] ?? 0);
                    $totals['total_net'] += (float) ($lineItem['net_pay'] ?? 0);
                    $lineItems[] = $lineItem;
                }

                $totals['total_gross'] = round($totals['total_gross'], 2);
                $totals['total_deductions'] = round($totals['total_deductions'], 2);
                $totals['total_net'] = round($totals['total_net'], 2);

                $this->runModel->update($run['id'], [
                    'status' => 'Computed',
                    'employee_count' => $totals['employee_count'],
                    'total_gross' => $totals['total_gross'],
                    'total_deductions' => $totals['total_deductions'],
                    'total_net' => $totals['total_net']
                ]);

                $this->periodModel->update($periodId, ['status' => 'Processing']);
                $freshRun = $this->runModel->find($run['id']);

                // End performance monitoring
                $duration = \Core\PerformanceMonitor::end('payroll_generation_total');
                error_log("Payroll generation completed in {$duration}ms for {$totals['employee_count']} employees");

                return [
                    'run' => $freshRun ?? $run,
                    'summary' => $totals,
                    'line_items' => $lineItems
                ];
            } catch (\Throwable $e) {
                if (!empty($run['id'])) {
                    $this->runModel->delete($run['id']);
                }
                throw $e;
            }
        });
    }

    public function recomputePayrollLine(string $runId, string $employeeId): array
    {
        return $this->withOperationLock('recompute:' . $runId . ':' . $employeeId, function () use ($runId, $employeeId): array {
            $run = $this->runModel->find($runId);
            if (!$run) {
                throw new NotFoundException('Payroll run not found');
            }

            if (($run['status'] ?? '') === 'Finalized' || ($run['status'] ?? '') === 'Paid' || ($run['status'] ?? '') === 'Approved') {
                throw new BusinessLogicException('Cannot recompute finalized, approved, or paid payroll run');
            }

            $periodId = (string) ($run['payroll_period_id'] ?? '');
            $period = $this->periodModel->find($periodId);
            if (!$period) {
                throw new NotFoundException('Payroll period not found');
            }

            // Get employee to fetch position
            $employee = $this->employeeModel->find($employeeId);
            if (!$employee) {
                throw new NotFoundException('Employee not found');
            }

            // Get compensation from position salary (new approach)
            $position = (string) ($employee['position'] ?? '');
            $compensation = null;
            
            if ($position !== '') {
                $compensation = $this->positionSalaryModel->getByPosition($position);
            }
            
            // Fallback to old employee compensation if position salary not found
            if (!$compensation) {
                $compensation = $this->compensationModel->getActiveByEmployeeAndDate($employeeId, (string) ($period['end_date'] ?? ''));
            }
            
            if (!$compensation) {
                throw new NotFoundException('Active compensation not found for employee');
            }

            $attendance = $this->attendanceModel->getByDateRange(
                $employeeId,
                (string) ($period['start_date'] ?? ''),
                (string) ($period['end_date'] ?? '')
            );

            $periodDays = max(1, $this->getDateDiffInDays((string) ($period['start_date'] ?? ''), (string) ($period['end_date'] ?? '')));
            $payload = $this->buildLineItemPayload($employeeId, $compensation, $attendance, $periodDays, true);
            $lineItem = $this->lineItemModel->upsertLineItem($runId, $employeeId, $payload);

            $this->runModel->update($runId, ['status' => 'Computed']);
            $this->refreshRunTotals($runId);

            return $lineItem;
        });
    }

    public function applyManualAdjustment(string $lineItemId, array $payload): array
    {
        return $this->withOperationLock('adjustment:' . $lineItemId, function () use ($lineItemId, $payload): array {
            $lineItem = $this->lineItemModel->find($lineItemId);
            if (!$lineItem) {
                throw new NotFoundException('Payroll line item not found');
            }

            $adjustmentType = (string) ($payload['adjustment_type'] ?? '');
            $amount = (float) ($payload['amount'] ?? 0);
            if (!in_array($adjustmentType, ['Earning', 'Deduction'], true)) {
                throw new ValidationException('Validation failed', ['adjustment_type' => 'Adjustment type must be Earning or Deduction']);
            }
            if ($amount < 0) {
                throw new ValidationException('Validation failed', ['amount' => 'Amount must be non-negative']);
            }
            if (empty($payload['category']) || empty($payload['reason'])) {
                throw new ValidationException('Validation failed', [
                    'category' => 'Category is required',
                    'reason' => 'Reason is required'
                ]);
            }

            $adjustment = $this->adjustmentModel->create([
                'payroll_line_item_id' => $lineItemId,
                'adjustment_type' => $adjustmentType,
                'category' => (string) $payload['category'],
                'amount' => $amount,
                'reason' => (string) $payload['reason'],
                'created_by' => $payload['actor_id'] ?? null
            ]);

            $updatePayload = [];
            if ($adjustmentType === 'Earning') {
                $updatePayload['adjustment_earnings'] = round(((float) ($lineItem['adjustment_earnings'] ?? 0)) + $amount, 2);
            } else {
                $updatePayload['adjustment_deductions'] = round(((float) ($lineItem['adjustment_deductions'] ?? 0)) + $amount, 2);
            }

            $gross = round(
                (float) ($lineItem['basic_pay'] ?? 0) +
                (float) ($lineItem['overtime_pay'] ?? 0) +
                (float) ($lineItem['leave_pay'] ?? 0) +
                (float) ($lineItem['allowance_total'] ?? 0) +
                ($updatePayload['adjustment_earnings'] ?? (float) ($lineItem['adjustment_earnings'] ?? 0)),
                2
            );

            $totalDeductions = round(
                (float) ($lineItem['tax_amount'] ?? 0) +
                (float) ($lineItem['sss_amount'] ?? 0) +
                (float) ($lineItem['philhealth_amount'] ?? 0) +
                (float) ($lineItem['pagibig_amount'] ?? 0) +
                (float) ($lineItem['loan_deductions'] ?? 0) +
                ($updatePayload['adjustment_deductions'] ?? (float) ($lineItem['adjustment_deductions'] ?? 0)),
                2
            );

            $updatePayload['gross_pay'] = $gross;
            $updatePayload['total_deductions'] = $totalDeductions;
            $updatePayload['net_pay'] = max(0, round($gross - $totalDeductions, 2));

            $this->lineItemModel->update($lineItemId, $updatePayload);
            $updatedLine = $this->lineItemModel->find($lineItemId);
            $runId = (string) ($lineItem['payroll_run_id'] ?? '');
            if ($runId !== '') {
                $this->refreshRunTotals($runId);
            }

            return [
                'adjustment' => $adjustment,
                'line_item' => $updatedLine
            ];
        });
    }

    public function finalizePayrollRun(string $runId, string $actorId): array
    {
        return $this->withOperationLock('finalize:' . $runId, function () use ($runId, $actorId): array {
            $run = $this->runModel->find($runId);
            if (!$run) {
                throw new NotFoundException('Payroll run not found');
            }

            $status = (string) ($run['status'] ?? '');
            if ($status === 'Paid') {
                throw new BusinessLogicException('Cannot finalize a paid payroll run');
            }
            if (!in_array($status, ['Draft', 'Computed'], true)) {
                throw new BusinessLogicException('Payroll run is not eligible for finalization');
            }

            $this->runModel->finalize($runId, $actorId);
            $updatedRun = $this->runModel->find($runId);

            $periodId = (string) ($run['payroll_period_id'] ?? '');
            if ($periodId !== '') {
                $this->periodModel->update($periodId, [
                    'status' => 'Finalized',
                    'finalized_by' => $actorId,
                    'finalized_at' => date('Y-m-d H:i:s')
                ]);
            }

            return $updatedRun ?? $run;
        });
    }

    public function markPayrollAsPaid(string $runId, array $paymentData, string $actorId): array
    {
        return $this->withOperationLock('pay:' . $runId, function () use ($runId, $paymentData, $actorId): array {
            $run = $this->runModel->find($runId);
            if (!$run) {
                throw new NotFoundException('Payroll run not found');
            }

            if (!in_array((string) ($run['status'] ?? ''), ['Finalized', 'Approved'], true)) {
                throw new BusinessLogicException('Payroll run must be finalized or approved before marking as paid');
            }

            $paidAt = $paymentData['payment_date'] ?? date('Y-m-d H:i:s');
            $this->runModel->update($runId, [
                'status' => 'Paid',
                'finalized_by' => $actorId,
                'finalized_at' => $paidAt,
                'notes' => $paymentData['payment_reference'] ?? ($run['notes'] ?? null)
            ]);

            $periodId = (string) ($run['payroll_period_id'] ?? '');
            if ($periodId !== '') {
                $this->periodModel->update($periodId, [
                    'status' => 'Paid',
                    'paid_by' => $actorId,
                    'paid_at' => $paidAt
                ]);
            }

            return $this->runModel->find($runId) ?? $run;
        });
    }

    public function approvePayrollRun(string $runId, string $actorId): array
    {
        return $this->withOperationLock('approve:' . $runId, function () use ($runId, $actorId): array {
            $run = $this->runModel->find($runId);
            if (!$run) {
                throw new NotFoundException('Payroll run not found');
            }

            if (($run['status'] ?? '') === 'Paid') {
                throw new BusinessLogicException('Cannot approve a paid payroll run');
            }
            if (($run['status'] ?? '') !== 'Finalized') {
                throw new BusinessLogicException('Payroll run must be finalized before approval');
            }

            $this->runModel->update($runId, [
                'status' => 'Approved',
                'finalized_by' => $actorId,
                'finalized_at' => date('Y-m-d H:i:s')
            ]);

            return $this->runModel->find($runId) ?? $run;
        });
    }

    public function reversePayrollRun(string $runId, array $payload, string $actorId): array
    {
        return $this->withOperationLock('reverse:' . $runId, function () use ($runId, $payload, $actorId): array {
            $run = $this->runModel->find($runId);
            if (!$run) {
                throw new NotFoundException('Payroll run not found');
            }

            $status = (string) ($run['status'] ?? '');
            if (!in_array($status, ['Computed', 'Finalized', 'Approved', 'Paid'], true)) {
                throw new BusinessLogicException('Payroll run is not eligible for reversal');
            }

            $reason = trim((string) ($payload['reason'] ?? ''));
            if ($reason === '') {
                throw new ValidationException('Validation failed', ['reason' => 'Reason is required for reversal']);
            }

            $existingNotes = (string) ($run['notes'] ?? '');
            $reversalNote = 'REVERSED by ' . $actorId . ': ' . $reason;
            $notes = trim($existingNotes . ' | ' . $reversalNote, ' |');

            $this->runModel->update($runId, [
                'status' => 'Reversed',
                'notes' => $notes
            ]);

            $periodId = (string) ($run['payroll_period_id'] ?? '');
            if ($periodId !== '') {
                $this->periodModel->update($periodId, [
                    'status' => 'Processing'
                ]);
            }

            return $this->runModel->find($runId) ?? $run;
        });
    }

    public function getPeriodRuns(string $periodId): array
    {
        $period = $this->periodModel->find($periodId);
        if (!$period) {
            throw new NotFoundException('Payroll period not found');
        }

        $runs = $this->runModel->getByPeriod($periodId);
        
        return [
            'period' => $period,
            'runs' => $runs,
            'total' => count($runs)
        ];
    }

    public function getRunSummary(string $runId): array
    {
        $run = $this->runModel->find($runId);
        if (!$run) {
            throw new NotFoundException('Payroll run not found');
        }

        $lineItems = $this->lineItemModel->getByRun($runId);
        
        // Enrich line items with employee data
        foreach ($lineItems as &$lineItem) {
            $employeeId = (string) ($lineItem['employee_id'] ?? '');
            if ($employeeId !== '') {
                $employee = $this->employeeModel->find($employeeId);
                $lineItem['employee'] = $employee;
            }
        }
        unset($lineItem);
        
        $totals = $this->calculateRunTotals($lineItems);

        return [
            'run' => $run,
            'summary' => $totals,
            'line_items' => $lineItems
        ];
    }

    public function markLineItemPaid(string $lineItemId, array $data): array
    {
        $lineItem = $this->lineItemModel->find($lineItemId);
        if (!$lineItem) {
            throw new NotFoundException('Payroll line item not found');
        }

        if (($lineItem['payment_status'] ?? 'Unpaid') === 'Paid') {
            throw new BusinessLogicException('This employee has already been marked as paid');
        }

        $updateData = [
            'payment_status' => 'Paid',
            'payment_reference' => $data['payment_reference'] ?? null,
            'remarks' => $data['remarks'] ?? null
        ];

        $this->lineItemModel->update($lineItemId, $updateData);
        return $this->lineItemModel->find($lineItemId) ?? $lineItem;
    }

    public function getEmployeePayslips(string $employeeId, array $filters = []): array
    {
        $limit = isset($filters['limit']) ? max(1, min(200, (int) $filters['limit'])) : 20;
        $offset = isset($filters['offset']) ? max(0, (int) $filters['offset']) : 0;

        $lineItems = $this->lineItemModel->getPayslipsByEmployee($employeeId, $limit, $offset);
        $payslips = [];

        foreach ($lineItems as $lineItem) {
            $runId = (string) ($lineItem['payroll_run_id'] ?? '');
            $run = $runId !== '' ? $this->runModel->find($runId) : null;
            $period = null;
            if ($run && !empty($run['payroll_period_id'])) {
                $period = $this->periodModel->find((string) $run['payroll_period_id']);
            }

            if (!$this->matchesPayslipFilters($period, $filters)) {
                continue;
            }

            $payslips[] = [
                'line_item' => $lineItem,
                'run' => $run,
                'period' => $period
            ];
        }

        return [
            'payslips' => $payslips,
            'total' => count($payslips),
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    public function getEmployeePayslipDetail(string $employeeId, string $lineItemId): array
    {
        $lineItem = $this->lineItemModel->find($lineItemId);
        if (!$lineItem) {
            throw new NotFoundException('Payslip not found');
        }
        if ((string) ($lineItem['employee_id'] ?? '') !== $employeeId) {
            throw new BusinessLogicException('You do not have access to this payslip');
        }

        $run = $this->runModel->find((string) ($lineItem['payroll_run_id'] ?? ''));
        $period = null;
        if ($run && !empty($run['payroll_period_id'])) {
            $period = $this->periodModel->find((string) $run['payroll_period_id']);
        }
        $adjustments = $this->adjustmentModel->getByLineItem($lineItemId);

        return [
            'line_item' => $lineItem,
            'run' => $run,
            'period' => $period,
            'adjustments' => $adjustments
        ];
    }

    private function buildLineItemPayload(
        string $employeeId,
        array $compensation,
        array $attendance,
        int $periodDays,
        bool $includeOvertime
    ): array {
        $attendanceDays = 0.0;
        $attendanceHours = 0.0;
        $lateMinutes = 0;
        $undertimeMinutes = 0;
        $overtimeHours = 0.0;
        $leaveDays = 0.0;

        foreach ($attendance as $record) {
            $hours = (float) ($record['work_hours'] ?? 0);
            $attendanceHours += $hours;
            if ($hours > 0) {
                $attendanceDays += 1;
            }

            $status = (string) ($record['status'] ?? '');
            if ($status === 'On Leave') {
                $leaveDays += 1;
            }
            if ($status === 'Late') {
                $lateMinutes += 15;
            }

            $standard = (float) ($compensation['standard_work_hours_per_day'] ?? 8);
            if ($hours > $standard) {
                $overtimeHours += ($hours - $standard);
            } elseif ($hours > 0 && $hours < $standard) {
                $undertimeMinutes += (int) round(($standard - $hours) * 60);
            }
        }

        $payrollType = (string) ($compensation['payroll_type'] ?? 'Monthly');
        $baseSalary = (float) ($compensation['base_salary'] ?? 0);
        $dailyRate = (float) ($compensation['daily_rate'] ?? 0);
        $hourlyRate = (float) ($compensation['hourly_rate'] ?? 0);

        if ($dailyRate <= 0 && $baseSalary > 0) {
            $dailyRate = $baseSalary / max(1, $periodDays);
        }
        if ($hourlyRate <= 0 && $dailyRate > 0) {
            $hourlyRate = $dailyRate / max(1, (float) ($compensation['standard_work_hours_per_day'] ?? 8));
        }

        $basicPay = 0.0;
        if ($payrollType === 'Daily') {
            $basicPay = $dailyRate * $attendanceDays;
        } elseif ($payrollType === 'Hourly') {
            $basicPay = $hourlyRate * $attendanceHours;
        } else {
            if ($baseSalary > 0) {
                $basicPay = ($baseSalary / max(1, $periodDays)) * $attendanceDays;
            } else {
                $basicPay = $dailyRate * $attendanceDays;
            }
        }

        $overtimePay = $includeOvertime ? ($overtimeHours * $hourlyRate * $this->overtimeMultiplier) : 0.0;
        $leavePay = $leaveDays * $dailyRate;
        $allowanceTotal = 0.0;
        $adjustmentEarnings = 0.0;

        $grossPay = round($basicPay + $overtimePay + $leavePay + $allowanceTotal + $adjustmentEarnings, 2);

        $taxAmount = (float) ($compensation['tax_value'] ?? 0);
        $sssAmount = (float) ($compensation['sss_employee_share'] ?? 0);
        $philhealthAmount = (float) ($compensation['philhealth_employee_share'] ?? 0);
        $pagibigAmount = (float) ($compensation['pagibig_employee_share'] ?? 0);
        $loanDeductions = 0.0;
        $adjustmentDeductions = 0.0;

        $totalDeductions = round($taxAmount + $sssAmount + $philhealthAmount + $pagibigAmount + $loanDeductions + $adjustmentDeductions, 2);
        $netPay = max(0, round($grossPay - $totalDeductions, 2));

        return [
            'employee_id' => $employeeId,
            'attendance_days' => round($attendanceDays, 2),
            'attendance_hours' => round($attendanceHours, 2),
            'late_minutes' => $lateMinutes,
            'undertime_minutes' => $undertimeMinutes,
            'overtime_hours' => round($overtimeHours, 2),
            'basic_pay' => round($basicPay, 2),
            'overtime_pay' => round($overtimePay, 2),
            'leave_pay' => round($leavePay, 2),
            'allowance_total' => round($allowanceTotal, 2),
            'adjustment_earnings' => round($adjustmentEarnings, 2),
            'gross_pay' => $grossPay,
            'tax_amount' => round($taxAmount, 2),
            'sss_amount' => round($sssAmount, 2),
            'philhealth_amount' => round($philhealthAmount, 2),
            'pagibig_amount' => round($pagibigAmount, 2),
            'loan_deductions' => round($loanDeductions, 2),
            'adjustment_deductions' => round($adjustmentDeductions, 2),
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'payment_status' => 'Unpaid'
        ];
    }

    private function refreshRunTotals(string $runId): void
    {
        $lineItems = $this->lineItemModel->getByRun($runId);
        $totals = $this->calculateRunTotals($lineItems);

        $this->runModel->update($runId, [
            'employee_count' => $totals['employee_count'],
            'total_gross' => $totals['total_gross'],
            'total_deductions' => $totals['total_deductions'],
            'total_net' => $totals['total_net']
        ]);
    }

    private function calculateRunTotals(array $lineItems): array
    {
        $employeeCount = 0;
        $totalGross = 0.0;
        $totalDeductions = 0.0;
        $totalNet = 0.0;

        foreach ($lineItems as $lineItem) {
            $employeeCount++;
            $totalGross += (float) ($lineItem['gross_pay'] ?? 0);
            $totalDeductions += (float) ($lineItem['total_deductions'] ?? 0);
            $totalNet += (float) ($lineItem['net_pay'] ?? 0);
        }

        return [
            'employee_count' => $employeeCount,
            'total_gross' => round($totalGross, 2),
            'total_deductions' => round($totalDeductions, 2),
            'total_net' => round($totalNet, 2)
        ];
    }

    private function matchesPayslipFilters(?array $period, array $filters): bool
    {
        if (!$period) {
            return false;
        }

        if (!empty($filters['year'])) {
            $year = (string) $filters['year'];
            $startDate = (string) ($period['start_date'] ?? '');
            if (strpos($startDate, $year . '-') !== 0) {
                return false;
            }
        }

        if (!empty($filters['month'])) {
            $month = str_pad((string) $filters['month'], 2, '0', STR_PAD_LEFT);
            $startDate = (string) ($period['start_date'] ?? '');
            if (substr($startDate, 5, 2) !== $month) {
                return false;
            }
        }

        return true;
    }

    private function getDateDiffInDays(string $startDate, string $endDate): int
    {
        $start = \DateTime::createFromFormat('Y-m-d', $startDate);
        $end = \DateTime::createFromFormat('Y-m-d', $endDate);
        if (!$start || !$end) {
            return 1;
        }

        return (int) $start->diff($end)->format('%a') + 1;
    }

    private function withOperationLock(string $scope, callable $callback)
    {
        $lockPath = dirname(__DIR__, 2) . '/storage/cache/payroll-locks';
        if (!is_dir($lockPath)) {
            mkdir($lockPath, 0755, true);
        }

        $filePath = $lockPath . '/' . md5($scope) . '.lock';
        $handle = fopen($filePath, 'c+');
        if ($handle === false) {
            throw new BusinessLogicException('Unable to acquire operation lock');
        }

        $locked = flock($handle, LOCK_EX);
        if (!$locked) {
            fclose($handle);
            throw new BusinessLogicException('Unable to acquire operation lock');
        }

        try {
            return $callback();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
