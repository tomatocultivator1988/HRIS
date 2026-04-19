<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Health Dashboard - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <style>
        .status-healthy { color: #10b981; }
        .status-warning { color: #f59e0b; }
        .status-error { color: #ef4444; }
        .metric-card { transition: all 0.3s ease; }
        .metric-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="h-full bg-slate-900">
    <!-- Main Container -->
    <div class="flex h-full bg-slate-900">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                    HRIS MVP
                </h1>
                <p class="text-xs text-slate-400 mt-1">System Health Monitor</p>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <div class="px-4 py-3 text-slate-400 text-sm">
                    <p class="mb-2">📊 System Monitoring</p>
                    <p class="text-xs text-slate-500">For developers only</p>
                </div>
            </nav>
            
            <!-- Footer Info -->
            <div class="p-4 border-t border-slate-700">
                <div class="text-xs text-slate-400 space-y-1">
                    <p>Last updated:</p>
                    <p id="last-updated" class="text-slate-300 font-mono"><?= date('Y-m-d H:i:s') ?></p>
                    <button onclick="location.reload()" class="mt-2 w-full px-3 py-2 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition-all">
                        🔄 Refresh
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">🔧 System Health Dashboard</h1>
                    <p class="text-slate-400">Real-time monitoring for developers and system administrators</p>
                </div>

                <!-- Overall Status -->
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-slate-300 mb-2">System Status</h2>
                            <p class="text-4xl font-bold status-<?= $metrics['health']['status'] ?>">
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
                    <div class="metric-card bg-slate-800 border border-slate-700 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-slate-300">Errors</h3>
                            <span class="text-3xl">🐛</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Critical:</span>
                                <span class="font-bold text-red-400"><?= $metrics['errors']['critical'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Errors:</span>
                                <span class="font-bold text-orange-400"><?= $metrics['errors']['errors'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Warnings:</span>
                                <span class="font-bold text-yellow-400"><?= $metrics['errors']['warnings'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Performance -->
                    <div class="metric-card bg-slate-800 border border-slate-700 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-slate-300">Performance</h3>
                            <span class="text-3xl">⚡</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Slow Requests:</span>
                                <span class="font-bold text-blue-400"><?= $metrics['performance']['slow_requests'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Threshold:</span>
                                <span class="text-sm text-slate-500"><?= $metrics['performance']['threshold'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Rate Limiting -->
                    <div class="metric-card bg-slate-800 border border-slate-700 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-slate-300">Rate Limiting</h3>
                            <span class="text-3xl">🚦</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Active Clients:</span>
                                <span class="font-bold text-green-400"><?= $metrics['rate_limiting']['active_clients'] ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Blocked IPs:</span>
                                <span class="font-bold text-red-400"><?= $metrics['rate_limiting']['blocked_ips'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Disk Space -->
                    <div class="metric-card bg-slate-800 border border-slate-700 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-slate-300">Disk Space</h3>
                            <span class="text-3xl">💾</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Used:</span>
                                <span class="font-bold <?= $metrics['system']['disk_used_percent'] > 80 ? 'text-red-400' : 'text-green-400' ?>">
                                    <?= $metrics['system']['disk_used_percent'] ?>%
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Free:</span>
                                <span class="text-sm text-slate-500"><?= $metrics['system']['disk_free'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Errors -->
                <?php if (!empty($metrics['errors']['recent'])): ?>
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-slate-300 mb-4">📋 Recent Errors</h2>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <?php foreach ($metrics['errors']['recent'] as $error): ?>
                        <div class="border-l-4 <?= $error['level'] === 'critical' ? 'border-red-500' : ($error['level'] === 'error' ? 'border-orange-500' : 'border-yellow-500') ?> pl-4 py-2 bg-slate-900/50">
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded <?= $error['level'] === 'critical' ? 'bg-red-900/50 text-red-300' : ($error['level'] === 'error' ? 'bg-orange-900/50 text-orange-300' : 'bg-yellow-900/50 text-yellow-300') ?>">
                                <?= strtoupper($error['level']) ?>
                            </span>
                            <p class="text-sm text-slate-400 mt-1 font-mono"><?= htmlspecialchars($error['message']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Slow Requests -->
                <?php if (!empty($metrics['performance']['recent_slow'])): ?>
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-slate-300 mb-4">🐌 Recent Slow Requests</h2>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        <?php foreach ($metrics['performance']['recent_slow'] as $slow): ?>
                        <div class="border-l-4 border-blue-500 pl-4 py-2 bg-slate-900/50">
                            <p class="text-sm text-slate-400 font-mono"><?= htmlspecialchars($slow) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Blocked IPs -->
                <?php if (!empty($metrics['rate_limiting']['blocked_list'])): ?>
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-slate-300 mb-4">🚫 Blocked IPs</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-700">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">IP Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase">Blocked Until</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                <?php foreach ($metrics['rate_limiting']['blocked_list'] as $blocked): ?>
                                <tr class="hover:bg-slate-900/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-slate-300"><?= htmlspecialchars($blocked['ip']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-400"><?= htmlspecialchars($blocked['blocked_until']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- System Information -->
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-slate-300 mb-4">ℹ️ System Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex justify-between border-b border-slate-700 pb-2">
                            <span class="text-slate-400">PHP Version:</span>
                            <span class="font-mono text-slate-300"><?= $metrics['system']['php_version'] ?></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-700 pb-2">
                            <span class="text-slate-400">Server:</span>
                            <span class="font-mono text-sm text-slate-300"><?= htmlspecialchars($metrics['system']['server_software']) ?></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-700 pb-2">
                            <span class="text-slate-400">Memory Limit:</span>
                            <span class="font-mono text-slate-300"><?= $metrics['system']['memory_limit'] ?></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-700 pb-2">
                            <span class="text-slate-400">Max Execution Time:</span>
                            <span class="font-mono text-slate-300"><?= $metrics['system']['max_execution_time'] ?>s</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-700 pb-2">
                            <span class="text-slate-400">Timezone:</span>
                            <span class="font-mono text-slate-300"><?= $metrics['system']['timezone'] ?></span>
                        </div>
                        <div class="flex justify-between border-b border-slate-700 pb-2">
                            <span class="text-slate-400">Current Time:</span>
                            <span class="font-mono text-slate-300"><?= $metrics['system']['current_time'] ?></span>
                        </div>
                    </div>
                </div>

                <!-- Health Checks -->
                <div class="bg-slate-800 border border-slate-700 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-slate-300 mb-4">✅ Health Checks</h2>
                    <div class="space-y-3">
                        <?php foreach ($metrics['health']['checks'] as $check => $status): ?>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 capitalize"><?= str_replace('_', ' ', $check) ?>:</span>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?= is_array($status) ? ($status['status'] === 'ok' ? 'bg-green-900/50 text-green-300' : 'bg-yellow-900/50 text-yellow-300') : ($status === 'ok' ? 'bg-green-900/50 text-green-300' : 'bg-red-900/50 text-red-300') ?>">
                                <?= is_array($status) ? strtoupper($status['status']) : strtoupper($status) ?>
                                <?= is_array($status) && isset($status['usage_percent']) ? ' (' . $status['usage_percent'] . '%)' : '' ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </main>
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
