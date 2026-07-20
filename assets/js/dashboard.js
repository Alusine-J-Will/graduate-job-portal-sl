/*
 * Dashboard JavaScript.
 * Purpose: small UI interactions for authenticated dashboard pages.
 */

document.addEventListener('DOMContentLoaded', function () {
    var sidebarToggle = document.getElementById('sidebarToggle');
    var body = document.body;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            body.classList.toggle('sidebar-collapsed');
        });
    }

    document.addEventListener('click', function (event) {
        var logoutElement = event.target.closest('.logout-confirm');
        if (!logoutElement) {
            return;
        }

        event.preventDefault();
        var confirmed = window.confirm('Are you sure you want to logout?');
        if (confirmed) {
            window.location.href = logoutElement.getAttribute('href');
        }
    });
});
