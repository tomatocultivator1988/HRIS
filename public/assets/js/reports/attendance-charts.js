/**
 * Attendance Reports - Charts and Data Management
 */

let charts = {};
let reportData = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates (last 30 days)
    const endDate = new Date();
    const startDate = new Date();
    startDate.setDate(startDate.getDate() - 30);
    
    document.getElementById('start-date').valueAsDate = startDate;
    document.getElementById('end-date').valueAsDate = endDate;
    
    // Initialize charts first (before loading skeleton)
    initializeCharts();
    
    // Load initial data (this will show skeleton and restore content)
    loadReports();
});

// Show loading skeleton
function showLoadingSkeleton() {
    const content = document.getElementById('report-content');
    if (content && window.LoadingSkeletons) {
        content.innerHTML = window.LoadingSkeletons.reportPage();
    }
}

// Initialize all charts
function initializeCharts() {
    // Check if chart elements exist
    const trendCanvas = document.getElementById('trendChart');
    const statusCanvas = document.getElementById('statusChart');
    
    if (!trendCanvas || !statusCanvas) {
        console.error('Chart canvas elements not found. Skipping chart initialization.');
        return;
    }
    
    // Trend Chart (Line)
    const trendCtx = trendCanvas.getContext('2d');
    charts.trend = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Present',
                data: [],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }, {
                label: 'Late',
                data: [],
                borderColor: 'rgb(234, 179, 8)',
                backgroundColor: 'rgba(234, 179, 8, 0.1)',
                tension: 0.4
            }, {
                label: 'Absent',
                data: [],
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { labels: { color: '#cbd5e1' } }
            },
            scales: {
                y: { ticks: { color: '#cbd5e1' }, grid: { color: '#334155' } },
                x: { ticks: { color: '#cbd5e1' }, grid: { color: '#334155' } }
            }
        }
    });

    // Status Chart (Pie)
    const statusCtx = statusCanvas.getContext('2d');
    charts.status = new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Present', 'Late', 'Absent', 'On Leave'],
            datasets: [{
                data: [0, 0, 0, 0],
                backgroundColor: ['rgb(34, 197, 94)', 'rgb(234, 179, 8)', 'rgb(239, 68, 68)', 'rgb(168, 85, 247)']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#cbd5e1' } }
            }
        }
    });
}

// Load reports data
async function loadReports() {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    if (!startDate || !endDate) {
        showToast('Please select date range', 'error');
        return;
    }
    
    // Show loading skeleton when regenerating
    const isRegenerate = reportData !== null;
    if (isRegenerate) {
        showLoadingSkeleton();
    }
    
    try {
        const response = await fetch(AppConfig.getApiUrl(`/reports/attendance?start_date=${startDate}&end_date=${endDate}`), {
            headers: { 'Authorization': `Bearer ${localStorage.getItem('hris_token')}` }
        });
        
        const result = await response.json();
        
        if (result.success) {
            reportData = result.data.report;
            
            // Restore actual content
            restoreContent();
            
            // Update with data
            updateSummaryCards(reportData.summary);
            updateCharts(reportData);
            updateTable(reportData.records);
        } else {
            restoreContent();
            showToast(result.message || 'Failed to load report', 'error');
        }
    } catch (error) {
        console.error('Error loading report:', error);
        restoreContent();
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
                    <div><p class="text-slate-400 text-sm">Total Records</p><p class="text-3xl font-bold text-white mt-2" id="total-records">0</p></div>
                    <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Present</p><p class="text-3xl font-bold text-green-400 mt-2" id="total-present">0</p></div>
                    <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Late</p><p class="text-3xl font-bold text-yellow-400 mt-2" id="total-late">0</p></div>
                    <div class="w-12 h-12 bg-yellow-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <div class="flex items-center justify-between">
                    <div><p class="text-slate-400 text-sm">Absent</p><p class="text-3xl font-bold text-red-400 mt-2" id="total-absent">0</p></div>
                    <div class="w-12 h-12 bg-red-500/10 rounded-lg flex items-center justify-center"><svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Daily Attendance Trend</h3>
                <canvas id="trendChart"></canvas>
            </div>
            <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Status Distribution</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Data Table -->
        <div class="bg-slate-800 rounded-lg border border-slate-700 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Detailed Records</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-slate-300 font-medium">Date</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Employee</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Department</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Time In</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Time Out</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Hours</th>
                            <th class="px-4 py-3 text-slate-300 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody id="records-table" class="divide-y divide-slate-700">
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
    
    // Reinitialize charts after restoring content
    initializeCharts();
}

// Update summary cards
function updateSummaryCards(summary) {
    const totalRecords = document.getElementById('total-records');
    totalRecords.textContent = summary.total_records || 0;
    totalRecords.classList.add('loaded');
    
    const totalPresent = document.getElementById('total-present');
    totalPresent.textContent = summary.present || 0;
    totalPresent.classList.add('loaded');
    
    const totalLate = document.getElementById('total-late');
    totalLate.textContent = summary.late || 0;
    totalLate.classList.add('loaded');
    
    const totalAbsent = document.getElementById('total-absent');
    totalAbsent.textContent = summary.absent || 0;
    totalAbsent.classList.add('loaded');
}

// Update all charts
function updateCharts(data) {
    // Update trend chart (simplified - group by date)
    const trendData = groupByDate(data.records);
    charts.trend.data.labels = trendData.labels;
    charts.trend.data.datasets[0].data = trendData.present;
    charts.trend.data.datasets[1].data = trendData.late;
    charts.trend.data.datasets[2].data = trendData.absent;
    charts.trend.update();
    
    // Update status chart
    charts.status.data.datasets[0].data = [
        data.summary.present || 0,
        data.summary.late || 0,
        data.summary.absent || 0,
        data.summary.on_leave || 0
    ];
    charts.status.update();
}

// Group records by date
function groupByDate(records) {
    const grouped = {};
    records.forEach(record => {
        const date = record.date;
        if (!grouped[date]) {
            grouped[date] = { present: 0, late: 0, absent: 0 };
        }
        if (record.status === 'Present') grouped[date].present++;
        else if (record.status === 'Late') grouped[date].late++;
        else if (record.status === 'Absent') grouped[date].absent++;
    });
    
    const labels = Object.keys(grouped).sort();
    return {
        labels,
        present: labels.map(d => grouped[d].present),
        late: labels.map(d => grouped[d].late),
        absent: labels.map(d => grouped[d].absent)
    };
}

// Update table
function updateTable(records) {
    const tbody = document.getElementById('records-table');
    if (!records || records.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">No records found for selected period</td></tr>';
        return;
    }
    
    tbody.innerHTML = records.slice(0, 100).map(record => `
        <tr class="hover:bg-slate-700/50">
            <td class="px-4 py-3 text-slate-300">${record.date}</td>
            <td class="px-4 py-3 text-slate-300">${record.employee?.name || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-300">${record.employee?.department || 'N/A'}</td>
            <td class="px-4 py-3 text-slate-300">${record.time_in || '-'}</td>
            <td class="px-4 py-3 text-slate-300">${record.time_out || '-'}</td>
            <td class="px-4 py-3 text-slate-300">${record.work_hours || '0'}</td>
            <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs ${getStatusClass(record.status)}">${record.status}</span></td>
        </tr>
    `).join('');
}

function getStatusClass(status) {
    const classes = {
        'Present': 'bg-green-500/20 text-green-400',
        'Late': 'bg-yellow-500/20 text-yellow-400',
        'Absent': 'bg-red-500/20 text-red-400',
        'On Leave': 'bg-purple-500/20 text-purple-400'
    };
    return classes[status] || 'bg-slate-500/20 text-slate-400';
}

function showToast(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    // Don't call window.showToast to avoid infinite recursion
    // Just log to console for now
}
