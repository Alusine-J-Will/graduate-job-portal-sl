<?php
/**
 * Flash message helper.
 *
 * Displays Bootstrap alerts for session-based messages.
 */

/**
 * Render all flash messages.
 *
 * @return void
 */
function renderFlashMessages(): void
{
    if (!session_id()) {
        session_start();
    }

    $types = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
    ];

    foreach ($types as $key => $class) {
        if (!empty($_SESSION[$key])) {
            echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">'
                . htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8')
                . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                . '</div>';
            unset($_SESSION[$key]);
        }
    }
}
