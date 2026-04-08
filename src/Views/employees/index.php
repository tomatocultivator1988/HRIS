<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <!-- Loading Screen -->
    <div id="page-loading" class="fixed inset-0 bg-slate-900 z-50 flex items-center justify-center">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
            <h2 class="text-2xl font-semibold text-white">Loading Employees...</h2>
            <p class="text-slate-400 mt-2">Please wait</p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="flex h-full bg-slate-900">
        
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
                <a href="<?= base_url('/dashboard/admin') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
                
                <a href="<?= base_url('/employees') ?>" class="flex items-center px-4 py-3 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg shadow-blue-900/50">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Employees
                </a>
                
                <a href="<?= base_url('/attendance') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Attendance
                </a>
                
                <a href="<?= base_url('/leave') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Leave Requests
                </a>
                
                <a href="<?= base_url('/reports') ?>" class="flex items-center px-4 py-3 text-slate-300 hover:text-white hover:bg-slate-700 rounded-lg transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Reports
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
                        <h2 class="text-3xl font-bold text-white">Employees</h2>
                        <p class="text-slate-400 mt-1">Manage your workforce</p>
                    </div>
                    <button id="add-employee-btn" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-900/50">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Employee
                    </button>
                </div>
            </header>
            
            <!-- Content -->
            <div class="p-8">
                <!-- Search and Filter -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <input type="text" id="search-input" placeholder="Search employees..." class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white placeholder-slate-400 focus:outline-none focus:border-blue-500">
                        </div>
                        <div>
                            <select id="department-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                <option value="">All Departments</option>
                            </select>
                        </div>
                        <div>
                            <select id="status-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                <option value="">All Status</option>
                                <option value="Regular">Regular</option>
                                <option value="Probationary">Probationary</option>
                                <option value="Contractual">Contractual</option>
                                <option value="Part-time">Part-time</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Employee Table -->
                <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Employee ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Position</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employee-table-body" class="divide-y divide-slate-700">
                                <!-- Employee rows will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Loading State -->
                    <div id="table-loading" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                        <p class="text-slate-400 mt-2">Loading employees...</p>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="table-empty" class="text-center py-12 hidden">
                        <svg class="w-16 h-16 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-white mb-2">No Employees Found</h3>
                        <p class="text-slate-400">Try adjusting your search or filters</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-4 border-blue-500 mb-4"></div>
            <h3 class="text-xl font-semibold text-white mb-2" id="loading-message">Processing...</h3>
            <p class="text-slate-400">Please wait</p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2" id="success-title">Success!</h3>
                <p class="text-slate-300" id="success-message"></p>
            </div>
            <div id="success-details" class="hidden bg-slate-700 rounded-lg p-4 mb-4">
                <!-- Additional details will be shown here -->
            </div>
            <button onclick="closeSuccessModal()" class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                Close
            </button>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2" id="confirm-title">Confirm Action</h3>
                <p class="text-slate-300" id="confirm-message"></p>
            </div>
            <div class="flex space-x-3">
                <button onclick="closeConfirmModal()" class="flex-1 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                    Cancel
                </button>
                <button id="confirm-action-btn" class="flex-1 px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div id="add-employee-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white">Add New Employee</h3>
                    <button onclick="closeAddModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="add-employee-form" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">First Name *</label>
                        <input type="text" name="first_name" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Last Name *</label>
                        <input type="text" name="last_name" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Email *</label>
                        <input type="email" name="email" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Phone</label>
                        <input type="tel" name="phone" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Department *</label>
                        <input type="text" name="department" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Position *</label>
                        <input type="text" name="position" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Employment Status *</label>
                        <select name="employment_status" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                            <option value="">Select Status</option>
                            <option value="Regular">Regular</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Date Hired *</label>
                        <input type="date" name="date_hired" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                        Add Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div id="view-employee-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white">Employee Details</h3>
                    <button onclick="closeViewModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="view-employee-content" class="p-6">
                <!-- Employee details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="edit-employee-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white">Edit Employee</h3>
                    <button onclick="closeEditModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="edit-employee-form" class="p-6 space-y-4">
                <input type="hidden" name="employee_id" id="edit-employee-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">First Name *</label>
                        <input type="text" name="first_name" id="edit-first-name" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Last Name *</label>
                        <input type="text" name="last_name" id="edit-last-name" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Email *</label>
                        <input type="email" name="email" id="edit-email" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Phone</label>
                        <input type="tel" name="phone" id="edit-phone" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Department *</label>
                        <input type="text" name="department" id="edit-department" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Position *</label>
                        <input type="text" name="position" id="edit-position" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Employment Status *</label>
                        <select name="employment_status" id="edit-employment-status" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                            <option value="">Select Status</option>
                            <option value="Regular">Regular</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Date Hired *</label>
                        <input type="date" name="date_hired" id="edit-date-hired" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all">
                        Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/loading-skeletons.js') ?>"></script>
    <script>
        let employees = [];
        let filteredEmployees = [];
        let confirmCallback = null;

        // Modal functions
        function showLoading(message = 'Processing...') {
            document.getElementById('loading-message').textContent = message;
            document.getElementById('loading-modal').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading-modal').classList.add('hidden');
        }

        function showSuccess(title, message, details = null) {
            document.getElementById('success-title').textContent = title;
            document.getElementById('success-message').textContent = message;
            
            const detailsDiv = document.getElementById('success-details');
            if (details) {
                detailsDiv.innerHTML = details;
                detailsDiv.classList.remove('hidden');
            } else {
                detailsDiv.classList.add('hidden');
            }
            
            document.getElementById('success-modal').classList.remove('hidden');
        }

        function closeSuccessModal() {
            document.getElementById('success-modal').classList.add('hidden');
        }

        function showConfirm(title, message, callback) {
            document.getElementById('confirm-title').textContent = title;
            document.getElementById('confirm-message').textContent = message;
            confirmCallback = callback;
            document.getElementById('confirm-modal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirm-modal').classList.add('hidden');
            confirmCallback = null;
        }

        document.getElementById('confirm-action-btn').addEventListener('click', function() {
            if (confirmCallback) {
                confirmCallback();
            }
            closeConfirmModal();
        });

        function closeAddModal() {
            document.getElementById('add-employee-modal').classList.add('hidden');
            document.getElementById('add-employee-form').reset();
        }

        function closeViewModal() {
            document.getElementById('view-employee-modal').classList.add('hidden');
        }

        function closeEditModal() {
            document.getElementById('edit-employee-modal').classList.add('hidden');
            document.getElementById('edit-employee-form').reset();
        }

        // Load employees from API
        async function loadEmployees() {
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }

            try {
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('employees') : '/HRIS/api/employees';
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success && data.data) {
                    employees = data.data.employees || [];
                    filteredEmployees = employees;
                    
                    // Populate department filter
                    populateDepartmentFilter();
                    
                    // Display employees
                    displayEmployees();
                } else {
                    console.error('Failed to load employees:', data.message);
                    showEmptyState();
                }
            } catch (error) {
                console.error('Error loading employees:', error);
                showEmptyState();
            }
        }

        function populateDepartmentFilter() {
            const departments = [...new Set(employees.map(e => e.department))].sort();
            const select = document.getElementById('department-filter');
            
            departments.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept;
                option.textContent = dept;
                select.appendChild(option);
            });
        }

        function displayEmployees() {
            const tbody = document.getElementById('employee-table-body');
            const loading = document.getElementById('table-loading');
            const empty = document.getElementById('table-empty');
            
            loading.classList.add('hidden');
            
            if (filteredEmployees.length === 0) {
                tbody.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }
            
            empty.classList.add('hidden');
            
            tbody.innerHTML = filteredEmployees.map(emp => `
                <tr class="hover:bg-slate-700 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">${emp.employee_id || 'N/A'}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold mr-3">
                                ${(emp.first_name[0] + emp.last_name[0]).toUpperCase()}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">${emp.first_name} ${emp.last_name}</div>
                                <div class="text-sm text-slate-400">${emp.work_email || emp.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">${emp.department}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">${emp.position}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(emp.employment_status)}">
                            ${emp.employment_status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="viewEmployee('${emp.id}')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>View</span>
                            </button>
                            <button onclick="editEmployee('${emp.id}')" class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit</span>
                            </button>
                            <button onclick="deleteEmployee('${emp.id}')" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        }

        function getStatusColor(status) {
            const colors = {
                'Regular': 'bg-green-900 text-green-300',
                'Probationary': 'bg-yellow-900 text-yellow-300',
                'Contractual': 'bg-blue-900 text-blue-300',
                'Part-time': 'bg-purple-900 text-purple-300'
            };
            return colors[status] || 'bg-slate-700 text-slate-300';
        }

        function showEmptyState() {
            document.getElementById('table-loading').classList.add('hidden');
            document.getElementById('table-empty').classList.remove('hidden');
        }

        // Search and filter
        document.getElementById('search-input').addEventListener('input', filterEmployees);
        document.getElementById('department-filter').addEventListener('change', filterEmployees);
        document.getElementById('status-filter').addEventListener('change', filterEmployees);

        function filterEmployees() {
            const search = document.getElementById('search-input').value.toLowerCase();
            const department = document.getElementById('department-filter').value;
            const status = document.getElementById('status-filter').value;

            filteredEmployees = employees.filter(emp => {
                const matchSearch = !search || 
                    emp.first_name.toLowerCase().includes(search) ||
                    emp.last_name.toLowerCase().includes(search) ||
                    emp.email.toLowerCase().includes(search) ||
                    emp.employee_id.toLowerCase().includes(search);
                
                const matchDepartment = !department || emp.department === department;
                const matchStatus = !status || emp.employment_status === status;

                return matchSearch && matchDepartment && matchStatus;
            });

            displayEmployees();
        }

        // Add employee form submission
        document.getElementById('add-employee-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Disable submit button to prevent double-click
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            
            const token = localStorage.getItem('hris_token');
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            // Validate required fields on frontend first
            const requiredFields = ['first_name', 'last_name', 'email', 'department', 'position', 'employment_status', 'date_hired'];
            const missingFields = requiredFields.filter(field => !data[field] || data[field].trim() === '');
            
            if (missingFields.length > 0) {
                submitBtn.disabled = false;
                showSuccess('Validation Error', 'Please fill in all required fields: ' + missingFields.join(', '), null);
                return;
            }
            
            showLoading('Creating employee...');
            closeAddModal();
            
            // Map form fields to backend expected fields
            const employeeData = {
                employee_id: 'EMP-' + Date.now().toString().slice(-4),  // Auto-generate employee_id
                first_name: data.first_name.trim(),
                last_name: data.last_name.trim(),
                work_email: data.email.trim(),  // Map 'email' to 'work_email'
                mobile_number: data.phone ? data.phone.trim() : '',  // Map 'phone' to 'mobile_number'
                department: data.department.trim(),
                position: data.position.trim(),
                employment_status: data.employment_status,
                date_hired: data.date_hired
            };

            try {
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('employees') : '/HRIS/api/employees';
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(employeeData)
                });

                const result = await response.json();
                hideLoading();

                if (result.success) {
                    const employee = result.data.employee;
                    let details = '';
                    
                    if (employee.temporary_password) {
                        details = `
                            <div class="space-y-2">
                                <p class="text-sm text-slate-300 font-semibold">Temporary Password:</p>
                                <div class="flex items-center space-x-2 bg-slate-800 rounded p-3">
                                    <code class="flex-1 text-green-400 font-mono text-lg">${employee.temporary_password}</code>
                                    <button onclick="navigator.clipboard.writeText('${employee.temporary_password}')" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition-all">
                                        Copy
                                    </button>
                                </div>
                                <p class="text-xs text-slate-400">${employee.password_message || 'Please save this password. It will not be shown again.'}</p>
                            </div>
                        `;
                    }
                    
                    showSuccess(
                        'Employee Created!',
                        `${employee.first_name} ${employee.last_name} has been added successfully.`,
                        details
                    );
                    
                    e.target.reset();
                    loadEmployees(); // Reload the list
                } else {
                    let errorMsg = result.message || 'Failed to add employee';
                    if (result.errors) {
                        errorMsg += '\n\nValidation errors:\n';
                        for (const [field, error] of Object.entries(result.errors)) {
                            errorMsg += `- ${field}: ${error}\n`;
                        }
                    }
                    showSuccess('Error', errorMsg, null);
                }
            } catch (error) {
                hideLoading();
                console.error('Error adding employee:', error);
                showSuccess('Error', 'Failed to add employee. Please try again.', null);
            } finally {
                submitBtn.disabled = false;
            }
        });

        async function viewEmployee(id) {
            const token = localStorage.getItem('hris_token');
            const employee = employees.find(e => e.id === id);
            
            if (!employee) {
                alert('Employee not found');
                return;
            }

            const content = document.getElementById('view-employee-content');
            content.innerHTML = `
                <div class="space-y-6">
                    <div class="flex items-center space-x-4 pb-6 border-b border-slate-700">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-2xl font-semibold">
                            ${(employee.first_name[0] + employee.last_name[0]).toUpperCase()}
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-white">${employee.first_name} ${employee.last_name}</h4>
                            <p class="text-slate-400">${employee.position}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Employee ID</label>
                            <p class="text-white">${employee.employee_id || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Email</label>
                            <p class="text-white">${employee.work_email || employee.email}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Phone</label>
                            <p class="text-white">${employee.phone || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Department</label>
                            <p class="text-white">${employee.department}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Position</label>
                            <p class="text-white">${employee.position}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Employment Status</label>
                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(employee.employment_status)}">
                                ${employee.employment_status}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Date Hired</label>
                            <p class="text-white">${employee.date_hired || 'N/A'}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Status</label>
                            <p class="text-white">${employee.status || 'Active'}</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4 border-t border-slate-700">
                        <button onclick="closeViewModal(); editEmployee('${id}')" class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all">
                            Edit Employee
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('view-employee-modal').classList.remove('hidden');
        }

        async function editEmployee(id) {
            const token = localStorage.getItem('hris_token');
            const employee = employees.find(e => e.id === id);
            
            if (!employee) {
                alert('Employee not found');
                return;
            }

            // Populate form
            document.getElementById('edit-employee-id').value = employee.id;
            document.getElementById('edit-first-name').value = employee.first_name;
            document.getElementById('edit-last-name').value = employee.last_name;
            document.getElementById('edit-email').value = employee.work_email || employee.email;
            document.getElementById('edit-phone').value = employee.phone || '';
            document.getElementById('edit-department').value = employee.department;
            document.getElementById('edit-position').value = employee.position;
            document.getElementById('edit-employment-status').value = employee.employment_status;
            document.getElementById('edit-date-hired').value = employee.date_hired;

            document.getElementById('edit-employee-modal').classList.remove('hidden');
        }

        // Edit employee form submission
        document.getElementById('edit-employee-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Disable submit button to prevent double-click
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            
            const token = localStorage.getItem('hris_token');
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const employeeId = data.employee_id;
            
            // Show confirmation modal before updating
            const employeeName = `${data.first_name} ${data.last_name}`;
            showConfirm(
                'Update Employee',
                `Are you sure you want to update ${employeeName}?`,
                async function() {
                    await performEmployeeUpdate(data, employeeId, submitBtn);
                }
            );
            
            // Re-enable button if user cancels
            submitBtn.disabled = false;
        });
        
        async function performEmployeeUpdate(data, employeeId, submitBtn) {
            // Validate required fields on frontend first
            const requiredFields = ['first_name', 'last_name', 'email', 'department', 'position', 'employment_status', 'date_hired'];
            const missingFields = requiredFields.filter(field => !data[field] || data[field].trim() === '');
            
            if (missingFields.length > 0) {
                showSuccess('Validation Error', 'Please fill in all required fields: ' + missingFields.join(', '), null);
                return;
            }
            
            showLoading('Updating employee...');
            closeEditModal();
            
            const token = localStorage.getItem('hris_token');
            
            // Map form fields to backend expected fields
            const employeeData = {
                first_name: data.first_name.trim(),
                last_name: data.last_name.trim(),
                work_email: data.email.trim(),  // Map 'email' to 'work_email'
                mobile_number: data.phone ? data.phone.trim() : '',  // Map 'phone' to 'mobile_number'
                department: data.department.trim(),
                position: data.position.trim(),
                employment_status: data.employment_status,
                date_hired: data.date_hired
            };

            try {
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('employees/' + employeeId) : '/HRIS/api/employees/' + employeeId;
                const response = await fetch(apiUrl, {
                    method: 'PUT',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(employeeData)
                });

                const result = await response.json();
                hideLoading();

                if (result.success) {
                    const employee = result.data.employee;
                    showSuccess(
                        'Employee Updated!',
                        `${employee.first_name} ${employee.last_name} has been updated successfully.`,
                        null
                    );
                    loadEmployees(); // Reload the list
                } else {
                    let errorMsg = result.message || 'Failed to update employee';
                    if (result.errors) {
                        errorMsg += '\n\nValidation errors:\n';
                        for (const [field, error] of Object.entries(result.errors)) {
                            errorMsg += `- ${field}: ${error}\n`;
                        }
                    }
                    showSuccess('Error', errorMsg, null);
                }
            } catch (error) {
                hideLoading();
                console.error('Error updating employee:', error);
                showSuccess('Error', 'Failed to update employee. Please try again.', null);
            }
        }

        async function deleteEmployee(id) {
            const employee = employees.find(e => e.id === id);
            if (!employee) {
                showSuccess('Error', 'Employee not found', null);
                return;
            }

            showConfirm(
                'Delete Employee',
                `Are you sure you want to delete ${employee.first_name} ${employee.last_name}? This action cannot be undone.`,
                async function() {
                    showLoading('Deleting employee...');
                    
                    const token = localStorage.getItem('hris_token');

                    try {
                        const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('employees/' + id) : '/HRIS/api/employees/' + id;
                        const response = await fetch(apiUrl, {
                            method: 'DELETE',
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Content-Type': 'application/json'
                            }
                        });

                        const result = await response.json();
                        hideLoading();

                        if (result.success) {
                            showSuccess(
                                'Employee Deleted!',
                                `${employee.first_name} ${employee.last_name} has been deleted successfully.`,
                                null
                            );
                            loadEmployees(); // Reload the list
                        } else {
                            showSuccess('Error', result.message || 'Failed to delete employee', null);
                        }
                    } catch (error) {
                        hideLoading();
                        console.error('Error deleting employee:', error);
                        showSuccess('Error', 'Failed to delete employee. Please try again.', null);
                    }
                }
            );
        }

        // Hide loading screen and load employees
        document.addEventListener('DOMContentLoaded', function() {
            // Add employee button
            document.getElementById('add-employee-btn').addEventListener('click', function() {
                document.getElementById('add-employee-modal').classList.remove('hidden');
            });

            // Hide page loading and load employees
            setTimeout(() => {
                const loading = document.getElementById('page-loading');
                loading.style.opacity = '0';
                loading.style.transition = 'opacity 0.3s ease-out';
                setTimeout(() => {
                    loading.style.display = 'none';
                    loadEmployees();
                }, 300);
            }, 500);
        });
    </script>
</body>
</html>
