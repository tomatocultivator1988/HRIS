<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Payroll - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
</head>
<body class="h-full bg-slate-900">
    <div class="flex h-full">
        <?php $currentPage = 'payroll'; include __DIR__ . '/../layouts/admin_sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto p-8 space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-white">Simple Payroll</h2>
                    <p class="text-slate-400 mt-1">Just 2 steps: Create period, then calculate payroll</p>
                </div>
                <a href="<?= base_url('/payroll/manage') ?>" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    View All Periods
                </a>
            </header>

            <!-- Step 1: Create Period -->
            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-white">Step 1: Create Pay Period</h3>
                    <span class="px-3 py-1 bg-blue-600 text-white text-sm rounded-full">Required First</span>
                </div>
                <p class="text-slate-400 text-sm mb-4">Define the month you're paying for. Do this once per month.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">Period Code (e.g., 2026-04)</label>
                        <input id="period-code" type="text" placeholder="2026-04" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">Pay Date</label>
                        <input id="period-pay-date" type="date" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">Period Start</label>
                        <input id="period-start" type="date" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm mb-2">Period End</label>
                        <input id="period-end" type="date" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                    </div>
                </div>
                
                <button id="create-period-btn" class="mt-4 w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                    Create Period
                </button>
                
                <div id="period-result" class="mt-4 hidden">
                    <div class="bg-green-900/30 border border-green-700 rounded-lg p-4">
                        <p class="text-green-400 font-semibold">✓ Period Created!</p>
                        <p class="text-slate-300 text-sm mt-1">Period ID: <span id="created-period-id" class="font-mono"></span></p>
                    </div>
                </div>
            </section>

            <!-- Step 2: Calculate Payroll -->
            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-white">Step 2: Calculate Payroll</h3>
                    <span class="px-3 py-1 bg-emerald-600 text-white text-sm rounded-full">Do After Step 1</span>
                </div>
                <p class="text-slate-400 text-sm mb-4">This will calculate everyone's pay based on attendance.</p>
                
                <div>
                    <label class="block text-slate-300 text-sm mb-2">Period ID (from Step 1)</label>
                    <input id="generate-period-id" type="text" placeholder="Paste Period ID here" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                </div>
                
                <button id="generate-run-btn" class="mt-4 w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold">
                    Calculate Everyone's Payroll
                </button>
                
                <div id="run-result" class="mt-4 hidden">
                    <div class="bg-green-900/30 border border-green-700 rounded-lg p-4">
                        <p class="text-green-400 font-semibold">✓ Payroll Calculated!</p>
                        <p class="text-slate-300 text-sm mt-1">Run ID: <span id="created-run-id" class="font-mono"></span></p>
                        <p class="text-slate-300 text-sm">Total Employees: <span id="employee-count"></span></p>
                        <p class="text-slate-300 text-sm">Total Net Pay: ₱<span id="total-net"></span></p>
                    </div>
                </div>
            </section>

            <!-- Step 3: View Results -->
            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">Step 3: View Results</h3>
                <p class="text-slate-400 text-sm mb-4">See who gets paid what.</p>
                
                <div>
                    <label class="block text-slate-300 text-sm mb-2">Run ID (from Step 2)</label>
                    <input id="view-run-id" type="text" placeholder="Paste Run ID here" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                </div>
                
                <button id="view-run-btn" class="mt-4 w-full px-4 py-3 bg-slate-600 hover:bg-slate-500 text-white rounded-lg font-semibold">
                    View Payroll Details
                </button>
                
                <div id="payroll-details" class="mt-4 hidden">
                    <div class="rounded-lg border border-slate-700 overflow-hidden">
                        <table class="w-full text-xs text-slate-200">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="text-left px-2 py-2">Employee</th>
                                    <th class="text-center px-1 py-2">Days</th>
                                    <th class="text-right px-2 py-2">Basic</th>
                                    <th class="text-right px-1 py-2">OT</th>
                                    <th class="text-right px-2 py-2">Gross</th>
                                    <th class="text-right px-1 py-2">Tax</th>
                                    <th class="text-right px-1 py-2">SSS</th>
                                    <th class="text-right px-1 py-2">PhilH</th>
                                    <th class="text-right px-1 py-2">PagIB</th>
                                    <th class="text-right px-2 py-2">Deduct</th>
                                    <th class="text-right px-2 py-2 font-semibold">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody id="payroll-rows" class="divide-y divide-slate-700"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Error Display -->
            <section id="error-section" class="bg-red-900/30 border border-red-700 rounded-xl p-6 hidden">
                <h3 class="text-xl font-semibold text-red-400 mb-2">Error</h3>
                <pre id="error-message" class="text-sm text-red-300 whitespace-pre-wrap"></pre>
            </section>
        </main>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 w-full max-w-md">
            <div class="p-6 border-b border-slate-700">
                <h3 class="text-xl font-bold text-white" id="confirm-modal-title">Confirm Action</h3>
            </div>
            
            <div class="p-6">
                <p class="text-slate-300" id="confirm-modal-message"></p>
            </div>
            
            <div class="p-6 border-t border-slate-700 flex gap-3">
                <button id="cancel-confirm" class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">Cancel</button>
                <button id="proceed-confirm" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Proceed</button>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 w-full max-w-md p-8">
            <div class="flex flex-col items-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mb-4"></div>
                <p class="text-white text-lg font-semibold" id="loading-message">Processing...</p>
                <p class="text-slate-400 text-sm mt-2">Please wait, this may take a moment</p>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-red-700 w-full max-w-2xl">
            <div class="p-6 border-b border-red-700 bg-red-900/30">
                <h3 class="text-xl font-bold text-red-400">Error</h3>
            </div>
            
            <div class="p-6 max-h-96 overflow-y-auto">
                <pre id="error-modal-message" class="text-sm text-red-300 whitespace-pre-wrap"></pre>
            </div>
            
            <div class="p-6 border-t border-slate-700 flex justify-end">
                <button id="close-error-modal" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">Close</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="success-toast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <p id="success-message"></p>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script>
        const token = localStorage.getItem('hris_token');
        if (!token) {
            window.location.href = window.AppConfig.getBaseUrl('/login');
        }

        const authHeaders = (idempotency = false) => {
            const headers = {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            if (idempotency) {
                headers['Idempotency-Key'] = 'payroll-' + Date.now() + '-' + Math.random().toString(16).slice(2);
            }
            return headers;
        };

        const showError = (message) => {
            document.getElementById('error-modal-message').textContent = message;
            document.getElementById('error-modal').classList.remove('hidden');
        };

        const hideError = () => {
            document.getElementById('error-modal').classList.add('hidden');
        };

        document.getElementById('close-error-modal').addEventListener('click', () => {
            hideError();
        });

        const money = (value) => {
            return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        let confirmCallback = null;

        function showConfirmModal(title, message, callback) {
            document.getElementById('confirm-modal-title').textContent = title;
            document.getElementById('confirm-modal-message').textContent = message;
            confirmCallback = callback;
            document.getElementById('confirm-modal').classList.remove('hidden');
        }

        function showLoadingModal(message) {
            document.getElementById('loading-message').textContent = message;
            document.getElementById('loading-modal').classList.remove('hidden');
        }

        function hideLoadingModal() {
            document.getElementById('loading-modal').classList.add('hidden');
        }

        function showSuccessToast(message) {
            const toast = document.getElementById('success-toast');
            document.getElementById('success-message').textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        document.getElementById('proceed-confirm').addEventListener('click', () => {
            document.getElementById('confirm-modal').classList.add('hidden');
            if (confirmCallback) {
                confirmCallback();
                confirmCallback = null;
            }
        });

        document.getElementById('cancel-confirm').addEventListener('click', () => {
            document.getElementById('confirm-modal').classList.add('hidden');
            confirmCallback = null;
        });

        // Step 1: Create Period
        document.getElementById('create-period-btn').addEventListener('click', async () => {
            hideError();
            
            // Get values and log for debugging
            const code = document.getElementById('period-code').value.trim();
            const start = document.getElementById('period-start').value;
            const end = document.getElementById('period-end').value;
            const payDate = document.getElementById('period-pay-date').value;
            
            console.log('Validation check:', { code, start, end, payDate });

            if (!code || !start || !end || !payDate) {
                showError('Please fill in all fields:\n- Period Code\n- Period Start\n- Period End\n- Pay Date');
                return;
            }

            showConfirmModal(
                'Create Payroll Period',
                `Create payroll period "${code}" from ${start} to ${end}?`,
                async () => {
                    showLoadingModal('Creating payroll period...');
                    
                    try {
                        const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/payroll/periods'), {
                            method: 'POST',
                            headers: authHeaders(true),
                            body: JSON.stringify({ code, start_date: start, end_date: end, pay_date: payDate })
                        });

                        const result = await response.json();
                        hideLoadingModal();

                        if (!response.ok) {
                            let errorMsg = `HTTP ${response.status}: ${response.statusText}\n\n`;
                            if (result.message) {
                                errorMsg += `Message: ${result.message}\n`;
                            }
                            if (result.errors) {
                                errorMsg += `Validation Errors:\n${JSON.stringify(result.errors, null, 2)}`;
                            } else {
                                errorMsg += `Full Response:\n${JSON.stringify(result, null, 2)}`;
                            }
                            showError(errorMsg);
                            return;
                        }

                        const periodId = result.data?.period?.id;
                        document.getElementById('created-period-id').textContent = periodId;
                        document.getElementById('generate-period-id').value = periodId;
                        document.getElementById('period-result').classList.remove('hidden');
                        showSuccessToast('Payroll period created successfully!');
                    } catch (error) {
                        hideLoadingModal();
                        showError('Network error: ' + error.message);
                    }
                }
            );
        });

        // Step 2: Generate Run
        document.getElementById('generate-run-btn').addEventListener('click', async () => {
            hideError();
            const periodId = document.getElementById('generate-period-id').value.trim();

            if (!periodId) {
                showError('Please enter a Period ID from Step 1');
                return;
            }

            showConfirmModal(
                'Calculate Payroll',
                'Calculate payroll for all employees based on their attendance?',
                async () => {
                    showLoadingModal('Calculating payroll for all employees...');
                    
                    try {
                        const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/payroll/runs/generate'), {
                            method: 'POST',
                            headers: authHeaders(true),
                            body: JSON.stringify({ payroll_period_id: periodId, include_overtime: true })
                        });

                        const result = await response.json();
                        hideLoadingModal();

                        if (!response.ok) {
                            showError(JSON.stringify(result, null, 2));
                            return;
                        }

                        const runId = result.data?.run?.id;
                        const employeeCount = result.data?.run?.employee_count || 0;
                        const totalNet = result.data?.run?.total_net || 0;

                        document.getElementById('created-run-id').textContent = runId;
                        document.getElementById('employee-count').textContent = employeeCount;
                        document.getElementById('total-net').textContent = money(totalNet);
                        document.getElementById('view-run-id').value = runId;
                        document.getElementById('run-result').classList.remove('hidden');
                        showSuccessToast(`Payroll calculated for ${employeeCount} employees!`);
                    } catch (error) {
                        hideLoadingModal();
                        showError('Network error: ' + error.message);
                    }
                }
            );
        });

        // Step 3: View Run
        document.getElementById('view-run-btn').addEventListener('click', async () => {
            hideError();
            const runId = document.getElementById('view-run-id').value.trim();

            if (!runId) {
                showError('Please enter a Run ID from Step 2');
                return;
            }

            try {
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/payroll/runs/' + encodeURIComponent(runId)), {
                    method: 'GET',
                    headers: authHeaders()
                });

                const result = await response.json();

                if (!response.ok) {
                    showError(JSON.stringify(result, null, 2));
                    return;
                }

                const lineItems = result.data?.line_items || [];
                const rows = document.getElementById('payroll-rows');
                rows.innerHTML = '';

                if (lineItems.length === 0) {
                    rows.innerHTML = '<tr><td colspan="11" class="px-2 py-3 text-slate-400 text-center">No employees found</td></tr>';
                } else {
                    lineItems.forEach(item => {
                        const employee = item.employee || {};
                        const employeeName = `${employee.first_name || 'Unknown'} ${employee.last_name || ''}`.trim();
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-700';
                        tr.innerHTML = `
                            <td class="px-2 py-2">${employeeName}</td>
                            <td class="px-1 py-2 text-center">${Number(item.attendance_days || 0).toFixed(0)}</td>
                            <td class="px-2 py-2 text-right">${money(item.basic_pay)}</td>
                            <td class="px-1 py-2 text-right">${money(item.overtime_pay)}</td>
                            <td class="px-2 py-2 text-right font-semibold">${money(item.gross_pay)}</td>
                            <td class="px-1 py-2 text-right text-red-400">${money(item.tax_amount)}</td>
                            <td class="px-1 py-2 text-right text-red-400">${money(item.sss_amount)}</td>
                            <td class="px-1 py-2 text-right text-red-400">${money(item.philhealth_amount)}</td>
                            <td class="px-1 py-2 text-right text-red-400">${money(item.pagibig_amount)}</td>
                            <td class="px-2 py-2 text-right text-red-400">${money(item.total_deductions)}</td>
                            <td class="px-2 py-2 text-right font-bold text-green-400">${money(item.net_pay)}</td>
                        `;
                        rows.appendChild(tr);
                    });
                }

                document.getElementById('payroll-details').classList.remove('hidden');
            } catch (error) {
                showError('Network error: ' + error.message);
            }
        });

        document.getElementById('logout-btn').addEventListener('click', () => {
            localStorage.removeItem('hris_token');
            localStorage.removeItem('hris_user');
            window.location.href = window.AppConfig.getBaseUrl('/login');
        });

        // Auto-fill next period based on existing periods
        async function loadNextPeriod() {
            try {
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/payroll/periods'), {
                    headers: authHeaders()
                });
                const result = await response.json();

                let targetDate = new Date();
                
                if (result.success && result.data?.periods && result.data.periods.length > 0) {
                    // Find the latest period
                    const periods = result.data.periods;
                    const latestPeriod = periods[0]; // Already sorted by start_date DESC
                    const latestEndDate = new Date(latestPeriod.end_date);
                    
                    // Suggest next month after the latest period
                    targetDate = new Date(latestEndDate);
                    targetDate.setMonth(targetDate.getMonth() + 1);
                    targetDate.setDate(1); // First day of next month
                }

                // Calculate dates for the target month
                const firstDay = new Date(targetDate.getFullYear(), targetDate.getMonth(), 1);
                const lastDay = new Date(targetDate.getFullYear(), targetDate.getMonth() + 1, 0);
                const payDay = new Date(targetDate.getFullYear(), targetDate.getMonth() + 1, 5);

                document.getElementById('period-code').value = targetDate.getFullYear() + '-' + String(targetDate.getMonth() + 1).padStart(2, '0');
                document.getElementById('period-start').value = firstDay.toISOString().split('T')[0];
                document.getElementById('period-end').value = lastDay.toISOString().split('T')[0];
                document.getElementById('period-pay-date').value = payDay.toISOString().split('T')[0];
            } catch (error) {
                // Fallback to current month if fetch fails
                const today = new Date();
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                const payDay = new Date(today.getFullYear(), today.getMonth() + 1, 5);

                document.getElementById('period-code').value = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
                document.getElementById('period-start').value = firstDay.toISOString().split('T')[0];
                document.getElementById('period-end').value = lastDay.toISOString().split('T')[0];
                document.getElementById('period-pay-date').value = payDay.toISOString().split('T')[0];
            }
        }

        // Load next period on page load
        loadNextPeriod();
    </script>
</body>
</html>
