<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment - HRIS MVP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('/assets/css/custom.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/assets/css/loading-skeletons.css') ?>">
</head>
<body class="h-full bg-slate-900 overflow-hidden">
    <!-- Main Container -->
    <div class="flex h-full bg-slate-900">
        
        <!-- Sidebar -->
        <?php $currentPage = 'recruitment'; include __DIR__ . '/../layouts/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-900">
            <!-- Header -->
            <header class="bg-slate-800 border-b border-slate-700 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-bold text-white">Recruitment</h2>
                        <p class="text-slate-400 mt-1">Manage job postings and applicants</p>
                    </div>
                    <div class="flex space-x-3">
                        <button id="new-posting-btn" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-lg shadow-blue-900/50">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Posting
                        </button>
                        <button id="new-applicant-btn" class="hidden px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-700 text-white rounded-lg hover:from-purple-700 hover:to-pink-800 transition-all shadow-lg shadow-purple-900/50">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Add Applicant
                        </button>
                    </div>
                </div>
            </header>
            
            <!-- Tab Navigation -->
            <div class="bg-slate-800 border-b border-slate-700 px-8">
                <nav class="flex space-x-8">
                    <button id="tab-job-postings" class="tab-btn py-4 px-2 border-b-2 border-blue-500 text-blue-500 font-medium transition-colors">
                        Job Postings
                    </button>
                    <button id="tab-applicants" class="tab-btn py-4 px-2 border-b-2 border-transparent text-slate-400 hover:text-white font-medium transition-colors">
                        Applicants
                    </button>
                    <button id="tab-pipeline" class="tab-btn py-4 px-2 border-b-2 border-transparent text-slate-400 hover:text-white font-medium transition-colors">
                        Pipeline View
                    </button>
                </nav>
            </div>
            
            <!-- Content -->
            <div class="p-8">
                <!-- Job Postings Tab -->
                <div id="content-job-postings" class="tab-content">
                    <!-- Filters -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <select id="job-status-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                    <option value="">All Status</option>
                                    <option value="Open">Open</option>
                                    <option value="Closed">Closed</option>
                                    <option value="On Hold">On Hold</option>
                                </select>
                            </div>
                            <div>
                                <select id="job-department-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                    <option value="">All Departments</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Job Postings Cards -->
                    <div>
                        <!-- Loading State -->
                        <div id="job-postings-loading" class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <p class="text-slate-400 mt-2">Loading job postings...</p>
                        </div>
                        
                        <!-- Cards Grid -->
                        <div id="job-postings-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                            <!-- Job posting cards will be inserted here -->
                        </div>
                        
                        <!-- Empty State -->
                        <div id="job-postings-empty" class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl text-center py-12 hidden">
                            <svg class="w-16 h-16 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-white mb-2">No Job Postings Found</h3>
                            <p class="text-slate-400">Try adjusting your filters or create a new posting</p>
                        </div>
                    </div>
                </div>

                <!-- Applicants Tab -->
                <div id="content-applicants" class="tab-content hidden">
                    <!-- Filters -->
                    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <select id="applicant-job-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                    <option value="">All Job Postings</option>
                                </select>
                            </div>
                            <div>
                                <select id="applicant-status-filter" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                                    <option value="">All Status</option>
                                    <option value="Applied">Applied</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Passed">Passed</option>
                                    <option value="Failed">Failed</option>
                                    <option value="Hired">Hired</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Applicants Cards -->
                    <div>
                        <!-- Loading State -->
                        <div id="applicants-loading" class="text-center py-12">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <p class="text-slate-400 mt-2">Loading applicants...</p>
                        </div>
                        
                        <!-- Cards Grid -->
                        <div id="applicants-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
                            <!-- Applicant cards will be inserted here -->
                        </div>
                        
                        <!-- Empty State -->
                        <div id="applicants-empty" class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl text-center py-12 hidden">
                            <svg class="w-16 h-16 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-white mb-2">No Applicants Found</h3>
                            <p class="text-slate-400">Try adjusting your filters or add a new applicant</p>
                        </div>
                    </div>
                </div>

                <!-- Pipeline View Tab -->
                <div id="content-pipeline" class="tab-content hidden">
                    <!-- Pipeline Board - Grid Layout -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Applied Column -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl flex flex-col h-[calc(50vh-120px)]">
                            <div class="p-4 border-b border-slate-700 bg-gradient-to-r from-blue-600/20 to-blue-700/20 rounded-t-xl flex-shrink-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Applied</h3>
                                    <span id="pipeline-applied-count" class="px-3 py-1 bg-blue-600 text-white text-sm font-bold rounded-full">0</span>
                                </div>
                            </div>
                            <div id="pipeline-applied" class="p-4 space-y-3 flex-1 overflow-y-auto">
                                <!-- Applicant cards will be inserted here -->
                            </div>
                        </div>

                        <!-- Screening Column -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl flex flex-col h-[calc(50vh-120px)]">
                            <div class="p-4 border-b border-slate-700 bg-gradient-to-r from-yellow-600/20 to-yellow-700/20 rounded-t-xl flex-shrink-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Screening</h3>
                                    <span id="pipeline-screening-count" class="px-3 py-1 bg-yellow-600 text-white text-sm font-bold rounded-full">0</span>
                                </div>
                            </div>
                            <div id="pipeline-screening" class="p-4 space-y-3 flex-1 overflow-y-auto">
                                <!-- Applicant cards will be inserted here -->
                            </div>
                        </div>

                        <!-- Interview 1 Column -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl flex flex-col h-[calc(50vh-120px)]">
                            <div class="p-4 border-b border-slate-700 bg-gradient-to-r from-purple-600/20 to-purple-700/20 rounded-t-xl flex-shrink-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Interview 1</h3>
                                    <span id="pipeline-interview1-count" class="px-3 py-1 bg-purple-600 text-white text-sm font-bold rounded-full">0</span>
                                </div>
                            </div>
                            <div id="pipeline-interview1" class="p-4 space-y-3 flex-1 overflow-y-auto">
                                <!-- Applicant cards will be inserted here -->
                            </div>
                        </div>

                        <!-- Interview 2 Column -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl flex flex-col h-[calc(50vh-120px)]">
                            <div class="p-4 border-b border-slate-700 bg-gradient-to-r from-pink-600/20 to-pink-700/20 rounded-t-xl flex-shrink-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Interview 2</h3>
                                    <span id="pipeline-interview2-count" class="px-3 py-1 bg-pink-600 text-white text-sm font-bold rounded-full">0</span>
                                </div>
                            </div>
                            <div id="pipeline-interview2" class="p-4 space-y-3 flex-1 overflow-y-auto">
                                <!-- Applicant cards will be inserted here -->
                            </div>
                        </div>

                        <!-- Offer Column -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl flex flex-col h-[calc(50vh-120px)]">
                            <div class="p-4 border-b border-slate-700 bg-gradient-to-r from-green-600/20 to-green-700/20 rounded-t-xl flex-shrink-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Offer</h3>
                                    <span id="pipeline-offer-count" class="px-3 py-1 bg-green-600 text-white text-sm font-bold rounded-full">0</span>
                                </div>
                            </div>
                            <div id="pipeline-offer" class="p-4 space-y-3 flex-1 overflow-y-auto">
                                <!-- Applicant cards will be inserted here -->
                            </div>
                        </div>

                        <!-- Hired Column -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl flex flex-col h-[calc(50vh-120px)]">
                            <div class="p-4 border-b border-slate-700 bg-gradient-to-r from-emerald-600/20 to-emerald-700/20 rounded-t-xl flex-shrink-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Hired</h3>
                                    <span id="pipeline-hired-count" class="px-3 py-1 bg-emerald-600 text-white text-sm font-bold rounded-full">0</span>
                                </div>
                            </div>
                            <div id="pipeline-hired" class="p-4 space-y-3 flex-1 overflow-y-auto">
                                <!-- Applicant cards will be inserted here -->
                            </div>
                        </div>
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

    <!-- Scripts -->
    <script src="<?= base_url('/assets/js/config.js') ?>"></script>
    <script src="<?= base_url('/assets/js/auth.js') ?>"></script>
    <script src="<?= base_url('/assets/js/loading-skeletons.js') ?>"></script>
    <script>
        // Global state
        let jobPostings = [];
        let filteredJobPostings = [];
        let applicants = [];
        let filteredApplicants = [];
        let confirmCallback = null;
        let currentApplicantId = null;

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

        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.id.replace('tab-', '');
                switchTab(tabId);
            });
        });

        function switchTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-500');
                btn.classList.add('border-transparent', 'text-slate-400');
            });
            document.getElementById('tab-' + tabName).classList.remove('border-transparent', 'text-slate-400');
            document.getElementById('tab-' + tabName).classList.add('border-blue-500', 'text-blue-500');

            // Update content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Show/hide appropriate action buttons
            const newPostingBtn = document.getElementById('new-posting-btn');
            const newApplicantBtn = document.getElementById('new-applicant-btn');
            
            if (tabName === 'job-postings') {
                newPostingBtn.classList.remove('hidden');
                newApplicantBtn.classList.add('hidden');
            } else if (tabName === 'applicants') {
                newPostingBtn.classList.add('hidden');
                newApplicantBtn.classList.remove('hidden');
            } else {
                newPostingBtn.classList.add('hidden');
                newApplicantBtn.classList.add('hidden');
            }

            // Load data if needed
            if (tabName === 'job-postings' && jobPostings.length === 0) {
                loadJobPostings();
            } else if (tabName === 'applicants' && applicants.length === 0) {
                loadApplicants();
            } else if (tabName === 'pipeline') {
                loadPipelineView();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadJobPostings();
            
            // New posting button
            document.getElementById('new-posting-btn').addEventListener('click', function() {
                document.getElementById('job-posting-modal').classList.remove('hidden');
            });

            // New applicant button
            document.getElementById('new-applicant-btn').addEventListener('click', function() {
                populateApplicantJobPostingDropdown();
                document.getElementById('applicant-modal').classList.remove('hidden');
            });

            // Auto-fill department and position when job posting is selected
            document.getElementById('applicant-job-posting').addEventListener('change', function() {
                const jobId = this.value;
                if (jobId) {
                    const job = jobPostings.find(j => j.id === jobId);
                    if (job) {
                        document.getElementById('applicant-department').value = job.department;
                        document.getElementById('applicant-position').value = job.position;
                    }
                } else {
                    document.getElementById('applicant-department').value = '';
                    document.getElementById('applicant-position').value = '';
                }
            });

            // Filter listeners
            document.getElementById('job-status-filter').addEventListener('change', filterJobPostings);
            document.getElementById('job-department-filter').addEventListener('change', filterJobPostings);
            document.getElementById('applicant-job-filter').addEventListener('change', filterApplicants);
            document.getElementById('applicant-status-filter').addEventListener('change', filterApplicants);
        });
    </script>

    <!-- Job Posting Modal -->
    <div id="job-posting-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white" id="job-posting-modal-title">New Job Posting</h3>
                    <button onclick="closeJobPostingModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="job-posting-form" class="p-6 space-y-4">
                <input type="hidden" name="job_posting_id" id="job-posting-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Job Title *</label>
                        <input type="text" name="job_title" id="job-title" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Department *</label>
                        <input type="text" name="department" id="job-department" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Position *</label>
                        <input type="text" name="position" id="job-position" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Number of Openings *</label>
                        <input type="number" name="num_openings" id="job-openings" min="1" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Status *</label>
                        <select name="status" id="job-status" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                            <option value="Open">Open</option>
                            <option value="Closed">Closed</option>
                            <option value="On Hold">On Hold</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Description</label>
                        <textarea name="description" id="job-description" rows="4" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeJobPostingModal()" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Applicant Modal -->
    <div id="applicant-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white" id="applicant-modal-title">New Applicant</h3>
                    <button onclick="closeApplicantModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="applicant-form" class="p-6 space-y-4">
                <input type="hidden" name="applicant_id" id="applicant-id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Job Posting *</label>
                        <select name="job_posting_id" id="applicant-job-posting" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                            <option value="">Select Job Posting</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">First Name *</label>
                        <input type="text" name="first_name" id="applicant-first-name" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Last Name *</label>
                        <input type="text" name="last_name" id="applicant-last-name" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Email *</label>
                        <input type="email" name="work_email" id="applicant-email" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Phone</label>
                        <input type="tel" name="mobile_number" id="applicant-phone" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Department *</label>
                        <input type="text" name="department" id="applicant-department" required readonly class="w-full px-4 py-2 bg-slate-600 border border-slate-600 rounded-lg text-slate-300 cursor-not-allowed focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Position *</label>
                        <input type="text" name="position" id="applicant-position" required readonly class="w-full px-4 py-2 bg-slate-600 border border-slate-600 rounded-lg text-slate-300 cursor-not-allowed focus:outline-none">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Employment Status *</label>
                        <select name="employment_status" id="applicant-employment-status" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                            <option value="">Select Status</option>
                            <option value="Regular">Regular</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeApplicantModal()" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Applicant Detail Modal -->
    <div id="applicant-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white" id="applicant-detail-name">Applicant Details</h3>
                    <button onclick="closeApplicantDetailModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="applicant-detail-content" class="p-6">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Evaluation Modal -->
    <div id="evaluation-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white" id="evaluation-modal-title">Evaluation</h3>
                    <button onclick="closeEvaluationModal()" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="evaluation-form" class="p-6 space-y-3">
                <input type="hidden" name="applicant_id" id="eval-applicant-id">
                <input type="hidden" name="stage_name" id="eval-stage-name">
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Stage</label>
                    <input type="text" id="eval-stage-display" readonly class="w-full px-4 py-2 bg-slate-600 border border-slate-600 rounded-lg text-slate-300 cursor-not-allowed">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Score (0-100) *</label>
                    <input type="number" name="score" id="eval-score" min="0" max="100" step="0.01" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Interviewer Name *</label>
                    <input type="text" name="interviewer_name" id="eval-interviewer" required class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Evaluation Date *</label>
                    <input type="date" name="evaluation_date" id="eval-date" required max="<?= date('Y-m-d') ?>" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Result *</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="pass_fail" value="true" required class="mr-2">
                            <span class="text-white">Pass</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="pass_fail" value="false" required class="mr-2">
                            <span class="text-white">Fail</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" id="eval-notes" rows="3" class="w-full px-4 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:border-blue-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" onclick="closeEvaluationModal()" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all">
                        Save Evaluation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hire Confirmation Modal -->
    <div id="hire-confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-[60] flex items-center justify-center p-4">
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl max-w-md w-full p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Confirm Hiring</h3>
                <div id="hire-confirm-content" class="text-left text-slate-300 space-y-2">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
            <div class="flex space-x-3">
                <button onclick="closeHireConfirmModal()" class="flex-1 px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                    Cancel
                </button>
                <button id="hire-confirm-btn" class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all">
                    Confirm Hire
                </button>
            </div>
        </div>
    </div>

    <script>
        // ==================== JOB POSTINGS FUNCTIONS ====================
        
        async function loadJobPostings() {
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }

            try {
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('recruitment/jobs') : '/HRIS/api/recruitment/jobs';
                console.log('Loading job postings from:', apiUrl);
                console.log('AppConfig.basePath:', window.AppConfig?.basePath);
                console.log('AppConfig.apiPath:', window.AppConfig?.apiPath);
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success && data.data) {
                    jobPostings = data.data.job_postings || [];
                    filteredJobPostings = jobPostings;
                    
                    // Populate department filter
                    populateJobDepartmentFilter();
                    
                    // Display job postings
                    displayJobPostings();
                } else {
                    showJobPostingsEmpty();
                }
            } catch (error) {
                console.error('Error loading job postings:', error);
                showJobPostingsEmpty();
            }
        }

        function populateJobDepartmentFilter() {
            const departments = [...new Set(jobPostings.map(j => j.department))].sort();
            const select = document.getElementById('job-department-filter');
            
            // Clear existing options except first
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            departments.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept;
                option.textContent = dept;
                select.appendChild(option);
            });
        }

        function displayJobPostings() {
            const grid = document.getElementById('job-postings-grid');
            const loading = document.getElementById('job-postings-loading');
            const empty = document.getElementById('job-postings-empty');
            
            loading.classList.add('hidden');
            
            if (filteredJobPostings.length === 0) {
                grid.innerHTML = '';
                grid.classList.add('hidden');
                empty.classList.remove('hidden');
                return;
            }
            
            empty.classList.add('hidden');
            grid.classList.remove('hidden');
            
            grid.innerHTML = filteredJobPostings.map(job => `
                <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl hover:shadow-2xl hover:border-blue-500 transition-all duration-300 overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-white mb-1">${job.job_title}</h3>
                                <p class="text-blue-100 text-sm">${job.department}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${getJobStatusColor(job.status)}">
                                ${job.status}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="p-4 space-y-3">
                        <div class="flex items-center text-slate-300">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm">${job.position}</span>
                        </div>
                        
                        <div class="flex items-center text-slate-300">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span class="text-sm">${job.num_openings} ${job.num_openings === 1 ? 'Opening' : 'Openings'}</span>
                        </div>
                        
                        ${job.description ? `
                        <div class="pt-2 border-t border-slate-700">
                            <p class="text-sm text-slate-400 line-clamp-2">${job.description}</p>
                        </div>
                        ` : ''}
                    </div>
                    
                    <!-- Card Footer -->
                    <div class="bg-slate-700 px-4 py-3 flex space-x-2">
                        <button onclick="editJobPosting('${job.id}')" class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all text-sm font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                        <button onclick="viewJobApplicants('${job.id}')" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all text-sm font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Applicants
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function getJobStatusColor(status) {
            const colors = {
                'Open': 'bg-green-900 text-green-300',
                'Closed': 'bg-red-900 text-red-300',
                'On Hold': 'bg-yellow-900 text-yellow-300'
            };
            return colors[status] || 'bg-slate-700 text-slate-300';
        }

        function showJobPostingsEmpty() {
            document.getElementById('job-postings-loading').classList.add('hidden');
            document.getElementById('job-postings-empty').classList.remove('hidden');
        }

        function filterJobPostings() {
            const status = document.getElementById('job-status-filter').value;
            const department = document.getElementById('job-department-filter').value;

            filteredJobPostings = jobPostings.filter(job => {
                const matchStatus = !status || job.status === status;
                const matchDepartment = !department || job.department === department;
                return matchStatus && matchDepartment;
            });

            displayJobPostings();
        }

        function closeJobPostingModal() {
            document.getElementById('job-posting-modal').classList.add('hidden');
            document.getElementById('job-posting-form').reset();
            document.getElementById('job-posting-id').value = '';
            document.getElementById('job-posting-modal-title').textContent = 'New Job Posting';
        }

        function editJobPosting(id) {
            const job = jobPostings.find(j => j.id === id);
            if (!job) {
                showSuccess('Error', 'Job posting not found', null);
                return;
            }

            document.getElementById('job-posting-id').value = job.id;
            document.getElementById('job-title').value = job.job_title;
            document.getElementById('job-department').value = job.department;
            document.getElementById('job-position').value = job.position;
            document.getElementById('job-openings').value = job.num_openings;
            document.getElementById('job-status').value = job.status;
            document.getElementById('job-description').value = job.description || '';
            document.getElementById('job-posting-modal-title').textContent = 'Edit Job Posting';
            document.getElementById('job-posting-modal').classList.remove('hidden');
        }

        function viewJobApplicants(jobId) {
            // Switch to applicants tab and filter by job posting
            switchTab('applicants');
            document.getElementById('applicant-job-filter').value = jobId;
            filterApplicants();
        }

        // Job posting form submission
        document.getElementById('job-posting-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const jobId = data.job_posting_id;
            const isEdit = !!jobId;
            
            // Show confirmation modal
            showConfirm(
                isEdit ? 'Update Job Posting?' : 'Create Job Posting?',
                isEdit ? `Are you sure you want to update "${data.job_title}"?` : `Are you sure you want to create "${data.job_title}"?`,
                async function() {
                    submitBtn.disabled = true;
                    
                    const baseUrl = window.AppConfig ? window.AppConfig.apiUrl('recruitment/jobs') : '/HRIS/api/recruitment/jobs';
                    const url = isEdit ? `${baseUrl}/${jobId}` : baseUrl;
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    showLoading(isEdit ? 'Updating job posting...' : 'Creating job posting...');
                    closeJobPostingModal();
                    
                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                job_title: data.job_title,
                                department: data.department,
                                position: data.position,
                                num_openings: parseInt(data.num_openings),
                                status: data.status,
                                description: data.description
                            })
                        });

                        const result = await response.json();
                        hideLoading();

                        if (result.success) {
                            showSuccess(
                                isEdit ? 'Job Posting Updated!' : 'Job Posting Created!',
                                `${data.job_title} has been ${isEdit ? 'updated' : 'created'} successfully.`,
                                null
                            );
                            loadJobPostings();
                        } else {
                            showSuccess('Error', result.message || 'Failed to save job posting', null);
                        }
                    } catch (error) {
                        hideLoading();
                        console.error('Error saving job posting:', error);
                        showSuccess('Error', 'Failed to save job posting. Please try again.', null);
                    } finally {
                        submitBtn.disabled = false;
                    }
                }
            );
        });

        // ==================== APPLICANTS FUNCTIONS ====================
        
        async function loadApplicants() {
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }

            try {
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('recruitment/applicants') : '/HRIS/api/recruitment/applicants';
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success && data.data) {
                    applicants = data.data.applicants || [];
                    filteredApplicants = applicants;
                    
                    // Populate job posting filter
                    populateApplicantJobFilter();
                    
                    // Display applicants
                    displayApplicants();
                } else {
                    showApplicantsEmpty();
                }
            } catch (error) {
                console.error('Error loading applicants:', error);
                showApplicantsEmpty();
            }
        }

        function populateApplicantJobFilter() {
            const select = document.getElementById('applicant-job-filter');
            
            // Clear existing options except first
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            jobPostings.forEach(job => {
                const option = document.createElement('option');
                option.value = job.id;
                option.textContent = job.job_title;
                select.appendChild(option);
            });
        }

        async function populateApplicantJobPostingDropdown() {
            const select = document.getElementById('applicant-job-posting');
            
            // Clear existing options except first
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            // Load job postings if not already loaded
            if (jobPostings.length === 0) {
                await loadJobPostings();
            }
            
            jobPostings.filter(j => j.status === 'Open').forEach(job => {
                const option = document.createElement('option');
                option.value = job.id;
                option.textContent = `${job.job_title} - ${job.department}`;
                select.appendChild(option);
            });
        }

        function displayApplicants() {
            const grid = document.getElementById('applicants-grid');
            const loading = document.getElementById('applicants-loading');
            const empty = document.getElementById('applicants-empty');
            
            loading.classList.add('hidden');
            
            if (filteredApplicants.length === 0) {
                grid.innerHTML = '';
                grid.classList.add('hidden');
                empty.classList.remove('hidden');
                return;
            }
            
            empty.classList.add('hidden');
            grid.classList.remove('hidden');
            
            grid.innerHTML = filteredApplicants.map(applicant => {
                const job = jobPostings.find(j => j.id === applicant.job_posting_id);
                const jobTitle = job ? job.job_title : 'N/A';
                const initials = (applicant.first_name[0] + applicant.last_name[0]).toUpperCase();
                const scoreDisplay = applicant.final_score !== null && applicant.final_score !== undefined ? applicant.final_score.toFixed(2) : 'N/A';
                
                return `
                <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-xl hover:shadow-2xl hover:border-blue-500 transition-all duration-300 overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-16 h-16 rounded-full bg-white bg-opacity-20 backdrop-blur-sm flex items-center justify-center text-white font-bold text-xl border-2 border-white border-opacity-30">
                                ${initials}
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${getApplicantStatusColor(applicant.status)}">
                                ${applicant.status}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-white">${applicant.first_name} ${applicant.last_name}</h3>
                        <p class="text-purple-100 text-sm">${applicant.position}</p>
                    </div>
                    
                    <!-- Card Body -->
                    <div class="p-4 space-y-3">
                        <div class="flex items-center text-slate-300">
                            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm truncate">${applicant.work_email}</span>
                        </div>
                        
                        <div class="flex items-center text-slate-300">
                            <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm truncate">${jobTitle}</span>
                        </div>
                        
                        <div class="pt-2 border-t border-slate-700 flex items-center justify-between">
                            <span class="text-sm text-slate-400">Final Score</span>
                            <span class="text-lg font-bold ${scoreDisplay !== 'N/A' ? (parseFloat(scoreDisplay) >= 70 ? 'text-green-400' : 'text-red-400') : 'text-slate-400'}">${scoreDisplay}</span>
                        </div>
                    </div>
                    
                    <!-- Card Footer -->
                    <div class="bg-slate-700 px-4 py-3 flex space-x-2">
                        <button onclick="viewApplicantDetails('${applicant.id}')" class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-all text-sm font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </button>
                        <button onclick="editApplicant('${applicant.id}')" class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all text-sm font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                    </div>
                </div>
            `;
            }).join('');
        }

        function getApplicantStatusColor(status) {
            const colors = {
                'Applied': 'bg-blue-900 text-blue-300',
                'In Progress': 'bg-yellow-900 text-yellow-300',
                'Passed': 'bg-green-900 text-green-300',
                'Failed': 'bg-red-900 text-red-300',
                'Hired': 'bg-purple-900 text-purple-300'
            };
            return colors[status] || 'bg-slate-700 text-slate-300';
        }

        function showApplicantsEmpty() {
            document.getElementById('applicants-loading').classList.add('hidden');
            document.getElementById('applicants-empty').classList.remove('hidden');
        }

        function filterApplicants() {
            const jobId = document.getElementById('applicant-job-filter').value;
            const status = document.getElementById('applicant-status-filter').value;

            filteredApplicants = applicants.filter(applicant => {
                const matchJob = !jobId || applicant.job_posting_id === jobId;
                const matchStatus = !status || applicant.status === status;
                return matchJob && matchStatus;
            });

            displayApplicants();
        }

        function closeApplicantModal() {
            document.getElementById('applicant-modal').classList.add('hidden');
            document.getElementById('applicant-form').reset();
            document.getElementById('applicant-id').value = '';
            document.getElementById('applicant-modal-title').textContent = 'New Applicant';
        }

        async function editApplicant(id) {
            const applicant = applicants.find(a => a.id === id);
            if (!applicant) {
                showSuccess('Error', 'Applicant not found', null);
                return;
            }

            await populateApplicantJobPostingDropdown();

            document.getElementById('applicant-id').value = applicant.id;
            document.getElementById('applicant-job-posting').value = applicant.job_posting_id;
            document.getElementById('applicant-first-name').value = applicant.first_name;
            document.getElementById('applicant-last-name').value = applicant.last_name;
            document.getElementById('applicant-email').value = applicant.work_email;
            document.getElementById('applicant-phone').value = applicant.mobile_number || '';
            document.getElementById('applicant-department').value = applicant.department;
            document.getElementById('applicant-position').value = applicant.position;
            document.getElementById('applicant-employment-status').value = applicant.employment_status;
            document.getElementById('applicant-modal-title').textContent = 'Edit Applicant';
            document.getElementById('applicant-modal').classList.remove('hidden');
        }

        // Applicant form submission
        document.getElementById('applicant-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const applicantId = data.applicant_id;
            const isEdit = !!applicantId;
            
            // Show confirmation modal
            showConfirm(
                isEdit ? 'Update Applicant?' : 'Create Applicant?',
                isEdit ? `Are you sure you want to update "${data.first_name} ${data.last_name}"?` : `Are you sure you want to create applicant "${data.first_name} ${data.last_name}"?`,
                async function() {
                    submitBtn.disabled = true;
                    
                    const baseUrl = window.AppConfig ? window.AppConfig.apiUrl('recruitment/applicants') : '/HRIS/api/recruitment/applicants';
                    const url = isEdit ? `${baseUrl}/${applicantId}` : baseUrl;
                    const method = isEdit ? 'PUT' : 'POST';
                    
                    showLoading(isEdit ? 'Updating applicant...' : 'Creating applicant...');
                    closeApplicantModal();
                    
                    try {
                        const response = await fetch(url, {
                            method: method,
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                job_posting_id: data.job_posting_id,
                                first_name: data.first_name,
                                last_name: data.last_name,
                                work_email: data.work_email,
                                mobile_number: data.mobile_number,
                                department: data.department,
                                position: data.position,
                                employment_status: data.employment_status
                            })
                        });

                        const result = await response.json();
                        hideLoading();

                        if (result.success) {
                            showSuccess(
                                isEdit ? 'Applicant Updated!' : 'Applicant Created!',
                                `${data.first_name} ${data.last_name} has been ${isEdit ? 'updated' : 'created'} successfully.`,
                                null
                            );
                            loadApplicants();
                        } else {
                            showSuccess('Error', result.message || 'Failed to save applicant', null);
                        }
                    } catch (error) {
                        hideLoading();
                        console.error('Error saving applicant:', error);
                        showSuccess('Error', 'Failed to save applicant. Please try again.', null);
                    } finally {
                        submitBtn.disabled = false;
                    }
                }
            );
        });

        // ==================== PIPELINE VIEW FUNCTIONS ====================
        
        async function loadPipelineView() {
            // Load applicants if not already loaded
            if (applicants.length === 0) {
                await loadApplicants();
            }
            
            // Group applicants by status
            const stages = {
                'Applied': [],
                'Screening': [],
                'Interview 1': [],
                'Interview 2': [],
                'Offer': [],
                'Hired': []
            };
            
            applicants.forEach(applicant => {
                const status = applicant.status || 'Applied';
                if (stages[status]) {
                    stages[status].push(applicant);
                }
            });
            
            // Display applicants in each column
            displayPipelineColumn('applied', stages['Applied']);
            displayPipelineColumn('screening', stages['Screening']);
            displayPipelineColumn('interview1', stages['Interview 1']);
            displayPipelineColumn('interview2', stages['Interview 2']);
            displayPipelineColumn('offer', stages['Offer']);
            displayPipelineColumn('hired', stages['Hired']);
        }
        
        function displayPipelineColumn(columnId, applicantsList) {
            const column = document.getElementById(`pipeline-${columnId}`);
            const countBadge = document.getElementById(`pipeline-${columnId}-count`);
            
            // Update count
            countBadge.textContent = applicantsList.length;
            
            // Clear column
            column.innerHTML = '';
            
            if (applicantsList.length === 0) {
                column.innerHTML = '<div class="text-center py-8"><svg class="w-12 h-12 mx-auto text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg><p class="text-slate-500 text-sm">No applicants</p></div>';
                return;
            }
            
            // Add applicant cards
            applicantsList.forEach(applicant => {
                const job = jobPostings.find(j => j.id === applicant.job_posting_id);
                const jobTitle = job ? job.job_title : 'N/A';
                const initials = (applicant.first_name[0] + applicant.last_name[0]).toUpperCase();
                
                const card = document.createElement('div');
                card.className = 'bg-slate-700/50 rounded-lg p-3 border border-slate-600 hover:border-blue-500 hover:shadow-md transition-all cursor-pointer';
                card.onclick = () => viewApplicantDetails(applicant.id);
                
                card.innerHTML = `
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                            ${initials}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-white text-sm font-semibold truncate">${applicant.first_name} ${applicant.last_name}</h4>
                            <p class="text-slate-400 text-xs truncate">${jobTitle}</p>
                        </div>
                    </div>
                    ${applicant.final_score ? `
                        <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-600">
                            <span class="text-slate-400">Score</span>
                            <span class="${applicant.final_score >= 80 ? 'text-green-400' : applicant.final_score >= 70 ? 'text-yellow-400' : 'text-red-400'} font-semibold">${applicant.final_score.toFixed(1)}</span>
                        </div>
                    ` : ''}
                `;
                
                column.appendChild(card);
            });
        }

        // ==================== APPLICANT DETAIL & EVALUATION FUNCTIONS ====================
        
        async function viewApplicantDetails(id) {
            currentApplicantId = id;
            showLoading('Loading applicant details...');
            
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }
            
            try {
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl(`recruitment/applicants/${id}`) : `/HRIS/api/recruitment/applicants/${id}`;
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                
                hideLoading();
                
                if (data.success && data.data) {
                    const applicant = data.data.applicant;
                    console.log('Applicant data received:', applicant);
                    console.log('Evaluations:', applicant.evaluations);
                    displayApplicantDetails(applicant);
                    document.getElementById('applicant-detail-modal').classList.remove('hidden');
                } else {
                    showSuccess('Error', 'Failed to load applicant details', null);
                }
            } catch (error) {
                hideLoading();
                console.error('Error loading applicant details:', error);
                showSuccess('Error', 'Failed to load applicant details. Please try again.', null);
            }
        }

        function displayApplicantDetails(applicant) {
            const job = jobPostings.find(j => j.id === applicant.job_posting_id);
            const jobTitle = job ? job.job_title : 'N/A';
            
            document.getElementById('applicant-detail-name').textContent = `${applicant.first_name} ${applicant.last_name}`;
            
            const stages = ['Screening', 'Interview 1', 'Interview 2', 'Final Interview'];
            const evaluations = applicant.evaluations || [];
            
            let stagesHtml = '';
            stages.forEach(stage => {
                const eval = evaluations.find(e => e.stage_name === stage);
                const hasEval = !!eval;
                const icon = hasEval ? (eval.pass_fail ? '✓' : '✗') : '○';
                const iconColor = hasEval ? (eval.pass_fail ? 'text-green-400' : 'text-red-400') : 'text-slate-500';
                
                stagesHtml += `
                    <div class="bg-slate-700 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <span class="${iconColor} text-2xl mr-3">${icon}</span>
                                <h4 class="text-lg font-semibold text-white">${stage}</h4>
                            </div>
                            ${hasEval ? `<span class="text-2xl font-bold text-blue-400">${eval.score}</span>` : ''}
                        </div>
                        ${hasEval ? `
                            <div class="text-sm text-slate-300 space-y-1">
                                <p>Interviewer: ${eval.interviewer_name}</p>
                                <p>Date: ${eval.evaluation_date}</p>
                                <p>Status: <span class="${eval.pass_fail ? 'text-green-400' : 'text-red-400'}">${eval.pass_fail ? 'Pass' : 'Fail'}</span></p>
                                ${eval.notes ? `<p class="mt-2 text-slate-400">Notes: ${eval.notes}</p>` : ''}
                            </div>
                        ` : `
                            <p class="text-sm text-slate-400">No evaluation yet</p>
                        `}
                        <button onclick="openEvaluationModal('${applicant.id}', '${stage}')" class="mt-3 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-all">
                            ${hasEval ? 'Edit' : 'Add'} Evaluation
                        </button>
                    </div>
                `;
            });
            
            const allStagesComplete = stages.every(stage => evaluations.find(e => e.stage_name === stage));
            const allStagesPassed = evaluations.every(e => e.pass_fail);
            const finalScore = applicant.final_score;
            const isEligible = allStagesComplete && allStagesPassed && finalScore >= 70;
            
            const content = `
                <div class="space-y-6">
                    <!-- Personal Info -->
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-3">Personal Information</h4>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-400">Email:</span>
                                <span class="text-white ml-2">${applicant.work_email}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Phone:</span>
                                <span class="text-white ml-2">${applicant.mobile_number || 'N/A'}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Department:</span>
                                <span class="text-white ml-2">${applicant.department}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Position:</span>
                                <span class="text-white ml-2">${applicant.position}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Employment Status:</span>
                                <span class="text-white ml-2">${applicant.employment_status}</span>
                            </div>
                            <div>
                                <span class="text-slate-400">Job Posting:</span>
                                <span class="text-white ml-2">${jobTitle}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Final Score -->
                    <div class="bg-slate-700 rounded-lg p-4 text-center">
                        <h4 class="text-lg font-semibold text-white mb-2">Final Score</h4>
                        <div class="text-4xl font-bold ${finalScore >= 70 ? 'text-green-400' : 'text-red-400'}">
                            ${finalScore !== null && finalScore !== undefined ? finalScore.toFixed(2) : 'N/A'}
                        </div>
                        ${finalScore !== null && finalScore !== undefined ? `
                            <p class="text-sm text-slate-400 mt-2">
                                ${finalScore >= 70 ? 'Meets minimum passing score' : 'Below minimum passing score (70)'}
                            </p>
                        ` : ''}
                    </div>
                    
                    <!-- Evaluation Stages -->
                    <div>
                        <h4 class="text-lg font-semibold text-white mb-3">Evaluation Stages</h4>
                        <div class="space-y-3">
                            ${stagesHtml}
                        </div>
                    </div>
                    
                    <!-- Hiring Eligibility -->
                    <div class="bg-slate-700 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-white mb-2">Hiring Eligibility</h4>
                        ${isEligible ? `
                            <div class="flex items-center text-green-400 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">All requirements met</span>
                            </div>
                        ` : `
                            <div class="flex items-center text-red-400 mb-3">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="font-semibold">Requirements not met</span>
                            </div>
                            <ul class="text-sm text-slate-300 space-y-1 ml-8">
                                ${!allStagesComplete ? '<li>• Not all evaluation stages completed</li>' : ''}
                                ${allStagesComplete && !allStagesPassed ? '<li>• Not all stages passed</li>' : ''}
                                ${finalScore !== null && finalScore < 70 ? '<li>• Final score below minimum (70)</li>' : ''}
                            </ul>
                        `}
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-slate-700">
                        <button onclick="closeApplicantDetailModal()" class="px-4 py-2 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition-all">
                            Close
                        </button>
                        ${applicant.status !== 'Hired' ? `
                            <button onclick="openHireConfirmModal('${applicant.id}')" ${!isEligible ? 'disabled' : ''} class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all ${!isEligible ? 'opacity-50 cursor-not-allowed' : ''}">
                                Hire Applicant
                            </button>
                        ` : `
                            <span class="px-4 py-2 bg-purple-900 text-purple-300 rounded-lg">
                                Already Hired
                            </span>
                        `}
                    </div>
                </div>
            `;
            
            document.getElementById('applicant-detail-content').innerHTML = content;
        }

        function closeApplicantDetailModal() {
            document.getElementById('applicant-detail-modal').classList.add('hidden');
        }

        function openEvaluationModal(applicantId, stageName) {
            currentApplicantId = applicantId;
            
            // Get applicant name for modal title
            const applicant = applicants.find(a => a.id === applicantId);
            const applicantName = applicant ? `${applicant.first_name} ${applicant.last_name}` : 'Applicant';
            
            document.getElementById('eval-applicant-id').value = applicantId;
            document.getElementById('eval-stage-name').value = stageName;
            document.getElementById('eval-stage-display').value = stageName;
            document.getElementById('evaluation-modal-title').textContent = `Evaluate ${applicantName}: ${stageName}`;
            
            // Load existing evaluation if any
            if (applicant && applicant.evaluations) {
                const eval = applicant.evaluations.find(e => e.stage_name === stageName);
                if (eval) {
                    document.getElementById('eval-score').value = eval.score;
                    document.getElementById('eval-interviewer').value = eval.interviewer_name;
                    document.getElementById('eval-date').value = eval.evaluation_date;
                    document.getElementById('eval-notes').value = eval.notes || '';
                    
                    // Set radio button
                    const passRadio = document.querySelector('input[name="pass_fail"][value="true"]');
                    const failRadio = document.querySelector('input[name="pass_fail"][value="false"]');
                    if (eval.pass_fail) {
                        passRadio.checked = true;
                    } else {
                        failRadio.checked = true;
                    }
                }
            }
            
            document.getElementById('evaluation-modal').classList.remove('hidden');
        }

        function closeEvaluationModal() {
            document.getElementById('evaluation-modal').classList.add('hidden');
            document.getElementById('evaluation-form').reset();
        }

        // Evaluation form submission
        document.getElementById('evaluation-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }
            
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            // Show confirmation modal
            showConfirm(
                'Save Evaluation?',
                `Are you sure you want to save this ${data.stage_name} evaluation with a score of ${data.score}?`,
                async function() {
                    submitBtn.disabled = true;
                    
                    showLoading('Saving evaluation...');
                    closeEvaluationModal();
                    
                    try {
                        const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('recruitment/evaluations') : '/HRIS/api/recruitment/evaluations';
                        const response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                applicant_id: data.applicant_id,
                                stage_name: data.stage_name,
                                score: parseFloat(data.score),
                                interviewer_name: data.interviewer_name,
                                evaluation_date: data.evaluation_date,
                                pass_fail: data.pass_fail === 'true',
                                notes: data.notes
                            })
                        });

                        const result = await response.json();
                        console.log('Save evaluation response:', result);
                        hideLoading();

                        if (result.success) {
                            showSuccess(
                                'Evaluation Saved!',
                                `${data.stage_name} evaluation has been saved successfully.`,
                                null
                            );
                            
                            // Reload applicant details to show updated evaluation
                            viewApplicantDetails(data.applicant_id);
                            
                            // Reload applicants list in background to update cards
                            loadApplicants();
                        } else {
                            showSuccess('Error', result.message || 'Failed to save evaluation', null);
                        }
                    } catch (error) {
                        hideLoading();
                        console.error('Error saving evaluation:', error);
                        showSuccess('Error', 'Failed to save evaluation. Please try again.', null);
                    } finally {
                        submitBtn.disabled = false;
                    }
                }
            );
        });

        // ==================== HIRING FUNCTIONS ====================
        
        async function openHireConfirmModal(applicantId) {
            showLoading('Loading applicant details...');
            
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }
            
            try {
                // Fetch full applicant details including final_score
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl(`recruitment/applicants/${applicantId}`) : `/HRIS/api/recruitment/applicants/${applicantId}`;
                const response = await fetch(apiUrl, {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                
                hideLoading();
                
                if (!data.success || !data.data) {
                    showSuccess('Error', 'Failed to load applicant details', null);
                    return;
                }
                
                const applicant = data.data.applicant;
                const job = jobPostings.find(j => j.id === applicant.job_posting_id);
                const jobTitle = job ? job.job_title : 'N/A';
                
                const content = `
                    <p class="mb-4">You are about to hire:</p>
                    <div class="bg-slate-700 rounded-lg p-3 mb-4 space-y-2">
                        <p><span class="text-slate-400">Name:</span> <span class="text-white font-semibold">${applicant.first_name} ${applicant.last_name}</span></p>
                        <p><span class="text-slate-400">Position:</span> <span class="text-white">${applicant.position}</span></p>
                        <p><span class="text-slate-400">Final Score:</span> <span class="text-white">${applicant.final_score ? applicant.final_score.toFixed(2) : 'N/A'} / 100</span></p>
                    </div>
                    <p class="mb-2 font-semibold">This will:</p>
                    <ul class="space-y-1 mb-4">
                        <li class="flex items-center text-green-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create an employee record
                        </li>
                        <li class="flex items-center text-green-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Generate Supabase authentication account
                        </li>
                        <li class="flex items-center text-green-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Send temporary password to admin
                        </li>
                        <li class="flex items-center text-green-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Initialize leave credits
                        </li>
                        <li class="flex items-center text-green-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Decrement job posting openings
                        </li>
                    </ul>
                    <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-3 flex items-start">
                        <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-yellow-200 text-sm">This action cannot be undone.</p>
                    </div>
                `;
                
                document.getElementById('hire-confirm-content').innerHTML = content;
                document.getElementById('hire-confirm-modal').classList.remove('hidden');
                
                // Set up confirm button
                document.getElementById('hire-confirm-btn').onclick = function() {
                    closeHireConfirmModal();
                    performHire(applicantId);
                };
            } catch (error) {
                hideLoading();
                console.error('Error loading applicant for hire:', error);
                showSuccess('Error', 'Failed to load applicant details', null);
            }
        }

        function closeHireConfirmModal() {
            document.getElementById('hire-confirm-modal').classList.add('hidden');
        }

        async function performHire(applicantId) {
            const token = localStorage.getItem('hris_token');
            if (!token) {
                window.location.href = window.AppConfig ? window.AppConfig.url('login') : '/HRIS/login';
                return;
            }
            
            showLoading('Hiring applicant...');
            
            try {
                const apiUrl = window.AppConfig ? window.AppConfig.apiUrl(`recruitment/applicants/${applicantId}/hire`) : `/HRIS/api/recruitment/applicants/${applicantId}/hire`;
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        minimum_passing_score: 70.0
                    })
                });

                const result = await response.json();
                hideLoading();

                if (result.success) {
                    const employee = result.data.employee;
                    const tempPassword = employee.temporary_password;
                    
                    let details = '';
                    if (tempPassword) {
                        details = `
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-slate-300 font-semibold mb-2">Employee ID:</p>
                                    <div class="bg-slate-800 rounded p-2 text-center">
                                        <code class="text-blue-400 font-mono text-lg">${employee.employee_id}</code>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-300 font-semibold mb-2">Temporary Password:</p>
                                    <div class="flex items-center space-x-2 bg-slate-800 rounded p-3">
                                        <code class="flex-1 text-green-400 font-mono text-lg break-all">${tempPassword}</code>
                                        <button onclick="navigator.clipboard.writeText('${tempPassword}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy', 2000)" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded transition-all flex-shrink-0">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                                <div class="bg-yellow-900 border border-yellow-700 rounded p-3">
                                    <p class="text-xs text-yellow-200">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Please save this password. It will not be shown again. Employee must change password on first login.
                                    </p>
                                </div>
                            </div>
                        `;
                    }
                    
                    showSuccess(
                        'Employee Created Successfully!',
                        `${employee.first_name} ${employee.last_name} has been hired as ${employee.position}.`,
                        details
                    );
                    
                    // Reload data
                    await loadApplicants();
                    await loadJobPostings();
                    
                    // Close detail modal
                    closeApplicantDetailModal();
                } else {
                    showSuccess('Error', result.message || 'Failed to hire applicant', null);
                }
            } catch (error) {
                hideLoading();
                console.error('Error hiring applicant:', error);
                showSuccess('Error', 'Failed to hire applicant. Please try again.', null);
            }
        }
    </script>
</body>
</html>