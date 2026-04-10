<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <!-- Main Container -->
    <div class="flex h-full bg-slate-900">
        
        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
        
        <!-- Time In/Out Modal -->
        <div id="attendance-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl shadow-2xl max-w-md w-full mx-4">
                <div id="modal-header" class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 id="modal-title" class="text-xl font-bold text-white">Time In</h3>
                    </div>
                    <button onclick="closeAttendanceModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div id="current-time" class="text-4xl font-bold text-white mb-2">--:--:--</div>
                        <div id="current-date" class="text-slate-400">--</div>
                    </div>
                    <div id="modal-message" class="text-center text-slate-300 mb-6">
                        Are you sure you want to record your time in?
                    </div>
                </div>
                <div class="bg-slate-700 px-6 py-4 flex justify-end space-x-3">
                    <button onclick="closeAttendanceModal()" class="px-6 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                        Cancel
                    </button>
                    <button id="confirm-attendance-btn" class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg transition-all">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Request Leave Modal -->
        <div id="request-leave-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="request-modal-title" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 id="request-modal-title" class="text-xl font-bold text-white">Request Leave</h3>
                    </div>
                    <button onclick="closeRequestModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="leave-request-form" class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Leave Type</label>
                            <select id="leave-type" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                                <option value="">Select leave type</option>
                                <!-- Leave types will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Start Date</label>
                                <input type="date" id="start-date" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">End Date</label>
                                <input type="date" id="end-date" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Reason</label>
                            <textarea id="leave-reason" rows="4" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-blue-500" placeholder="Please provide a reason for your leave request..."></textarea>
                        </div>
                        <div id="calculated-days" class="hidden p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                            <p class="text-blue-300 text-sm">
                                <strong>Total Working Days:</strong> <span id="total-days">0</span> day(s)
                            </p>
                        </div>
                    </div>
                </form>
                <div class="bg-slate-700 px-6 py-4 flex justify-end space-x-3">
                    <button onclick="closeRequestModal()" class="px-6 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                        Cancel
                    </button>
                    <button onclick="submitLeaveRequest()" class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all">
                        Submit Request
                    </button>
                </div>
            </div>
        </div>

        <div id="submit-confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="submit-confirm-title" aria-describedby="submit-confirm-description" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-xl w-full p-6">
                <div class="mb-6">
                    <h3 id="submit-confirm-title" class="text-xl font-semibold text-white mb-2">Confirm Leave Request</h3>
                    <p id="submit-confirm-description" class="text-slate-300">Please review your request details before submitting.</p>
                </div>
                <div class="space-y-3 mb-6">
                    <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                        <span class="text-slate-400 text-sm">Leave Type</span>
                        <span id="submit-confirm-leave-type" class="text-white text-sm font-medium text-right"></span>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                        <span class="text-slate-400 text-sm">Start Date</span>
                        <span id="submit-confirm-start-date" class="text-white text-sm font-medium text-right"></span>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                        <span class="text-slate-400 text-sm">End Date</span>
                        <span id="submit-confirm-end-date" class="text-white text-sm font-medium text-right"></span>
                    </div>
                    <div class="flex items-center justify-between border-b border-slate-700 pb-2">
                        <span class="text-slate-400 text-sm">Total Working Days</span>
                        <span id="submit-confirm-total-days" class="text-white text-sm font-semibold text-right"></span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-sm block mb-2">Reason</span>
                        <p id="submit-confirm-reason" class="text-white text-sm bg-slate-700/50 rounded-lg p-3 break-words"></p>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <button onclick="closeSubmitConfirmModal()" class="flex-1 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">Cancel</button>
                    <button id="submit-confirm-action-btn" class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all">Confirm Submission</button>
                </div>
            </div>
        </div>

        <div id="submit-loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="submit-loading-message" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
                <h3 id="submit-loading-message" class="text-xl font-semibold text-white">Submitting your leave request...</h3>
                <p class="text-slate-400 mt-2">Please wait</p>
            </div>
        </div>

        <!-- Error Modal -->
        <div id="error-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="error-modal-title" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl border border-red-500/50 shadow-2xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 id="error-modal-title" class="text-xl font-semibold text-white mb-2">Error</h3>
                            <p id="error-modal-message" class="text-slate-300 whitespace-pre-line"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-900/50 px-6 py-4 flex justify-end">
                    <button onclick="closeErrorModal()" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all">
                        Close
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <?php $currentPage = 'dashboard'; include __DIR__ . '/../layouts/employee_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-900">
            <!-- Header -->
            <header class="bg-slate-800 border-b border-slate-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Welcome Back!</h2>
                        <p class="text-slate-400 mt-1" id="welcome-message">Good morning, Employee</p>
                    </div>
                    <div id="current-time-header" class="text-right">
                        <div class="text-sm text-slate-400">Current Time</div>
                        <div class="text-2xl font-bold text-white" id="clock">--:--:--</div>
                        <div class="text-sm text-slate-400" id="current-date-header">--</div>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="p-8 space-y-6">
                
                <!-- Quick Actions Card -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-xl border border-slate-700 shadow-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-white">Quick Actions</h3>
                            <p class="text-sm text-slate-400 mt-1">Record your time and manage requests</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-slate-400">Today's Status</div>
                            <div id="today-status-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-slate-700/50 text-slate-400 border-slate-600 mt-1">
                                Not yet timed in
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <button onclick="openRequestModal()" class="flex items-center justify-center px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-semibold">Request Leave</span>
                        </button>
                    </div>
                    <div id="attendance-status" class="mt-4 p-4 bg-slate-700/50 rounded-lg hidden">
                        <p class="text-sm text-slate-300"></p>
                    </div>
                </div>
                
                <!-- Dashboard Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Leave Balance -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-slate-400">Leave Balance</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-white" id="leave-balance">--</p>
                                    <p class="ml-2 text-sm text-slate-400">days</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Rate -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-slate-400">Attendance Rate</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-white" id="attendance-rate">--</p>
                                    <p class="ml-2 text-sm text-slate-400">%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Requests -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-slate-400">Pending Requests</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-white" id="pending-requests">--</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Work Hours This Month -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-lg p-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-medium text-slate-400">Work Hours</p>
                                <div class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-white" id="work-hours">--</p>
                                    <p class="ml-2 text-sm text-slate-400">hrs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity and Leave History -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Activity -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-slate-700">
                            <h3 class="text-xl font-semibold text-white">Recent Activity</h3>
                            <p class="text-slate-400 text-sm mt-1">Your latest attendance and leave activities</p>
                        </div>
                        <div class="p-6">
                            <div id="recent-activity" class="space-y-4">
                                <div class="flex items-center justify-center py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                    <p class="text-slate-400 ml-3">Loading recent activity...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Leave Requests -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-slate-700">
                            <h3 class="text-xl font-semibold text-white">My Leave Requests</h3>
                            <p class="text-slate-400 text-sm mt-1">Recent leave request status</p>
                        </div>
                        <div class="p-6">
                            <div id="leave-requests" class="space-y-4">
                                <div class="flex items-center justify-center py-8">
                                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                    <p class="text-slate-400 ml-3">Loading leave requests...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- This Week's Attendance -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-slate-700">
                        <h3 class="text-xl font-semibold text-white">This Week's Attendance</h3>
                        <p class="text-slate-400 text-sm mt-1">Your attendance record for this week</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Time In</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Time Out</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Work Hours</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody id="weekly-attendance" class="divide-y divide-slate-700">
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
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
        let currentDate = getPhilippinesDate();
        let attendanceAction = null;
        let leaveTypesMap = {}; // Store leave types for display
        let submitConfirmCallback = null;
        const attendanceSyncKey = 'attendance:last-update';

        function setModalVisibility(modalId, isVisible) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.toggle('hidden', !isVisible);
            modal.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            syncModalInteractivityState();
            if (isVisible) {
                modal.focus();
            }
        }

        function syncModalInteractivityState() {
            const modalIds = ['attendance-modal', 'request-leave-modal', 'submit-confirm-modal', 'submit-loading-modal'];
            const hasOpenModal = modalIds.some(id => {
                const modal = document.getElementById(id);
                return modal && !modal.classList.contains('hidden');
            });
            const appShellElements = [document.querySelector('aside'), document.querySelector('main')];
            appShellElements.forEach(element => {
                if (!element) return;
                if (hasOpenModal) {
                    element.setAttribute('inert', '');
                    element.setAttribute('aria-hidden', 'true');
                } else {
                    element.removeAttribute('inert');
                    element.removeAttribute('aria-hidden');
                }
            });
        }

        function getPhilippinesDate() {
            return getPhilippinesDateFromDate(new Date());
        }

        function getPhilippinesDateFromDate(date) {
            const philippinesDateStr = date.toLocaleString('en-US', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
            const parts = philippinesDateStr.match(/(\d+)\/(\d+)\/(\d+)/);
            return `${parts[3]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
        }

        function getPhilippinesDateWithOffset(offsetDays) {
            const base = new Date();
            base.setDate(base.getDate() + offsetDays);
            return getPhilippinesDateFromDate(base);
        }

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
            const parts = philippinesTimeStr.match(/(\d+)\/(\d+)\/(\d+),\s+(\d+):(\d+):(\d+)/);
            return `${parts[3]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')} ${parts[4].padStart(2, '0')}:${parts[5].padStart(2, '0')}:${parts[6].padStart(2, '0')}`;
        }

        // Update clock
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateStr = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            
            document.getElementById('clock').textContent = timeStr;
            document.getElementById('current-date-header').textContent = dateStr;
            document.getElementById('current-time').textContent = timeStr;
            document.getElementById('current-date').textContent = dateStr;
        }
        
        // Start clock
        updateClock();
        setInterval(updateClock, 1000);

        // Initialize page
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                // Get current user
                currentUser = await getCurrentUser();
                
                if (!currentUser) {
                    console.error('No user found');
                    hideLoading();
                    return;
                }
                
                // Update user info
                updateUserInfo(currentUser);
                updateWelcomeMessage(currentUser);
                
                // Load dashboard data
                await loadDashboardData();
                
                window.addEventListener('storage', function(event) {
                    if (event.key === attendanceSyncKey && document.visibilityState === 'visible') {
                        refreshAttendanceState();
                    }
                });

                document.addEventListener('visibilitychange', function() {
                    if (document.visibilityState === 'visible') {
                        refreshAttendanceState();
                    }
                });

                // Hide loading screen
                hideLoading();
            } catch (error) {
                console.error('Initialization error:', error);
                showError('Failed to load dashboard: ' + error.message);
                hideLoading();
            }
        });

        // Update user info in sidebar (handled by employee_sidebar.php component)
        function updateUserInfo(user) {
            // User info is now automatically loaded by the employee_sidebar.php component
            // This function is kept for backward compatibility
        }
        
        // Update welcome message
        function updateWelcomeMessage(user) {
            const hour = new Date().getHours();
            let greeting = 'Good morning';
            if (hour >= 12 && hour < 18) greeting = 'Good afternoon';
            else if (hour >= 18) greeting = 'Good evening';
            
            const firstName = user.first_name || user.name?.split(' ')[0] || 'Employee';
            document.getElementById('welcome-message').textContent = `${greeting}, ${firstName}`;
        }
        
        // Load dashboard data
        async function loadDashboardData() {
            try {
                // Load all data in parallel
                await Promise.all([
                    loadTodayAttendance(),
                    loadLeaveBalance(),
                    loadAttendanceRate(),
                    loadPendingRequests(),
                    loadWorkHours(),
                    loadRecentActivity(),
                    loadLeaveRequests(),
                    loadWeeklyAttendance(),
                    loadLeaveTypesForModal()
                ]);
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        }
        
        // Load today's attendance status
        async function loadTodayAttendance() {
            try {
                currentDate = getPhilippinesDate();
                const response = await fetch(AppConfig.getApiUrl(`/attendance/daily?date=${currentDate}`), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                console.log('Today attendance response:', result);
                
                if (result.success) {
                    // For employees, the API returns { date, employee_id, record: {...} }
                    // For admins, it returns { date, records: [...], summary: {...} }
                    let todayRecord = null;
                    
                    if (result.data.record) {
                        // Employee response
                        todayRecord = result.data.record;
                    } else if (result.data.records) {
                        // Admin response - find current user's record
                        todayRecord = result.data.records.find(r => r.employee_id === currentUser.id);
                    }
                    
                    updateTodayStatus(todayRecord);
                } else {
                    // No record found - not timed in yet
                    updateTodayStatus(null);
                }
            } catch (error) {
                console.error('Error loading today attendance:', error);
                // On error, assume not timed in yet
                updateTodayStatus(null);
            }
        }
        
        // Update today's status badge
        function updateTodayStatus(record) {
            const badge = document.getElementById('today-status-badge');
            const timeInBtn = document.getElementById('time-in-btn');
            const timeOutBtn = document.getElementById('time-out-btn');
            
            if (!record) {
                // Not yet timed in
                badge.textContent = 'Not yet timed in';
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-slate-700/50 text-slate-400 border-slate-600 mt-1';
                setActionButtonState(timeInBtn, true);
                setActionButtonState(timeOutBtn, false);
            } else if (record.time_in && !record.time_out) {
                // Timed in, not yet timed out
                badge.textContent = 'Timed in at ' + formatTime(record.time_in);
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-green-500/20 text-green-400 border-green-500/30 mt-1';
                setActionButtonState(timeInBtn, false);
                setActionButtonState(timeOutBtn, true);
            } else if (record.time_in && record.time_out) {
                // Completed
                badge.textContent = 'Completed - ' + record.work_hours + ' hours';
                badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border bg-blue-500/20 text-blue-400 border-blue-500/30 mt-1';
                setActionButtonState(timeInBtn, false);
                setActionButtonState(timeOutBtn, false);
            }
        }
        
        // Load leave balance
        async function loadLeaveBalance() {
            try {
                const response = await fetch(AppConfig.getApiUrl('/leave/balance'), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.balance) {
                    const totalBalance = result.data.balance.reduce((sum, item) => sum + (item.remaining_credits || 0), 0);
                    document.getElementById('leave-balance').textContent = totalBalance;
                } else {
                    document.getElementById('leave-balance').textContent = '15'; // Default
                }
            } catch (error) {
                console.error('Error loading leave balance:', error);
                document.getElementById('leave-balance').textContent = '15';
            }
        }
        
        // Load attendance rate
        async function loadAttendanceRate() {
            try {
                const startDate = getPhilippinesDateWithOffset(-(new Date().getDate() - 1));
                const endDate = getPhilippinesDate();
                
                const response = await fetch(AppConfig.getApiUrl(`/attendance/history?start_date=${startDate}&end_date=${endDate}`), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.records) {
                    const records = result.data.records;
                    const presentDays = records.filter(r => r.status === 'Present' || r.status === 'Late').length;
                    const totalDays = records.length;
                    const rate = totalDays > 0 ? Math.round((presentDays / totalDays) * 100) : 0;
                    document.getElementById('attendance-rate').textContent = rate;
                } else {
                    document.getElementById('attendance-rate').textContent = '0';
                }
            } catch (error) {
                console.error('Error loading attendance rate:', error);
                document.getElementById('attendance-rate').textContent = '0';
            }
        }
        
        // Load pending requests
        async function loadPendingRequests() {
            try {
                const response = await fetch(AppConfig.getApiUrl('/leave/history'), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.requests) {
                    const pendingCount = result.data.requests.filter(r => r.status === 'Pending').length;
                    document.getElementById('pending-requests').textContent = pendingCount;
                } else {
                    document.getElementById('pending-requests').textContent = '0';
                }
            } catch (error) {
                console.error('Error loading pending requests:', error);
                document.getElementById('pending-requests').textContent = '0';
            }
        }
        
        // Load work hours
        async function loadWorkHours() {
            try {
                const startDate = getPhilippinesDateWithOffset(-(new Date().getDate() - 1));
                const endDate = getPhilippinesDate();
                
                const response = await fetch(AppConfig.getApiUrl(`/attendance/history?start_date=${startDate}&end_date=${endDate}`), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.records) {
                    const totalHours = result.data.records.reduce((sum, record) => {
                        return sum + (parseFloat(record.work_hours) || 0);
                    }, 0);
                    document.getElementById('work-hours').textContent = Math.round(totalHours);
                } else {
                    document.getElementById('work-hours').textContent = '0';
                }
            } catch (error) {
                console.error('Error loading work hours:', error);
                document.getElementById('work-hours').textContent = '0';
            }
        }
        
        // Load recent activity
        async function loadRecentActivity() {
            try {
                const [attendanceResponse, leaveResponse] = await Promise.all([
                    fetch(AppConfig.getApiUrl('/attendance/history?limit=5'), {
                        headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                    }),
                    fetch(AppConfig.getApiUrl('/leave/history?limit=3'), {
                        headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                    })
                ]);
                
                const attendanceResult = await attendanceResponse.json();
                const leaveResult = await leaveResponse.json();
                
                const activities = [];
                
                // Add attendance activities
                if (attendanceResult.success && attendanceResult.data.records) {
                    attendanceResult.data.records.forEach(record => {
                        const hasTimeIn = !!record.time_in;
                        const hasTimeOut = !!record.time_out;
                        const activityTime = record.time_out || record.time_in || record.date;
                        let description = `Attendance: ${record.status}`;
                        if (hasTimeIn && !hasTimeOut) {
                            description = `Timed in (${record.status})`;
                        } else if (hasTimeIn && hasTimeOut) {
                            description = `Timed out (${record.status})`;
                        }
                        activities.push({
                            type: 'attendance',
                            date: activityTime,
                            description,
                            status: record.status,
                            icon: 'clock'
                        });
                    });
                }
                
                // Add leave activities
                if (leaveResult.success && leaveResult.data.requests) {
                    leaveResult.data.requests.forEach(request => {
                        activities.push({
                            type: 'leave',
                            date: request.created_at,
                            description: `Leave request ${request.status.toLowerCase()}`,
                            status: request.status,
                            icon: 'calendar'
                        });
                    });
                }
                
                // Sort by date
                activities.sort((a, b) => new Date(b.date) - new Date(a.date));
                
                displayRecentActivity(activities.slice(0, 5));
            } catch (error) {
                console.error('Error loading recent activity:', error);
                document.getElementById('recent-activity').innerHTML = '<p class="text-sm text-red-400">Failed to load recent activity</p>';
            }
        }
        
        // Display recent activity
        function displayRecentActivity(activities) {
            const container = document.getElementById('recent-activity');
            
            if (activities.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-400">No recent activity</p>';
                return;
            }
            
            container.innerHTML = activities.map(activity => {
                const statusColors = {
                    'Present': 'text-green-400',
                    'Late': 'text-yellow-400',
                    'Absent': 'text-red-400',
                    'Approved': 'text-green-400',
                    'Pending': 'text-yellow-400',
                    'Denied': 'text-red-400'
                };
                
                const iconSvg = activity.icon === 'clock' 
                    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'
                    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />';
                
                return `
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    ${iconSvg}
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-300">${activity.description}</p>
                            <p class="text-xs text-slate-500 mt-1">${formatDate(activity.date)}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="text-xs ${statusColors[activity.status] || 'text-slate-400'}">${activity.status}</span>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        // Load leave requests
        async function loadLeaveRequests() {
            try {
                const response = await fetch(AppConfig.getApiUrl('/leave/history?limit=5'), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                console.log('=== LEAVE HISTORY DEBUG ===');
                console.log('Current user:', currentUser);
                console.log('API response:', result);
                
                if (result.success && result.data.requests) {
                    console.log('Leave requests found:', result.data.requests.length);
                    console.log('Requests:', result.data.requests);
                    displayLeaveRequests(result.data.requests);
                } else {
                    console.log('No leave requests in response');
                    document.getElementById('leave-requests').innerHTML = '<p class="text-sm text-slate-400">No leave requests found</p>';
                }
            } catch (error) {
                console.error('Error loading leave requests:', error);
                document.getElementById('leave-requests').innerHTML = '<p class="text-sm text-red-400">Failed to load leave requests</p>';
            }
        }
        
        // Display leave requests
        function displayLeaveRequests(requests) {
            const container = document.getElementById('leave-requests');
            
            if (requests.length === 0) {
                container.innerHTML = '<p class="text-sm text-slate-400">No leave requests found</p>';
                return;
            }
            
            container.innerHTML = requests.map(request => {
                const statusColors = {
                    'Approved': 'bg-green-500/20 text-green-400 border-green-500/30',
                    'Pending': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                    'Denied': 'bg-red-500/20 text-red-400 border-red-500/30'
                };
                
                return `
                    <div class="flex items-center justify-between p-4 bg-slate-700/30 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-300">${leaveTypesMap[request.leave_type_id] || 'Leave'}</p>
                            <p class="text-xs text-slate-500 mt-1">${formatDate(request.start_date)} - ${formatDate(request.end_date)}</p>
                            <p class="text-xs text-slate-500">${request.total_days} day(s)</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border ${statusColors[request.status] || 'bg-slate-500/20 text-slate-400 border-slate-500/30'}">
                            ${request.status}
                        </span>
                    </div>
                `;
            }).join('');
        }
        
        // Load weekly attendance
        async function loadWeeklyAttendance() {
            try {
                const startDate = getWeekStart();
                const endDate = getWeekEnd();
                
                const response = await fetch(AppConfig.getApiUrl(`/attendance/history?start_date=${startDate}&end_date=${endDate}`), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.records) {
                    displayWeeklyAttendance(result.data.records);
                } else {
                    document.getElementById('weekly-attendance').innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">No attendance records found</td></tr>';
                }
            } catch (error) {
                console.error('Error loading weekly attendance:', error);
                document.getElementById('weekly-attendance').innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-red-400">Failed to load attendance records</td></tr>';
            }
        }
        
        // Display weekly attendance
        function displayWeeklyAttendance(records) {
            const tbody = document.getElementById('weekly-attendance');
            
            if (records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">No attendance records found</td></tr>';
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
                
                return `
                    <tr class="hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-300">${formatDate(record.date)}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${record.time_in ? formatTime(record.time_in) : '-'}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${record.time_out ? formatTime(record.time_out) : '-'}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${record.work_hours || '-'}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border ${statusColors[record.status] || 'bg-slate-500/20 text-slate-400 border-slate-500/30'}">
                                ${record.status}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        // Time In/Out handlers
        document.getElementById('time-in-btn').addEventListener('click', function() {
            attendanceAction = 'timein';
            openAttendanceModal('Time In', 'Are you sure you want to record your time in?', 'from-green-600 to-green-700');
        });
        
        document.getElementById('time-out-btn').addEventListener('click', function() {
            attendanceAction = 'timeout';
            openAttendanceModal('Time Out', 'Are you sure you want to record your time out?', 'from-red-600 to-red-700');
        });
        
        // Open attendance modal
        function openAttendanceModal(title, message, headerColor) {
            document.getElementById('modal-title').textContent = title;
            document.getElementById('modal-message').textContent = message;
            document.getElementById('modal-header').className = `bg-gradient-to-r ${headerColor} px-6 py-4 flex items-center justify-between`;
            setModalVisibility('attendance-modal', true);
        }
        
        // Close attendance modal
        function closeAttendanceModal() {
            setModalVisibility('attendance-modal', false);
            attendanceAction = null;
        }
        
        // Confirm attendance action
        document.getElementById('confirm-attendance-btn').addEventListener('click', async function() {
            if (!attendanceAction) return;
            
            try {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<span class="inline-block animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span>Processing...';
                
                currentDate = getPhilippinesDate();
                const formattedTime = getPhilippinesTime();
                console.log('=== TIME IN/OUT DEBUG ===');
                console.log('Formatted time to send:', formattedTime);
                console.log('Action:', attendanceAction);
                
                const endpoint = attendanceAction === 'timein' ? '/attendance/timein' : '/attendance/timeout';
                const requestBody = {
                    date: currentDate,
                    [attendanceAction === 'timein' ? 'time_in' : 'time_out']: formattedTime
                };
                
                console.log('Request body:', requestBody);
                
                const response = await fetch(AppConfig.getApiUrl(endpoint), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAccessToken()}`
                    },
                    body: JSON.stringify(requestBody)
                });
                
                const result = await response.json();
                console.log('Backend response:', result);
                
                if (result.success) {
                    showSuccess(attendanceAction === 'timein' ? 'Time-in recorded successfully!' : 'Time-out recorded successfully!');
                    closeAttendanceModal();
                    localStorage.setItem(attendanceSyncKey, String(Date.now()));
                    await refreshAttendanceState();
                } else {
                    showError(result.message || `Failed to record ${attendanceAction}`);
                }
            } catch (error) {
                console.error('Attendance error:', error);
                showError(`Failed to record ${attendanceAction}`);
            } finally {
                const btn = document.getElementById('confirm-attendance-btn');
                btn.disabled = false;
                btn.innerHTML = 'Confirm';
            }
        });
        
        // Leave request modal functions
        function openRequestModal() {
            setModalVisibility('request-leave-modal', true);
            document.getElementById('start-date').min = new Date().toISOString().split('T')[0];
            document.getElementById('end-date').min = new Date().toISOString().split('T')[0];
        }
        
        // Load leave types for the modal
        async function loadLeaveTypesForModal() {
            try {
                const response = await fetch(AppConfig.getApiUrl('/leave/types'), {
                    headers: { 'Authorization': `Bearer ${getAccessToken()}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.types) {
                    const select = document.getElementById('leave-type');
                    select.innerHTML = '<option value="">Select leave type</option>';
                    
                    // Clear and populate the leave types map
                    leaveTypesMap = {};
                    
                    result.data.types.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id; // Use the UUID from database
                        option.textContent = type.name;
                        select.appendChild(option);
                        
                        // Store in map for display purposes
                        leaveTypesMap[type.id] = type.name;
                    });
                }
            } catch (error) {
                console.error('Error loading leave types:', error);
            }
        }
        
        function closeRequestModal() {
            setModalVisibility('request-leave-modal', false);
            document.getElementById('leave-request-form').reset();
            document.getElementById('calculated-days').classList.add('hidden');
        }
        
        // Calculate working days
        document.getElementById('start-date').addEventListener('change', calculateDays);
        document.getElementById('end-date').addEventListener('change', calculateDays);
        
        function calculateDays() {
            const startDate = document.getElementById('start-date').value;
            const endDate = document.getElementById('end-date').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end < start) {
                    showError('End date must be after start date');
                    document.getElementById('calculated-days').classList.add('hidden');
                    return;
                }
                
                let workingDays = 0;
                let current = new Date(start);
                
                while (current <= end) {
                    const dayOfWeek = current.getDay();
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                        workingDays++;
                    }
                    current.setDate(current.getDate() + 1);
                }
                
                document.getElementById('total-days').textContent = workingDays;
                document.getElementById('calculated-days').classList.remove('hidden');
            }
        }
        
        // Submit leave request
        async function submitLeaveRequest() {
            try {
                const leaveType = document.getElementById('leave-type').value;
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                const reason = document.getElementById('leave-reason').value;
                
                if (!leaveType || !startDate || !endDate || !reason) {
                    showError('Please fill in all required fields');
                    return;
                }

                const requestDetails = {
                    leaveType,
                    startDate,
                    endDate,
                    reason,
                    leaveTypeName: leaveTypesMap[leaveType] || 'Unknown',
                    totalDays: document.getElementById('total-days').textContent || '0'
                };

                const confirmed = await showSubmitConfirmModal(requestDetails);
                if (!confirmed) {
                    return;
                }

                showSubmitLoadingModal();
                
                const response = await fetch(AppConfig.getApiUrl('/leave/request'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAccessToken()}`
                    },
                    body: JSON.stringify({
                        leave_type_id: leaveType,
                        start_date: startDate,
                        end_date: endDate,
                        reason: reason
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Leave request submitted successfully!');
                    closeRequestModal();
                    await loadPendingRequests();
                    await loadLeaveRequests();
                    await loadLeaveBalance(); // Refresh balance after submission
                } else {
                    // Handle validation errors (422) and other errors
                    let errorMessage = result.message || 'Failed to submit leave request';
                    
                    // If there are validation errors, show them
                    if (result.errors && typeof result.errors === 'object') {
                        const errorMessages = Object.values(result.errors).flat();
                        errorMessage = errorMessages.join('\n');
                    }
                    
                    showError(errorMessage);
                }
            } catch (error) {
                console.error('Submit error:', error);
                showError('Failed to submit leave request. Please try again.');
            } finally {
                hideSubmitLoadingModal();
            }
        }

        function showSubmitConfirmModal(requestDetails) {
            document.getElementById('submit-confirm-leave-type').textContent = requestDetails.leaveTypeName;
            document.getElementById('submit-confirm-start-date').textContent = formatDate(requestDetails.startDate);
            document.getElementById('submit-confirm-end-date').textContent = formatDate(requestDetails.endDate);
            document.getElementById('submit-confirm-total-days').textContent = `${requestDetails.totalDays} day(s)`;
            document.getElementById('submit-confirm-reason').textContent = requestDetails.reason;
            setModalVisibility('submit-confirm-modal', true);
            return new Promise((resolve) => {
                submitConfirmCallback = resolve;
            });
        }

        function closeSubmitConfirmModal() {
            setModalVisibility('submit-confirm-modal', false);
            if (submitConfirmCallback) {
                submitConfirmCallback(false);
                submitConfirmCallback = null;
            }
        }

        document.getElementById('submit-confirm-action-btn')?.addEventListener('click', function() {
            if (submitConfirmCallback) {
                submitConfirmCallback(true);
                submitConfirmCallback = null;
            }
            setModalVisibility('submit-confirm-modal', false);
        });

        function showSubmitLoadingModal() {
            setModalVisibility('submit-loading-modal', true);
        }

        function hideSubmitLoadingModal() {
            setModalVisibility('submit-loading-modal', false);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;
            if (!document.getElementById('submit-confirm-modal')?.classList.contains('hidden')) {
                closeSubmitConfirmModal();
                return;
            }
            if (!document.getElementById('request-leave-modal')?.classList.contains('hidden')) {
                closeRequestModal();
                return;
            }
            if (!document.getElementById('attendance-modal')?.classList.contains('hidden')) {
                closeAttendanceModal();
            }
        });
        
        // Utility functions
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                weekday: 'short',
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
        
        function formatTime(timeStr) {
            if (!timeStr) return '-';
            
            console.log('formatTime input:', timeStr);
            
            // Handle both full timestamp and time-only formats
            let time;
            if (timeStr.includes('T') || timeStr.includes('Z')) {
                // ISO format (e.g., "2026-04-07T10:43:39Z" or "2026-04-07T10:43:39")
                // This is UTC time from Supabase, will be converted to local time automatically
                time = new Date(timeStr);
            } else if (timeStr.includes(' ') && timeStr.length > 10) {
                // SQL timestamp format (e.g., "2026-04-07 10:43:39")
                // Assume this is UTC from Supabase
                const [datePart, timePart] = timeStr.split(' ');
                time = new Date(`${datePart}T${timePart}Z`); // Add Z to indicate UTC
            } else if (timeStr.includes(':')) {
                // Time-only format (e.g., "18:50:58")
                // Assume local time
                const today = new Date().toISOString().split('T')[0];
                time = new Date(`${today}T${timeStr}`);
            } else {
                console.error('Unknown time format:', timeStr);
                return '-';
            }
            
            // Check if date is valid
            if (isNaN(time.getTime())) {
                console.error('Invalid time format:', timeStr);
                return '-';
            }
            
            // Convert to local time (Philippines)
            const formatted = time.toLocaleTimeString('en-PH', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila'
            });
            
            console.log('formatTime output:', formatted, '(converted from UTC to Philippines time)');
            
            return formatted;
        }
        
        function setActionButtonState(button, isEnabled) {
            button.disabled = !isEnabled;
            if (isEnabled) {
                button.classList.remove('opacity-50', 'cursor-not-allowed', 'grayscale', 'from-slate-700', 'to-slate-800', 'hover:from-slate-700', 'hover:to-slate-800', 'text-slate-400');
                button.classList.add('text-white');
                if (button.id === 'time-in-btn') {
                    button.classList.add('from-green-600', 'to-green-700', 'hover:from-green-700', 'hover:to-green-800');
                    button.classList.remove('from-red-600', 'to-red-700', 'hover:from-red-700', 'hover:to-red-800');
                } else {
                    button.classList.add('from-red-600', 'to-red-700', 'hover:from-red-700', 'hover:to-red-800');
                    button.classList.remove('from-green-600', 'to-green-700', 'hover:from-green-700', 'hover:to-green-800');
                }
            } else {
                button.classList.add('opacity-50', 'cursor-not-allowed', 'grayscale', 'from-slate-700', 'to-slate-800', 'hover:from-slate-700', 'hover:to-slate-800', 'text-slate-400');
                button.classList.remove('from-green-600', 'to-green-700', 'hover:from-green-700', 'hover:to-green-800', 'from-red-600', 'to-red-700', 'hover:from-red-700', 'hover:to-red-800', 'text-white');
            }
        }

        async function refreshAttendanceState() {
            await Promise.all([
                loadTodayAttendance(),
                loadWeeklyAttendance(),
                loadRecentActivity(),
                loadAttendanceRate(),
                loadWorkHours()
            ]);
        }

        function getWeekStart() {
            const now = new Date();
            const day = now.getDay() || 7;
            now.setDate(now.getDate() - day + 1);
            return getPhilippinesDateFromDate(now);
        }
        
        function getWeekEnd() {
            const now = new Date();
            const day = now.getDay() || 7;
            now.setDate(now.getDate() + (7 - day));
            return getPhilippinesDateFromDate(now);
        }
        
        function hideLoading() {
            // Check if page-loading exists before trying to hide it
            const pageLoading = document.getElementById('page-loading');
            if (pageLoading) {
                setTimeout(() => {
                    pageLoading.style.opacity = '0';
                    pageLoading.style.transition = 'opacity 0.3s ease-out';
                    setTimeout(() => pageLoading.style.display = 'none', 300);
                }, 500);
            }
        }
        
        function showSuccess(message) {
            showToast(message, 'success');
        }
        
        function showError(message) {
            document.getElementById('error-modal-message').textContent = message;
            document.getElementById('error-modal').classList.remove('hidden');
        }
        
        function closeErrorModal() {
            document.getElementById('error-modal').classList.add('hidden');
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
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);
            
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        async function getCurrentUser() {
            try {
                let userStr = localStorage.getItem('user') || localStorage.getItem('hris_user');
                if (!userStr) {
                    window.location.href = AppConfig.getBaseUrl('/login');
                    return null;
                }
                return JSON.parse(userStr);
            } catch (error) {
                console.error('Error getting current user:', error);
                window.location.href = AppConfig.getBaseUrl('/login');
                return null;
            }
        }
        
        function getAccessToken() {
            const token = localStorage.getItem('hris_token') || localStorage.getItem('access_token');
            if (!token) {
                console.error('No access token found');
                window.location.href = AppConfig.getBaseUrl('/login');
                return null;
            }
            return token;
        }
    </script>
</body>
</html>
