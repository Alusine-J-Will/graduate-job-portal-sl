/*
 * Main JavaScript file for the Graduate Job Portal project.
 * Purpose: Frontend interactivity and UI enhancements.
 */

function debounce(callback, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), delay);
    };
}

function initRegistrationEnhancements() {
    if (document.getElementById('registration-form') || document.getElementById('graduate-register-form')) {
        return;
    }

    const passwordInput = document.getElementById('password');
    const strengthFill = document.getElementById('password-strength-fill');
    const strengthLabel = document.getElementById('password-strength-label');
    const requirementItems = document.querySelectorAll('[data-requirement]');
    const emailInput = document.getElementById('email');
    const emailStatus = document.getElementById('email-status');

    if (!passwordInput || !strengthFill || !strengthLabel || !requirementItems.length) {
        return;
    }

    const requirements = {
        length: (value) => value.length >= 8,
        uppercase: (value) => /[A-Z]/.test(value),
        lowercase: (value) => /[a-z]/.test(value),
        number: (value) => /\d/.test(value),
        special: (value) => /[^A-Za-z0-9]/.test(value)
    };

    function updatePasswordRequirements(value) {
        let passed = 0;
        Object.entries(requirements).forEach(([key, validator]) => {
            const item = document.querySelector(`[data-requirement="${key}"]`);
            if (!item) {
                return;
            }
            const isValid = validator(value);
            item.classList.toggle('valid', isValid);
            const icon = item.querySelector('.requirement-icon');
            if (icon) {
                icon.textContent = isValid ? '✓' : '✖';
            }
            if (isValid) {
                passed += 1;
            }
        });

        const score = Math.round((passed / Object.keys(requirements).length) * 100);
        let strength = 'Weak';
        let color = '#dc3545';

        if (score >= 80) {
            strength = 'Strong';
            color = '#198754';
        } else if (score >= 60) {
            strength = 'Good';
            color = '#0d6efd';
        } else if (score >= 40) {
            strength = 'Fair';
            color = '#ffc107';
        }

        strengthFill.style.width = `${score}%`;
        strengthFill.style.backgroundColor = color;
        strengthLabel.textContent = `Strength: ${strength}`;
        strengthLabel.style.color = color;
    }

    passwordInput.addEventListener('input', function () {
        updatePasswordRequirements(this.value);
    });

    async function checkEmailAvailability(email) {
        if (!email || !email.includes('@')) {
            emailStatus.textContent = '';
            emailStatus.className = 'email-status';
            return;
        }

        const reserved = ['admin@example.com', 'test@example.com'];
        const isReserved = reserved.includes(email.toLowerCase());

        await new Promise((resolve) => setTimeout(resolve, 350));

        if (isReserved) {
            emailStatus.textContent = '✖ Email already exists';
            emailStatus.className = 'email-status error';
        } else {
            emailStatus.textContent = '✓ Email available';
            emailStatus.className = 'email-status success';
        }
    }

    const validateEmail = debounce(function () {
        checkEmailAvailability(emailInput.value.trim());
    }, 400);

    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
    }
}

document.addEventListener('DOMContentLoaded', initRegistrationEnhancements);
