<?php
/**
 * Main configuration bootstrap.
 *
 * Loads constants, session handling, and database connection in the correct order.
 */

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/email_config.php';
