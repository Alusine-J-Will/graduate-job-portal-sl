<?php
/**
 * Application constants.
 *
 * Define common settings for the application environment.
 */

define('PROJECT_NAME', 'Graduate Job Portal SL');
define('BASE_URL', '/graduate-job-portal-sl/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('CV_UPLOAD_PATH', UPLOAD_PATH . 'cv/');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profile_photos/');
define('LOGO_UPLOAD_PATH', UPLOAD_PATH . 'company_logos/');
define('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf,doc,docx');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

date_default_timezone_set('Africa/Freetown');

define('DB_HOST', 'localhost');
define('DB_NAME', 'graduate_job_portal_db');
define('DB_USER', 'root');
define('DB_PASS', '');
