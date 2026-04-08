/**
 * Authentication Manager for HRIS MVP
 * Handles user authentication, session management, and role-based access control
 */

class AuthManager {
    constructor() {
        this.token = localStorage.getItem('hris_token');
        this.user = JSON.parse(localStorage.getItem('hris_user') || 'null');
        this.refreshTimer = null;
        
        // Set up automatic token refresh
        if (this.token) {
            this.setupTokenRefresh();
        }
    }

    /**
     * Authenticate user with email and password
     * @param {string} email - User email
     * @param {string} password - User password
     * @returns {Promise<Object>} Authentication result
     */
    async login(email, password) {
        try {
            const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('auth/login') : '/HRIS/api/auth/login';
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            });

            const data = await response.json();
            const payload = this.normalizePayload(data);

            console.log('🔍 AUTH.JS DEBUG:');
            console.log('  - Raw response data:', data);
            console.log('  - Normalized payload:', payload);
            console.log('  - Payload user:', payload.user);
            console.log('  - User force_password_change:', payload.user?.force_password_change);

            if (data.success) {
                this.token = payload.access_token ?? null;
                this.user = payload.user ?? null;

                console.log('  - this.token set:', this.token ? 'YES' : 'NO');
                console.log('  - this.user set:', this.user ? 'YES' : 'NO');
                console.log('  - this.user.force_password_change:', this.user?.force_password_change);

                if (!this.token || !this.user) {
                    return {
                        success: false,
                        message: data.message || 'Login failed'
                    };
                }
                
                localStorage.setItem('hris_token', this.token);
                localStorage.setItem('hris_user', JSON.stringify(this.user));
                
                // Store refresh_token if available (from Supabase login response)
                if (payload.refresh_token) {
                    localStorage.setItem('hris_refresh_token', payload.refresh_token);
                    console.log('  - Refresh token stored');
                }
                
                // Set up token refresh
                this.setupTokenRefresh();
                
                // Log successful login
                this.logActivity('LOGIN');
                
                // Check if employee needs to change password
                console.log('  - Checking force password change...');
                console.log('  - this.user.role:', this.user.role);
                console.log('  - this.user.force_password_change:', this.user.force_password_change);
                
                if (this.user.role === 'employee' && this.user.force_password_change) {
                    console.log('  ✅ FORCE PASSWORD CHANGE CONDITION MET!');
                    return {
                        success: true,
                        user: this.user,
                        force_password_change: true,
                        redirectUrl: window.AppConfig ? window.AppConfig.url('password/change') : '/password/change'
                    };
                }
                
                console.log('  - Normal redirect');
                return {
                    success: true,
                    user: this.user,
                    redirectUrl: this.getRedirectUrl()
                };
            } else {
                return {
                    success: false,
                    message: data.message || 'Login failed'
                };
            }
        } catch (error) {
            console.error('Login error:', error);
            return {
                success: false,
                message: 'Network error. Please try again.'
            };
        }
    }

    /**
     * Logout user and clear session data
     * @returns {Promise<void>}
     */
    async logout() {
        // Show confirmation modal
        if (!await this.confirmLogout()) {
            return; // User cancelled
        }
        
        // Show loading state
        this.showLogoutLoading();
        
        try {
            // Call logout endpoint to invalidate server session
            if (this.token) {
                const apiPath = this.getApiPath('api/auth/logout.php');
                
                await fetch(apiPath, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Content-Type': 'application/json'
                    }
                });
            }
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Clear local storage and session data
            this.clearSession();
            
            // Redirect to login page using AppConfig
            if (window.AppConfig) {
                window.location.href = window.AppConfig.url('login');
            } else {
                window.location.href = '../login';
            }
        }
    }
    
    /**
     * Show logout confirmation modal
     * @returns {Promise<boolean>}
     */
    confirmLogout() {
        return new Promise((resolve) => {
            // Create modal if it doesn't exist
            let modal = document.getElementById('logout-confirm-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'logout-confirm-modal';
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
                modal.innerHTML = `
                    <div class="bg-slate-800 rounded-xl shadow-2xl max-w-md w-full mx-4 border border-slate-700">
                        <div class="p-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-12 h-12 bg-yellow-500/10 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white">Confirm Logout</h3>
                                    <p class="text-sm text-slate-400 mt-1">Are you sure you want to logout?</p>
                                </div>
                            </div>
                            <div class="flex space-x-3 mt-6">
                                <button id="logout-cancel-btn" class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">
                                    Cancel
                                </button>
                                <button id="logout-confirm-btn" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                    Logout
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }
            
            // Show modal
            modal.classList.remove('hidden');
            
            // Handle confirm
            const confirmBtn = document.getElementById('logout-confirm-btn');
            const cancelBtn = document.getElementById('logout-cancel-btn');
            
            const handleConfirm = () => {
                modal.classList.add('hidden');
                cleanup();
                resolve(true);
            };
            
            const handleCancel = () => {
                modal.classList.add('hidden');
                cleanup();
                resolve(false);
            };
            
            const cleanup = () => {
                confirmBtn.removeEventListener('click', handleConfirm);
                cancelBtn.removeEventListener('click', handleCancel);
            };
            
            confirmBtn.addEventListener('click', handleConfirm);
            cancelBtn.addEventListener('click', handleCancel);
        });
    }
    
    /**
     * Show logout loading state
     */
    showLogoutLoading() {
        let loadingModal = document.getElementById('logout-loading-modal');
        if (!loadingModal) {
            loadingModal = document.createElement('div');
            loadingModal.id = 'logout-loading-modal';
            loadingModal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center';
            loadingModal.innerHTML = `
                <div class="bg-slate-800 rounded-xl shadow-2xl p-8 border border-slate-700">
                    <div class="flex flex-col items-center space-y-4">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                        <p class="text-white font-medium">Logging out...</p>
                    </div>
                </div>
            `;
            document.body.appendChild(loadingModal);
        }
        loadingModal.classList.remove('hidden');
    }

    /**
     * Get correct API path based on current location
     * @private
     * @param {string} apiPath - API endpoint path
     * @returns {string} Correct relative path
     */
    getApiPath(apiPath) {
        const currentPath = window.location.pathname;
        
        // If we're in a subdirectory (like dashboard/), go up one level
        if (currentPath.includes('/dashboard/') || currentPath.includes('/modules/')) {
            return '../' + apiPath;
        }
        
        return apiPath;
    }

    /**
     * Check if user is authenticated
     * @returns {boolean} True if authenticated
     */
    isAuthenticated() {
        return !!(this.token && this.user);
    }

    /**
     * Check if user has specific role
     * @param {string} role - Role to check (admin, employee)
     * @returns {boolean} True if user has role
     */
    hasRole(role) {
        if (!this.user) return false;
        
        return this.getUserRole() === role;
    }

    /**
     * Get current user data
     * @returns {Object|null} User data or null
     */
    getCurrentUser() {
        return this.user;
    }

    /**
     * Get current authentication token
     * @returns {string|null} JWT token or null
     */
    getToken() {
        return this.token;
    }

    getUserRole() {
        if (!this.user) {
            return null;
        }

        return this.user.role ?? this.user.user_metadata?.role ?? null;
    }

    normalizePayload(responseData) {
        if (!responseData || typeof responseData !== 'object') {
            return {};
        }

        if (responseData.data && typeof responseData.data === 'object') {
            return responseData.data;
        }

        return responseData;
    }

    /**
     * Verify current token with server
     * @returns {Promise<boolean>} True if token is valid
     */
    async verifyToken() {
        if (!this.token) return false;

        try {
            // Use AppConfig for correct API URL
            const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('auth/verify') : '/HRIS/api/auth/verify';
            
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            const payload = this.normalizePayload(data);

            if (data.success) {
                if (payload.user) {
                    this.user = payload.user;
                    localStorage.setItem('hris_user', JSON.stringify(this.user));
                }
                return true;
            } else {
                // Token is invalid, clear session
                this.clearSession();
                return false;
            }
        } catch (error) {
            console.error('Token verification error:', error);
            return false;
        }
    }

    /**
     * Refresh authentication token
     * @returns {Promise<boolean>} True if refresh successful
     */
    async refreshToken() {
        if (!this.token) return false;

        try {
            // Use AppConfig for correct API URL
            const apiUrl = window.AppConfig ? window.AppConfig.apiUrl('auth/refresh') : '/HRIS/api/auth/refresh';
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            const payload = this.normalizePayload(data);

            if (data.success && payload.access_token) {
                this.token = payload.access_token;
                localStorage.setItem('hris_token', this.token);
                
                if (payload.user) {
                    this.user = payload.user;
                    localStorage.setItem('hris_user', JSON.stringify(this.user));
                }
                
                return true;
            } else {
                // Refresh failed, clear session
                this.clearSession();
                return false;
            }
        } catch (error) {
            console.error('Token refresh error:', error);
            this.clearSession();
            return false;
        }
    }

    /**
     * Set up automatic token refresh
     * @private
     */
    setupTokenRefresh() {
        // Clear existing timer
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
        }

        // Refresh token every 50 minutes (tokens expire in 1 hour)
        this.refreshTimer = setInterval(async () => {
            const refreshed = await this.refreshToken();
            if (!refreshed) {
                // Refresh failed, redirect to login
                if (window.AppConfig) {
                    window.location.href = window.AppConfig.url('login');
                } else {
                    window.location.href = 'login';
                }
            }
        }, 50 * 60 * 1000); // 50 minutes
    }

    /**
     * Clear session data
     * @private
     */
    clearSession() {
        this.token = null;
        this.user = null;
        
        localStorage.removeItem('hris_token');
        localStorage.removeItem('hris_user');
        
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }

    /**
     * Get redirect URL based on user role
     * @private
     * @returns {string} Redirect URL
     */
    getRedirectUrl() {
        const basePath = window.AppConfig ? window.AppConfig.basePath : '/HRIS';
        
        if (this.hasRole('admin')) {
            return basePath + '/dashboard/admin';
        } else if (this.hasRole('employee')) {
            return basePath + '/dashboard/employee';
        } else {
            return basePath + '/';
        }
    }

    /**
     * Log user activity for audit purposes
     * @private
     * @param {string} action - Action performed
     */
    async logActivity(action) {
        // Activity logging is optional and handled server-side
        // This method is kept for backward compatibility but does nothing
        // Server-side logging happens in middleware and controllers
        return;
    }

    /**
     * Check session timeout and redirect if necessary
     */
    checkSessionTimeout() {
        if (!this.isAuthenticated()) {
            if (window.AppConfig) {
                window.location.href = window.AppConfig.url('login');
            } else {
                window.location.href = 'login';
            }
            return false;
        }
        return true;
    }

    /**
     * Initialize authentication on page load
     */
    async initialize() {
        // Check if we're on the login page
        const basePath = window.AppConfig ? window.AppConfig.basePath : '/HRIS';
        const isLoginPage = window.location.pathname.endsWith('/login') || 
                           window.location.pathname === '/' || 
                           window.location.pathname === basePath + '/' || 
                           window.location.pathname === basePath;
        
        if (isLoginPage) {
            // If already authenticated, redirect to dashboard
            if (this.isAuthenticated()) {
                const isValid = await this.verifyToken();
                if (isValid) {
                    window.location.href = this.getRedirectUrl();
                }
            }
            return;
        }

        // For other pages, check if user is authenticated
        if (!this.isAuthenticated()) {
            // No token in localStorage, redirect to login
            if (window.AppConfig) {
                window.location.href = window.AppConfig.url('login');
            } else {
                window.location.href = '../login';
            }
            return;
        }

        // User has token in localStorage, trust it for now
        // Token will be verified on next API call
        // This prevents unnecessary redirects
        console.log('User authenticated from localStorage:', this.user);
        
        // Check role-based access
        this.checkPageAccess();
    }

    /**
     * Check if user has access to current page
     * @private
     */
    checkPageAccess() {
        const path = window.location.pathname.replace(/\/+$/, '');
        const isAdminPage = path.endsWith('/dashboard/admin') || path.includes('/employees') || path.includes('/reports');
        const isEmployeeDashboard = path.endsWith('/dashboard/employee') || path.endsWith('/dashboard');
        const isChangePasswordPage = path.includes('/password/change');
        
        console.log('checkPageAccess - Path:', path);
        console.log('checkPageAccess - User:', this.user);
        console.log('checkPageAccess - Is change password page:', isChangePasswordPage);
        
        // Check if employee needs to change password (except on change password page and login page)
        if (!isChangePasswordPage && this.user && this.user.role === 'employee' && this.user.force_password_change) {
            console.log('Force password change detected! Redirecting to change password page...');
            const changePasswordUrl = window.AppConfig ? window.AppConfig.url('password/change') : '/HRIS/password/change';
            console.log('Redirect URL:', changePasswordUrl);
            window.location.href = changePasswordUrl;
            return;
        }
        
        if (isAdminPage) {
            if (!this.hasRole('admin')) {
                if (window.AppConfig) {
                    window.location.href = window.AppConfig.url('dashboard/employee');
                } else {
                    window.location.href = 'dashboard/employee';
                }
                return;
            }
        }

        if (isEmployeeDashboard && !this.hasRole('employee') && !this.hasRole('admin')) {
            if (window.AppConfig) {
                window.location.href = window.AppConfig.url('dashboard/admin');
            } else {
                window.location.href = 'dashboard/admin';
            }
            return;
        }
    }
}

// Create global instance
window.AuthManager = new AuthManager();

// Initialize authentication when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.AuthManager.initialize();
    
    // Set up logout button if present
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.AuthManager.logout();
        });
    }
});

// Set up session timeout check
setInterval(() => {
    if (window.AuthManager) {
        window.AuthManager.checkSessionTimeout();
    }
}, 60000); // Check every minute
