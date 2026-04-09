<?php

namespace Controllers;

use Core\AuthenticationException;
use Core\AuthorizationException;
use Core\BusinessLogicException;
use Core\Controller;
use Core\NotFoundException;
use Core\Request;
use Core\Response;
use Core\ValidationException;
use Services\IdempotencyService;
use Services\PayrollService;

class PayrollController extends Controller
{
    private PayrollService $payrollService;
    private IdempotencyService $idempotencyService;

    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->payrollService = $container->resolve(PayrollService::class);
        $this->idempotencyService = $container->resolve(IdempotencyService::class);
    }

    public function indexView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/payroll/index.php';
            $html = ob_get_clean();
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function payslipsView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/payroll/payslips.php';
            $html = ob_get_clean();
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function simpleView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/payroll/simple.php';
            $html = ob_get_clean();
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function manageView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/payroll/manage.php';
            $html = ob_get_clean();
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function createPeriod(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $data = $this->getJsonData();
            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:create-period:' . (string) ($data['code'] ?? '') . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll period created'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            try {
                $period = $this->payrollService->createPayrollPeriod($data);
                $this->logActivity('CREATE_PAYROLL_PERIOD', ['payroll_period_id' => $period['id'] ?? null]);
                $payload = ['period' => $period];
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll period created',
                    'data' => $payload
                ]);

                return $this->success($payload, 'Payroll period created');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function listPeriods(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $result = $this->payrollService->listPayrollPeriods([
                'status' => $this->getQueryParam('status'),
                'year' => $this->getQueryParam('year')
            ]);

            return $this->success($result, 'Payroll periods retrieved');
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function generateRun(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $data = $this->getJsonData();
            $periodId = (string) ($data['payroll_period_id'] ?? '');
            if ($periodId === '') {
                return $this->validationError(['payroll_period_id' => 'Payroll period ID is required']);
            }

            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:generate:' . $periodId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll run generated'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            $data['actor_id'] = $actor['id'] ?? null;
            try {
                $result = $this->payrollService->generatePayrollRun($periodId, $data);
                $this->logActivity('GENERATE_PAYROLL_RUN', [
                    'payroll_period_id' => $periodId,
                    'payroll_run_id' => $result['run']['id'] ?? null
                ]);
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll run generated',
                    'data' => $result
                ]);

                return $this->success($result, 'Payroll run generated');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getPeriodRuns(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $periodId = (string) ($this->getRouteParam('id') ?? '');
            if ($periodId === '') {
                return $this->error('Payroll period ID is required', 400);
            }

            $result = $this->payrollService->getPeriodRuns($periodId);
            return $this->success($result, 'Payroll runs retrieved');
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getRun(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $runId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($runId === '') {
                return $this->error('Payroll run ID is required', 400);
            }

            $result = $this->payrollService->getRunSummary($runId);
            return $this->success($result, 'Payroll run summary retrieved');
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function recomputeRun(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $runId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($runId === '') {
                return $this->error('Payroll run ID is required', 400);
            }

            $data = $this->getJsonData();
            $employeeId = (string) ($data['employee_id'] ?? $this->getQueryParam('employee_id', ''));
            if ($employeeId === '') {
                return $this->validationError(['employee_id' => 'Employee ID is required']);
            }

            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:recompute:' . $runId . ':' . $employeeId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll line recomputed'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            try {
                $lineItem = $this->payrollService->recomputePayrollLine($runId, $employeeId);
                $this->logActivity('RECOMPUTE_PAYROLL_RUN_LINE', [
                    'payroll_run_id' => $runId,
                    'employee_id' => $employeeId
                ]);
                $payload = ['line_item' => $lineItem];
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll line recomputed',
                    'data' => $payload
                ]);

                return $this->success($payload, 'Payroll line recomputed');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function approveRun(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $runId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($runId === '') {
                return $this->error('Payroll run ID is required', 400);
            }

            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:approve:' . $runId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll run approved'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            try {
                $run = $this->payrollService->approvePayrollRun($runId, (string) ($actor['id'] ?? ''));
                $this->logActivity('APPROVE_PAYROLL_RUN', ['payroll_run_id' => $runId]);
                $payload = ['run' => $run];
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll run approved',
                    'data' => $payload
                ]);

                return $this->success($payload, 'Payroll run approved');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function reverseRun(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $runId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($runId === '') {
                return $this->error('Payroll run ID is required', 400);
            }

            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:reverse:' . $runId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll run reversed'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            $data = $this->getJsonData();
            try {
                $run = $this->payrollService->reversePayrollRun($runId, $data, (string) ($actor['id'] ?? ''));
                $this->logActivity('REVERSE_PAYROLL_RUN', ['payroll_run_id' => $runId]);
                $payload = ['run' => $run];
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll run reversed',
                    'data' => $payload
                ]);

                return $this->success($payload, 'Payroll run reversed');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function finalizeRun(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $runId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($runId === '') {
                return $this->error('Payroll run ID is required', 400);
            }

            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:finalize:' . $runId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll run finalized'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            try {
                $run = $this->payrollService->finalizePayrollRun($runId, (string) ($actor['id'] ?? ''));
                $this->logActivity('FINALIZE_PAYROLL_RUN', ['payroll_run_id' => $runId]);
                $payload = ['run' => $run];
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll run finalized',
                    'data' => $payload
                ]);

                return $this->success($payload, 'Payroll run finalized');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function markRunPaid(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $runId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($runId === '') {
                return $this->error('Payroll run ID is required', 400);
            }

            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:pay:' . $runId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll run marked as paid'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            $data = $this->getJsonData();
            try {
                $run = $this->payrollService->markPayrollAsPaid($runId, $data, (string) ($actor['id'] ?? ''));
                $this->logActivity('MARK_PAYROLL_RUN_PAID', ['payroll_run_id' => $runId]);
                $payload = ['run' => $run];
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll run marked as paid',
                    'data' => $payload
                ]);

                return $this->success($payload, 'Payroll run marked as paid');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function updateLineItem(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $lineItemId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($lineItemId === '') {
                return $this->error('Payroll line item ID is required', 400);
            }

            $data = $this->getJsonData();
            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:update-line-item:' . $lineItemId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Payroll line item updated'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            $data['actor_id'] = $actor['id'] ?? null;
            try {
                $result = $this->payrollService->applyManualAdjustment($lineItemId, $data);
                $this->logActivity('APPLY_PAYROLL_ADJUSTMENT', ['payroll_line_item_id' => $lineItemId]);
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Payroll line item updated',
                    'data' => $result
                ]);

                return $this->success($result, 'Payroll line item updated');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (ValidationException $e) {
            return $this->validationError($e->getErrors(), $e->getMessage());
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function markLineItemPaid(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $lineItemId = (string) ($this->getRouteParam('id') ?? '');
            if ($lineItemId === '') {
                return $this->error('Payroll line item ID is required', 400);
            }

            $data = $this->getJsonData();
            $actor = $this->getAuthenticatedUser();
            $idempotencyKey = $this->requireIdempotencyKey($request);
            if ($idempotencyKey === null) {
                return $this->validationError(['idempotency_key' => 'Idempotency-Key header is required']);
            }

            $scope = 'payroll:pay-line:' . $lineItemId . ':' . (string) ($actor['id'] ?? 'unknown');
            $cached = $this->idempotencyService->getSuccessfulResult($scope, $idempotencyKey);
            if ($cached) {
                return $this->success($cached['data'] ?? [], (string) ($cached['message'] ?? 'Employee payment recorded'));
            }
            if (!$this->idempotencyService->begin($scope, $idempotencyKey)) {
                return $this->error('A request with this Idempotency-Key is still being processed', 409);
            }

            try {
                $result = $this->payrollService->markLineItemPaid($lineItemId, $data);
                $this->logActivity('MARK_LINE_ITEM_PAID', ['payroll_line_item_id' => $lineItemId]);
                $payload = ['line_item' => $result];
                $this->idempotencyService->complete($scope, $idempotencyKey, [
                    'message' => 'Employee payment recorded',
                    'data' => $payload
                ]);

                return $this->success($payload, 'Employee payment recorded');
            } finally {
                $this->idempotencyService->release($scope, $idempotencyKey);
            }
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function employeePayslips(Request $request): Response
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthenticatedUser();
            $employeeId = (string) ($user['id'] ?? '');
            if (($user['role'] ?? '') === 'admin' && !empty($this->getQueryParam('employee_id'))) {
                $employeeId = (string) $this->getQueryParam('employee_id');
            }

            $result = $this->payrollService->getEmployeePayslips($employeeId, [
                'year' => $this->getQueryParam('year'),
                'month' => $this->getQueryParam('month'),
                'limit' => $this->getQueryParam('limit', 20),
                'offset' => $this->getQueryParam('offset', 0)
            ]);

            return $this->success($result, 'Payslips retrieved');
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function employeePayslipDetail(Request $request): Response
    {
        try {
            $this->requireAuth();
            $lineItemId = (string) ($this->getRouteParam('id') ?? $this->getQueryParam('id', ''));
            if ($lineItemId === '') {
                return $this->error('Payslip ID is required', 400);
            }

            $user = $this->getAuthenticatedUser();
            if (($user['role'] ?? '') === 'admin' && !empty($this->getQueryParam('employee_id'))) {
                $employeeId = (string) $this->getQueryParam('employee_id');
            } else {
                $employeeId = (string) ($user['id'] ?? '');
            }

            $result = $this->payrollService->getEmployeePayslipDetail($employeeId, $lineItemId);
            return $this->success($result, 'Payslip details retrieved');
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (NotFoundException $e) {
            return $this->error($e->getMessage(), 404);
        } catch (BusinessLogicException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    private function requireIdempotencyKey(Request $request): ?string
    {
        $value = $request->getHeader('Idempotency-Key');
        if (!is_string($value)) {
            return null;
        }

        $key = trim($value);
        return $key === '' ? null : $key;
    }
}
