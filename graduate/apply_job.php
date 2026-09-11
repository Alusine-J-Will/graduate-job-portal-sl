<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email_helper.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('graduate');
$pageTitle = 'Apply for Job';
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$graduateStmt = $conn->prepare('SELECT g.graduate_id, g.cv, u.full_name FROM graduates g INNER JOIN users u ON g.user_id = u.user_id WHERE g.user_id = ? LIMIT 1');
$graduateStmt->bind_param('i', $userId);
$graduateStmt->execute();
$graduateResult = $graduateStmt->get_result();
$graduate = $graduateResult->fetch_assoc();
$graduateStmt->close();

if (!$graduate) {
    $_SESSION['error'] = 'Please complete your graduate profile before applying.';
    redirect('profile.php');
}

$graduateId = (int) $graduate['graduate_id'];
$existingCv = $graduate['cv'] ?? null;

if (!columnExists($conn, 'applications', 'cover_letter_text')) {
    $conn->query('ALTER TABLE applications ADD COLUMN cover_letter_text TEXT NULL');
}

$jobId = isset($_GET['job_id']) ? (int) $_GET['job_id'] : (isset($_POST['job_id']) ? (int) $_POST['job_id'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($jobId <= 0) {
        $_SESSION['error'] = 'Invalid job selection.';
        redirect('jobs.php');
    }

    $coverLetter = trim($_POST['cover_letter'] ?? '');
    $uploadedCv = $_FILES['cv_file'] ?? null;
    $cvPath = null;

    if ($uploadedCv && isset($uploadedCv['tmp_name']) && $uploadedCv['tmp_name'] !== '') {
        if ($uploadedCv['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please upload a valid CV file.';
            redirect('apply_job.php?job_id=' . $jobId);
        }

        $uploadResult = uploadFile($uploadedCv, CV_UPLOAD_PATH, ['pdf', 'doc', 'docx'], MAX_FILE_SIZE);
        if (!$uploadResult['success']) {
            $_SESSION['error'] = $uploadResult['message'];
            redirect('apply_job.php?job_id=' . $jobId);
        }
        $cvPath = $uploadResult['path'];
    } elseif (!empty($existingCv)) {
        $cvPath = $existingCv;
    } else {
        $_SESSION['error'] = 'Please upload a CV before applying.';
        redirect('apply_job.php?job_id=' . $jobId);
    }

    $jobStmt = $conn->prepare('SELECT job_id, title, status, deadline FROM jobs WHERE job_id = ? LIMIT 1');
    $jobStmt->bind_param('i', $jobId);
    $jobStmt->execute();
    $jobResult = $jobStmt->get_result();
    $job = $jobResult->fetch_assoc();
    $jobStmt->close();

    if (!$job) {
        $_SESSION['error'] = 'The selected job could not be found.';
        redirect('jobs.php');
    }

    if (($job['status'] ?? '') !== 'Open') {
        $_SESSION['error'] = 'This job is not currently accepting applications.';
        redirect('job_details.php?job_id=' . $jobId);
    }

    if (!empty($job['deadline']) && strtotime($job['deadline']) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = 'The deadline for this job has already passed.';
        redirect('job_details.php?job_id=' . $jobId);
    }

    $duplicateStmt = $conn->prepare('SELECT application_id FROM applications WHERE graduate_id = ? AND job_id = ? LIMIT 1');
    $duplicateStmt->bind_param('ii', $graduateId, $jobId);
    $duplicateStmt->execute();
    $duplicateResult = $duplicateStmt->get_result();
    if ($duplicateResult->num_rows > 0) {
        $duplicateStmt->close();
        $_SESSION['error'] = 'You have already applied for this job.';
        redirect('my_applications.php');
    }
    $duplicateStmt->close();

    $companyEmployerStmt = $conn->prepare(
        'SELECT e.user_id
         FROM employers e
         INNER JOIN jobs j ON e.company_id = j.company_id
         WHERE j.job_id = ?
         LIMIT 1'
    );
    $companyEmployerStmt->bind_param('i', $jobId);
    $companyEmployerStmt->execute();
    $companyEmployerResult = $companyEmployerStmt->get_result();
    $companyEmployer = $companyEmployerResult->fetch_assoc();
    $companyEmployerStmt->close();

    $employerUserId = isset($companyEmployer['user_id']) ? (int) $companyEmployer['user_id'] : 0;

    $conn->begin_transaction();
    try {
        $insertStmt = $conn->prepare('INSERT INTO applications (graduate_id, job_id, cv_path, cover_letter_path, cover_letter_text, status, application_date, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
        $status = 'pending';
        $coverLetterPath = null;
        $coverLetterText = $coverLetter;
        $insertStmt->bind_param('iissss', $graduateId, $jobId, $cvPath, $coverLetterPath, $coverLetterText, $status);
        $insertStmt->execute();
        $applicationId = $insertStmt->insert_id;
        $insertStmt->close();

        if ($employerUserId > 0) {
            $notification = generateNotification(
                $employerUserId,
                'new_application',
                'New Applicant',
                sprintf('A new applicant has applied for your %s position.', $job['title']),
                BASE_URL . 'employer/application_details.php?application_id=' . $applicationId
            );

            if (!saveNotification($notification)) {
                throw new Exception('Unable to save employer notification.');
            }

            $employerEmailStmt = $conn->prepare('SELECT email, full_name FROM users WHERE user_id = ? LIMIT 1');
            if ($employerEmailStmt) {
                $employerEmailStmt->bind_param('i', $employerUserId);
                $employerEmailStmt->execute();
                $employerEmailResult = $employerEmailStmt->get_result();
                $employerAccount = $employerEmailResult->fetch_assoc();
                $employerEmailStmt->close();

                if (!empty($employerAccount['email'])) {
                    sendApplicationEmail(
                        $employerAccount['email'],
                        $employerAccount['full_name'] ?? 'Employer',
                        'new_application',
                        ['job_title' => $job['title'], 'company_name' => $job['company_name'] ?? '']
                    );
                }
            }
        }

        // After inserting application, check for high activity threshold and notify admins if needed
        $countStmt = $conn->prepare('SELECT COUNT(*) FROM applications WHERE job_id = ?');
        if ($countStmt) {
            $countStmt->bind_param('i', $jobId);
            $countStmt->execute();
            $countStmt->bind_result($appCount);
            $countStmt->fetch();
            $countStmt->close();

            if (isset($appCount) && (int) $appCount >= 20) {
                // avoid duplicate admin alerts for same job
                $notifType = 'admin_activity';
                $title = 'High Application Activity';
                $message = sprintf('The job "%s" has reached %d applications.', $job['title'], (int) $appCount);
                $link = BASE_URL . 'admin/jobs.php?job_id=' . $jobId;
                notifyAdmins($conn, $notifType, $title, $message, $link);
            }
        }

        $conn->commit();

        $_SESSION['success'] = 'Application submitted successfully.';
        redirect('my_applications.php');
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Application submission failed: ' . $e->getMessage());
        $_SESSION['error'] = 'Unable to submit your application right now.';
        redirect('apply_job.php?job_id=' . $jobId);
    }
}

$jobStmt = $conn->prepare('SELECT j.job_id, j.title, j.description, j.deadline, c.company_name FROM jobs j LEFT JOIN companies c ON j.company_id = c.company_id WHERE j.job_id = ? LIMIT 1');
$jobStmt->bind_param('i', $jobId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'The selected job could not be found.';
    redirect('jobs.php');
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/dashboard_topbar.php';
?>

<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
        </div>
        <div class="col-lg-9">
            <?php displayFlashMessages(); ?>

            <div class="card-ui p-4 bg-white mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Apply for <?php echo htmlspecialchars($job['title']); ?></h1>
                        <p class="text-muted mb-0">Submit your CV and a short cover letter for this opportunity.</p>
                    </div>
                    <a href="job_details.php?job_id=<?php echo (int) $job['job_id']; ?>" class="btn btn-outline-custom">Back to Job</a>
                </div>

                <div class="border rounded-4 p-3 bg-light mb-4">
                    <p class="fw-semibold mb-1"><?php echo htmlspecialchars($job['company_name'] ?: 'Company not listed'); ?></p>
                    <p class="text-muted mb-0">Deadline: <?php echo !empty($job['deadline']) ? date('d M Y', strtotime($job['deadline'])) : 'No deadline'; ?></p>
                </div>

                <form method="post" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">

                    <div class="col-12">
                        <label class="form-label fw-semibold">Select or Upload CV</label>
                        <?php if (!empty($existingCv)): ?>
                            <div class="alert alert-light border mb-2">
                                <strong>Current CV:</strong> <?php echo htmlspecialchars($existingCv); ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="cv_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <div class="form-text">Upload a new CV if you want to replace the current one. Otherwise your saved CV will be used.</div>
                    </div>

                    <div class="col-12">
                        <label for="cover_letter" class="form-label fw-semibold">Cover Letter</label>
                        <textarea class="form-control" id="cover_letter" name="cover_letter" rows="8" placeholder="Introduce yourself and explain why you're a strong fit for this role."></textarea>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary-custom">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
