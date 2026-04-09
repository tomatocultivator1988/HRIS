<?php

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Services\IdempotencyService;

class IdempotencyServiceTest
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        $service = new IdempotencyService();
        $scope = 'payroll:test:scope';
        $key = 'idem-123';

        $this->assertTrue($service->begin($scope, $key) === true, 'Begin first request succeeds');
        $this->assertTrue($service->begin($scope, $key) === false, 'Begin duplicate in-progress request is blocked');

        $service->complete($scope, $key, [
            'message' => 'ok',
            'data' => ['value' => 1]
        ]);

        $stored = $service->getSuccessfulResult($scope, $key);
        $this->assertTrue(is_array($stored), 'Stored result is retrievable');
        $this->assertTrue(($stored['data']['value'] ?? null) === 1, 'Stored payload matches expected value');

        $service->release($scope, $key);
        $this->assertTrue($service->begin($scope, $key) === true, 'Begin works again after release');
        $service->release($scope, $key);

        $this->printSummary();
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

(new IdempotencyServiceTest())->run();
