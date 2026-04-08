/**
 * Token Manager - Handles JWT token lifecycle
 * 
 * Features:
 * - Auto-refresh tokens before expiration
 * - Token expiration detection
 * - Automatic logout on expiration
 * - Warning notifications before expiration
 */

class TokenManager {
    constructor() {
        this.refreshInterval = null;
        this.warningShown = false;
        this.refreshThreshold = 10 * 60; // Refresh when 10 minutes remaining
        this.warningThreshold = 5 * 60;  // Show warning at 5 minutes remaining
        this.checkInterval = 60 * 1000;  // Check every minute
    }

    /**
     * Initialize token manager
     */
    init() {
        console.log('TokenManager: Initializing...');
        this.startMonitoring();
    }

    /**
     * Start monitoring token expiration
     */
    startMonitoring() {
        // Check immediately
        this.checkToken();
        
        // Then check every minute
        this.refreshInterval = setInterval(() => {
            this.checkToken();
        }, this.checkInterval);
        
        console.log('TokenManager: Monitoring started');
    }

    /**
     * Stop monitoring
     */
    stopMonitoring() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
            console.log('TokenManager: Monitoring stopped');
        }
    }

    /**
     * Check token and handle expiration
     */
    async checkToken() {
        const token = this.getToken();
        
        if (!token) {
            console.log('TokenManager: No token found');
            this.handleExpiredToken();
            return;
        }

        const tokenData = this.decodeToken(token);
        
        if (!tokenData || !tokenData.exp) {
            console.log('TokenManager: Invalid token format');
            this.handleExpiredToken();
            return;
        }

        const now = Math.floor(Date.now() / 1000);
        const timeRemaining = tokenData.exp - now;
        const minutesRemaining = Math.floor(timeRemaining / 60);

        console.log(`TokenManager: Token expires in ${minutesRemaining} minutes`);

        // Token already expired
        if (timeRemaining <= 0) {
            console.log('TokenManager: Token expired');
            this.handleExpiredToken();
            return;
        }

        // Show warning if approaching expiration
        if (timeRemaining <= this.warningThreshold && !this.warningShown) {
            this.showExpirationWarning(minutesRemaining);
            this.warningShown = true;
        }

        // Auto-refresh if within threshold
        if (timeRemaining <= this.refreshThreshold) {
            console.log('TokenManager: Attempting auto-refresh...');
            await this.refreshToken();
        }
    }

    /**
     * Decode JWT token
     */
    decodeToken(token) {
        try {
            const parts = token.split('.');
            if (parts.length !== 3) {
                return null;
            }

            const payload = parts[1];
            const decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
            return JSON.parse(decoded);
        } catch (error) {
            console.error('TokenManager: Error decoding token:', error);
            return null;
        }
    }

    /**
     * Get token from localStorage
     */
    getToken() {
        return localStorage.getItem('hris_token') || localStorage.getItem('access_token');
    }

    /**
     * Refresh token via API
     */
    async refreshToken() {
        try {
            const refreshToken = localStorage.getItem('hris_refresh_token');
            
            if (!refreshToken) {
                console.log('TokenManager: No refresh token available');
                this.handleExpiredToken();
                return false;
            }

            console.log('TokenManager: Refreshing token...');

            const response = await fetch(AppConfig.getApiUrl('/auth/refresh'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    refresh_token: refreshToken
                })
            });

            const result = await response.json();

            if (result.success && result.data && result.data.access_token) {
                // Update token in localStorage
                localStorage.setItem('hris_token', result.data.access_token);
                
                // Update refresh token if new one provided
                if (result.data.refresh_token) {
                    localStorage.setItem('hris_refresh_token', result.data.refresh_token);
                }
                
                // Update user data if provided
                if (result.data.user) {
                    localStorage.setItem('hris_user', JSON.stringify(result.data.user));
                }

                console.log('TokenManager: Token refreshed successfully');
                this.warningShown = false; // Reset warning flag
                
                // Show success notification
                this.showNotification('Session extended successfully', 'success');
                
                return true;
            } else {
                console.error('TokenManager: Token refresh failed:', result.message);
                
                // If refresh fails, handle as expired
                this.handleExpiredToken();
                return false;
            }
        } catch (error) {
            console.error('TokenManager: Error refreshing token:', error);
            
            // On error, try to continue but log the issue
            // Don't immediately logout as it might be a network issue
            return false;
        }
    }

    /**
     * Handle expired token
     */
    handleExpiredToken() {
        console.log('TokenManager: Handling expired token');
        
        // Stop monitoring
        this.stopMonitoring();
        
        // Clear storage
        localStorage.removeItem('hris_token');
        localStorage.removeItem('hris_refresh_token');
        localStorage.removeItem('access_token');
        localStorage.removeItem('hris_user');
        localStorage.removeItem('user');
        
        // Show notification
        this.showNotification('Your session has expired. Please login again.', 'error');
        
        // Redirect to login after a short delay
        setTimeout(() => {
            window.location.href = AppConfig.getBaseUrl('/login');
        }, 2000);
    }

    /**
     * Show expiration warning
     */
    showExpirationWarning(minutesRemaining) {
        const message = `Your session will expire in ${minutesRemaining} minute(s). Please save your work.`;
        this.showNotification(message, 'warning', 10000); // Show for 10 seconds
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info', duration = 5000) {
        // Check if showToast function exists (from utils.js or page-specific)
        if (typeof showToast === 'function') {
            showToast(message, type);
            return;
        }

        // Fallback: Create simple notification
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-600' : 
                       type === 'error' ? 'bg-red-600' : 
                       type === 'warning' ? 'bg-yellow-600' : 'bg-blue-600';
        
        notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md`;
        notification.innerHTML = `
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, duration);
    }

    /**
     * Manually trigger token refresh
     */
    async manualRefresh() {
        console.log('TokenManager: Manual refresh triggered');
        return await this.refreshToken();
    }

    /**
     * Get token expiration info
     */
    getTokenInfo() {
        const token = this.getToken();
        
        if (!token) {
            return null;
        }

        const tokenData = this.decodeToken(token);
        
        if (!tokenData || !tokenData.exp) {
            return null;
        }

        const now = Math.floor(Date.now() / 1000);
        const timeRemaining = tokenData.exp - now;
        const minutesRemaining = Math.floor(timeRemaining / 60);

        return {
            expiresAt: new Date(tokenData.exp * 1000),
            issuedAt: new Date(tokenData.iat * 1000),
            timeRemaining: timeRemaining,
            minutesRemaining: minutesRemaining,
            isExpired: timeRemaining <= 0,
            needsRefresh: timeRemaining <= this.refreshThreshold
        };
    }
}

// Create global instance
window.tokenManager = new TokenManager();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.tokenManager.init();
    });
} else {
    window.tokenManager.init();
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    window.tokenManager.stopMonitoring();
});

console.log('TokenManager: Script loaded');
