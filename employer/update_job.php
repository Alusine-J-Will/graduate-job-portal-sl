<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$employerStmt = $conn->prepare('SELECT company_id FROM employers WHERE user_id = ? LIMIT 1');
$employerStmt->bind_param('i', $userId);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

$companyId = $employer['company_id'] ?? null;
if (!$companyId) {
    $_SESSION['error'] = 'Unable to update jobs without a linked company profile.';
    redirect('profile.php');
}

$postData = [
    'job_id' => trim($_POST['job_id'] ?? ''),
    'title' => trim($_POST['title'] ?? ''),
    'category_id' => trim($_POST['category_id'] ?? ''),
    'employment_type' => trim($_POST['employment_type'] ?? ''),
    'work_mode' => trim($_POST['work_mode'] ?? ''),
    'location' => trim($_POST['location'] ?? ''),
    'min_salary' => trim($_POST['min_salary'] ?? ''),
    'max_salary' => trim($_POST['max_salary'] ?? ''),
    'deadline' => trim($_POST['deadline'] ?? ''),
    'vacancies' => trim($_POST['vacancies'] ?? ''),
    'experience_level' => trim($_POST['experience_level'] ?? ''),
    'education_level' => trim($_POST['education_level'] ?? ''),
    'skills' => trim($_POST['skills'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'responsibilities' => trim($_POST['responsibilities'] ?? ''),
    'requirements' => trim($_POST['requirements'] ?? ''),
    'benefits' => trim($_POST['benefits'] ?? ''),
    'status' => trim($_POST['status'] ?? ''),
];

$errors = [];

if (!ctype_digit($postData['job_id'])) {
    $errors[] = 'Invalid job ID.';
}

if ($postData['title'] === '' || mb_strlen($postData['title']) > 255) {
    $errors[] = 'Job title is required and must be less than 255 characters.';
}

if (!ctype_digit($postData['category_id'])) {
    $errors[] = 'Please select a valid job category.';
}

$validEmploymentTypes = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Temporary'];
if (!in_array($postData['employment_type'], $validEmploymentTypes, true)) {
    $errors[] = 'Please select a valid employment type.';
}

$validWorkModes = ['On-site', 'Remote', 'Hybrid'];
if (!in_array($postData['work_mode'], $validWorkModes, true)) {
    $errors[] = 'Please select a valid work mode.';
}

if ($postData['location'] === '' || mb_strlen($postData['location']) > 150) {
    $errors[] = 'Job location is required and must be less than 150 characters.';
}

if ($postData['min_salary'] !== '' && (!is_numeric($postData['min_salary']) || (float) $postData['min_salary'] < 0)) {
    $errors[] = 'Minimum salary must be a valid positive number.';
}

if ($postData['max_salary'] !== '' && (!is_numeric($postData['max_salary']) || (float) $postData['max_salary'] < 0)) {
    $errors[] = 'Maximum salary must be a valid positive number.';
}

if ($postData['min_salary'] !== '' && $postData['max_salary'] !== '' && (float) $postData['max_salary'] < (float) $postData['min_salary']) {
    $errors[] = 'Maximum salary cannot be lower than minimum salary.';
}

if ($postData['deadline'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $postData['deadline']) || strtotime($postData['deadline']) === false || strtotime($postData['deadline']) < strtotime(date('Y-m-d'))) {
    $errors[] = 'Please enter a valid future deadline date.';
}

if (!ctype_digit($postData['vacancies']) || (int) $postData['vacancies'] < 1) {
    $errors[] = 'Please enter a valid number of vacancies.';
}

$validExperienceLevels = ['Entry Level', 'Mid Level', 'Senior Level', 'Manager', 'Director'];
if (!in_array($postData['experience_level'], $validExperienceLevels, true)) {
    $errors[] = 'Please select a valid experience level.';
}

$validEducationLevels = ['High School', 'Diploma', 'Bachelor\'s Degree', 'Master\'s Degree', 'Doctorate', 'Any'];
if (!in_array($postData['education_level'], $validEducationLevels, true)) {
    $errors[] = 'Please select a valid education level.';
}

if ($postData['skills'] === '' || mb_strlen($postData['skills']) > 1000) {
    $errors[] = 'Required skills are required and must be less than 1000 characters.';
}

if ($postData['description'] === '' || mb_strlen($postData['description']) > 5000) {
    $errors[] = 'Job description is required and must be less than 5000 characters.';
}

if ($postData['responsibilities'] === '' || mb_strlen($postData['responsibilities']) > 5000) {
    $errors[] = 'Responsibilities are required and must be less than 5000 characters.';
}

if ($postData['requirements'] === '' || mb_strlen($postData['requirements']) > 5000) {
    $errors[] = 'Requirements are required and must be less than 5000 characters.';
}

if ($postData['benefits'] !== '' && mb_strlen($postData['benefits']) > 5000) {
    $errors[] = 'Benefits must be less than 5000 characters.';
}

$validStatuses = ['Draft', 'Open'];
if (!in_array($postData['status'], $validStatuses, true)) {
    $errors[] = 'Please select a valid job status.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    $_SESSION['edit_job_data'] = $postData;
    redirect('edit_job.php?job_id=' . urlencode($postData['job_id']));
}

$jobId = (int) $postData['job_id'];

$checkJobStmt = $conn->prepare('SELECT job_id FROM jobs WHERE job_id = ? AND company_id = ? LIMIT 1');
$checkJobStmt->bind_param('ii', $jobId, $companyId);
$checkJobStmt->execute();
$checkJobStmt->store_result();
if ($checkJobStmt->num_rows !== 1) {
    $checkJobStmt->close();
    $_SESSION['error'] = 'The selected job was not found or you do not have permission to edit it.';
    redirect('manage_jobs.php');
}
$checkJobStmt->close();

$salaryParts = [];
if ($postData['min_salary'] !== '') {
    $salaryParts[] = $postData['min_salary'];
}
if ($postData['max_salary'] !== '') {
    $salaryParts[] = $postData['max_salary'];
}
$salary = !empty($salaryParts) ? implode(' - ', $salaryParts) : '';

$conn->begin_transaction();

try {
    $updateStmt = $conn->prepare(
        'UPDATE jobs SET category_id = ?, title = ?, description = ?, requirements = ?, location = ?, employment_type = ?, experience_level = ?, work_mode = ?, salary = ?, vacancies = ?, education_level = ?, skills = ?, responsibilities = ?, benefits = ?, deadline = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE job_id = ? AND company_id = ?'
    );
    $updateStmt->bind_param(
        'issssssssississsii',
        $postData['category_id'],
        $postData['title'],
        $postData['description'],
        $postData['requirements'],
        $postData['location'],
        $postData['employment_type'],
        $postData['experience_level'],
        $postData['work_mode'],
        $salary,
        $postData['vacancies'],
        $postData['education_level'],
        $postData['skills'],
        $postData['responsibilities'],
        $postData['benefits'],
        $postData['deadline'],
        $postData['status'],
        $jobId,
        $companyId
    );

    if (!$updateStmt->execute() || $updateStmt->affected_rows < 1) {
        throw new Exception('Unable to update job posting.');
    }

    $updateStmt->close();
    $conn->commit();

    $_SESSION['success'] = 'Job updated successfully.';
    redirect('manage_jobs.php');
} catch (Exception $e) {
    $conn->rollback();
    error_log('Job update failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Unable to update job posting at this time. Please try again later.';
    $_SESSION['edit_job_data'] = $postData;
    redirect('edit_job.php?job_id=' . urlencode($jobId));
}
