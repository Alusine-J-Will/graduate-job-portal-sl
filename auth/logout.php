<?php
require_once '../config/config.php';

session_unset();
session_destroy();

session_start();
$_SESSION['success'] = 'You have been logged out.';

header('Location: login.php');
exit;
