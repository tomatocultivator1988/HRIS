<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health Dashboard - HRIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-healthy { color: #10b981; }
        .status-warning { color: #f59e0b; }
        .status-error { color: #ef4444; }
        .metric-card { transition: all 0.3s ease; }
        .metric-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🔧 System Health Dashboard</h1>
            <p class="text-gray-600">Real-time monitoring for developers and system administrators</p>
            <p class="text-sm text-gray-500 mt-2">Last updated: <span id="last-updated"><?= date('Y-m-d H:i:s') ?></span></p>
        </div>

        <!-- Overall Status -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">System Status</h2>
                    <p class="text-3xl font-bold status-<?= $metrics['health']['status'] ?>">
                        <?= strtoupper($metrics['health']['status']) ?>
                    </p>
                </div>
                <div class="text-6xl">
                    <?php if ($metrics['health']['status'] === 'healthy'): ?>
                        ✅
                    <?php elseif ($metrics['health']['status'] === 'warning'): ?>
                        ⚠️
                    <?php else: ?>
                        ❌
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Errors -->
            <div class="metric-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Errors</h3>
                    <span class="text-3xl">🐛</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Critical:</span>
                        <span class="font-bold text-red-600"><?= $metrics['errors']['critical'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Errors:</span>
                        <span class="font-bold text-orange-600"><?= $metrics['errors']['errors'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Warnings:</span>
                        <span class="font-bold text-yellow-600"><?= $metrics['errors']['warnings'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Performance -->
            <div class="metric-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Performance</h3>
                    <span class="text-3xl">⚡</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Slow Requests:</span>
                        <span class="font-bold text-blue-600"><?= $metrics['performance']['slow_requests'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Threshold:</span>
                        <span class="text-sm text-gray-500"><?= $metrics['performance']['threshold'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Rate Limiting -->
            <div class="metric-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Rate Limiting</h3>
                    <span class="text-3xl">🚦</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Active Clients:</span>
                        <span class="font-bold text-green-600"><?= $metrics['rate_limiting']['active_clients'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Blocked IPs:</span>
                        <span class="font-bold text-red-600"><?= $metrics['rate_limiting']['blocked_ips'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Disk Space -->
            <div class="metric-card bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-700">Disk Space</h3>
                    <span class="text-3xl">💾</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Used:</span>
                        <span class="font-bold <?= $metrics['system']['disk_used_percent'] > 80 ? 'text-red-600' : 'text-green-600' ?>">
                            <?= $metrics['system']['disk_used_percent'] ?>%
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Free:</span>
                        <span class="text-sm text-gray-500"><?= $metrics['system']['disk_free'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Errors -->
        <?php if (!empty($metrics['errors']['recent'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">📋 Recent Errors</h2>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                <?php foreach ($metrics['errors']['recent'] as $error): ?>
                <div class="border-l-4 <?= $error['level'] === 'critical' ? 'border-red-500' : ($error['level'] === 'error' ? 'border-orange-500' : 'border-yellow-500') ?> pl-4 py-2">
                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded <?= $error['level'] === 'critical' ? 'bg-red-100 text-red-800' : ($error['level'] === 'error' ? 'bg-orange-100 text-orange-800' : 'bg-yellow-100 text-yellow-800') ?>">
                        <?= strtoupper($error['level']) ?>
                    </span>
                    <p class="text-sm text-gray-700 mt-1 font-mono"><?= htmlspecialchars($error['message']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Slow Requests -->
        <?php if (!empty($metrics['performance']['recent_slow'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">🐌 Recent Slow Requests</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php foreach ($metrics['performance']['recent_slow'] as $slow): ?>
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <p class="text-sm text-gray-700 font-mono"><?= htmlspecialchars($slow) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Blocked IPs -->
        <?php if (!empty($metrics['rate_limiting']['blocked_list'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">🚫 Blocked IPs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blocked Until</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($metrics['rate_limiting']['blocked_list'] as $blocked): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900"><?= htmlspecialchars($blocked['ip']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($blocked['blocked_until']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- System Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">ℹ️ System Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">PHP Version:</span>
                    <span class="font-mono text-gray-900"><?= $metrics['system']['php_version'] ?></span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Server:</span>
                    <span class="font-mono text-sm text-gray-900"><?= htmlspecialchars($metrics['system']['server_software']) ?></span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Memory Limit:</span>
                    <span class="font-mono text-gray-900"><?= $metrics['system']['memory_limit'] ?></span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Max Execution Time:</span>
                    <span class="font-mono text-gray-900"><?= $metrics['system']['max_execution_time'] ?>s</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Timezone:</span>
                    <span class="font-mono text-gray-900"><?= $metrics['system']['timezone'] ?></span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Current Time:</span>
                    <span class="font-mono text-gray-900"><?= $metrics['system']['current_time'] ?></span>
                </div>
            </div>
        </div>

        <!-- Health Checks -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">✅ Health Checks</h2>
            <div class="space-y-3">
                <?php foreach ($metrics['health']['checks'] as $check => $status): ?>
                <div class="flex items-center justify-between">
                    <span class="text-gray-700 capitalize"><?= str_replace('_', ' ', $check) ?>:</span>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold <?= is_array($status) ? ($status['status'] === 'ok' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') : ($status === 'ok' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') ?>">
                        <?= is_array($status) ? strtoupper($status['status']) : strtoupper($status) ?>
                        <?= is_array($status) && isset($status['usage_percent']) ? ' (' . $status['usage_percent'] . '%)' : '' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500 text-sm">
            <p>System Health Dashboard | For Developers Only</p>
            <p class="mt-2">
                <a href="/dashboard/admin" class="text-blue-600 hover:underline">← Back to Admin Dashboard</a>
                <span class="mx-2">|</span>
                <a href="/health" class="text-blue-600 hover:underline">Health API</a>
                <span class="mx-2">|</span>
                <button onclick="location.reload()" class="text-blue-600 hover:underline">Refresh</button>
            </p>
        </div>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
        
        // Update last updated time
        setInterval(() => {
            document.getElementById('last-updated').textContent = new Date().toLocaleString();
        }, 1000);
    </script>
</body>
</html>
