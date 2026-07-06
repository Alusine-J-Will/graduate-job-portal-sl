<?php
/**
 * Database connection bootstrap.
 *
 * Creates a secure MySQLi connection using constants defined in constants.php.
 */

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/constants.php';
}

$connection = mysqli_init();

mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

if (!mysqli_real_connect(
    $connection,
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME
)) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    die('Database connection error. Please try again later.');
}

$conn = $connection;
