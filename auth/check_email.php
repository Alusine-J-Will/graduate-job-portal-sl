<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_GET['email'])) {
    http_response_code(400);
    echo json_encode(['available' => false, 'message' => 'Invalid email.']);
    exit;
}

$email = trim($_GET['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['available' => false, 'message' => 'Invalid email format.']);
    exit;
}

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['available' => false, 'message' => 'Email already registered.']);
} else {
    echo json_encode(['available' => true, 'message' => 'Email available.']);
}

$stmt->close();
