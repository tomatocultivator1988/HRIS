/**
 * Form Validation Library for HRIS MVP
 * Provides comprehensive form validation with real-time feedback
 */

class FormValidator {
    constructor(formElement, rules = {}) {
        this.form = typeof formElement === 'string' 
            ? document.querySelector(formElement) 
            : formElement;
        this.rules = rules;
        this.errors = {};
        this.isValid = true;
        
        if (this.form) {
            this.init();
        }
    }
    
    /**
     * Initialize form validation
     */
    init() {
        // Add event listeners for real-time validation
        Object.keys(this.rules).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                // Validate on blur
                field.addEventListener('blur', () => {
                    this.validateField(fieldName);
                });
                
                // Clear errors on input (for better UX)
                field.addEventListener('input', () => {
                    this.clearFieldError(fieldName);
                });
            }
        });
        
        // Validate on form submit
        this.form.addEventListener('submit', (e) => {
            if (!this.validateAll()) {
                e.preventDefault();
                this.focusFirstError();
            }
        });
    }
    
    /**
     * Validate a single field
     * @param {string} fieldName - Field name to validate
     * @returns {boolean} True if valid
     */
    validateField(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        const rule = this.rules[fieldName];
        
        if (!field || !rule) return true;
        
        const value = this.getFieldValue(field);
        const errors = [];
        
        // Required validation
        if (rule.required && this.isEmpty(value)) {
            errors.push(rule.messages?.required || `${rule.label || fieldName} is required`);
        }
        
        // Skip other validations if field is empty and not required
        if (!rule.required && this.isEmpty(value)) {
            this.clearFieldError(fieldName);
            return true;
        }
        
        // Type validations
        if (rule.type) {
            switch (rule.type) {
                case 'email':
                    if (!this.isValidEmail(value)) {
                        errors.push(rule.messages?.email || 'Please enter a valid email address');
                    }
                    break;
                case 'phone':
                    if (!this.isValidPhone(value)) {
                        errors.push(rule.messages?.phone || 'Please enter a valid phone number');
                    }
                    break;
                case 'number':
                    if (!this.isValidNumber(value)) {
                        errors.push(rule.messages?.number || 'Please enter a valid number');
                    }
                    break;
                case 'date':
                    if (!this.isValidDate(value)) {
                        errors.push(rule.messages?.date || 'Please enter a valid date');
                    }
                    break;
            }
        }
        
        // Length validations
        if (rule.minLength && value.length < rule.minLength) {
            errors.push(rule.messages?.minLength || `Minimum ${rule.minLength} characters required`);
        }
        
        if (rule.maxLength && value.length > rule.maxLength) {
            errors.push(rule.messages?.maxLength || `Maximum ${rule.maxLength} characters allowed`);
        }
        
        // Numeric validations
        if (rule.min !== undefined) {
            const numValue = parseFloat(value);
            if (!isNaN(numValue) && numValue < rule.min) {
                errors.push(rule.messages?.min || `Minimum value is ${rule.min}`);
            }
        }
        
        if (rule.max !== undefined) {
            const numValue = parseFloat(value);
            if (!isNaN(numValue) && numValue > rule.max) {
                errors.push(rule.messages?.max || `Maximum value is ${rule.max}`);
            }
        }
        
        // Pattern validation
        if (rule.pattern) {
            const regex = typeof rule.pattern === 'string' 
                ? new RegExp(rule.pattern) 
                : rule.pattern;
            
            if (!regex.test(value)) {
                errors.push(rule.messages?.pattern || 'Invalid format');
            }
        }
        
        // Custom validation
        if (rule.custom && typeof rule.custom === 'function') {
            const customResult = rule.custom(value, this.getFormData());
            if (customResult !== true) {
                errors.push(typeof customResult === 'string' ? customResult : 'Invalid value');
            }
        }
        
        // Confirmation field validation
        if (rule.confirm) {
            const confirmField = this.form.querySelector(`[name="${rule.confirm}"]`);
            if (confirmField && value !== this.getFieldValue(confirmField)) {
                errors.push(rule.messages?.confirm || 'Values do not match');
            }
        }
        
        // Update field validation state
        if (errors.length > 0) {
            this.showFieldError(fieldName, errors[0]);
            this.errors[fieldName] = errors;
            return false;
        } else {
            this.clearFieldError(fieldName);
            delete this.errors[fieldName];
            return true;
        }
    }
    
    /**
     * Validate all fields
     * @returns {boolean} True if all fields are valid
     */
    validateAll() {
        this.errors = {};
        this.isValid = true;
        
        Object.keys(this.rules).forEach(fieldName => {
            if (!this.validateField(fieldName)) {
                this.isValid = false;
            }
        });
        
        return this.isValid;
    }
    
    /**
     * Get field value
     * @param {HTMLElement} field - Field element
     * @returns {string} Field value
     */
    getFieldValue(field) {
        if (field.type === 'checkbox') {
            return field.checked ? field.value : '';
        } else if (field.type === 'radio') {
            const checked = this.form.querySelector(`[name="${field.name}"]:checked`);
            return checked ? checked.value : '';
        } else {
            return field.value.trim();
        }
    }
    
    /**
     * Get all form data
     * @returns {Object} Form data
     */
    getFormData() {
        const data = {};
        const formData = new FormData(this.form);
        
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }
    
    /**
     * Show field error
     * @param {string} fieldName - Field name
     * @param {string} message - Error message
     */
    showFieldError(fieldName, message) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        // Add error class to field
        field.classList.add('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
        field.classList.remove('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
        
        // Show error message
        let errorElement = this.form.querySelector(`#${fieldName}-error`);
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.id = `${fieldName}-error`;
            errorElement.className = 'form-error mt-1 text-sm text-red-600';
            
            // Insert after field or field container
            const container = field.closest('.form-group') || field.parentElement;
            container.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    }
    
    /**
     * Clear field error
     * @param {string} fieldName - Field name
     */
    clearFieldError(fieldName) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        // Remove error classes
        field.classList.remove('border-red-300', 'focus:ring-red-500', 'focus:border-red-500');
        field.classList.add('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
        
        // Hide error message
        const errorElement = this.form.querySelector(`#${fieldName}-error`);
        if (errorElement) {
            errorElement.classList.add('hidden');
        }
        
        // Remove from errors object
        delete this.errors[fieldName];
    }
    
    /**
     * Focus first field with error
     */
    focusFirstError() {
        const firstErrorField = Object.keys(this.errors)[0];
        if (firstErrorField) {
            const field = this.form.querySelector(`[name="${firstErrorField}"]`);
            if (field) {
                field.focus();
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
    
    /**
     * Clear all errors
     */
    clearAllErrors() {
        Object.keys(this.errors).forEach(fieldName => {
            this.clearFieldError(fieldName);
        });
        this.errors = {};
    }
    
    /**
     * Check if value is empty
     * @param {string} value - Value to check
     * @returns {boolean} True if empty
     */
    isEmpty(value) {
        return value === null || value === undefined || value === '';
    }
    
    /**
     * Validate email format
     * @param {string} email - Email to validate
     * @returns {boolean} True if valid
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    /**
     * Validate phone number
     * @param {string} phone - Phone to validate
     * @returns {boolean} True if valid
     */
    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
    }
    
    /**
     * Validate number
     * @param {string} value - Value to validate
     * @returns {boolean} True if valid
     */
    isValidNumber(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }
    
    /**
     * Validate date
     * @param {string} date - Date to validate
     * @returns {boolean} True if valid
     */
    isValidDate(date) {
        const d = new Date(date);
        return d instanceof Date && !isNaN(d.getTime());
    }
    
    /**
     * Get validation errors
     * @returns {Object} Errors object
     */
    getErrors() {
        return { ...this.errors };
    }
    
    /**
     * Check if form is valid
     * @returns {boolean} True if valid
     */
    isFormValid() {
        return Object.keys(this.errors).length === 0;
    }
    
    /**
     * Add custom validation rule
     * @param {string} fieldName - Field name
     * @param {Object} rule - Validation rule
     */
    addRule(fieldName, rule) {
        this.rules[fieldName] = rule;
        
        // Add event listeners for new field
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.addEventListener('blur', () => {
                this.validateField(fieldName);
            });
            
            field.addEventListener('input', () => {
                this.clearFieldError(fieldName);
            });
        }
    }
    
    /**
     * Remove validation rule
     * @param {string} fieldName - Field name
     */
    removeRule(fieldName) {
        delete this.rules[fieldName];
        this.clearFieldError(fieldName);
    }
}

/**
 * Common validation rules
 */
const ValidationRules = {
    required: (label) => ({
        required: true,
        label: label
    }),
    
    email: (label, required = true) => ({
        required: required,
        type: 'email',
        label: label
    }),
    
    phone: (label, required = true) => ({
        required: required,
        type: 'phone',
        label: label
    }),
    
    password: (label, minLength = 8) => ({
        required: true,
        type: 'password',
        minLength: minLength,
        label: label,
        pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/,
        messages: {
            pattern: 'Password must contain at least one uppercase letter, one lowercase letter, and one number'
        }
    }),
    
    confirmPassword: (label, passwordField = 'password') => ({
        required: true,
        label: label,
        confirm: passwordField,
        messages: {
            confirm: 'Passwords do not match'
        }
    }),
    
    employeeId: (label) => ({
        required: true,
        label: label,
        pattern: /^[A-Z0-9]{3,20}$/,
        messages: {
            pattern: 'Employee ID must be 3-20 characters, letters and numbers only'
        }
    }),
    
    name: (label, minLength = 2, maxLength = 50) => ({
        required: true,
        label: label,
        minLength: minLength,
        maxLength: maxLength,
        pattern: /^[a-zA-Z\s\-'\.]+$/,
        messages: {
            pattern: 'Name can only contain letters, spaces, hyphens, apostrophes, and periods'
        }
    }),
    
    date: (label, required = true) => ({
        required: required,
        type: 'date',
        label: label
    }),
    
    futureDate: (label) => ({
        required: true,
        type: 'date',
        label: label,
        custom: (value) => {
            const selectedDate = new Date(value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            return selectedDate >= today || 'Date must be today or in the future';
        }
    }),
    
    pastDate: (label) => ({
        required: true,
        type: 'date',
        label: label,
        custom: (value) => {
            const selectedDate = new Date(value);
            const today = new Date();
            
            return selectedDate <= today || 'Date must be today or in the past';
        }
    }),
    
    number: (label, min, max) => ({
        required: true,
        type: 'number',
        label: label,
        min: min,
        max: max
    }),
    
    select: (label, options = []) => ({
        required: true,
        label: label,
        custom: (value) => {
            return options.includes(value) || 'Please select a valid option';
        }
    })
};

// Make classes available globally
window.FormValidator = FormValidator;
window.ValidationRules = ValidationRules;