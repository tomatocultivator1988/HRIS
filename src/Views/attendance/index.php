<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <!-- Loading Screen -->
    <div id="page-loading" class="fixed inset-0 bg-slate-900 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
            <h2 class="text-2xl font-semibold text-white">Loading Attendance...</h2>
            <p class="text-slate-400 mt-2">Please wait</p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="flex h-full bg-slate-900">
        
        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
        
        <!-- Confirmation Modal -->
        <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center p-4">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-md w-full p-6">
                <div class="text-center mb-6">
                    <div id="confirm-icon" class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4">
                        <!-- Icon will be inserted here -->
                    </div>
                    <h3 id="confirm-title" class="text-xl font-semibold text-white mb-2"></h3>
                    <p id="confirm-message" class="text-slate-300"></p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                        Cancel
                    </button>
                    <button id="confirm-action-btn" class="flex-1 px-4 py-2 text-white rounded-lg transition-all">
                        Confirm
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading Modal -->
        <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[70] flex items-center justify-center p-4">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
                <h3 id="loading-message" class="text-xl font-semibold text-white">Processing...</h3>
                <p class="text-slate-400 mt-2">Please wait</p>
            </div>
        </div>

        <!-- Modal for Detect Absences Result -->
        <div id="absences-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
                <div class="bg-gradient-to-r from-orange-600 to-orange-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-xl font-bold text-white">Absence Detection Results</h3>
                    </div>
                    <button onclick="closeAbsencesModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <div id="absences-modal-content">
                        <!-- Content will be inserted here -->
                    </div>
                </div>
                <div class="bg-slate-700 px-6 py-4 flex justify-end">
                    <button onclick="closeAbsencesModal()" class="px-6 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Modal for Employee Attendance History -->
        <div id="employee-history-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
            <div class="bg-slate-800 rounded-xl shadow-2xl max-w-4xl w-full mx-4 max-h-[80vh] overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="text-xl font-bold text-white" id="employee-history-title">Employee Attendance History</h3>
                    </div>
                    <button onclick="closeEmployeeHistoryModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <div id="employee-history-content">
                        <div class="flex items-center justify-center py-12">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <p class="text-slate-400 ml-3">Loading attendance history...</p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-700 px-6 py-4 flex justify-end">
                    <button onclick="closeEmployeeHistoryModal()" class="px-6 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 border-r border-slate-700 flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                    HRIS MVP
                </h1>
                <p class="text-xs text-slate-400 mt-1">Human Resources System</p>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="<?= base_url('/dashboard/admin') ?>" id="dashboard-link" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
                
                <a href="<?= base_url('/employees') ?>" id="employees-link" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all hidden">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Employees
                </a>
                
                <a href="<?= base_url('/attendance') ?>" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg shadow-blue-900/50">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <span id="attendance-text">Attendance</span>
                </a>
                
                <a href="<?= base_url('/leave') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Leave Requests
                </a>
                
                <a href="<?= base_url('/reports') ?>" id="reports-link" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all hidden">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Reports
                </a>
                
                <a href="<?= base_url('/employees/profile') ?>" id="profile-link" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all hidden">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    My Profile
                </a>
            </nav>
            
            <!-- User Profile -->
            <div class="p-4 border-t border-slate-700">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                        A
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">Admin User</p>
                        <p class="text-xs text-slate-400 truncate">admin@company.com</p>
                    </div>
                </div>
                <button id="logout-btn" class="w-full flex items-center justify-center px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-900">
            <!-- Header -->
            <header class="bg-slate-800 border-b border-slate-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Attendance Management</h2>
                        <p class="text-slate-400 mt-1" id="header-subtitle">Track employee attendance and time records</p>
                    </div>
                    <div id="current-time" class="text-right">
                        <div class="text-sm text-slate-400">Current Time</div>
                        <div class="text-2xl font-bold text-white" id="clock">--:--:--</div>
                        <div class="text-sm text-slate-400" id="current-date">--</div>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="p-8 space-y-6">
                
                <!-- Quick Actions Card (Employee Only) -->
                <div id="quick-actions-card" class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 shadow-xl p-6 hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-white">Quick Actions</h3>
                            <p class="text-sm text-slate-400 mt-1">Record your time in and time out</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400">Today's Status</div>
                            <div id="today-status-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-slate-700/50 text-slate-400 border-slate-600 mt-1">
                                Not yet timed in
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button id="time-in-btn" class="flex items-center justify-center px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all shadow-lg">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold">Time In</span>
                        </button>
                        <button id="time-out-btn" class="flex items-center justify-center px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all shadow-lg">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-semibold">Time Out</span>
                        </button>
                    </div>
                    <div id="attendance-status" class="mt-4 p-4 bg-slate-700/50 rounded-lg hidden">
                        <p class="text-sm text-slate-300"></p>
                    </div>
                </div>

                <!-- Date Filter (Admin Only) -->
                <div id="date-filter-card" class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6 hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Daily Attendance</h3>
                            <p class="text-sm text-slate-400 mt-1">View and manage employee attendance records</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div>
                                <label class="text-xs text-slate-400 block mb-1">Select Date</label>
                                <input type="date" id="filter-date" class="px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 block mb-1">&nbsp;</label>
                                <button id="detect-absences-btn" class="px-4 py-2 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-lg hover:from-orange-700 hover:to-orange-800 transition-all flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    Detect Absences
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-300">
                                <strong>Detect Absences:</strong> Automatically marks employees as absent if they haven't recorded time-in for the selected date. This is typically done at the end of the day.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-slate-700">
                        <h3 class="text-xl font-semibold text-white">Attendance Records</h3>
                        <p class="text-slate-400 text-sm mt-1">View and manage attendance records</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Time In</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Time Out</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Work Hours</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body" class="divide-y divide-slate-700">
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                        <p class="text-slate-400 mt-2">Loading attendance records...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script src="<?= base_url('/assets/js/loading-skeletons.js') ?>"></script>
    <script src="<?= base_url('/assets/js/utils.js') ?>"></script>
    <script>
        let currentUser = null;
        
        // Get current date in Philippines timezone (Asia/Manila)
        function getPhilippinesDate() {
            const now = new Date();
            const philippinesDateStr = now.toLocaleString('en-US', { 
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
            
            // Parse MM/DD/YYYY and convert to YYYY-MM-DD
            const parts = philippinesDateStr.match(/(\d+)\/(\d+)\/(\d+)/);
            return `${parts[3]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
        }
        
        let currentDate = getPhilippinesDate();

        // Helper function to get Philippines time in YYYY-MM-DD HH:MM:SS format
        function getPhilippinesTime() {
            const now = new Date();
            const philippinesTimeStr = now.toLocaleString('en-US', { 
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            
            // Parse and format as YYYY-MM-DD HH:MM:SS
            const parts = philippinesTimeStr.match(/(\d+)\/(\d+)\/(\d+),\s+(\d+):(\d+):(\d+)/);
            return `${parts[3]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')} ${parts[4].padStart(2, '0')}:${parts[5].padStart(2, '0')}:${parts[6].padStart(2, '0')}`;
        }

        // Update clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateStr = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            
            document.getElementById('clock').textContent = timeStr;
            document.getElementById('current-date').textContent = dateStr;
        }
        
        // Start clock
        updateClock();
        setInterval(updateClock, 1000);

        // Initialize page
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                // Check if AppConfig is loaded
                if (typeof AppConfig === 'undefined') {
                    console.error('AppConfig not loaded!');
                    showError('Configuration not loaded. Please refresh the page.');
                    hideLoading();
                    return;
                }
                
                // Get current user
                currentUser = await getCurrentUser();
                
                if (!currentUser) {
                    console.error('No user found');
                    hideLoading();
                    return;
                }
                
                // Update user info in sidebar
                updateUserInfo(currentUser);
                
                // Update navigation based on role
                updateNavigationForRole(currentUser.role);
                
                // Update header based on role
                if (currentUser.role === 'admin') {
                    document.getElementById('header-subtitle').textContent = 'Manage employee attendance and detect absences';
                } else {
                    document.getElementById('header-subtitle').textContent = 'Record your time in and time out';
                }
                
                // Show appropriate UI based on role
                if (currentUser.role === 'admin') {
                    document.getElementById('date-filter-card').classList.remove('hidden');
                    document.getElementById('filter-date').value = currentDate;
                    loadDailyAttendance(currentDate);
                } else {
                    document.getElementById('quick-actions-card').classList.remove('hidden');
                    loadMyAttendance();
                }
                
                // Hide loading screen
                hideLoading();
            } catch (error) {
                console.error('Initialization error:', error);
                console.error('Error details:', error.message, error.stack);
                showError('Failed to load attendance page: ' + error.message);
                hideLoading();
            }
        });

        // Update user info in sidebar
        function updateUserInfo(user) {
            const initial = user.name ? user.name.charAt(0).toUpperCase() : 'U';
            document.querySelector('.w-10.h-10').textContent = initial;
            document.querySelector('.text-sm.font-medium').textContent = user.name || 'User';
            document.querySelector('.text-xs.text-slate-400').textContent = user.email || '';
        }
        
        // Update navigation based on role
        function updateNavigationForRole(role) {
            const dashboardLink = document.getElementById('dashboard-link');
            const employeesLink = document.getElementById('employees-link');
            const reportsLink = document.getElementById('reports-link');
            const profileLink = document.getElementById('profile-link');
            const attendanceText = document.getElementById('attendance-text');
            
            if (role === 'admin') {
                // Admin navigation
                dashboardLink.href = '<?= base_url('/dashboard/admin') ?>';
                employeesLink.classList.remove('hidden');
                reportsLink.classList.remove('hidden');
                profileLink.classList.add('hidden');
                attendanceText.textContent = 'Attendance';
            } else {
                // Employee navigation
                dashboardLink.href = '<?= base_url('/dashboard/employee') ?>';
                employeesLink.classList.add('hidden');
                reportsLink.classList.add('hidden');
                profileLink.classList.remove('hidden');
                attendanceText.textContent = 'My Attendance';
            }
        }

        // Time In
        document.getElementById('time-in-btn')?.addEventListener('click', async function() {
            try {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span>Processing...';
                
                const response = await fetch(AppConfig.getApiUrl('/attendance/timein'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAccessToken()}`
                    },
                    body: JSON.stringify({
                        date: currentDate,
                        time_in: getPhilippinesTime()
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Time-in recorded successfully!');
                    loadMyAttendance();
                } else {
                    showError(result.message || 'Failed to record time-in');
                }
            } catch (error) {
                console.error('Time-in error:', error);
                showError('Failed to record time-in');
            } finally {
                const btn = document.getElementById('time-in-btn');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="font-semibold">Time In</span>';
            }
        });

        // Time Out
        document.getElementById('time-out-btn')?.addEventListener('click', async function() {
            try {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span>Processing...';
                
                const response = await fetch(AppConfig.getApiUrl('/attendance/timeout'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAccessToken()}`
                    },
                    body: JSON.stringify({
                        date: currentDate,
                        time_out: getPhilippinesTime()
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Time-out recorded successfully!');
                    loadMyAttendance();
                } else {
                    showError(result.message || 'Failed to record time-out');
                }
            } catch (error) {
                console.error('Time-out error:', error);
                showError('Failed to record time-out');
            } finally {
                const btn = document.getElementById('time-out-btn');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="font-semibold">Time Out</span>';
            }
        });

        // Load my attendance (employee)
        async function loadMyAttendance() {
            try {
                const startDate = new Date();
                startDate.setDate(startDate.getDate() - 30);
                const endDate = new Date();
                
                const response = await fetch(AppConfig.getApiUrl(`/attendance/history?start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`), {
                    headers: {
                        'Authorization': `Bearer ${getAccessToken()}`
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayAttendanceRecords(result.data.records, false);
                    updateAttendanceStatus(result.data.records);
                } else {
                    showError('Failed to load attendance records');
                }
            } catch (error) {
                console.error('Load attendance error:', error);
                showError('Failed to load attendance records');
            }
        }

        // Load daily attendance (admin)
        async function loadDailyAttendance(date) {
            try {
                const response = await fetch(AppConfig.getApiUrl(`/attendance/daily?date=${date}`), {
                    headers: {
                        'Authorization': `Bearer ${getAccessToken()}`
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayAttendanceRecords(result.data.records, true);
                } else {
                    showError('Failed to load daily attendance');
                }
            } catch (error) {
                console.error('Load daily attendance error:', error);
                showError('Failed to load daily attendance');
            }
        }

        // Display attendance records
        function displayAttendanceRecords(records, showEmployee) {
            const tbody = document.getElementById('attendance-table-body');
            
            if (!records || records.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            No attendance records found
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = records.map(record => {
                const statusColors = {
                    'Present': 'bg-green-500/20 text-green-400 border-green-500/30',
                    'Late': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                    'Absent': 'bg-red-500/20 text-red-400 border-red-500/30',
                    'Half-day': 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                    'On Leave': 'bg-purple-500/20 text-purple-400 border-purple-500/30'
                };
                
                const statusClass = statusColors[record.status] || 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                
                // Make whole row clickable for admin
                const employeeCell = showEmployee 
                    ? `<td class="px-6 py-4 text-sm text-blue-400 font-medium">
                        ${record.employee_name || 'N/A'}
                       </td>` 
                    : '<td class="px-6 py-4 text-sm text-white">You</td>';
                
                const rowClickHandler = showEmployee 
                    ? `onclick="showEmployeeHistory('${record.employee_id}', '${record.employee_name}')" style="cursor: pointer;"`
                    : '';
                
                return `
                    <tr class="hover:bg-slate-700/30 transition-colors" ${rowClickHandler}>
                        ${employeeCell}
                        <td class="px-6 py-4 text-sm text-slate-300">${formatDate(record.date)}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${formatTime(record.time_in)}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${record.time_out ? formatTime(record.time_out) : '<span class="text-slate-500">-</span>'}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${record.work_hours ? record.work_hours + ' hrs' : '<span class="text-slate-500">-</span>'}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${statusClass}">
                                ${record.status}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Update attendance status for employee
        function updateAttendanceStatus(records) {
            const today = records.find(r => r.date === currentDate);
            const statusDiv = document.getElementById('attendance-status');
            const badge = document.getElementById('today-status-badge');
            const timeInBtn = document.getElementById('time-in-btn');
            const timeOutBtn = document.getElementById('time-out-btn');
            
            if (today) {
                statusDiv.classList.remove('hidden');
                const hasTimeOut = today.time_out !== null;
                statusDiv.querySelector('p').innerHTML = `
                    <strong>Today's Status:</strong> ${today.status} | 
                    Time In: ${formatTime(today.time_in)} | 
                    ${hasTimeOut ? `Time Out: ${formatTime(today.time_out)} | Work Hours: ${today.work_hours} hrs` : 'Not yet timed out'}
                `;
                
                // Update the badge and buttons
                if (!today.time_in) {
                    // Not yet timed in
                    badge.textContent = 'Not yet timed in';
                    badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-slate-700/50 text-slate-400 border-slate-600 mt-1';
                    timeInBtn.disabled = false;
                    timeInBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'grayscale');
                    timeOutBtn.disabled = true;
                    timeOutBtn.classList.add('opacity-50', 'cursor-not-allowed', 'grayscale');
                } else if (today.time_in && !today.time_out) {
                    // Timed in, not yet timed out
                    badge.textContent = 'Timed in at ' + formatTime(today.time_in);
                    badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-green-500/20 text-green-400 border-green-500/30 mt-1';
                    timeInBtn.disabled = true;
                    timeInBtn.classList.add('opacity-50', 'cursor-not-allowed', 'grayscale');
                    timeOutBtn.disabled = false;
                    timeOutBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'grayscale');
                } else if (today.time_in && today.time_out) {
                    // Completed
                    badge.textContent = 'Completed - ' + today.work_hours + ' hrs';
                    badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-blue-500/20 text-blue-400 border-blue-500/30 mt-1';
                    timeInBtn.disabled = true;
                    timeInBtn.classList.add('opacity-50', 'cursor-not-allowed', 'grayscale');
                    timeOutBtn.disabled = true;
                    timeOutBtn.classList.add('opacity-50', 'cursor-not-allowed', 'grayscale');
                }
            } else {
                // No record for today
                badge.textContent = 'Not yet timed in';
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-slate-700/50 text-slate-400 border-slate-600 mt-1';
                timeInBtn.disabled = false;
                timeInBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'grayscale');
                timeOutBtn.disabled = true;
                timeOutBtn.classList.add('opacity-50', 'cursor-not-allowed', 'grayscale');
            }
        }

        // Date filter change (admin)
        document.getElementById('filter-date')?.addEventListener('change', function() {
            currentDate = this.value;
            loadDailyAttendance(currentDate);
        });

        let confirmCallback = null;

        function showConfirm(title, message, type = 'warning') {
            const iconContainer = document.getElementById('confirm-icon');
            const confirmBtn = document.getElementById('confirm-action-btn');
            
            // Set icon and colors based on type
            if (type === 'warning') {
                iconContainer.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-500/20 mb-4';
                iconContainer.innerHTML = `
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                `;
                confirmBtn.className = 'flex-1 px-4 py-2 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white rounded-lg transition-all';
            } else if (type === 'danger') {
                iconContainer.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-500/20 mb-4';
                iconContainer.innerHTML = `
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
                confirmBtn.className = 'flex-1 px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg transition-all';
            } else {
                iconContainer.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-500/20 mb-4';
                iconContainer.innerHTML = `
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                `;
                confirmBtn.className = 'flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all';
            }
            
            document.getElementById('confirm-title').textContent = title;
            document.getElementById('confirm-message').textContent = message;
            document.getElementById('confirm-modal').classList.remove('hidden');
            
            return new Promise((resolve) => {
                confirmCallback = resolve;
            });
        }

        function closeConfirmModal() {
            document.getElementById('confirm-modal').classList.add('hidden');
            if (confirmCallback) {
                confirmCallback(false);
                confirmCallback = null;
            }
        }

        document.getElementById('confirm-action-btn')?.addEventListener('click', function() {
            if (confirmCallback) {
                confirmCallback(true);
                confirmCallback = null;
            }
            closeConfirmModal();
        });

        function showLoadingModal(message = 'Processing...') {
            document.getElementById('loading-message').textContent = message;
            document.getElementById('loading-modal').classList.remove('hidden');
        }

        function hideLoadingModal() {
            document.getElementById('loading-modal').classList.add('hidden');
        }

        // Detect absences (admin)
        document.getElementById('detect-absences-btn')?.addEventListener('click', async function() {
            // Show confirmation modal
            const confirmed = await showConfirm(
                'Detect Absences',
                'Are you sure you want to mark all employees without time-in as absent for the selected date? This action will create absence records for employees who haven\'t recorded their attendance.',
                'warning'
            );
            
            if (!confirmed) return;
            
            try {
                showLoadingModal('Detecting absences...');
                
                const response = await fetch(AppConfig.getApiUrl('/attendance/detect-absences'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAccessToken()}`
                    },
                    body: JSON.stringify({ date: currentDate })
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text.substring(0, 500));
                    throw new Error('Server returned an error. Please check the console for details.');
                }
                
                const result = await response.json();
                
                hideLoadingModal();
                
                if (result.success) {
                    showAbsencesModal(result.data);
                    loadDailyAttendance(currentDate);
                    
                    const newlyMarked = result.data.absences_marked || 0;
                    const totalAbsent = result.data.absent_count || 0;
                    
                    if (newlyMarked > 0) {
                        showSuccess(`Marked ${newlyMarked} employee${newlyMarked !== 1 ? 's' : ''} as absent. Total absent: ${totalAbsent}`);
                    } else if (totalAbsent > 0) {
                        showSuccess(`All absences already recorded. Total absent: ${totalAbsent}`);
                    } else {
                        showSuccess('All employees have recorded their attendance!');
                    }
                } else {
                    showError(result.message || 'Failed to detect absences');
                }
            } catch (error) {
                hideLoadingModal();
                console.error('Detect absences error:', error);
                showError(error.message || 'Failed to detect absences');
            }
        });

        // Show absences modal
        function showAbsencesModal(data) {
            const modal = document.getElementById('absences-modal');
            const content = document.getElementById('absences-modal-content');
            
            const newlyMarked = data.absences_marked || 0;
            const totalAbsent = data.absent_count || 0;
            
            let html = `
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-semibold text-white">Date: ${formatDate(data.date)}</h4>
                            <p class="text-sm text-slate-400">${data.is_working_day ? 'Working Day' : 'Non-Working Day'}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold ${totalAbsent > 0 ? 'text-red-400' : 'text-green-400'}">
                                ${totalAbsent}
                            </div>
                            <div class="text-sm text-slate-400">Total Absent</div>
                            ${newlyMarked > 0 ? `<div class="text-xs text-orange-400 mt-1">${newlyMarked} newly marked</div>` : ''}
                        </div>
                    </div>
            `;
            
            if (!data.is_working_day) {
                html += `
                    <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-blue-300">This is not a working day (weekend). No absences detected.</span>
                        </div>
                    </div>
                `;
            } else if (totalAbsent === 0 && data.on_leave_count === 0) {
                html += `
                    <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-300">Great! All employees have recorded their attendance.</span>
                        </div>
                    </div>
                `;
            } else {
                if (newlyMarked > 0) {
                    html += `
                        <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-orange-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="text-orange-300">Marked ${newlyMarked} employee${newlyMarked !== 1 ? 's' : ''} as absent.</span>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-blue-300">All absences have already been recorded for this date.</span>
                            </div>
                        </div>
                    `;
                }
                
                if (totalAbsent > 0) {
                    html += `
                        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span class="text-red-300">Total of ${totalAbsent} absent employee${totalAbsent !== 1 ? 's' : ''} for this date:</span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                    `;
                    
                    data.absent_employees.forEach((emp, index) => {
                        const isNewlyMarked = !emp.already_marked;
                        html += `
                            <div class="bg-slate-700/50 rounded-lg p-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center text-red-400 font-semibold mr-3">
                                        ${index + 1}
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">${emp.employee_name}</div>
                                        <div class="text-sm text-slate-400">${emp.department || 'N/A'}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    ${isNewlyMarked ? '<span class="px-2 py-1 bg-orange-500/20 text-orange-400 rounded text-xs font-medium border border-orange-500/30">New</span>' : ''}
                                    <span class="px-3 py-1 bg-red-500/20 text-red-400 rounded-full text-xs font-medium border border-red-500/30">
                                        Absent
                                    </span>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `</div>`;
                }
            }
            
            // Show employees on leave
            if (data.on_leave_count > 0) {
                html += `
                    <div class="mt-6">
                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-blue-300">The following employees are on approved leave:</span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                `;
                
                data.on_leave_employees.forEach((emp, index) => {
                    html += `
                        <div class="bg-slate-700/50 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 font-semibold mr-3">
                                    ${index + 1}
                                </div>
                                <div>
                                    <div class="text-white font-medium">${emp.employee_name}</div>
                                    <div class="text-sm text-slate-400">${emp.department || 'N/A'}</div>
                                    <div class="text-xs text-blue-400 mt-1">
                                        Leave: ${formatDate(emp.leave_start)} - ${formatDate(emp.leave_end)} (${emp.leave_duration})
                                    </div>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-xs font-medium border border-blue-500/30">
                                On Leave
                            </span>
                        </div>
                    `;
                });
                
                html += `</div></div>`;
            }
            
            html += `</div>`;
            
            content.innerHTML = html;
            modal.classList.remove('hidden');
        }

        // Close absences modal
        function closeAbsencesModal() {
            document.getElementById('absences-modal').classList.add('hidden');
        }

        // Close modal on background click
        document.getElementById('absences-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAbsencesModal();
            }
        });
        
        // Show employee attendance history
        async function showEmployeeHistory(employeeId, employeeName) {
            const modal = document.getElementById('employee-history-modal');
            const title = document.getElementById('employee-history-title');
            const content = document.getElementById('employee-history-content');
            
            // Show modal with loading state
            title.textContent = `${employeeName} - Attendance History`;
            content.innerHTML = `
                <div class="flex items-center justify-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    <p class="text-slate-400 ml-3">Loading attendance history...</p>
                </div>
            `;
            modal.classList.remove('hidden');
            
            try {
                // Fetch employee's full attendance history (from created_at, not hire date)
                // We'll get all records by using a very old start date
                const veryOldDate = '2000-01-01'; // Far enough back to catch everything
                const today = new Date().toISOString().split('T')[0];
                
                const response = await fetch(
                    AppConfig.getApiUrl(`/attendance/history?employee_id=${employeeId}&start_date=${veryOldDate}&end_date=${today}&limit=500`),
                    {
                        headers: {
                            'Authorization': `Bearer ${getAccessToken()}`
                        }
                    }
                );
                
                const result = await response.json();
                
                if (result.success && result.data.records) {
                    const records = result.data.records;
                    
                    if (records.length === 0) {
                        content.innerHTML = `
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-slate-400">No attendance records found for this employee</p>
                            </div>
                        `;
                        return;
                    }
                    
                    // Calculate statistics
                    const stats = {
                        total: records.length,
                        present: records.filter(r => r.status === 'Present').length,
                        late: records.filter(r => r.status === 'Late').length,
                        absent: records.filter(r => r.status === 'Absent').length,
                        halfDay: records.filter(r => r.status === 'Half-day').length
                    };
                    
                    const totalWorkHours = records
                        .filter(r => r.work_hours)
                        .reduce((sum, r) => sum + parseFloat(r.work_hours), 0);
                    const avgWorkHours = totalWorkHours / (stats.present + stats.late);
                    
                    // Display statistics and records
                    content.innerHTML = `
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold text-white mb-4">Summary Statistics</h4>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                                <div class="bg-slate-700/50 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-white">${stats.total}</div>
                                    <div class="text-xs text-slate-400 mt-1">Total Records</div>
                                </div>
                                <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-green-400">${stats.present}</div>
                                    <div class="text-xs text-slate-400 mt-1">Present</div>
                                </div>
                                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-yellow-400">${stats.late}</div>
                                    <div class="text-xs text-slate-400 mt-1">Late</div>
                                </div>
                                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-red-400">${stats.absent}</div>
                                    <div class="text-xs text-slate-400 mt-1">Absent</div>
                                </div>
                                <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-blue-400">${avgWorkHours.toFixed(1)}</div>
                                    <div class="text-xs text-slate-400 mt-1">Avg Hours</div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-semibold text-white mb-4">Attendance Records (All Time)</h4>
                            <div class="bg-slate-700/30 rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-slate-700/50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Time In</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Time Out</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Hours</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-700">
                                        ${records.map(record => {
                                            const statusColors = {
                                                'Present': 'bg-green-500/20 text-green-400 border-green-500/30',
                                                'Late': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                                'Absent': 'bg-red-500/20 text-red-400 border-red-500/30',
                                                'Half-day': 'bg-blue-500/20 text-blue-400 border-blue-500/30'
                                            };
                                            const statusClass = statusColors[record.status] || 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                                            
                                            return `
                                                <tr class="hover:bg-slate-700/30">
                                                    <td class="px-4 py-3 text-sm text-slate-300">${formatDate(record.date)}</td>
                                                    <td class="px-4 py-3 text-sm text-slate-300">${formatTime(record.time_in)}</td>
                                                    <td class="px-4 py-3 text-sm text-slate-300">${record.time_out ? formatTime(record.time_out) : '<span class="text-slate-500">-</span>'}</td>
                                                    <td class="px-4 py-3 text-sm text-slate-300">${record.work_hours ? record.work_hours + ' hrs' : '<span class="text-slate-500">-</span>'}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border ${statusClass}">
                                                            ${record.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-slate-400">${result.message || 'Failed to load attendance history'}</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading employee history:', error);
                content.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-slate-400">Failed to load attendance history</p>
                    </div>
                `;
            }
        }
        
        // Close employee history modal
        function closeEmployeeHistoryModal() {
            document.getElementById('employee-history-modal').classList.add('hidden');
        }
        
        // Close modal on background click
        document.getElementById('employee-history-modal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEmployeeHistoryModal();
            }
        });

        // Helper functions
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function formatTime(timeStr) {
            if (!timeStr) return '-';
            const date = new Date(timeStr);
            return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }

        function hideLoading() {
            setTimeout(() => {
                const loading = document.getElementById('page-loading');
                loading.style.opacity = '0';
                loading.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => loading.style.display = 'none', 300);
            }, 500);
        }

        function showSuccess(message) {
            showToast(message, 'success');
        }

        function showError(message) {
            showToast(message, 'error');
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600';
            const icon = type === 'success' 
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                : type === 'error'
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
            
            toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${icon}
                </svg>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            
            document.getElementById('toast-container').appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        async function getCurrentUser() {
            try {
                // Check both possible localStorage keys
                let userStr = localStorage.getItem('user');
                if (!userStr) {
                    userStr = localStorage.getItem('hris_user');
                }
                
                if (!userStr) {
                    console.log('No user in localStorage, redirecting to login...');
                    window.location.href = AppConfig.getBaseUrl('/login');
                    return null;
                }
                
                const user = JSON.parse(userStr);
                console.log('Current user:', user);
                return user;
            } catch (error) {
                console.error('Error getting current user:', error);
                window.location.href = AppConfig.getBaseUrl('/login');
                return null;
            }
        }

        function getAccessToken() {
            // Check both possible localStorage keys
            return localStorage.getItem('access_token') || localStorage.getItem('hris_token');
        }
    </script>
</body>
</html>
