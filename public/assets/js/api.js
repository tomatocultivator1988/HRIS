/**
 * API Communication Layer for HRIS MVP
 * Handles all API requests with authentication, error handling, and response processing
 */

class APIClient {
    constructor() {
        this.baseURL = '';
        this.defaultHeaders = {
            'Content-Type': 'application/json'
        };
    }

    /**
     * Get authentication headers
     * @private
     * @returns {Object} Headers with authentication
     */
    getAuthHeaders() {
        const headers = { ...this.defaultHeaders };
        
        if (window.AuthManager && window.AuthManager.getToken()) {
            headers['Authorization'] = `Bearer ${window.AuthManager.getToken()}`;
        }
        
        return headers;
    }

    /**
     * Make HTTP request with error handling
     * @private
     * @param {string} url - Request URL
     * @param {Object} options - Request options
     * @returns {Promise<Object>} Response data
     */
    async makeRequest(url, options = {}) {
        try {
            // Add authentication headers
            options.headers = {
                ...this.getAuthHeaders(),
                ...options.headers
            };

            const response = await fetch(url, options);
            
            // Handle different response types
            let data;
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = { success: false, message: 'Invalid response format' };
            }

            // Handle authentication errors
            if (response.status === 401) {
                if (window.AuthManager) {
                    window.AuthManager.logout();
                }
                throw new Error('Authentication required');
            }

            // Handle other HTTP errors
            if (!response.ok && !data.success) {
                throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
            }

            return data;
        } catch (error) {
            console.error('API Request Error:', error);
            
            // Return standardized error response
            return {
                success: false,
                message: error.message || 'Network error occurred',
                error: error
            };
        }
    }

    /**
     * GET request
     * @param {string} endpoint - API endpoint
     * @param {Object} params - Query parameters
     * @returns {Promise<Object>} Response data
     */
    async get(endpoint, params = {}) {
        let url = `api/${endpoint}`;
        
        // Add query parameters
        if (Object.keys(params).length > 0) {
            const searchParams = new URLSearchParams(params);
            url += `?${searchParams.toString()}`;
        }

        return this.makeRequest(url, {
            method: 'GET'
        });
    }

    /**
     * POST request
     * @param {string} endpoint - API endpoint
     * @param {Object} data - Request body data
     * @returns {Promise<Object>} Response data
     */
    async post(endpoint, data = {}) {
        return this.makeRequest(`api/${endpoint}`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     * @param {string} endpoint - API endpoint
     * @param {Object} data - Request body data
     * @returns {Promise<Object>} Response data
     */
    async put(endpoint, data = {}) {
        return this.makeRequest(`api/${endpoint}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     * @param {string} endpoint - API endpoint
     * @returns {Promise<Object>} Response data
     */
    async delete(endpoint) {
        return this.makeRequest(`api/${endpoint}`, {
            method: 'DELETE'
        });
    }

    /**
     * PATCH request
     * @param {string} endpoint - API endpoint
     * @param {Object} data - Request body data
     * @returns {Promise<Object>} Response data
     */
    async patch(endpoint, data = {}) {
        return this.makeRequest(`api/${endpoint}`, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }

    // Employee API methods
    async getEmployees(params = {}) {
        return this.get('employees/list', params);
    }

    async getEmployee(id) {
        return this.get(`employees/profile?id=${id}`);
    }

    async createEmployee(employeeData) {
        return this.post('employees/create', employeeData);
    }

    async updateEmployee(id, employeeData) {
        return this.put('employees/update', { id, ...employeeData });
    }

    async deleteEmployee(id) {
        return this.delete(`employees/delete?id=${id}`);
    }

    async searchEmployees(query) {
        return this.get('employees/search', { q: query });
    }

    // Attendance API methods
    async recordTimeIn(employeeId) {
        return this.post('attendance/timein', { employee_id: employeeId });
    }

    async recordTimeOut(employeeId) {
        return this.post('attendance/timeout', { employee_id: employeeId });
    }

    async getDailyAttendance(date = null) {
        const params = date ? { date } : {};
        return this.get('attendance/daily', params);
    }

    async getWeeklyAttendance(startDate, endDate) {
        return this.get('attendance/weekly', { start_date: startDate, end_date: endDate });
    }

    async getAttendanceHistory(employeeId, params = {}) {
        return this.get('attendance/history', { employee_id: employeeId, ...params });
    }

    async overrideAttendance(attendanceId, data) {
        return this.post('attendance/override', { attendance_id: attendanceId, ...data });
    }

    // Leave API methods
    async submitLeaveRequest(leaveData) {
        return this.post('leave/request.php', leaveData);
    }

    async approveLeaveRequest(requestId, comments = '') {
        return this.put('leave/approve.php', { request_id: requestId, comments });
    }

    async denyLeaveRequest(requestId, reason) {
        return this.put('leave/deny.php', { request_id: requestId, reason });
    }

    async getLeaveBalance(employeeId = null, year = null) {
        const params = {};
        if (employeeId) params.employee_id = employeeId;
        if (year) params.year = year;
        return this.get('leave/balance.php', params);
    }

    async getLeaveHistory(employeeId = null, params = {}) {
        const queryParams = employeeId ? { employee_id: employeeId, ...params } : params;
        return this.get('leave/history.php', queryParams);
    }

    async getLeaveTypes() {
        return this.get('leave/types.php');
    }

    async getPendingLeaveRequests(params = {}) {
        return this.get('leave/pending.php', params);
    }

    async manageLeaveCredits(employeeId, leaveTypeId, credits) {
        return this.post('leave/credits.php', {
            employee_id: employeeId,
            leave_type_id: leaveTypeId,
            credits
        });
    }

    // Dashboard API methods
    async getDashboardMetrics() {
        return this.get('dashboard/metrics');
    }

    async getChartData() {
        return this.get('dashboard/charts');
    }

    async getTrendAnalysis() {
        return this.get('dashboard/trends');
    }

    // Reports API methods
    async getAttendanceReport(params = {}) {
        return this.get('reports/attendance', params);
    }

    async getLeaveReport(params = {}) {
        return this.get('reports/leave', params);
    }

    async getHeadcountReport(params = {}) {
        return this.get('reports/headcount', params);
    }

    async exportReport(type, format, params = {}) {
        return this.get('reports/export', { type, format, ...params });
    }

    // Announcements API methods
    async getAnnouncements(params = {}) {
        return this.get('announcements/list', params);
    }

    async createAnnouncement(announcementData) {
        return this.post('announcements/create', announcementData);
    }

    async updateAnnouncement(id, announcementData) {
        return this.put('announcements/update', { id, ...announcementData });
    }

    async deactivateAnnouncement(id) {
        return this.post('announcements/deactivate', { id });
    }
}

/**
 * Utility functions for API responses
 */
class APIUtils {
    /**
     * Show success message to user
     * @param {string} message - Success message
     */
    static showSuccess(message) {
        this.showMessage(message, 'success');
    }

    /**
     * Show error message to user
     * @param {string} message - Error message
     */
    static showError(message) {
        this.showMessage(message, 'error');
    }

    /**
     * Show message to user
     * @param {string} message - Message text
     * @param {string} type - Message type (success, error, warning, info)
     */
    static showMessage(message, type = 'info') {
        // Create message element
        const messageEl = document.createElement('div');
        messageEl.className = `alert alert-${type} fixed top-4 right-4 z-50 max-w-sm fade-in`;
        messageEl.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button class="ml-4 text-lg font-bold" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        `;

        // Add to page
        document.body.appendChild(messageEl);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageEl.parentElement) {
                messageEl.remove();
            }
        }, 5000);
    }

    /**
     * Show loading indicator
     * @param {string} message - Loading message
     * @returns {HTMLElement} Loading element
     */
    static showLoading(message = 'Loading...') {
        const loadingEl = document.createElement('div');
        loadingEl.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        loadingEl.innerHTML = `
            <div class="bg-white p-6 rounded-lg flex items-center space-x-3">
                <div class="loading-spinner"></div>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(loadingEl);
        return loadingEl;
    }

    /**
     * Hide loading indicator
     * @param {HTMLElement} loadingEl - Loading element to remove
     */
    static hideLoading(loadingEl) {
        if (loadingEl && loadingEl.parentElement) {
            loadingEl.remove();
        }
    }

    /**
     * Format date for display
     * @param {string|Date} date - Date to format
     * @returns {string} Formatted date
     */
    static formatDate(date) {
        if (!date) return '';
        
        const d = new Date(date);
        return d.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    /**
     * Format datetime for display
     * @param {string|Date} datetime - Datetime to format
     * @returns {string} Formatted datetime
     */
    static formatDateTime(datetime) {
        if (!datetime) return '';
        
        const d = new Date(datetime);
        return d.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Format time for display
     * @param {string|Date} time - Time to format
     * @returns {string} Formatted time
     */
    static formatTime(time) {
        if (!time) return '';
        
        const d = new Date(time);
        return d.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Validate form data
     * @param {Object} data - Form data to validate
     * @param {Object} rules - Validation rules
     * @returns {Object} Validation result
     */
    static validateForm(data, rules) {
        const errors = {};
        
        for (const field in rules) {
            const rule = rules[field];
            const value = data[field];
            
            // Required field check
            if (rule.required && (!value || value.toString().trim() === '')) {
                errors[field] = `${rule.label || field} is required`;
                continue;
            }
            
            // Skip other validations if field is empty and not required
            if (!value) continue;
            
            // Email validation
            if (rule.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                errors[field] = `${rule.label || field} must be a valid email`;
            }
            
            // Minimum length
            if (rule.minLength && value.length < rule.minLength) {
                errors[field] = `${rule.label || field} must be at least ${rule.minLength} characters`;
            }
            
            // Maximum length
            if (rule.maxLength && value.length > rule.maxLength) {
                errors[field] = `${rule.label || field} must not exceed ${rule.maxLength} characters`;
            }
            
            // Pattern validation
            if (rule.pattern && !rule.pattern.test(value)) {
                errors[field] = rule.patternMessage || `${rule.label || field} format is invalid`;
            }
        }
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }

    /**
     * Debounce function calls
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     */
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Create global instances
window.API = new APIClient();
window.APIUtils = APIUtils;