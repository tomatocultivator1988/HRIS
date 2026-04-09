<?php

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Controllers\PayrollController;
use Core\Container;
use Core\Router;
use Models\PayrollAdjustment;
use Models\PayrollLineItem;
use Models\PayrollPeriod;
use Models\PayrollRun;
use Services\PayrollService;

class PayrollApiIntegrationTest
{
    private Container $container;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    public function run(): void
    {
        $this->testContainerRegistrations();
        $this->testControllerMethods();
        $this->testRoutesRegistered();
        $this->testIdempotencyCoverageOnMutationEndpoints();
        $this->printSummary();
    }

    private function testContainerRegistrations(): void
    {
        $classes = [
            PayrollPeriod::class,
            PayrollRun::class,
            PayrollLineItem::class,
            PayrollAdjustment::class,
            PayrollService::class,
            PayrollController::class
        ];

        foreach ($classes as $class) {
            try {
                $instance = $this->container->resolve($class);
                $this->assertTrue(is_object($instance), "Container resolves {$class}");
            } catch (\Exception $e) {
                $this->assertTrue(false, "Container resolves {$class}");
            }
        }
    }

    private function testControllerMethods(): void
    {
        $controller = $this->container->resolve(PayrollController::class);
        $methods = [
            'indexView',
            'payslipsView',
            'createPeriod',
            'listPeriods',
            'generateRun',
            'getRun',
            'recomputeRun',
            'finalizeRun',
            'approveRun',
            'markRunPaid',
            'reverseRun',
            'updateLineItem',
            'employeePayslips',
            'employeePayslipDetail'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(method_exists($controller, $method), "PayrollController has {$method}");
        }
    }

    private function testRoutesRegistered(): void
    {
        $router = new Router();
        $routeDefinition = require dirname(__DIR__, 2) . '/config/routes.php';
        $routeDefinition($router);

        $routes = $router->getRoutes();
        $needles = [
            ['GET', '/payroll'],
            ['GET', '/payslips'],
            ['POST', '/api/payroll/periods'],
            ['GET', '/api/payroll/periods'],
            ['POST', '/api/payroll/runs/generate'],
            ['GET', '/api/payroll/runs/{id}'],
            ['POST', '/api/payroll/runs/{id}/recompute'],
            ['PUT', '/api/payroll/runs/{id}/finalize'],
            ['PUT', '/api/payroll/runs/{id}/approve'],
            ['PUT', '/api/payroll/runs/{id}/pay'],
            ['POST', '/api/payroll/runs/{id}/reverse'],
            ['PUT', '/api/payroll/line-items/{id}'],
            ['GET', '/api/payroll/payslips'],
            ['GET', '/api/payroll/payslips/{id}']
        ];

        foreach ($needles as $needle) {
            [$method, $pattern] = $needle;
            $found = false;

            foreach ($routes as $route) {
                if (($route['method'] ?? '') === $method && ($route['pattern'] ?? '') === $pattern) {
                    $found = true;
                    break;
                }
            }

            $this->assertTrue($found, "Route registered {$method} {$pattern}");
        }
    }

    private function testIdempotencyCoverageOnMutationEndpoints(): void
    {
        $controllerFile = dirname(__DIR__, 2) . '/src/Controllers/PayrollController.php';
        $content = file_get_contents($controllerFile);
        if (!is_string($content) || $content === '') {
            $this->assertTrue(false, 'PayrollController source is readable');
            return;
        }

        $this->assertMethodContains($content, 'createPeriod', '$this->requireIdempotencyKey($request)', 'createPeriod requires Idempotency-Key');
        $this->assertMethodContains($content, 'createPeriod', '$this->idempotencyService->begin(', 'createPeriod starts idempotent processing lock');
        $this->assertMethodContains($content, 'recomputeRun', '$this->requireIdempotencyKey($request)', 'recomputeRun requires Idempotency-Key');
        $this->assertMethodContains($content, 'recomputeRun', '$this->idempotencyService->begin(', 'recomputeRun starts idempotent processing lock');
        $this->assertMethodContains($content, 'approveRun', '$this->requireIdempotencyKey($request)', 'approveRun requires Idempotency-Key');
        $this->assertMethodContains($content, 'approveRun', '$this->idempotencyService->begin(', 'approveRun starts idempotent processing lock');
        $this->assertMethodContains($content, 'reverseRun', '$this->requireIdempotencyKey($request)', 'reverseRun requires Idempotency-Key');
        $this->assertMethodContains($content, 'reverseRun', '$this->idempotencyService->begin(', 'reverseRun starts idempotent processing lock');
        $this->assertMethodContains($content, 'updateLineItem', '$this->requireIdempotencyKey($request)', 'updateLineItem requires Idempotency-Key');
        $this->assertMethodContains($content, 'updateLineItem', '$this->idempotencyService->begin(', 'updateLineItem starts idempotent processing lock');
    }

    private function assertMethodContains(string $content, string $methodName, string $needle, string $message): void
    {
        $pattern = '/public function\s+' . preg_quote($methodName, '/') . '\s*\(Request \$request\): Response\s*\{([\s\S]*?)\n    \}/';
        $matches = [];
        if (!preg_match($pattern, $content, $matches)) {
            $this->assertTrue(false, "PayrollController has parsable {$methodName} method body");
            return;
        }

        $methodBody = (string) ($matches[1] ?? '');
        $this->assertTrue(strpos($methodBody, $needle) !== false, $message);
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

(new PayrollApiIntegrationTest())->run();
