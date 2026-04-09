<?php

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Core\BusinessLogicException;
use Core\NotFoundException;
use Models\Attendance;
use Models\Employee;
use Models\EmployeeCompensation;
use Models\PayrollAdjustment;
use Models\PayrollLineItem;
use Models\PayrollPeriod;
use Models\PayrollRun;
use Services\PayrollService;

class PayrollServiceTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        $this->testDuplicatePeriodRejected();
        $this->testGenerateRunCalculatesTotals();
        $this->testFinalizeAndMarkPaidTransition();
        $this->testApproveTransitionAndPayCompatibility();
        $this->testReverseRunRequiresReasonAndUpdatesStatus();
        $this->testRecomputeLineUpdatesRunAndBlocksAfterApproval();
        $this->testApproveRequiresFinalizedStatus();
        $this->testMarkPaidRejectsDraftRun();
        $this->testReverseCannotRunTwice();
        $this->testGenerateRunPerformanceWithThousandEmployees();
        $this->testManualAdjustmentUpdatesLineItem();
        $this->testPayslipDetailOwnershipProtection();
        $this->printSummary();
    }

    private function testDuplicatePeriodRejected(): void
    {
        $service = $this->createService();
        $thrown = false;

        try {
            $service->createPayrollPeriod([
                'code' => '2026-04',
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
                'pay_date' => '2026-05-05'
            ]);
        } catch (BusinessLogicException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Duplicate payroll period code is rejected');
    }

    private function testGenerateRunCalculatesTotals(): void
    {
        $service = $this->createService();
        $result = $service->generatePayrollRun('period-seed-1', ['include_overtime' => true, 'actor_id' => 'admin-1']);

        $this->assertTrue(isset($result['run']['id']), 'Generate run returns run id');
        $this->assertTrue(($result['run']['status'] ?? '') === 'Computed', 'Generated run is computed');
        $this->assertTrue(($result['summary']['employee_count'] ?? 0) === 1, 'Generate run includes expected employee count');
        $this->assertTrue(($result['summary']['total_net'] ?? 0) > 0, 'Generate run computes positive total net');
    }

    private function testFinalizeAndMarkPaidTransition(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $runId = (string) ($generated['run']['id'] ?? '');

        $finalized = $service->finalizePayrollRun($runId, 'admin-1');
        $this->assertTrue(($finalized['status'] ?? '') === 'Finalized', 'Finalize run sets status Finalized');

        $paid = $service->markPayrollAsPaid($runId, ['payment_reference' => 'BATCH-1'], 'admin-1');
        $this->assertTrue(($paid['status'] ?? '') === 'Paid', 'Mark paid sets status Paid');
    }

    private function testManualAdjustmentUpdatesLineItem(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $lineItems = $generated['line_items'] ?? [];
        $lineItemId = (string) (($lineItems[0]['id'] ?? ''));

        $before = $service->getEmployeePayslipDetail('emp-1', $lineItemId);
        $beforeNet = (float) ($before['line_item']['net_pay'] ?? 0);

        $result = $service->applyManualAdjustment($lineItemId, [
            'adjustment_type' => 'Deduction',
            'category' => 'Late Penalty',
            'amount' => 200,
            'reason' => 'Manual correction',
            'actor_id' => 'admin-1'
        ]);

        $afterNet = (float) ($result['line_item']['net_pay'] ?? 0);
        $this->assertTrue($afterNet <= $beforeNet, 'Manual deduction reduces or keeps net pay');
        $this->assertTrue(isset($result['adjustment']['id']), 'Manual adjustment is persisted');
    }

    private function testApproveTransitionAndPayCompatibility(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $runId = (string) ($generated['run']['id'] ?? '');

        $service->finalizePayrollRun($runId, 'admin-1');
        $approved = $service->approvePayrollRun($runId, 'approver-1');
        $this->assertTrue(($approved['status'] ?? '') === 'Approved', 'Approve run sets status Approved');

        $paid = $service->markPayrollAsPaid($runId, ['payment_reference' => 'BATCH-2'], 'admin-1');
        $this->assertTrue(($paid['status'] ?? '') === 'Paid', 'Mark paid accepts approved run');
    }

    private function testReverseRunRequiresReasonAndUpdatesStatus(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $runId = (string) ($generated['run']['id'] ?? '');

        $missingReasonThrown = false;
        try {
            $service->reversePayrollRun($runId, [], 'admin-1');
        } catch (\Core\ValidationException $e) {
            $missingReasonThrown = true;
        }
        $this->assertTrue($missingReasonThrown, 'Reverse run requires reason');

        $reversed = $service->reversePayrollRun($runId, ['reason' => 'Batch issue'], 'admin-1');
        $this->assertTrue(($reversed['status'] ?? '') === 'Reversed', 'Reverse run sets status Reversed');
    }

    private function testRecomputeLineUpdatesRunAndBlocksAfterApproval(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => true, 'actor_id' => 'admin-1']);
        $runId = (string) ($generated['run']['id'] ?? '');

        $line = $service->recomputePayrollLine($runId, 'emp-1');
        $this->assertTrue(isset($line['id']), 'Recompute line returns line item');

        $service->finalizePayrollRun($runId, 'admin-1');
        $service->approvePayrollRun($runId, 'approver-1');
        $thrown = false;
        try {
            $service->recomputePayrollLine($runId, 'emp-1');
        } catch (BusinessLogicException $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown, 'Recompute line blocked after approval');
    }

    private function testApproveRequiresFinalizedStatus(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $runId = (string) ($generated['run']['id'] ?? '');
        $thrown = false;

        try {
            $service->approvePayrollRun($runId, 'approver-1');
        } catch (BusinessLogicException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Approve run rejects non-finalized status');
    }

    private function testMarkPaidRejectsDraftRun(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $runId = (string) ($generated['run']['id'] ?? '');

        $thrown = false;
        try {
            $service->markPayrollAsPaid($runId, ['payment_reference' => 'BATCH-REJECT'], 'admin-1');
        } catch (BusinessLogicException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Mark paid rejects draft run');
    }

    private function testReverseCannotRunTwice(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $runId = (string) ($generated['run']['id'] ?? '');

        $service->reversePayrollRun($runId, ['reason' => 'First reverse'], 'admin-1');
        $thrown = false;
        try {
            $service->reversePayrollRun($runId, ['reason' => 'Second reverse'], 'admin-1');
        } catch (BusinessLogicException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Reverse run cannot be executed twice');
    }

    private function testGenerateRunPerformanceWithThousandEmployees(): void
    {
        $service = $this->createServiceWithDataset(1000);
        $start = microtime(true);
        $result = $service->generatePayrollRun('period-seed-1', ['include_overtime' => true, 'actor_id' => 'admin-1']);
        $elapsedMs = (microtime(true) - $start) * 1000;

        $this->assertTrue(($result['summary']['employee_count'] ?? 0) === 1000, 'Generate run processes 1000 employees');
        $this->assertTrue($elapsedMs < 5000, 'Generate run performance for 1000 employees is under 5000ms in test harness');
    }

    private function testPayslipDetailOwnershipProtection(): void
    {
        $service = $this->createService();
        $generated = $service->generatePayrollRun('period-seed-1', ['include_overtime' => false, 'actor_id' => 'admin-1']);
        $lineItems = $generated['line_items'] ?? [];
        $lineItemId = (string) (($lineItems[0]['id'] ?? ''));
        $thrown = false;

        try {
            $service->getEmployeePayslipDetail('emp-other', $lineItemId);
        } catch (BusinessLogicException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown, 'Payslip detail blocks access for non-owner employee');
    }

    private function createService(): PayrollService
    {
        return $this->createServiceWithDataset(1);
    }

    private function createServiceWithDataset(int $employeeCount): PayrollService
    {
        $periodModel = new FakePayrollPeriodModel();
        $compensationModel = new FakeEmployeeCompensationModel($employeeCount);
        $runModel = new FakePayrollRunModel();
        $lineItemModel = new FakePayrollLineItemModel();
        $adjustmentModel = new FakePayrollAdjustmentModel();
        $employeeModel = new FakeEmployeeModel($employeeCount);
        $attendanceModel = new FakeAttendanceModel($employeeCount);

        return new PayrollService(
            $periodModel,
            $compensationModel,
            $runModel,
            $lineItemModel,
            $adjustmentModel,
            $employeeModel,
            $attendanceModel
        );
    }

    private function assertTrue(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "PASS: {$message}\n";
            return;
        }

        $this->failed++;
        echo "FAIL: {$message}\n";
    }

    private function printSummary(): void
    {
        echo "\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        if ($this->failed > 0) {
            exit(1);
        }
    }
}

class FakeQuery
{
    private array $records;

    public function __construct(array $records)
    {
        $this->records = array_values($records);
    }

    public function first(): ?array
    {
        return $this->records[0] ?? null;
    }

    public function get(): array
    {
        return $this->records;
    }
}

class FakePayrollPeriodModel extends PayrollPeriod
{
    public array $periods = [];

    public function __construct()
    {
        $this->periods = [
            [
                'id' => 'period-seed-1',
                'code' => '2026-04',
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
                'pay_date' => '2026-05-05',
                'status' => 'Draft'
            ]
        ];
    }

    public function where(array $conditions): \Core\SupabaseQueryBuilder
    {
        $matches = array_filter($this->periods, function (array $period) use ($conditions): bool {
            foreach ($conditions as $key => $value) {
                if (($period[$key] ?? null) !== $value) {
                    return false;
                }
            }
            return true;
        });

        return new class(array_values($matches)) extends \Core\SupabaseQueryBuilder {
            private array $records;
            public function __construct(array $records) { $this->records = $records; }
            public function first(): ?array { return $this->records[0] ?? null; }
            public function get(): array { return $this->records; }
        };
    }

    public function find($id): ?array
    {
        foreach ($this->periods as $period) {
            if ($period['id'] === $id) {
                return $period;
            }
        }
        return null;
    }

    public function all(array $conditions = [], array $orderBy = [], ?int $limit = null, int $offset = 0): array
    {
        return $this->periods;
    }

    public function create(array $data): array
    {
        $data['id'] = 'period-new';
        $this->periods[] = $data;
        return $data;
    }

    public function findOverlappingPeriod(string $startDate, string $endDate, ?string $excludeId = null): ?array
    {
        foreach ($this->periods as $period) {
            if ($excludeId && $period['id'] === $excludeId) {
                continue;
            }
            if ($period['start_date'] <= $endDate && $period['end_date'] >= $startDate) {
                return $period;
            }
        }
        return null;
    }

    public function update($id, array $data): bool
    {
        foreach ($this->periods as $index => $period) {
            if ($period['id'] === $id) {
                $this->periods[$index] = array_merge($period, $data);
                return true;
            }
        }
        return false;
    }
}

class FakeEmployeeCompensationModel extends EmployeeCompensation
{
    public array $records = [];

    public function __construct(int $employeeCount = 1)
    {
        $count = max(1, $employeeCount);
        for ($i = 1; $i <= $count; $i++) {
            $this->records[] = [
                'id' => 'comp-' . $i,
                'employee_id' => 'emp-' . $i,
                'payroll_type' => 'Monthly',
                'base_salary' => 30000,
                'daily_rate' => 0,
                'hourly_rate' => 0,
                'standard_work_hours_per_day' => 8,
                'tax_mode' => 'Flat',
                'tax_value' => 500,
                'sss_employee_share' => 100,
                'philhealth_employee_share' => 100,
                'pagibig_employee_share' => 100,
                'effective_start_date' => '2026-01-01',
                'effective_end_date' => null,
                'is_active' => true
            ];
        }
    }

    public function getActiveByEmployeeAndDate(string $employeeId, string $date): ?array
    {
        foreach ($this->records as $record) {
            if ($record['employee_id'] === $employeeId && $record['is_active']) {
                return $record;
            }
        }
        return null;
    }
}

class FakePayrollRunModel extends PayrollRun
{
    public array $runs = [];

    public function __construct()
    {
    }

    public function createRun(array $data): array
    {
        $data['id'] = 'run-' . (count($this->runs) + 1);
        $this->runs[] = $data;
        return $data;
    }

    public function getByPeriod(string $periodId): array
    {
        return array_values(array_filter($this->runs, function (array $run) use ($periodId): bool {
            return ($run['payroll_period_id'] ?? '') === $periodId;
        }));
    }

    public function find($id): ?array
    {
        foreach ($this->runs as $run) {
            if (($run['id'] ?? '') === $id) {
                return $run;
            }
        }
        return null;
    }

    public function update($id, array $data): bool
    {
        foreach ($this->runs as $index => $run) {
            if (($run['id'] ?? '') === $id) {
                $this->runs[$index] = array_merge($run, $data);
                return true;
            }
        }
        return false;
    }

    public function finalize(string $runId, string $actorId): bool
    {
        return $this->update($runId, [
            'status' => 'Finalized',
            'finalized_by' => $actorId,
            'finalized_at' => date('Y-m-d H:i:s')
        ]);
    }
}

class FakePayrollLineItemModel extends PayrollLineItem
{
    public array $lineItems = [];

    public function __construct()
    {
    }

    public function upsertLineItem(string $runId, string $employeeId, array $payload): array
    {
        foreach ($this->lineItems as $index => $lineItem) {
            if (($lineItem['payroll_run_id'] ?? '') === $runId && ($lineItem['employee_id'] ?? '') === $employeeId) {
                $this->lineItems[$index] = array_merge($lineItem, $payload);
                return $this->lineItems[$index];
            }
        }

        $payload['id'] = 'line-' . (count($this->lineItems) + 1);
        $payload['payroll_run_id'] = $runId;
        $payload['employee_id'] = $employeeId;
        $this->lineItems[] = $payload;
        return $payload;
    }

    public function getByRun(string $runId): array
    {
        return array_values(array_filter($this->lineItems, function (array $lineItem) use ($runId): bool {
            return ($lineItem['payroll_run_id'] ?? '') === $runId;
        }));
    }

    public function find($id): ?array
    {
        foreach ($this->lineItems as $lineItem) {
            if (($lineItem['id'] ?? '') === $id) {
                return $lineItem;
            }
        }
        return null;
    }

    public function update($id, array $data): bool
    {
        foreach ($this->lineItems as $index => $lineItem) {
            if (($lineItem['id'] ?? '') === $id) {
                $this->lineItems[$index] = array_merge($lineItem, $data);
                return true;
            }
        }
        return false;
    }

    public function getPayslipsByEmployee(string $employeeId, int $limit = 20, int $offset = 0): array
    {
        $filtered = array_values(array_filter($this->lineItems, function (array $lineItem) use ($employeeId): bool {
            return ($lineItem['employee_id'] ?? '') === $employeeId;
        }));

        return array_slice($filtered, $offset, $limit);
    }
}

class FakePayrollAdjustmentModel extends PayrollAdjustment
{
    public array $adjustments = [];

    public function __construct()
    {
    }

    public function create(array $data): array
    {
        $data['id'] = 'adj-' . (count($this->adjustments) + 1);
        $this->adjustments[] = $data;
        return $data;
    }

    public function getByLineItem(string $lineItemId): array
    {
        return array_values(array_filter($this->adjustments, function (array $adjustment) use ($lineItemId): bool {
            return ($adjustment['payroll_line_item_id'] ?? '') === $lineItemId;
        }));
    }
}

class FakeEmployeeModel extends Employee
{
    public array $employees = [];

    public function __construct(int $employeeCount = 1)
    {
        $count = max(1, $employeeCount);
        for ($i = 1; $i <= $count; $i++) {
            $this->employees[] = ['id' => 'emp-' . $i, 'is_active' => true];
        }
        $this->employees[] = ['id' => 'emp-inactive', 'is_active' => false];
    }

    public function where(array $conditions): \Core\SupabaseQueryBuilder
    {
        $matches = array_filter($this->employees, function (array $employee) use ($conditions): bool {
            foreach ($conditions as $key => $value) {
                if (($employee[$key] ?? null) !== $value) {
                    return false;
                }
            }
            return true;
        });

        return new class(array_values($matches)) extends \Core\SupabaseQueryBuilder {
            private array $records;
            public function __construct(array $records) { $this->records = $records; }
            public function get(): array { return $this->records; }
            public function first(): ?array { return $this->records[0] ?? null; }
        };
    }
}

class FakeAttendanceModel extends Attendance
{
    private int $employeeCount;

    public function __construct(int $employeeCount = 1)
    {
        $this->employeeCount = max(1, $employeeCount);
    }

    public function getByDateRange(string $employeeId, string $startDate, string $endDate): array
    {
        if (!preg_match('/^emp-\d+$/', $employeeId)) {
            return [];
        }

        return [
            ['date' => '2026-04-01', 'work_hours' => 8, 'status' => 'Present'],
            ['date' => '2026-04-02', 'work_hours' => 10, 'status' => 'Late']
        ];
    }
}

(new PayrollServiceTest())->run();
