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
        <?php $currentPage = 'payroll'; include __DIR__ . '/../layouts/admin_sidebar.php'; ?>

        <main class="flex-1 overflow-y-auto bg-slate-900 p-8 space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-white">Payroll Management</h2>
                    <p class="text-slate-400 mt-1">View and manage all payroll periods and runs</p>
                </div>
                <a href="<?= base_url('/payroll/simple') ?>" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">+ New Payroll</a>
            </header>

            <!-- Payroll Periods List -->
            <section class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-white">Payroll Periods</h3>
                    <button id="refresh-btn" class="px-3 py-1 bg-slate-700 hover:bg-slate-600 text-white rounded text-sm">Refresh</button>
                </div>
                
                <div id="periods-list" class="space-y-3">
                    <div class="text-center py-8 text-slate-400">Loading...</div>
                </div>
            </section>
        </main>
    </div>

    <!-- View Details Modal -->
    <div id="details-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-2xl font-bold text-white">Payroll Details</h3>
                <button id="close-modal" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6">
                <div id="modal-content"></div>
            </div>
        </div>
    </div>

    <!-- Mark as Paid Modal -->
    <div id="payment-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 w-full max-w-md">
            <div class="p-6 border-b border-slate-700">
                <h3 class="text-xl font-bold text-white" id="payment-modal-title">Mark as Paid</h3>
            </div>
            
            <div class="p-6 space-y-4">
                <p class="text-slate-300 text-sm" id="payment-modal-message"></p>
                <div>
                    <label class="block text-slate-300 text-sm mb-2">Payment Reference</label>
                    <input id="payment-reference" type="text" placeholder="e.g., CHECK-001, GCASH-123" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm mb-2">Remarks (optional)</label>
                    <input id="payment-remarks" type="text" placeholder="Additional notes" class="w-full px-3 py-2 rounded-lg bg-slate-700 text-white border border-slate-600">
                </div>
            </div>
            
            <div class="p-6 border-t border-slate-700 flex gap-3">
                <button id="cancel-payment" class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg">Cancel</button>
                <button id="confirm-payment" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Confirm Payment</button>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="success-toast" class="hidden fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <p id="success-message"></p>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
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

        const money = (value) => Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        let currentRunId = null;
        let currentPeriodCode = null;
        let currentPaymentAction = null; // 'single' or 'all'
        let currentLineItemId = null;
        let currentEmployeeName = null;

        function showSuccessToast(message) {
            const toast = document.getElementById('success-toast');
            document.getElementById('success-message').textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        async function loadPeriods() {
            try {
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl('/payroll/periods'), {
                    headers: authHeaders()
                });
                const result = await response.json();

                if (!result.success || !result.data?.periods) {
                    document.getElementById('periods-list').innerHTML = '<div class="text-center py-8 text-slate-400">No payroll periods found</div>';
                    return;
                }

                const periods = result.data.periods;
                if (periods.length === 0) {
                    document.getElementById('periods-list').innerHTML = '<div class="text-center py-8 text-slate-400">No payroll periods yet. Create your first one!</div>';
                    return;
                }

                renderPeriods(periods);
            } catch (error) {
                document.getElementById('periods-list').innerHTML = '<div class="text-center py-8 text-red-400">Error loading periods</div>';
            }
        }

        function getStatusBadge(status) {
            const badges = {
                'Draft': 'bg-slate-600 text-slate-200',
                'Computed': 'bg-blue-600 text-white',
                'Finalized': 'bg-purple-600 text-white',
                'Paid': 'bg-green-600 text-white'
            };
            return badges[status] || 'bg-slate-600 text-slate-200';
        }

        function renderPeriods(periods) {
            const html = periods.map(period => `
                <div class="bg-slate-700 rounded-lg p-4 border border-slate-600">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h4 class="text-lg font-semibold text-white">${period.code}</h4>
                                <span class="px-2 py-1 rounded text-xs ${getStatusBadge(period.status)}">${period.status}</span>
                            </div>
                            <p class="text-sm text-slate-300">${period.start_date} to ${period.end_date}</p>
                            <p class="text-xs text-slate-400">Pay Date: ${period.pay_date}</p>
                        </div>
                        <button onclick="viewPeriodRuns('${period.id}', '${period.code}')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                            View Runs
                        </button>
                    </div>
                </div>
            `).join('');
            
            document.getElementById('periods-list').innerHTML = html;
        }

        async function viewPeriodRuns(periodId, periodCode) {
            try {
                document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-slate-400">Loading...</div>';
                document.getElementById('details-modal').classList.remove('hidden');
                
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl(`/payroll/periods/${periodId}/runs`), {
                    headers: authHeaders()
                });
                const result = await response.json();

                if (!result.success || !result.data?.runs) {
                    document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-red-400">No runs found for this period</div>';
                    return;
                }

                const runs = result.data.runs;
                if (runs.length === 0) {
                    document.getElementById('modal-content').innerHTML = `
                        <div class="bg-slate-700 rounded-lg p-6 text-center">
                            <p class="text-slate-300 mb-4">No payroll runs generated yet for ${periodCode}</p>
                            <a href="${window.AppConfig.getBaseUrl('/payroll/simple')}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg inline-block">
                                Generate Payroll
                            </a>
                        </div>
                    `;
                    return;
                }

                // Show list of runs
                const runsHtml = runs.map(run => `
                    <div class="bg-slate-700 rounded-lg p-4 border border-slate-600">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h5 class="text-lg font-semibold text-white">Run #${run.run_number}</h5>
                                    <span class="px-2 py-1 rounded text-xs ${getStatusBadge(run.status)}">${run.status}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <p class="text-slate-400">Employees</p>
                                        <p class="text-white font-semibold">${run.employee_count}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400">Total Gross</p>
                                        <p class="text-white font-semibold">₱${money(run.total_gross)}</p>
                                    </div>
                                    <div>
                                        <p class="text-slate-400">Total Net</p>
                                        <p class="text-green-400 font-semibold">₱${money(run.total_net)}</p>
                                    </div>
                                </div>
                            </div>
                            <button onclick="viewRunDetails('${run.id}', '${periodCode}')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm ml-4">
                                View Details
                            </button>
                        </div>
                    </div>
                `).join('');

                document.getElementById('modal-content').innerHTML = `
                    <div class="space-y-4">
                        <h4 class="text-xl font-semibold text-white">Payroll Runs for ${periodCode}</h4>
                        ${runsHtml}
                    </div>
                `;
            } catch (error) {
                document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-red-400">Error loading runs</div>';
            }
        }

        async function viewRunDetails(runId, periodCode) {
            currentRunId = runId;
            currentPeriodCode = periodCode;
            
            try {
                document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-slate-400">Loading details...</div>';
                
                const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl(`/payroll/runs/${runId}`), {
                    headers: authHeaders()
                });
                const result = await response.json();

                if (result.success && result.data) {
                    showRunDetails(result.data, periodCode);
                } else {
                    document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-red-400">Run not found: ' + (result.message || 'Invalid Run ID') + '</div>';
                }
            } catch (error) {
                document.getElementById('modal-content').innerHTML = '<div class="text-center py-8 text-red-400">Error loading payroll run: ' + error.message + '</div>';
            }
        }

        function showRunDetails(data, periodCode) {
            const run = data.run;
            const lineItems = data.line_items || [];

            const tableRows = lineItems.map(item => {
                const emp = item.employee || {};
                const name = `${emp.first_name || ''} ${emp.last_name || ''}`.trim();
                const isPaid = (item.payment_status || 'Unpaid') === 'Paid';
                const paymentBadge = isPaid 
                    ? '<span class="px-2 py-1 bg-green-600 text-white text-xs rounded">Paid</span>'
                    : '<span class="px-2 py-1 bg-yellow-600 text-white text-xs rounded">Unpaid</span>';
                
                const payButton = isPaid
                    ? ''
                    : `<button onclick="markEmployeePaid('${item.id}', '${name}')" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded">Mark Paid</button>`;
                
                return `
                    <tr class="hover:bg-slate-700">
                        <td class="px-2 py-2">${name}</td>
                        <td class="px-1 py-2 text-center">${Number(item.attendance_days || 0).toFixed(0)}</td>
                        <td class="px-2 py-2 text-right">${money(item.basic_pay)}</td>
                        <td class="px-1 py-2 text-right">${money(item.overtime_pay)}</td>
                        <td class="px-2 py-2 text-right font-semibold">${money(item.gross_pay)}</td>
                        <td class="px-2 py-2 text-right text-red-400">${money(item.total_deductions)}</td>
                        <td class="px-2 py-2 text-right font-bold text-green-400">${money(item.net_pay)}</td>
                        <td class="px-2 py-2 text-center">${paymentBadge}</td>
                        <td class="px-2 py-2 text-center">${payButton}</td>
                    </tr>
                `;
            }).join('');

            const paidCount = lineItems.filter(item => (item.payment_status || 'Unpaid') === 'Paid').length;
            const totalCount = lineItems.length;

            const content = `
                <div class="space-y-6">
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-3">Period: ${periodCode}</h4>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                            <div>
                                <p class="text-slate-400">Status</p>
                                <p class="text-white font-semibold">${run.status}</p>
                            </div>
                            <div>
                                <p class="text-slate-400">Employees</p>
                                <p class="text-white font-semibold">${run.employee_count}</p>
                            </div>
                            <div>
                                <p class="text-slate-400">Paid</p>
                                <p class="text-green-400 font-semibold">${paidCount} / ${totalCount}</p>
                            </div>
                            <div>
                                <p class="text-slate-400">Total Gross</p>
                                <p class="text-white font-semibold">₱${money(run.total_gross)}</p>
                            </div>
                            <div>
                                <p class="text-slate-400">Total Net</p>
                                <p class="text-green-400 font-semibold">₱${money(run.total_net)}</p>
                            </div>
                        </div>
                    </div>

                    ${paidCount < totalCount ? `
                        <div class="flex gap-3">
                            <button onclick="markAllPaid('${run.id}')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                                Mark All as Paid
                            </button>
                        </div>
                    ` : `
                        <div class="bg-green-900/30 border border-green-700 rounded-lg p-4">
                            <p class="text-green-400 font-semibold">✓ All Employees Paid</p>
                        </div>
                    `}

                    <div class="rounded-lg border border-slate-700 overflow-hidden">
                        <table class="w-full text-xs text-slate-200">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="text-left px-2 py-2">Employee</th>
                                    <th class="text-center px-1 py-2">Days</th>
                                    <th class="text-right px-2 py-2">Basic</th>
                                    <th class="text-right px-1 py-2">OT</th>
                                    <th class="text-right px-2 py-2">Gross</th>
                                    <th class="text-right px-2 py-2">Deduct</th>
                                    <th class="text-right px-2 py-2">Net Pay</th>
                                    <th class="text-center px-2 py-2">Status</th>
                                    <th class="text-center px-2 py-2">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700">
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;

            document.getElementById('modal-content').innerHTML = content;
            document.getElementById('details-modal').classList.remove('hidden');
        }

        async function markEmployeePaid(lineItemId, employeeName) {
            currentPaymentAction = 'single';
            currentLineItemId = lineItemId;
            currentEmployeeName = employeeName;
            
            document.getElementById('payment-modal-title').textContent = 'Mark Employee as Paid';
            document.getElementById('payment-modal-message').textContent = `Mark ${employeeName} as paid?`;
            document.getElementById('payment-reference').value = 'PAY-' + Date.now();
            document.getElementById('payment-remarks').value = '';
            document.getElementById('payment-modal').classList.remove('hidden');
        }

        async function markAllPaid(runId) {
            currentPaymentAction = 'all';
            currentRunId = runId;
            
            document.getElementById('payment-modal-title').textContent = 'Mark All as Paid';
            document.getElementById('payment-modal-message').textContent = 'Mark ALL employees in this run as paid?';
            document.getElementById('payment-reference').value = 'BATCH-' + Date.now();
            document.getElementById('payment-remarks').value = '';
            document.getElementById('payment-modal').classList.remove('hidden');
        }

        document.getElementById('confirm-payment').addEventListener('click', async () => {
            const reference = document.getElementById('payment-reference').value.trim();
            const remarks = document.getElementById('payment-remarks').value.trim();

            if (!reference) {
                alert('Please enter a payment reference');
                return;
            }

            // Show loading state in modal
            const confirmBtn = document.getElementById('confirm-payment');
            const cancelBtn = document.getElementById('cancel-payment');
            const originalText = confirmBtn.textContent;
            
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            confirmBtn.textContent = 'Processing Payment...';
            confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
            cancelBtn.classList.add('opacity-50', 'cursor-not-allowed');

            try {
                if (currentPaymentAction === 'single') {
                    // Mark single employee as paid
                    const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl(`/payroll/line-items/${currentLineItemId}/pay`), {
                        method: 'PUT',
                        headers: authHeaders(true),
                        body: JSON.stringify({
                            payment_reference: reference,
                            remarks: remarks || 'Individual payment'
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('payment-modal').classList.add('hidden');
                        showSuccessToast(`${currentEmployeeName} marked as paid!`);
                        // Refresh the run details without closing modal
                        await viewRunDetails(currentRunId, currentPeriodCode);
                    } else {
                        alert('Error: ' + (result.message || 'Failed to mark as paid'));
                    }
                } else if (currentPaymentAction === 'all') {
                    // Mark all employees as paid
                    const response = await window.AuthManager.authFetch(window.AppConfig.getApiUrl(`/payroll/runs/${currentRunId}/pay`), {
                        method: 'PUT',
                        headers: authHeaders(true),
                        body: JSON.stringify({
                            payment_date: new Date().toISOString().split('T')[0],
                            payment_reference: reference
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        document.getElementById('payment-modal').classList.add('hidden');
                        showSuccessToast('All employees marked as paid!');
                        // Refresh the run details without closing modal
                        await viewRunDetails(currentRunId, currentPeriodCode);
                        loadPeriods(); // Also refresh the periods list
                    } else {
                        alert('Error: ' + (result.message || 'Failed to mark as paid'));
                    }
                }
            } catch (error) {
                alert('Network error: ' + error.message);
            } finally {
                // Reset button state
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
                confirmBtn.textContent = originalText;
                confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                cancelBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });

        document.getElementById('cancel-payment').addEventListener('click', () => {
            document.getElementById('payment-modal').classList.add('hidden');
        });

        document.getElementById('close-modal').addEventListener('click', () => {
            document.getElementById('details-modal').classList.add('hidden');
        });

        document.getElementById('refresh-btn').addEventListener('click', loadPeriods);

        document.getElementById('logout-btn').addEventListener('click', () => {
            localStorage.removeItem('hris_token');
            localStorage.removeItem('hris_user');
            window.location.href = window.AppConfig.getBaseUrl('/login');
        });

        // Load periods on page load
        loadPeriods();
    </script>
</body>
</html>
