<?php

namespace Services;

use Core\Cache;

class IdempotencyService
{
    private Cache $cache;
    private int $processingTtl;
    private int $resultTtl;

    public function __construct(?Cache $cache = null)
    {
        $this->cache = $cache ?? Cache::getInstance();
        $this->processingTtl = (int) env('PAYROLL_IDEMPOTENCY_PROCESSING_TTL', 300);
        $this->resultTtl = (int) env('PAYROLL_IDEMPOTENCY_RESULT_TTL', 86400);
    }

    public function getSuccessfulResult(string $scope, string $idempotencyKey): ?array
    {
        $key = $this->resultKey($scope, $idempotencyKey);
        $result = $this->cache->get($key);
        return is_array($result) ? $result : null;
    }

    public function begin(string $scope, string $idempotencyKey): bool
    {
        $processingKey = $this->processingKey($scope, $idempotencyKey);
        if ($this->cache->has($processingKey)) {
            return false;
        }

        return $this->cache->set($processingKey, ['started_at' => date('Y-m-d H:i:s')], $this->processingTtl);
    }

    public function complete(string $scope, string $idempotencyKey, array $result): void
    {
        $this->cache->set($this->resultKey($scope, $idempotencyKey), $result, $this->resultTtl);
    }

    public function release(string $scope, string $idempotencyKey): void
    {
        $this->cache->delete($this->processingKey($scope, $idempotencyKey));
    }

    private function resultKey(string $scope, string $idempotencyKey): string
    {
        return 'idempotency:result:' . md5($scope . ':' . $idempotencyKey);
    }

    private function processingKey(string $scope, string $idempotencyKey): string
    {
        return 'idempotency:processing:' . md5($scope . ':' . $idempotencyKey);
    }
}
