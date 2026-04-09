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
use Services\CompensationService;

class CompensationController extends Controller
{
    private CompensationService $compensationService;

    public function __construct(\Core\Container $container)
    {
        parent::__construct($container);
        $this->compensationService = $container->resolve(CompensationService::class);
    }

    public function indexView(Request $request): Response
    {
        try {
            ob_start();
            include __DIR__ . '/../Views/compensation/index.php';
            $html = ob_get_clean();
            return new Response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function listPositions(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $result = $this->compensationService->listAllPositions();
            return $this->success($result, 'Position salaries retrieved');
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getPositionSalary(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $position = (string) ($this->getRouteParam('position') ?? '');
            if ($position === '') {
                return $this->error('Position is required', 400);
            }

            $result = $this->compensationService->getPositionSalary(urldecode($position));
            return $this->success($result, 'Position salary retrieved');
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

    public function createPositionSalary(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $data = $this->getJsonData();
            $actor = $this->getAuthenticatedUser();
            $data['created_by'] = $actor['id'] ?? null;

            $result = $this->compensationService->createPositionSalary($data);
            $this->logActivity('CREATE_POSITION_SALARY', ['position' => $data['position'] ?? null]);
            
            return $this->success($result, 'Position salary created successfully');
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

    public function updatePositionSalary(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $positionSalaryId = (string) ($this->getRouteParam('id') ?? '');
            if ($positionSalaryId === '') {
                return $this->error('Position salary ID is required', 400);
            }

            $data = $this->getJsonData();
            $actor = $this->getAuthenticatedUser();
            $data['updated_by'] = $actor['id'] ?? null;

            $result = $this->compensationService->updatePositionSalary($positionSalaryId, $data);
            $this->logActivity('UPDATE_POSITION_SALARY', ['position_salary_id' => $positionSalaryId]);
            
            return $this->success($result, 'Position salary updated successfully');
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

    public function listEmployeesWithCompensation(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $result = $this->compensationService->listEmployeesWithCompensation();
            return $this->success($result, 'Employees with compensation retrieved');
        } catch (AuthenticationException $e) {
            return $this->error($e->getMessage(), 401);
        } catch (AuthorizationException $e) {
            return $this->error($e->getMessage(), 403);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getEmployeeCompensation(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $employeeId = (string) ($this->getRouteParam('id') ?? '');
            if ($employeeId === '') {
                return $this->error('Employee ID is required', 400);
            }

            $result = $this->compensationService->getActiveCompensation($employeeId);
            return $this->success($result, 'Compensation retrieved');
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

    public function createCompensation(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $data = $this->getJsonData();
            $actor = $this->getAuthenticatedUser();
            $data['created_by'] = $actor['id'] ?? null;

            $result = $this->compensationService->createCompensation($data);
            $this->logActivity('CREATE_COMPENSATION', ['employee_id' => $data['employee_id'] ?? null]);
            
            return $this->success($result, 'Compensation created successfully');
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

    public function updateCompensation(Request $request): Response
    {
        try {
            $this->requireRole('admin');
            $compensationId = (string) ($this->getRouteParam('id') ?? '');
            if ($compensationId === '') {
                return $this->error('Compensation ID is required', 400);
            }

            $data = $this->getJsonData();
            $actor = $this->getAuthenticatedUser();
            $data['updated_by'] = $actor['id'] ?? null;

            $result = $this->compensationService->updateCompensation($compensationId, $data);
            $this->logActivity('UPDATE_COMPENSATION', ['compensation_id' => $compensationId]);
            
            return $this->success($result, 'Compensation updated successfully');
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
}
