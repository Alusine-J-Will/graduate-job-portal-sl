/*
 * Login page interactions.
 * Handles password visibility toggle and Bootstrap validation feedback.
 */

function initLoginPage() {
    const form = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const toggleButtons = document.querySelectorAll('.toggle-password');

    if (!form) {
        return;
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

    form.addEventListener('submit', function (event) {
        let isValid = true;

        [emailInput, passwordInput].forEach((field) => {
            if (!field) {
                return;
            }

            const value = field.value.trim();
            const isEmailField = field.type === 'email';
            const valid = isEmailField ? /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) : value.length > 0;

            field.classList.toggle('is-invalid', !valid);
            field.classList.toggle('is-valid', valid && value.length > 0);

            if (!valid) {
                isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault();
        }
    });
}

document.addEventListener('DOMContentLoaded', initLoginPage);
