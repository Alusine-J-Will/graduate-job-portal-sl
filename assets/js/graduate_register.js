/*
 * Graduate registration page interactions.
 * Handles password strength, toggle visibility, and live email availability checks.
 */

function debounce(callback, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), delay);
    };
}

function initGraduateRegistration() {
    const form = document.getElementById('graduate-register-form');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthFill = document.getElementById('password-strength-fill');
    const strengthLabel = document.getElementById('password-strength-label');
    const requirementItems = document.querySelectorAll('[data-requirement]');
    const emailInput = document.getElementById('email');
    const emailStatus = document.getElementById('email-status');
    const toggleButtons = document.querySelectorAll('.toggle-password');

    if (!form) {
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

        if (strengthFill) {
            strengthFill.style.width = `${score}%`;
            strengthFill.style.backgroundColor = color;
        }
        if (strengthLabel) {
            strengthLabel.textContent = `Strength: ${strength}`;
            strengthLabel.style.color = color;
        }
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            updatePasswordRequirements(this.value);
        });
    }

    toggleButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            if (!targetInput) {
                return;
            }
            const isPassword = targetInput.type === 'password';
            targetInput.type = isPassword ? 'text' : 'password';
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            }
        });
    });

    function validateField(input, valid) {
        input.classList.toggle('is-invalid', !valid);
        input.classList.toggle('is-valid', valid);
    }

    function validatePasswordMatch() {
        if (!confirmPasswordInput || !passwordInput) {
            return;
        }
        const isMatch = confirmPasswordInput.value.length > 0 && confirmPasswordInput.value === passwordInput.value;
        validateField(confirmPasswordInput, isMatch);
    }

    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    }

    async function checkEmailAvailability(email) {
        if (!email || !email.includes('@')) {
            emailStatus.textContent = '';
            emailStatus.className = 'email-status';
            return;
        }

        try {
            const response = await fetch(`check_email.php?email=${encodeURIComponent(email)}`);
            const data = await response.json();
            if (data.available) {
                emailStatus.textContent = '✓ Email available';
                emailStatus.className = 'email-status success';
            } else {
                emailStatus.textContent = '✖ Email already registered';
                emailStatus.className = 'email-status error';
            }
        } catch (error) {
            emailStatus.textContent = '⚠ Unable to verify email right now';
            emailStatus.className = 'email-status error';
        }
    }

    const validateEmail = debounce(function () {
        checkEmailAvailability(emailInput.value.trim());
    }, 400);

    if (emailInput) {
        emailInput.addEventListener('input', validateEmail);
    }

    form.addEventListener('submit', function (event) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach((field) => {
            const value = field.type === 'checkbox' ? field.checked : field.value.trim();
            if (!value) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        });

        if (passwordInput && confirmPasswordInput) {
            const passwordValue = passwordInput.value;
            const confirmValue = confirmPasswordInput.value;
            const passwordValid = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(passwordValue);
            const passwordsMatch = passwordValue === confirmValue && confirmValue.length > 0;
            if (!passwordValid) {
                isValid = false;
                passwordInput.classList.add('is-invalid');
            } else {
                passwordInput.classList.remove('is-invalid');
                passwordInput.classList.add('is-valid');
            }
            if (!passwordsMatch) {
                isValid = false;
                confirmPasswordInput.classList.add('is-invalid');
            } else {
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            }
        }

        if (!isValid) {
            event.preventDefault();
        }
    });
}

document.addEventListener('DOMContentLoaded', initGraduateRegistration);
