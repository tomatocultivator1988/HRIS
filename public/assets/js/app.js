/**
 * HRIS MVP - Main Application JavaScript
 * 
 * This file contains the main application logic and initialization.
 * It handles global functionality, error handling, and app-wide utilities.
 */

// Global application object
window.HRIS = window.HRIS || {};

// Application configuration
HRIS.config = {
    apiBaseUrl: '/api',
    sessionTimeout: 3600000, // 1 hour in milliseconds
    autoRefreshInterval: 300000, // 5 minutes in milliseconds
    maxRetries: 3,
    retryDelay: 1000,
    version: '1.0.0'
};

// Application state
HRIS.state = {
    user: null,
    isAuthenticated: false,
    currentPage: null,
    loading: false,
    errors: []
};

/**
 * Initialize the application
 */
HRIS.init = function() {
    console.log('Initializing HRIS MVP v' + HRIS.config.version);
    
    // Set up global error handling
    this.setupErrorHandling();
    
    // Initialize authentication check
    this.checkAuthentication();
    
    // Set up global event listeners
    this.setupEventListeners();
    
    // Initialize page-specific functionality
    this.initializePage();
    
    console.log('HRIS MVP initialized successfully');
};

/**
 * Set up global error handling
 */
HRIS.setupErrorHandling = function() {
    // Global error handler for unhandled JavaScript errors
    window.addEventListener('error', function(event) {
        console.error('Global error:', event.error);
        HRIS.showError('An unexpected error occurred. Please refresh the page and try again.');
    });
    
    // Global handler for unhandled promise rejections
    window.addEventListener('unhandledrejection', function(event) {
        console.error('Unhandled promise rejection:', event.reason);
        HRIS.showError('A network or processing error occurred. Please try again.');
        event.preventDefault();
    });
};

/**
 * Check if user is authenticated
 */
HRIS.checkAuthentication = function() {
    const token = localStorage.getItem('hris_token');
    const user = localStorage.getItem('hris_user');
    
    if (token && user) {
        try {
            HRIS.state.user = JSON.parse(user);
            HRIS.state.isAuthenticated = true;
            
            // Verify token is still valid
            this.verifyToken(token);
        } catch (error) {
            console.error('Error parsing user data:', error);
            this.logout();
        }
    } else {
        // Redirect to login if not on login page
        if (!window.location.pathname.includes('/login') && window.location.pathname !== '/' && window.location.pathname !== '/HRIS/' && window.location.pathname !== '/HRIS') {
            window.location.href = '/login';
        }
    }
};

/**
 * Verify authentication token
 */
HRIS.verifyToken = function(token) {
    fetch(HRIS.config.apiBaseUrl + '/auth/verify', {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Token verification failed');
        }
        return response.json();
    })
    .then(data => {
        if (!data.valid) {
            this.logout();
        }
    })
    .catch(error => {
        console.error('Token verification error:', error);
        this.logout();
    });
};

/**
 * Set up global event listeners
 */
HRIS.setupEventListeners = function() {
    // Handle logout buttons
    document.addEventListener('click', function(event) {
        if (event.target.id === 'logoutBtn' || event.target.closest('#logoutBtn')) {
            event.preventDefault();
            HRIS.logout();
        }
    });
    
    // Handle form submissions with loading states
    document.addEventListener('submit', function(event) {
        const form = event.target;
        if (form.tagName === 'FORM') {
            HRIS.handleFormSubmission(form, event);
        }
    });
    
    // Handle network status changes
    window.addEventListener('online', function() {
        HRIS.hideError();
        HRIS.showSuccess('Connection restored');
    });
    
    window.addEventListener('offline', function() {
        HRIS.showError('No internet connection. Some features may not work.');
    });
};

/**
 * Handle form submission with loading states
 */
HRIS.handleFormSubmission = function(form, event) {
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (submitButton) {
        // Show loading state
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.innerHTML = '<div class="loading-spinner"></div> Processing...';
        
        // Restore button state after a delay (will be overridden by actual response)
        setTimeout(() => {
            if (submitButton.disabled) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }, 10000); // 10 second timeout
    }
};

/**
 * Initialize page-specific functionality
 */
HRIS.initializePage = function() {
    const path = window.location.pathname;
    
    if (path.includes('dashboard')) {
        this.initializeDashboard();
    } else if (path.includes('employees')) {
        this.initializeEmployees();
    } else if (path.includes('attendance')) {
        this.initializeAttendance();
    } else if (path.includes('leave')) {
        this.initializeLeave();
    } else if (path.includes('reports')) {
        this.initializeReports();
    } else if (path.includes('announcements')) {
        this.initializeAnnouncements();
    }
};

/**
 * Initialize dashboard functionality
 */
HRIS.initializeDashboard = function() {
    console.log('Initializing dashboard');
    // Dashboard-specific initialization will be handled by dashboard components
};

/**
 * Initialize employees functionality
 */
HRIS.initializeEmployees = function() {
    console.log('Initializing employees module');
    // Employee-specific initialization
};

/**
 * Initialize attendance functionality
 */
HRIS.initializeAttendance = function() {
    console.log('Initializing attendance module');
    // Attendance-specific initialization
};

/**
 * Initialize leave functionality
 */
HRIS.initializeLeave = function() {
    console.log('Initializing leave module');
    // Leave-specific initialization
};

/**
 * Initialize reports functionality
 */
HRIS.initializeReports = function() {
    console.log('Initializing reports module');
    // Reports-specific initialization
};

/**
 * Initialize announcements functionality
 */
HRIS.initializeAnnouncements = function() {
    console.log('Initializing announcements module');
    // Announcements-specific initialization
};

/**
 * Logout user
 */
HRIS.logout = function() {
    // Clear local storage
    localStorage.removeItem('hris_token');
    localStorage.removeItem('hris_user');
    
    // Reset application state
    HRIS.state.user = null;
    HRIS.state.isAuthenticated = false;
    
    // Redirect to login page
    window.location.href = '/login';
};

/**
 * Show loading indicator
 */
HRIS.showLoading = function(message = 'Loading...') {
    HRIS.state.loading = true;
    const indicator = document.getElementById('loadingIndicator');
    if (indicator) {
        indicator.classList.remove('hidden');
        const messageEl = indicator.querySelector('h3');
        if (messageEl) {
            messageEl.textContent = message;
        }
    }
};

/**
 * Hide loading indicator
 */
HRIS.hideLoading = function() {
    HRIS.state.loading = false;
    const indicator = document.getElementById('loadingIndicator');
    if (indicator) {
        indicator.classList.add('hidden');
    }
};

/**
 * Show success message
 */
HRIS.showSuccess = function(message) {
    this.showMessage(message, 'success');
};

/**
 * Show error message
 */
HRIS.showError = function(message) {
    this.showMessage(message, 'error');
    HRIS.state.errors.push({
        message: message,
        timestamp: new Date().toISOString()
    });
};

/**
 * Show warning message
 */
HRIS.showWarning = function(message) {
    this.showMessage(message, 'warning');
};

/**
 * Show info message
 */
HRIS.showInfo = function(message) {
    this.showMessage(message, 'info');
};

/**
 * Show message with specified type
 */
HRIS.showMessage = function(message, type = 'info') {
    // Try to find existing message container
    let messageContainer = document.getElementById('messageContainer');
    
    // If no container exists, create one
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.id = 'messageContainer';
        messageContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(messageContainer);
    }
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `alert alert-${type} fade-in max-w-sm`;
    messageEl.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button class="ml-2 text-lg leading-none" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    // Add to container
    messageContainer.appendChild(messageEl);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (messageEl.parentElement) {
            messageEl.remove();
        }
    }, 5000);
};

/**
 * Hide all messages
 */
HRIS.hideMessages = function() {
    const messageContainer = document.getElementById('messageContainer');
    if (messageContainer) {
        messageContainer.innerHTML = '';
    }
};

/**
 * Hide error messages specifically
 */
HRIS.hideError = function() {
    const errorMessages = document.querySelectorAll('.alert-error');
    errorMessages.forEach(msg => msg.remove());
};

/**
 * Format date for display
 */
HRIS.formatDate = function(dateString, format = 'short') {
    if (!dateString) return '-';
    
    const date = new Date(dateString);
    
    if (format === 'short') {
        return date.toLocaleDateString();
    } else if (format === 'long') {
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } else if (format === 'time') {
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    } else if (format === 'datetime') {
        return date.toLocaleString();
    }
    
    return date.toLocaleDateString();
};

/**
 * Format time for display
 */
HRIS.formatTime = function(timeString) {
    if (!timeString) return '-';
    
    const time = new Date('1970-01-01T' + timeString);
    return time.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

/**
 * Debounce function for search inputs
 */
HRIS.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Get user role
 */
HRIS.getUserRole = function() {
    return HRIS.state.user ? HRIS.state.user.role : null;
};

/**
 * Check if user has specific role
 */
HRIS.hasRole = function(role) {
    const userRole = this.getUserRole();
    return userRole === role || userRole === 'super_admin';
};

/**
 * Check if user is admin
 */
HRIS.isAdmin = function() {
    return this.hasRole('admin') || this.hasRole('hr_manager') || this.hasRole('super_admin');
};

/**
 * Get current user ID
 */
HRIS.getCurrentUserId = function() {
    return HRIS.state.user ? HRIS.state.user.id : null;
};

// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    HRIS.init();
});

// Export for use in other modules
window.HRIS = HRIS;