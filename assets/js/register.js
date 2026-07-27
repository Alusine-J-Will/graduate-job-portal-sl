/*
 * Registration page interactions for both Graduate and Employer flows.
 * Handles account type switching, password strength, show/hide password,
 * confirm password validation, live email availability, and Bootstrap validation.
 */

function debounce(callback, delay) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), delay);
    };
}

function initCombinedRegistration() {
    const form = document.getElementById('registration-form');
    const roleSelect = document.getElementById('role');
    const graduateSection = document.getElementById('graduate-fields');
    const employerSection = document.getElementById('employer-fields');
    const emailInput = document.getElementById('email');
    const emailLabel = document.querySelector('label[for="email"]');
    const emailStatus = document.getElementById('email-status');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthFill = document.querySelector('.strength-fill');
    const strengthLabel = document.getElementById('password-strength-label-bottom');
    const requirementItems = document.querySelectorAll('[data-requirement]');
    const toggleButtons = document.querySelectorAll('.toggle-password');

    if (!form || !roleSelect || !emailInput || !passwordInput || !confirmPasswordInput) {
        return;
    }

    const graduateFields = [
        document.getElementById('first_name'),
        document.getElementById('last_name')
    ];

    const employerFields = [
        document.getElementById('full_name'),
        document.getElementById('company_name'),
        document.getElementById('industry'),
        document.getElementById('company_size'),
        document.getElementById('company_address')
    ];

    const requirements = {
        length: (value) => value.length >= 8,
        uppercase: (value) => /[A-Z]/.test(value),
        lowercase: (value) => /[a-z]/.test(value),
        number: (value) => /\d/.test(value),
        special: (value) => /[^A-Za-z0-9]/.test(value)
    };

    function setFieldState(fields, visible) {
        fields.forEach((field) => {
            if (!field) {
                return;
            }
            field.required = visible;
            field.disabled = !visible;
            if (!visible) {
                field.classList.remove('is-invalid', 'is-valid');
            }
        });
    }

    function updateFormType() {
        const isEmployer = roleSelect.value === 'employer';
        graduateSection.classList.toggle('d-none', isEmployer);
        employerSection.classList.toggle('d-none', !isEmployer);
        form.action = isEmployer ? 'employer_register_process.php' : 'graduate_register_process.php';
        setFieldState(graduateFields, !isEmployer);
        setFieldState(employerFields, isEmployer);
        if (emailLabel) {
            emailLabel.textContent = isEmployer ? 'Business Email' : 'Email Address';
        }
    }

    function updatePasswordRequirements(value) {
        let passed = 0;

        requirementItems.forEach((item) => {
            const requirement = item.dataset.requirement;
            const validator = requirements[requirement];
            if (!validator) {
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

    function validateField(field, valid) {
        if (!field) {
            return;
        }
        field.classList.toggle('is-invalid', !valid);
        field.classList.toggle('is-valid', valid);
    }

    function validatePasswordMatch() {
        if (!confirmPasswordInput || !passwordInput) {
            return;
        }
        const passwordsMatch = confirmPasswordInput.value.length > 0 && passwordInput.value === confirmPasswordInput.value;
        validateField(confirmPasswordInput, passwordsMatch);
        if (!passwordsMatch) {
            confirmPasswordInput.setCustomValidity('Passwords do not match.');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }

    function checkEmailAvailability(email) {
        if (!email || !email.includes('@')) {
            emailStatus.textContent = '';
            emailStatus.className = 'email-status';
            return;
        }

        fetch(`check_email.php?email=${encodeURIComponent(email)}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.available) {
                    emailStatus.textContent = '✓ Email available';
                    emailStatus.className = 'email-status success';
                } else {
                    emailStatus.textContent = '✖ Email already exists';
                    emailStatus.className = 'email-status error';
                }
            })
            .catch(() => {
                emailStatus.textContent = '⚠ Unable to verify email right now';
                emailStatus.className = 'email-status error';
            });
    }

    const debouncedEmailCheck = debounce(() => checkEmailAvailability(emailInput.value.trim()), 400);

    roleSelect.addEventListener('change', updateFormType);

    emailInput.addEventListener('input', () => {
        debouncedEmailCheck();
        validateField(emailInput, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim()));
    });

    passwordInput.addEventListener('input', function () {
        updatePasswordRequirements(this.value);
        validatePasswordMatch();
        const passwordValid = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(this.value);
        validateField(this, passwordValid);
    });

    confirmPasswordInput.addEventListener('input', validatePasswordMatch);

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

    form.addEventListener('submit', function (event) {
        let isValid = true;
        const visibleFields = form.querySelectorAll('input:not([disabled]), textarea:not([disabled]), select:not([disabled])');

        visibleFields.forEach((field) => {
            if (field.required) {
                const value = field.type === 'checkbox' ? field.checked : field.value.trim();
                const valid = value !== '';
                validateField(field, valid);
                if (!valid) {
                    isValid = false;
                }
            }
        });

        const passwordValid = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(passwordInput.value);
        const passwordsMatch = passwordInput.value === confirmPasswordInput.value && confirmPasswordInput.value.length > 0;

        if (!passwordValid) {
            validateField(passwordInput, false);
            isValid = false;
        }

        if (!passwordsMatch) {
            validateField(confirmPasswordInput, false);
            isValid = false;
        }

        if (emailStatus.classList.contains('error')) {
            isValid = false;
            validateField(emailInput, false);
        }

        if (!form.checkValidity()) {
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add('was-validated');
    });

    updateFormType();
}

document.addEventListener('DOMContentLoaded', initCombinedRegistration);
