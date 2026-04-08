/**
 * Leave Analytics - Charts and Data Management
 */

let charts = {};
let reportData = null;

document.addEventListener('DOMContentLoaded', function() {
    const endDate = new Date();
    endDate.setDate(endDate.getDate() + 60); // 60 days in the future
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30); // 30 days in the past
    
    document.getElementById('start-date').valueAsDate = startDate;
    document.getElementById('end-date').valueAsDate = endDate;
    
    initializeCharts();
    loadReports();
});

function showLoadingSkeleton() {
    const content = document.getElementById('report-content');
    if (content && window.LoadingSkeletons) {
        content.innerHTML = window.LoadingSkeletons.reportPage();
    }
}

function initializeCharts() {
    // Check if chart elements exist
    const statusCanvas = document.getElementById('statusChart');
    const typeCanvas = document.getElementById('typeChart');
    
    if (!statusCanvas || !typeCanvas) {
        console.error('Chart canvas elements not found. Skipping chart initialization.');
        return;
    }
    
    // Status Chart (Donut)
    const statusCtx = statusCanvas.getContext('2d');
    charts.status = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Denied'],
            datasets: [{
                data: [0, 0, 0],
                backgroundColor: ['rgb(34, 197, 94)', 'rgb(234, 179, 8)', 'rgb(239, 68, 68)']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#cbd5e1' } } }
        }
    });

    // Type Chart (Pie)
    const typeCtx = typeCanvas.getContext('2d');
    charts.type = new Chart(typeCtx, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: ['rgb(59, 130, 246)', 'rgb(16, 185, 129)', 'rgb(245, 158, 11)', 'rgb(168, 85, 247)']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#cbd5e1' } } }
        }
    });
}

async function loadReports() {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    if (!startDate || !endDate) {
        showToast('Please select date range', 'error');
        return;
    }
    
    console.log('Loading leave reports...', { startDate, endDate });
    
    // Show loading skeleton when regenerating (not on first load)
    const isRegenerate = reportData !== null;
    if (isRegenerate) {
        showLoadingSkeleton();
    }
    
    try {
        const url = AppConfig.getApiUrl(`/reports/leave?start_date=${startDate}&end_date=${endDate}`);
        console.log('Fetching from:', url);
        
        const response = await fetch(url, {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('hris_token')}` }
        });
        
        console.log('Response status:', response.status);
        
        // Get response as text first to see what we're getting
        const responseText = await response.text();
        console.log('Raw response:', responseText.substring(0, 500)); // First 500 chars
        
        // Try to parse as JSON
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response was not JSON, likely a PHP error');
            throw new Error('Server returned an error. Check browser console for details.');
        }
        
        console.log('Response data:', result);
        
        if (result.success) {
            reportData = result.data.report;
            
            console.log('Report data received:', reportData);
            console.log('Records count:', reportData.records?.length || 0);
            
            // Only restore content if regenerating (DOM already exists on first load)
            if (isRegenerate) {
                restoreContent();
            }
            
            updateSummaryCards(reportData.summary);
            updateCharts(reportData);
            updateTable(reportData.records);
        } else {
            console.error('API returned error:', result.message);
            if (isRegenerate) {
                restoreContent();
            }
            showToast(result.message || 'Failed to load report', 'error');
        }
    } catch (error) {
        console.error('Error loading report:', error);
        if (isRegenerate) {
            restoreContent();
        }
        showToast('Error loading report data', 'error');
    }
}

// Restore actual content structure
function restoreContent() {
    const content = document.getElementById('report-content');
    if (!content) return;
    
    content.innerHTML = `
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Total Requests</p><p class="text-3xl font-bold text-white mt-2" id="total-requests">0</p></div>
                    <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Approved</p><p class="text-3xl font-bold text-green-400 mt-2" id="total-approved">0</p></div>
                    <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Pending</p><p class="text-3xl font-bold text-yellow-400 mt-2" id="total-pending">0</p></div>
                    <div class="w-12 h-12 bg-yellow-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Total Days</p><p class="text-3xl font-bold text-purple-400 mt-2" id="total-days">0</p></div>
                    <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Leave Status</h3>
                <canvas id="statusChart"></canvas>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Leave Types Distribution</h3>
                <canvas id="typeChart"></canvas>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Leave Requests</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-slate-300 font-medium">Employee</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Leave Type</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Start Date</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">End Date</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Days</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody id="records-table" class="divide-y divide-slate-700">
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    // Reinitialize charts after restoring content
    initializeCharts();
}

function updateSummaryCards(summary) {
    const totalRequests = document.getElementById('total-requests');
    totalRequests.textContent = summary.total_requests || 0;
    totalRequests.classList.add('loaded');
    
    const totalApproved = document.getElementById('total-approved');
    totalApproved.textContent = summary.approved || 0;
    totalApproved.classList.add('loaded');
    
    const totalPending = document.getElementById('total-pending');
    totalPending.textContent = summary.pending || 0;
    totalPending.classList.add('loaded');
    
    const totalDays = document.getElementById('total-days');
    totalDays.textContent = summary.total_days || 0;
    totalDays.classList.add('loaded');
}

function updateCharts(data) {
    // Status chart
    charts.status.data.datasets[0].data = [
        data.summary.approved || 0,
        data.summary.pending || 0,
        data.summary.denied || 0
    ];
    charts.status.update();
    
    // Type chart
    const typeData = data.summary.by_leave_type || {};
    charts.type.data.labels = Object.keys(typeData);
    charts.type.data.datasets[0].data = Object.values(typeData).map(t => t.count);
    charts.type.update();
}

function updateTable(records) {
    const tbody = document.getElementById('records-table');
    if (!records || records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No records found for selected period</td></tr>';
        return;
    }
    
    tbody.innerHTML = records.slice(0, 100).map(record => `
        <tr class="hover:bg-slate-700/50">
            <td class="px-4 py-3 text-slate-300">${record.employee?.name || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-300">${record.leave_type?.name || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-300">${record.start_date}</td>
            <td class="px-4 py-3 text-slate-300">${record.end_date}</td>
            <td class="px-4 py-3 text-slate-300">${record.days}</td>
            <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs ${getStatusClass(record.status)}">${record.status}</span></td>
        </tr>
    `).join('');
}

function getStatusClass(status) {
    const statusClasses = {
        'Approved': 'bg-green-500/20 text-green-400 border-green-500/30',
        'Pending': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
        'Denied': 'bg-red-500/20 text-red-400 border-red-500/30'
    };
    return statusClasses[status] || 'bg-slate-500/20 text-slate-400 border-slate-500/30';
}

function showToast(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    // Don't call window.showToast to avoid infinite recursion
    // Just log to console for now
}
