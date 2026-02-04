// Form validation and enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if(passwordInput) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.id = 'password-strength';
        strengthIndicator.className = 'mt-1 small';
        passwordInput.parentNode.appendChild(strengthIndicator);
        
        passwordInput.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updatePasswordStrengthIndicator(strength);
        });
    }
    
    // Form submission confirmation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if(submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Mobile number validation
    const mobileInput = document.getElementById('mobile');
    if(mobileInput) {
        mobileInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    }
    
    // Portfolio link validation
    const portfolioInput = document.getElementById('portfolio_link');
    if(portfolioInput) {
        portfolioInput.addEventListener('blur', function() {
            if(this.value && !isValidURL(this.value)) {
                this.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Please enter a valid URL (e.g., https://example.com)';
                if(!this.nextElementSibling || !this.nextElementSibling.classList.contains('invalid-feedback')) {
                    this.parentNode.appendChild(feedback);
                }
            } else {
                this.classList.remove('is-invalid');
                const invalidFeedback = this.parentNode.querySelector('.invalid-feedback');
                if(invalidFeedback) {
                    invalidFeedback.remove();
                }
            }
        });
    }
});

function checkPasswordStrength(password) {
    let strength = 0;
    
    if(password.length >= 8) strength++;
    if(/[A-Z]/.test(password)) strength++;
    if(/[0-9]/.test(password)) strength++;
    if(/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

function updatePasswordStrengthIndicator(strength) {
    const indicator = document.getElementById('password-strength');
    if(!indicator) return;
    
    const texts = ['Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
    const colors = ['danger', 'danger', 'warning', 'success', 'success'];
    const messages = [
        'Minimum 6 characters with mixed case, numbers, and symbols',
        'Add uppercase letters and numbers',
        'Add a special character for better security',
        'Good password!',
        'Excellent password!'
    ];
    
    indicator.textContent = messages[strength] || '';
    indicator.className = `mt-1 small text-${colors[strength] || 'danger'}`;
}

function isValidURL(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

// Confirmation for delete actions
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to perform this action?');
}

// Copy to clipboard function
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}