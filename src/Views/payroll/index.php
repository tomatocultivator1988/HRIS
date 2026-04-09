<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <div class="flex h-full bg-slate-900">
        <aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">HRIS MVP</h1>
                <p class="text-xs text-slate-400 mt-1">Human Resources System</p>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="<?= base_url('/dashboard/admin') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Dashboard</a>
                <a href="<?= base_url('/employees') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Employees</a>
                <a href="<?= base_url('/attendance') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Attendance</a>
                <a href="<?= base_url('/leave') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Leave Requests</a>
                <a href="<?= base_url('/reports') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Reports</a>
                <a href="<?= base_url('/payroll') ?>" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg shadow-blue-900/50">Payroll</a>
            </nav>
            <div class="p-4 border-t border-slate-700">
                <button id="logout-btn" class="w-full px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">Logout</button>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto bg-slate-900 p-8 space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-white">Payroll Management</h2>
                    <p class="text-slate-400 mt-1">Create periods, generate runs, and execute payroll lifecycle actions.</p>
                </div>
                <button id="refresh-runs-btn" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">Refresh Runs</button>
            </header>

            <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Create Payroll Period</h3>
                    <div class="space-y-3">
                        <input id="period-code" type="text" placeholder="Code (e.g., 2026-05)" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                        <input id="period-start" type="date" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                        <input id="period-end" type="date" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                        <input id="period-pay-date" type="date" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                        <button id="create-period-btn" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Create Period</button>
                    </div>
                </div>

                <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                    <h3 class="text-xl font-semibold text-white mb-4">Generate Payroll Run</h3>
                    <div class="space-y-3">
                        <input id="generate-period-id" type="text" placeholder="Payroll Period ID" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                        <label class="flex items-center space-x-2 text-slate-300">
                            <input id="include-overtime" type="checkbox" checked>
                            <span>Include overtime</span>
                        </label>
                        <input id="employee-ids" type="text" placeholder="Employee IDs (comma-separated, optional)" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                        <button id="generate-run-btn" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">Generate Run</button>
                    </div>
                </div>
            </section>

            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Run Actions</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <input id="run-id" type="text" placeholder="Payroll Run ID" class="px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    <input id="line-item-id" type="text" placeholder="Line Item ID (for adjustment)" class="px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    <input id="recompute-employee-id" type="text" placeholder="Employee ID (for recompute)" class="px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    <input id="reverse-reason" type="text" placeholder="Reverse reason" class="px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 mt-4">
                    <button id="get-run-btn" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">Get Run</button>
                    <button id="recompute-btn" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Recompute</button>
                    <button id="finalize-btn" class="px-3 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg">Finalize</button>
                    <button id="approve-btn" class="px-3 py-2 bg-violet-600 hover:bg-violet-700 text-white rounded-lg">Approve</button>
                    <button id="pay-btn" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Pay</button>
                    <button id="reverse-btn" class="px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg">Reverse</button>
                    <button id="list-periods-btn" class="px-3 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg">Periods</button>
                    <button id="adjust-btn" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg">Adjust</button>
                </div>
            </section>

            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">API Output</h3>
                <pre id="output" class="text-sm text-slate-200 bg-slate-900 border border-slate-700 rounded-lg p-4 overflow-auto min-h-[260px]"></pre>
            </section>
        </main>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script>
        const token = localStorage.getItem('hris_token');
        if (!token) {
            window.location.href = window.AppConfig.getBaseUrl('/login');
        }

        const output = document.getElementById('output');
        const authHeaders = (idempotency = false) => {
            const headers = {
                'Authorization': 'Bearer ' + (localStorage.getItem('hris_token') || ''),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            if (idempotency) {
                headers['Idempotency-Key'] = 'payroll-' + Date.now() + '-' + Math.random().toString(16).slice(2);
            }
            return headers;
        };

        const render = (title, data) => {
            output.textContent = title + '\n\n' + JSON.stringify(data, null, 2);
        };

        const api = async (method, path, body = null, idempotency = false) => {
            const response = await fetch(window.AppConfig.getApiUrl(path), {
                method,
                headers: authHeaders(idempotency),
                body: body === null ? null : JSON.stringify(body)
            });
            const data = await response.json();
            return { status: response.status, data };
        };

        document.getElementById('logout-btn').addEventListener('click', () => {
            localStorage.removeItem('hris_token');
            localStorage.removeItem('hris_user');
            window.location.href = window.AppConfig.getBaseUrl('/login');
        });

        document.getElementById('create-period-btn').addEventListener('click', async () => {
            const payload = {
                code: document.getElementById('period-code').value.trim(),
                start_date: document.getElementById('period-start').value,
                end_date: document.getElementById('period-end').value,
                pay_date: document.getElementById('period-pay-date').value
            };
            const result = await api('POST', '/payroll/periods', payload, true);
            render('Create Period', result);
        });

        document.getElementById('generate-run-btn').addEventListener('click', async () => {
            const employeeIdsRaw = document.getElementById('employee-ids').value.trim();
            const payload = {
                payroll_period_id: document.getElementById('generate-period-id').value.trim(),
                include_overtime: document.getElementById('include-overtime').checked
            };
            if (employeeIdsRaw !== '') {
                payload.employee_ids = employeeIdsRaw.split(',').map(v => v.trim()).filter(Boolean);
            }
            const result = await api('POST', '/payroll/runs/generate', payload, true);
            render('Generate Run', result);
        });

        document.getElementById('get-run-btn').addEventListener('click', async () => {
            const runId = document.getElementById('run-id').value.trim();
            const result = await api('GET', '/payroll/runs/' + encodeURIComponent(runId));
            render('Get Run', result);
        });

        document.getElementById('recompute-btn').addEventListener('click', async () => {
            const runId = document.getElementById('run-id').value.trim();
            const employeeId = document.getElementById('recompute-employee-id').value.trim();
            const result = await api('POST', '/payroll/runs/' + encodeURIComponent(runId) + '/recompute', { employee_id: employeeId }, true);
            render('Recompute', result);
        });

        document.getElementById('finalize-btn').addEventListener('click', async () => {
            const runId = document.getElementById('run-id').value.trim();
            const result = await api('PUT', '/payroll/runs/' + encodeURIComponent(runId) + '/finalize', {}, true);
            render('Finalize', result);
        });

        document.getElementById('approve-btn').addEventListener('click', async () => {
            const runId = document.getElementById('run-id').value.trim();
            const result = await api('PUT', '/payroll/runs/' + encodeURIComponent(runId) + '/approve', {}, true);
            render('Approve', result);
        });

        document.getElementById('pay-btn').addEventListener('click', async () => {
            const runId = document.getElementById('run-id').value.trim();
            const result = await api('PUT', '/payroll/runs/' + encodeURIComponent(runId) + '/pay', {
                payment_date: new Date().toISOString().slice(0, 10),
                payment_reference: 'BATCH-' + Date.now()
            }, true);
            render('Pay', result);
        });

        document.getElementById('reverse-btn').addEventListener('click', async () => {
            const runId = document.getElementById('run-id').value.trim();
            const reason = document.getElementById('reverse-reason').value.trim();
            const result = await api('POST', '/payroll/runs/' + encodeURIComponent(runId) + '/reverse', { reason }, true);
            render('Reverse', result);
        });

        document.getElementById('list-periods-btn').addEventListener('click', async () => {
            const result = await api('GET', '/payroll/periods');
            render('List Periods', result);
        });

        document.getElementById('adjust-btn').addEventListener('click', async () => {
            const lineItemId = document.getElementById('line-item-id').value.trim();
            const result = await api('PUT', '/payroll/line-items/' + encodeURIComponent(lineItemId), {
                adjustment_type: 'Deduction',
                category: 'Manual',
                amount: 100,
                reason: 'UI quick adjustment'
            }, true);
            render('Adjust Line Item', result);
        });

        document.getElementById('refresh-runs-btn').addEventListener('click', async () => {
            const runId = document.getElementById('run-id').value.trim();
            if (runId === '') {
                const result = await api('GET', '/payroll/periods');
                render('Refresh', result);
                return;
            }
            const result = await api('GET', '/payroll/runs/' + encodeURIComponent(runId));
            render('Refresh', result);
        });
    </script>
</body>
</html>
