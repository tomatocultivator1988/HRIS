/**
 * Utility Functions for HRIS MVP
 * Common helper functions used throughout the application
 */

class Utils {
    /**
     * Navigate to a page with proper base path handling
     * Uses AppConfig for centralized path management
     * @param {string} path - Path relative to HRIS root (e.g., 'dashboard/admin', 'employees')
     */
    static navigateTo(path) {
        if (window.AppConfig) {
            window.AppConfig.navigate(path);
        } else {
            // Fallback if config not loaded
            console.warn('AppConfig not loaded, using fallback navigation');
            window.location.href = path;
        }
    }
    
    /**
     * Get API path with proper base path handling
     * Uses AppConfig for centralized path management
     * @param {string} apiPath - API endpoint path (e.g., 'api/auth/login.php' or 'auth/login.php')
     * @returns {string} Full API URL
     */
    static getApiPath(apiPath) {
        if (window.AppConfig) {
            // Remove 'api/' prefix if present since AppConfig.apiUrl adds it
            apiPath = apiPath.replace(/^api\/+/, '');
            return window.AppConfig.apiUrl(apiPath);
        }
        
        // Fallback
        const currentPath = window.location.pathname;
        apiPath = apiPath.replace(/^\/+/, '');
        
        if (currentPath.includes('/dashboard/') || currentPath.includes('/modules/')) {
            return '../' + apiPath;
        }
        
        return apiPath;
    }

    /**
     * Format date for display
     * @param {string|Date} date - Date to format
     * @param {string} format - Format type ('short', 'long', 'time', 'datetime')
     * @returns {string} Formatted date
     */
    static formatDate(date, format = 'short') {
        if (!date) return '';
        
        const d = new Date(date);
        
        if (isNaN(d.getTime())) return '';
        
        const options = {
            short: { year: 'numeric', month: 'short', day: 'numeric' },
            long: { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' },
            time: { hour: '2-digit', minute: '2-digit' },
            datetime: { 
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            }
        };
        
        return d.toLocaleString('en-US', options[format] || options.short);
    }
    
    /**
     * Format time for display
     * @param {string|Date} time - Time to format
     * @returns {string} Formatted time
     */
    static formatTime(time) {
        if (!time) return '';
        
        const d = new Date(time);
        if (isNaN(d.getTime())) return '';
        
        return d.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }
    
    /**
     * Calculate duration between two times
     * @param {string|Date} startTime - Start time
     * @param {string|Date} endTime - End time
     * @returns {string} Duration in hours and minutes
     */
    static calculateDuration(startTime, endTime) {
        if (!startTime || !endTime) return '';
        
        const start = new Date(startTime);
        const end = new Date(endTime);
        
        if (isNaN(start.getTime()) || isNaN(end.getTime())) return '';
        
        const diffMs = end - start;
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        
        if (diffHours === 0) {
            return `${diffMinutes}m`;
        }
        
        return `${diffHours}h ${diffMinutes}m`;
    }
    
    /**
     * Validate email format
     * @param {string} email - Email to validate
     * @returns {boolean} True if valid
     */
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Validate phone number format
     * @param {string} phone - Phone number to validate
     * @returns {boolean} True if valid
     */
    static isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }
    
    /**
     * Sanitize HTML content
     * @param {string} html - HTML to sanitize
     * @returns {string} Sanitized HTML
     */
    static sanitizeHTML(html) {
        const div = document.createElement('div');
        div.textContent = html;
        return div.innerHTML;
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
    
    /**
     * Throttle function calls
     * @param {Function} func - Function to throttle
     * @param {number} limit - Time limit in milliseconds
     * @returns {Function} Throttled function
     */
    static throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    /**
     * Generate random ID
     * @param {number} length - Length of ID
     * @returns {string} Random ID
     */
    static generateId(length = 8) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }
    
    /**
     * Copy text to clipboard
     * @param {string} text - Text to copy
     * @returns {Promise<boolean>} Success status
     */
    static async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            const success = document.execCommand('copy');
            document.body.removeChild(textArea);
            return success;
        }
    }
    
    /**
     * Download data as file
     * @param {string} data - Data to download
     * @param {string} filename - File name
     * @param {string} type - MIME type
     */
    static downloadFile(data, filename, type = 'text/plain') {
        const blob = new Blob([data], { type });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    
    /**
     * Parse CSV data
     * @param {string} csv - CSV string
     * @returns {Array} Parsed data
     */
    static parseCSV(csv) {
        const lines = csv.split('\n');
        const headers = lines[0].split(',').map(h => h.trim());
        const data = [];
        
        for (let i = 1; i < lines.length; i++) {
            if (lines[i].trim()) {
                const values = lines[i].split(',').map(v => v.trim());
                const row = {};
                headers.forEach((header, index) => {
                    row[header] = values[index] || '';
                });
                data.push(row);
            }
        }
        
        return data;
    }
    
    /**
     * Convert array to CSV
     * @param {Array} data - Data array
     * @returns {string} CSV string
     */
    static arrayToCSV(data) {
        if (!data.length) return '';
        
        const headers = Object.keys(data[0]);
        const csvContent = [
            headers.join(','),
            ...data.map(row => 
                headers.map(header => {
                    const value = row[header] || '';
                    return typeof value === 'string' && value.includes(',') 
                        ? `"${value}"` 
                        : value;
                }).join(',')
            )
        ].join('\n');
        
        return csvContent;
    }
    
    /**
     * Get query parameters from URL
     * @returns {Object} Query parameters
     */
    static getQueryParams() {
        const params = {};
        const searchParams = new URLSearchParams(window.location.search);
        
        for (const [key, value] of searchParams) {
            params[key] = value;
        }
        
        return params;
    }
    
    /**
     * Update URL with query parameters
     * @param {Object} params - Parameters to add/update
     * @param {boolean} replace - Whether to replace current history entry
     */
    static updateQueryParams(params, replace = false) {
        const url = new URL(window.location);
        
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        
        if (replace) {
            window.history.replaceState({}, '', url);
        } else {
            window.history.pushState({}, '', url);
        }
    }
    
    /**
     * Format file size
     * @param {number} bytes - File size in bytes
     * @returns {string} Formatted size
     */
    static formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    /**
     * Check if device is mobile
     * @returns {boolean} True if mobile
     */
    static isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    
    /**
     * Get browser information
     * @returns {Object} Browser info
     */
    static getBrowserInfo() {
        const ua = navigator.userAgent;
        let browser = 'Unknown';
        
        if (ua.includes('Chrome')) browser = 'Chrome';
        else if (ua.includes('Firefox')) browser = 'Firefox';
        else if (ua.includes('Safari')) browser = 'Safari';
        else if (ua.includes('Edge')) browser = 'Edge';
        else if (ua.includes('Opera')) browser = 'Opera';
        
        return {
            name: browser,
            userAgent: ua,
            isMobile: this.isMobile()
        };
    }
    
    /**
     * Scroll to element smoothly
     * @param {string|Element} element - Element or selector
     * @param {Object} options - Scroll options
     */
    static scrollTo(element, options = {}) {
        const target = typeof element === 'string' 
            ? document.querySelector(element) 
            : element;
            
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
                ...options
            });
        }
    }
    
    /**
     * Create loading overlay
     * @param {string} message - Loading message
     * @returns {HTMLElement} Overlay element
     */
    static createLoadingOverlay(message = 'Loading...') {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-white p-6 rounded-lg flex items-center space-x-3">
                <div class="loading-spinner"></div>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(overlay);
        return overlay;
    }
    
    /**
     * Remove loading overlay
     * @param {HTMLElement} overlay - Overlay element to remove
     */
    static removeLoadingOverlay(overlay) {
        if (overlay && overlay.parentElement) {
            overlay.remove();
        }
    }
    
    /**
     * Show confirmation dialog
     * @param {string} message - Confirmation message
     * @param {string} title - Dialog title
     * @returns {Promise<boolean>} User confirmation
     */
    static async confirm(message, title = 'Confirm') {
        return new Promise((resolve) => {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content max-w-md">
                    <h3 class="text-lg font-semibold mb-4">${title}</h3>
                    <p class="text-gray-600 mb-6">${message}</p>
                    <div class="flex justify-end space-x-3">
                        <button class="btn btn-outline cancel-btn">Cancel</button>
                        <button class="btn btn-primary confirm-btn">Confirm</button>
                    </div>
                </div>
            `;
            
            const cancelBtn = modal.querySelector('.cancel-btn');
            const confirmBtn = modal.querySelector('.confirm-btn');
            
            cancelBtn.addEventListener('click', () => {
                modal.remove();
                resolve(false);
            });
            
            confirmBtn.addEventListener('click', () => {
                modal.remove();
                resolve(true);
            });
            
            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    resolve(false);
                }
            });
            
            document.body.appendChild(modal);
            confirmBtn.focus();
        });
    }
    
    /**
     * Show alert dialog
     * @param {string} message - Alert message
     * @param {string} title - Dialog title
     * @param {string} type - Alert type (info, success, warning, error)
     * @returns {Promise<void>}
     */
    static async alert(message, title = 'Alert', type = 'info') {
        return new Promise((resolve) => {
            const typeClasses = {
                info: 'text-blue-600',
                success: 'text-green-600',
                warning: 'text-yellow-600',
                error: 'text-red-600'
            };
            
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content max-w-md">
                    <h3 class="text-lg font-semibold mb-4 ${typeClasses[type] || typeClasses.info}">${title}</h3>
                    <p class="text-gray-600 mb-6">${message}</p>
                    <div class="flex justify-end">
                        <button class="btn btn-primary ok-btn">OK</button>
                    </div>
                </div>
            `;
            
            const okBtn = modal.querySelector('.ok-btn');
            
            okBtn.addEventListener('click', () => {
                modal.remove();
                resolve();
            });
            
            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                    resolve();
                }
            });
            
            // Close on Escape key
            const handleKeydown = (e) => {
                if (e.key === 'Escape') {
                    modal.remove();
                    document.removeEventListener('keydown', handleKeydown);
                    resolve();
                }
            };
            
            document.addEventListener('keydown', handleKeydown);
            document.body.appendChild(modal);
            okBtn.focus();
        });
    }
}

// Make Utils available globally
window.Utils = Utils;