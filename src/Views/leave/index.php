<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <!-- Loading Screen -->
    

    <!-- Main Container -->
    <div class="flex h-full bg-slate-900">
        
        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
        
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

        <div id="submit-confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[65] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="submit-confirm-title" aria-describedby="submit-confirm-description" aria-hidden="true" tabindex="-1">
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
                    <button onclick="closeSubmitConfirmModal()" class="flex-1 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                        Cancel
                    </button>
                    <button id="submit-confirm-action-btn" class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all">
                        Confirm Submission
                    </button>
                </div>
            </div>
        </div>

        <!-- Review Leave Modal (Admin) -->
        <div id="review-leave-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="review-modal-title" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 flex items-center justify-between">
                    <h3 id="review-modal-title" class="text-xl font-bold text-white">Review Leave Request</h3>
                    <button onclick="closeReviewModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="review-leave-content" class="p-6">
                    <!-- Content will be inserted here -->
                </div>
                <div class="bg-slate-700 px-6 py-4 flex justify-end space-x-3">
                    <button onclick="closeReviewModal()" class="px-6 py-2 bg-slate-600 hover:bg-slate-500 text-white rounded-lg transition-all">
                        Cancel
                    </button>
                    <button onclick="denyLeaveRequest()" class="px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg transition-all">
                        Deny
                    </button>
                    <button onclick="approveLeaveRequest()" class="px-6 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg transition-all">
                        Approve
                    </button>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message" aria-hidden="true" tabindex="-1">
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
        <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="loading-message" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
                <h3 id="loading-message" class="text-xl font-semibold text-white">Processing...</h3>
                <p class="text-slate-400 mt-2">Please wait</p>
            </div>
        </div>

        <div id="submit-loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[75] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="submit-loading-message" aria-hidden="true" tabindex="-1">
            <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl p-8 text-center">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
                <h3 id="submit-loading-message" class="text-xl font-semibold text-white">Submitting your leave request...</h3>
                <p class="text-slate-400 mt-2">Please wait</p>
            </div>
        </div>
        
        <!-- Sidebar -->
        <?php $currentPage = 'leave'; include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-900">
            <header class="bg-slate-800 border-b border-slate-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Leave Requests</h2>
                        <p class="text-slate-400 mt-1">Manage employee leave requests and balances</p>
                    </div>
                    <button onclick="openRequestModal()" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-900/50">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Request Leave
                    </button>
                </div>
            </header>
            
            <div class="p-8 space-y-6">
                <!-- Search and Filter (Admin Only) -->
                <div id="admin-search-section" class="hidden">
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <input type="text" id="admin-search-input" placeholder="Search by employee name..." class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-blue-500">
                            </div>
                            <div>
                                <select id="status-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                    <option value="">All Status</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Denied">Denied</option>
                                </select>
                            </div>
                            <div>
                                <select id="leave-type-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                    <option value="">All Leave Types</option>
                                    <!-- Will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests (Admin Only) -->
                <div id="pending-requests-section" class="hidden">
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-slate-700">
                            <h3 class="text-xl font-semibold text-white">Pending Requests</h3>
                            <p class="text-slate-400 text-sm mt-1">Review and approve leave requests</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-700/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Employee</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Leave Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Start Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">End Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Days</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pending-requests-body" class="divide-y divide-slate-700">
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                            <p class="text-slate-400 mt-2">Loading pending requests...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Approved Requests (Admin Only) -->
                <div id="approved-requests-section" class="hidden">
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-slate-700">
                            <h3 class="text-xl font-semibold text-white">Approved Requests</h3>
                            <p class="text-slate-400 text-sm mt-1">History of approved leave requests</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-700/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Employee</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Leave Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Start Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">End Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Days</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Approved Date</th>
                                    </tr>
                                </thead>
                                <tbody id="approved-requests-body" class="divide-y divide-slate-700">
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                            <p class="text-slate-400 mt-2">Loading approved requests...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Denied Requests (Admin Only) -->
                <div id="denied-requests-section" class="hidden">
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                        <div class="p-6 border-b border-slate-700">
                            <h3 class="text-xl font-semibold text-white">Denied Requests</h3>
                            <p class="text-slate-400 text-sm mt-1">History of denied leave requests</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-700/50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Employee</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Leave Type</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Start Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">End Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Days</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Denied Date</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Reason</th>
                                    </tr>
                                </thead>
                                <tbody id="denied-requests-body" class="divide-y divide-slate-700">
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                            <p class="text-slate-400 mt-2">Loading denied requests...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- My Leave History (Employee Only) -->
                <div id="my-leave-history-section" class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden hidden">
                    <div class="p-6 border-b border-slate-700">
                        <h3 class="text-xl font-semibold text-white">My Leave History</h3>
                        <p class="text-slate-400 text-sm mt-1">View your leave request history</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Leave Type</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Start Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">End Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Days</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-300 uppercase">Submitted</th>
                                </tr>
                            </thead>
                            <tbody id="leave-history-body" class="divide-y divide-slate-700">
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                                        <p class="text-slate-400 mt-2">Loading leave history...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/token-manager.js') ?>"></script>
    <script src="<?= base_url('/assets/js/loading-skeletons.js') ?>"></script>
    <script src="<?= base_url('/assets/js/utils.js') ?>"></script>
    <script>
        let currentUser = null;
        let currentLeaveRequest = null;
        let pendingRequestMap = {};
        let submitConfirmCallback = null;
        let leaveTypesMap = {}; // Dynamic mapping of UUID to name

        document.addEventListener('DOMContentLoaded', async function() {
            try {
                currentUser = await getCurrentUser();
                if (!currentUser) {
                    hideLoading();
                    return;
                }
                
                updateUserInfo(currentUser);
                
                // Update navigation based on role
                updateNavigationForRole(currentUser.role);
                
                // Load leave types for the dropdown
                await loadLeaveTypes();
                
                if (currentUser.role === 'admin') {
                    // Show admin sections
                    document.getElementById('admin-search-section').classList.remove('hidden');
                    document.getElementById('pending-requests-section').classList.remove('hidden');
                    document.getElementById('approved-requests-section').classList.remove('hidden');
                    document.getElementById('denied-requests-section').classList.remove('hidden');
                    
                    // Load all admin data
                    loadPendingRequests();
                    loadApprovedRequests();
                    loadDeniedRequests();
                    
                    // Populate leave type filter
                    populateLeaveTypeFilter();
                    
                    // Setup search and filter listeners
                    setupAdminFilters();
                } else {
                    // Show My Leave History section for employees only
                    document.getElementById('my-leave-history-section').classList.remove('hidden');
                }
                
                // Load leave history (for employees, or admin's own history if they have one)
                if (currentUser.role === 'employee') {
                    loadLeaveHistory();
                }
                hideLoading();
            } catch (error) {
                console.error('Initialization error:', error);
                showError('Failed to load leave requests page');
                hideLoading();
            }
        });

        function updateUserInfo(user) {
            // This function is no longer needed with the new standard sidebar
            // The standard sidebar doesn't have user info display
            // Keeping function for backward compatibility but making it safe
            const avatarEl = document.querySelector('aside .w-10.h-10');
            const nameEl = document.querySelector('aside .text-sm.font-medium');
            const emailEl = document.querySelector('aside .text-xs.text-slate-400');
            
            if (avatarEl && user.name) {
                const initial = user.name.charAt(0).toUpperCase();
                avatarEl.textContent = initial;
            }
            if (nameEl) {
                nameEl.textContent = user.name || 'User';
            }
            if (emailEl) {
                emailEl.textContent = user.email || '';
            }
        }
        
        // Update navigation based on role
        function updateNavigationForRole(role) {
            // This function is no longer needed with the new standard sidebar
            // The standard sidebar is static and doesn't need dynamic role-based updates
            // Keeping function for backward compatibility but making it safe
            const dashboardLink = document.getElementById('dashboard-link');
            const employeesLink = document.getElementById('employees-link');
            const reportsLink = document.getElementById('reports-link');
            const profileLink = document.getElementById('profile-link');
            const attendanceText = document.getElementById('attendance-text');
            
            if (!dashboardLink) return; // Elements don't exist in new sidebar, skip
            
            if (role === 'admin') {
                // Admin navigation
                dashboardLink.href = '<?= base_url('/dashboard/admin') ?>';
                if (employeesLink) employeesLink.classList.remove('hidden');
                if (reportsLink) reportsLink.classList.remove('hidden');
                if (profileLink) profileLink.classList.add('hidden');
                if (attendanceText) attendanceText.textContent = 'Attendance';
            } else {
                // Employee navigation
                dashboardLink.href = '<?= base_url('/dashboard/employee') ?>';
                if (employeesLink) employeesLink.classList.add('hidden');
                if (reportsLink) reportsLink.classList.add('hidden');
                if (profileLink) profileLink.classList.remove('hidden');
                if (attendanceText) attendanceText.textContent = 'My Attendance';
            }
        }

        function openRequestModal() {
            setModalVisibility('request-leave-modal', true);
            document.getElementById('start-date').min = new Date().toISOString().split('T')[0];
            document.getElementById('end-date').min = new Date().toISOString().split('T')[0];
        }

        function closeRequestModal() {
            setModalVisibility('request-leave-modal', false);
            document.getElementById('leave-request-form').reset();
            document.getElementById('calculated-days').classList.add('hidden');
        }

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
            const modalIds = [
                'request-leave-modal',
                'submit-confirm-modal',
                'submit-loading-modal',
                'review-leave-modal',
                'confirm-modal',
                'loading-modal'
            ];
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

        document.getElementById('start-date')?.addEventListener('change', calculateDays);
        document.getElementById('end-date')?.addEventListener('change', calculateDays);

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
                const leaveTypeName = leaveTypesMap[leaveType] || 'Unknown';
                const totalDays = document.getElementById('total-days').textContent;
                const requestDetails = {
                    leaveType,
                    startDate,
                    endDate,
                    reason,
                    leaveTypeName,
                    totalDays
                };
                const confirmed = await showSubmitConfirmModal(requestDetails);
                if (!confirmed) {
                    return;
                }

                closeRequestModal();
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
                    loadLeaveHistory();
                    if (currentUser.role === 'admin') {
                        loadPendingRequests();
                    }
                } else {
                    openRequestModal();
                    restoreLeaveRequestForm(requestDetails);
                    showError(result.message || 'Failed to submit leave request');
                }
            } catch (error) {
                openRequestModal();
                console.error('Submit error:', error);
                showError('Failed to submit leave request');
            } finally {
                hideSubmitLoadingModal();
            }
        }

        function restoreLeaveRequestForm(requestDetails) {
            document.getElementById('leave-type').value = requestDetails.leaveType;
            document.getElementById('start-date').value = requestDetails.startDate;
            document.getElementById('end-date').value = requestDetails.endDate;
            document.getElementById('leave-reason').value = requestDetails.reason;
            calculateDays();
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
            if (!document.getElementById('confirm-modal')?.classList.contains('hidden')) {
                closeConfirmModal();
                return;
            }
            if (!document.getElementById('request-leave-modal')?.classList.contains('hidden')) {
                closeRequestModal();
                return;
            }
            if (!document.getElementById('review-leave-modal')?.classList.contains('hidden')) {
                closeReviewModal();
                return;
            }
        });

        async function loadLeaveTypes() {
            try {
                const token = getAccessToken();
                if (!token) {
                    console.error('No access token available');
                    return;
                }
                
                const response = await fetch(AppConfig.getApiUrl('/leave/types'), {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const result = await response.json();
                
                if (result.success && result.data.types) {
                    const leaveTypeSelect = document.getElementById('leave-type');
                    
                    // Clear existing options except the first one
                    while (leaveTypeSelect.children.length > 1) {
                        leaveTypeSelect.removeChild(leaveTypeSelect.lastChild);
                    }
                    
                    // Clear and populate the leave types map
                    leaveTypesMap = {};
                    
                    // Add leave type options and build map
                    result.data.types.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        leaveTypeSelect.appendChild(option);
                        
                        // Store in map for display
                        leaveTypesMap[type.id] = type.name;
                    });
                    
                    console.log('Leave types loaded:', leaveTypesMap);
                } else {
                    console.error('Failed to load leave types:', result.message);
                    showError('Failed to load leave types');
                }
            } catch (error) {
                console.error('Load leave types error:', error);
                showError('Failed to load leave types');
            }
        }

        async function loadPendingRequests() {
            try {
                const token = getAccessToken();
                if (!token) {
                    console.error('No access token available');
                    return;
                }
                
                const response = await fetch(AppConfig.getApiUrl('/leave/pending'), {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayPendingRequests(result.data.pending_requests);
                } else {
                    console.error('Failed to load pending requests:', result.message);
                    showError(result.message || 'Failed to load pending requests');
                }
            } catch (error) {
                console.error('Load pending error:', error);
                showError('Failed to load pending requests');
            }
        }

        function displayPendingRequests(requests) {
            const tbody = document.getElementById('pending-requests-body');
            pendingRequestMap = {};
            
            if (!requests || requests.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            No pending leave requests
                        </td>
                    </tr>
                `;
                return;
            }
            requests.forEach(request => {
                pendingRequestMap[request.id] = request;
            });
            
            tbody.innerHTML = requests.map(request => `
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm text-white font-medium">${request.employee_name}</div>
                        <div class="text-xs text-slate-400">${request.department || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-300">${leaveTypesMap[request.leave_type_id] || 'Unknown'}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.start_date)}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.end_date)}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${request.total_days} day(s)</td>
                    <td class="px-6 py-4">
                        <button onclick="openReviewModalById('${request.id}')" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition-all">
                            Review
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function openReviewModalById(requestId) {
            const request = pendingRequestMap[requestId];
            if (!request) {
                showError('Leave request details not found. Please refresh and try again.');
                return;
            }
            openReviewModal(request);
        }

        // Load approved requests (admin)
        async function loadApprovedRequests() {
            try {
                const token = getAccessToken();
                if (!token) return;
                
                const response = await fetch(AppConfig.getApiUrl('/leave/approved'), {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayApprovedRequests(result.data.approved_requests || []);
                } else {
                    console.error('Failed to load approved requests:', result.message);
                }
            } catch (error) {
                console.error('Load approved error:', error);
                document.getElementById('approved-requests-body').innerHTML = `
                    <tr><td colspan="6" class="px-6 py-8 text-center text-red-400">Failed to load approved requests</td></tr>
                `;
            }
        }

        function displayApprovedRequests(requests) {
            const tbody = document.getElementById('approved-requests-body');
            
            if (!requests || requests.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No approved leave requests</td></tr>
                `;
                return;
            }
            
            tbody.innerHTML = requests.map(request => `
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm text-white font-medium">${request.employee_name}</div>
                        <div class="text-xs text-slate-400">${request.department || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-300">${leaveTypesMap[request.leave_type_id] || 'Unknown'}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.start_date)}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.end_date)}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${request.total_days} day(s)</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.reviewed_at || request.updated_at)}</td>
                </tr>
            `).join('');
        }

        // Load denied requests (admin)
        async function loadDeniedRequests() {
            try {
                const token = getAccessToken();
                if (!token) return;
                
                const response = await fetch(AppConfig.getApiUrl('/leave/denied'), {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayDeniedRequests(result.data.denied_requests || []);
                } else {
                    console.error('Failed to load denied requests:', result.message);
                }
            } catch (error) {
                console.error('Load denied error:', error);
                document.getElementById('denied-requests-body').innerHTML = `
                    <tr><td colspan="7" class="px-6 py-8 text-center text-red-400">Failed to load denied requests</td></tr>
                `;
            }
        }

        function displayDeniedRequests(requests) {
            const tbody = document.getElementById('denied-requests-body');
            
            if (!requests || requests.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No denied leave requests</td></tr>
                `;
                return;
            }
            
            tbody.innerHTML = requests.map(request => `
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm text-white font-medium">${request.employee_name}</div>
                        <div class="text-xs text-slate-400">${request.department || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-300">${leaveTypesMap[request.leave_type_id] || 'Unknown'}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.start_date)}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.end_date)}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${request.total_days} day(s)</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.reviewed_at || request.updated_at)}</td>
                    <td class="px-6 py-4 text-sm text-slate-300">${request.denial_reason || 'No reason provided'}</td>
                </tr>
            `).join('');
        }

        // Populate leave type filter
        function populateLeaveTypeFilter() {
            const select = document.getElementById('leave-type-filter');
            Object.entries(leaveTypesMap).forEach(([id, name]) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = name;
                select.appendChild(option);
            });
        }

        // Setup admin search and filters
        let allRequests = { pending: [], approved: [], denied: [] };
        
        function setupAdminFilters() {
            const searchInput = document.getElementById('admin-search-input');
            const statusFilter = document.getElementById('status-filter');
            const leaveTypeFilter = document.getElementById('leave-type-filter');
            
            searchInput.addEventListener('input', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            leaveTypeFilter.addEventListener('change', applyFilters);
        }

        async function applyFilters() {
            const searchTerm = document.getElementById('admin-search-input').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;
            const leaveTypeFilter = document.getElementById('leave-type-filter').value;
            
            // Reload data with filters
            try {
                const token = getAccessToken();
                if (!token) return;
                
                let url = '/leave/all?';
                if (searchTerm) url += `search=${encodeURIComponent(searchTerm)}&`;
                if (statusFilter) url += `status=${statusFilter}&`;
                if (leaveTypeFilter) url += `leave_type=${leaveTypeFilter}&`;
                
                const response = await fetch(AppConfig.getApiUrl(url), {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Update all sections with filtered data
                    displayPendingRequests(result.data.pending || []);
                    displayApprovedRequests(result.data.approved || []);
                    displayDeniedRequests(result.data.denied || []);
                }
            } catch (error) {
                console.error('Filter error:', error);
            }
        }

        async function loadLeaveHistory() {
            try {
                const token = getAccessToken();
                if (!token) {
                    console.error('No access token available');
                    return;
                }
                
                const response = await fetch(AppConfig.getApiUrl('/leave/history'), {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    displayLeaveHistory(result.data.requests);
                } else {
                    console.error('Failed to load leave history:', result.message);
                    showError(result.message || 'Failed to load leave history');
                }
            } catch (error) {
                console.error('Load history error:', error);
                showError('Failed to load leave history');
            }
        }

        function displayLeaveHistory(requests) {
            const tbody = document.getElementById('leave-history-body');
            
            if (!requests || requests.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            No leave requests found
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = requests.map(request => {
                const statusColors = {
                    'Pending': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                    'Approved': 'bg-green-500/20 text-green-400 border-green-500/30',
                    'Denied': 'bg-red-500/20 text-red-400 border-red-500/30',
                    'Cancelled': 'bg-slate-500/20 text-slate-400 border-slate-500/30'
                };
                const statusClass = statusColors[request.status] || 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                
                return `
                    <tr class="hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-300">${leaveTypesMap[request.leave_type_id] || 'Unknown'}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.start_date)}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.end_date)}</td>
                        <td class="px-6 py-4 text-sm text-slate-300">${request.total_days} day(s)</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border ${statusClass}">
                                ${request.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-300">${formatDate(request.created_at)}</td>
                    </tr>
                `;
            }).join('');
        }

        function openReviewModal(request) {
            currentLeaveRequest = request;
            const content = document.getElementById('review-leave-content');
            
            content.innerHTML = `
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Employee</label>
                            <p class="text-white font-medium">${request.employee_name}</p>
                            <p class="text-sm text-slate-400">${request.department || 'N/A'} - ${request.position || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Leave Type</label>
                            <p class="text-white">${leaveTypesMap[request.leave_type_id] || 'Unknown'}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Start Date</label>
                            <p class="text-white">${formatDate(request.start_date)}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">End Date</label>
                            <p class="text-white">${formatDate(request.end_date)}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Total Days</label>
                            <p class="text-white font-semibold">${request.total_days} day(s)</p>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Reason</label>
                        <div class="p-4 bg-slate-700/50 rounded-lg">
                            <p class="text-white">${request.reason || 'No reason provided'}</p>
                        </div>
                    </div>
                    <div id="denial-reason-section" class="hidden">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Denial Reason (Optional)</label>
                        <textarea id="denial-reason" rows="3" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:ring-2 focus:ring-red-500" placeholder="Provide a reason for denying this request..."></textarea>
                    </div>
                </div>
            `;
            
            setModalVisibility('review-leave-modal', true);
        }

        function closeReviewModal(clearRequest = true) {
            setModalVisibility('review-leave-modal', false);
            if (clearRequest) {
                currentLeaveRequest = null;
            }
        }

        let confirmCallback = null;

        function showConfirm(title, message, type = 'warning') {
            const iconContainer = document.getElementById('confirm-icon');
            const confirmBtn = document.getElementById('confirm-action-btn');
            
            // Set icon and colors based on type
            if (type === 'approve') {
                iconContainer.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-500/20 mb-4';
                iconContainer.innerHTML = `
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                `;
                confirmBtn.className = 'flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg transition-all';
            } else if (type === 'deny') {
                iconContainer.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-500/20 mb-4';
                iconContainer.innerHTML = `
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
                confirmBtn.className = 'flex-1 px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg transition-all';
            } else {
                iconContainer.className = 'mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-500/20 mb-4';
                iconContainer.innerHTML = `
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                `;
                confirmBtn.className = 'flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all';
            }
            
            document.getElementById('confirm-title').textContent = title;
            document.getElementById('confirm-message').textContent = message;
            setModalVisibility('confirm-modal', true);
            
            return new Promise((resolve) => {
                confirmCallback = resolve;
            });
        }

        function closeConfirmModal() {
            setModalVisibility('confirm-modal', false);
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
            setModalVisibility('loading-modal', true);
        }

        function hideLoadingModal() {
            setModalVisibility('loading-modal', false);
        }

        async function approveLeaveRequest() {
            if (!currentLeaveRequest) {
                showError('Leave request details not found. Please reopen the request.');
                return;
            }
            const selectedRequest = currentLeaveRequest;
            
            closeReviewModal(false);
            
            // Show confirmation modal
            const confirmed = await showConfirm(
                'Approve Leave Request',
                `Are you sure you want to approve this leave request for ${selectedRequest.employee_name}?`,
                'approve'
            );
            
            if (!confirmed) {
                openReviewModal(selectedRequest);
                return;
            }
            
            // Show loading modal
            showLoadingModal('Approving leave request...');
            
            try {
                const response = await fetch(AppConfig.getApiUrl(`/leave/${selectedRequest.id}/approve`), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAccessToken()}`
                    }
                });
                
                const result = await response.json();
                
                hideLoadingModal();
                
                if (result.success) {
                    showSuccess('Leave request approved successfully!');
                    currentLeaveRequest = null;
                    loadPendingRequests();
                    loadApprovedRequests();
                } else {
                    showError(result.message || 'Failed to approve leave request');
                }
            } catch (error) {
                hideLoadingModal();
                console.error('Approve error:', error);
                showError('Failed to approve leave request');
            }
        }

        async function denyLeaveRequest() {
            if (!currentLeaveRequest) {
                showError('Leave request details not found. Please reopen the request.');
                return;
            }
            const selectedRequest = currentLeaveRequest;
            
            // Show denial reason section if not visible
            const denialSection = document.getElementById('denial-reason-section');
            if (denialSection.classList.contains('hidden')) {
                denialSection.classList.remove('hidden');
                return;
            }
            
            const denialReason = document.getElementById('denial-reason').value;
            
            closeReviewModal(false);
            
            // Show confirmation modal
            const confirmed = await showConfirm(
                'Deny Leave Request',
                `Are you sure you want to deny this leave request for ${selectedRequest.employee_name}?`,
                'deny'
            );
            
            if (!confirmed) {
                openReviewModal(selectedRequest);
                return;
            }
            
            // Show loading modal
            showLoadingModal('Denying leave request...');
            
            try {
                const response = await fetch(AppConfig.getApiUrl(`/leave/${selectedRequest.id}/deny`), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${getAccessToken()}`
                    },
                    body: JSON.stringify({
                        denial_reason: denialReason
                    })
                });
                
                const result = await response.json();
                
                hideLoadingModal();
                
                if (result.success) {
                    showSuccess('Leave request denied');
                    currentLeaveRequest = null;
                    loadPendingRequests();
                    loadDeniedRequests();
                } else {
                    showError(result.message || 'Failed to deny leave request');
                }
            } catch (error) {
                hideLoadingModal();
                console.error('Deny error:', error);
                showError('Failed to deny leave request');
            }
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function hideLoading() {
            // No longer needed - page loads instantly
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
            // Check hris_token first (primary), then access_token (fallback)
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

