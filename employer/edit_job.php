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
    $_SESSION['error'] = 'Unable to edit jobs without a linked company profile.';
    redirect('profile.php');
}

$jobId = $_GET['job_id'] ?? null;
if (!ctype_digit((string) $jobId)) {
    $_SESSION['error'] = 'Invalid job ID.';
    redirect('manage_jobs.php');
}

$jobId = (int) $jobId;

$jobStmt = $conn->prepare(
    'SELECT job_id, company_id, category_id, title, description, requirements, location, employment_type, experience_level, work_mode, salary, salary_type, salary_amount, salary_period, vacancies, education_level, skills, responsibilities, benefits, deadline, status
     FROM jobs
     WHERE job_id = ? AND company_id = ?
     LIMIT 1'
);
$jobStmt->bind_param('ii', $jobId, $companyId);
$jobStmt->execute();
$jobResult = $jobStmt->get_result();
$job = $jobResult->fetch_assoc();
$jobStmt->close();

if (!$job) {
    $_SESSION['error'] = 'The selected job was not found or you do not have permission to edit it.';
    redirect('manage_jobs.php');
}

$categoriesStmt = $conn->prepare('SELECT category_id, category_name FROM job_categories ORDER BY category_name ASC');
$categoriesStmt->execute();
$categoriesResult = $categoriesStmt->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStmt->close();

$formData = $_SESSION['edit_job_data'] ?? [];
unset($_SESSION['edit_job_data']);

if (!empty($formData)) {
    $job = array_merge($job, $formData);
}

$salaryState = parseJobSalaryState($job['salary'] ?? null, $job['salary_type'] ?? null, $job['salary_amount'] ?? null, $job['salary_period'] ?? null);
$job['salary_type'] = $salaryState['type'];
$job['salary_amount'] = $salaryState['amount'];
$job['salary_period'] = $salaryState['period'];

$deadlineValue = !empty($job['deadline']) ? date('Y-m-d', strtotime($job['deadline'])) : '';

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
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="h4 fw-bold mb-1">Edit Job</h1>
                        <p class="text-muted mb-0">Update the selected job posting and keep your listings current.</p>
                    </div>
                    <a href="manage_jobs.php" class="btn btn-outline-custom">Back to Manage Jobs</a>
                </div>

                <form method="post" action="update_job.php" novalidate>
                    <input type="hidden" name="job_id" value="<?php echo (int) $job['job_id']; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label fw-semibold">Job Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($job['title'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Job Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>"<?php echo ((string) ($job['category_id'] ?? '') === (string) $category['category_id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="employment_type" class="form-label fw-semibold">Employment Type</label>
                            <select class="form-select" id="employment_type" name="employment_type" required>
                                <option value="">Select employment type</option>
                                <?php $employmentOptions = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Temporary']; foreach ($employmentOptions as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (($job['employment_type'] ?? '') === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="work_mode" class="form-label fw-semibold">Work Mode</label>
                            <select class="form-select" id="work_mode" name="work_mode" required>
                                <option value="">Select work mode</option>
                                <?php $workModeOptions = ['On-site', 'Remote', 'Hybrid']; foreach ($workModeOptions as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (($job['work_mode'] ?? '') === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label fw-semibold">Job Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($job['location'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Salary / Compensation</label>
                            <div class="d-flex flex-wrap gap-3 mt-2">
                                <?php $salaryTypeOptions = ['negotiable' => 'Negotiable', 'competitive' => 'Competitive', 'not_disclosed' => 'Not disclosed', 'fixed' => 'Fixed amount']; $selectedSalaryType = isset($formData['salary_type']) ? $formData['salary_type'] : ($job['salary_type'] ?? 'negotiable'); foreach ($salaryTypeOptions as $value => $label): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="salary_type" id="salary_type_<?php echo htmlspecialchars($value); ?>" value="<?php echo htmlspecialchars($value); ?>" <?php echo ($selectedSalaryType === $value) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="salary_type_<?php echo htmlspecialchars($value); ?>"><?php echo htmlspecialchars($label); ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6" id="salary-fixed-fields" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label for="salary_amount" class="form-label fw-semibold">Salary Amount</label>
                                    <input type="number" class="form-control" id="salary_amount" name="salary_amount" value="<?php echo htmlspecialchars(isset($formData['salary_amount']) ? $formData['salary_amount'] : ($job['salary_amount'] ?? '')); ?>" min="0" step="0.01" placeholder="e.g. 8000">
                                </div>
                                <div class="col-md-5">
                                    <label for="salary_period" class="form-label fw-semibold">Salary Period</label>
                                    <select class="form-select" id="salary_period" name="salary_period">
                                        <option value="">Select</option>
                                        <option value="monthly"<?php echo ((isset($formData['salary_period']) ? $formData['salary_period'] : ($job['salary_period'] ?? '')) === 'monthly') ? ' selected' : ''; ?>>Monthly</option>
                                        <option value="annual"<?php echo ((isset($formData['salary_period']) ? $formData['salary_period'] : ($job['salary_period'] ?? '')) === 'annual') ? ' selected' : ''; ?>>Annual</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="deadline" class="form-label fw-semibold">Application Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo htmlspecialchars($deadlineValue); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="vacancies" class="form-label fw-semibold">Number of Vacancies</label>
                            <input type="number" class="form-control" id="vacancies" name="vacancies" value="<?php echo htmlspecialchars((string) ($job['vacancies'] ?? '')); ?>" min="1" required>
                        </div>

                        <div class="col-md-6">
                            <label for="experience_level" class="form-label fw-semibold">Experience Level</label>
                            <select class="form-select" id="experience_level" name="experience_level" required>
                                <option value="">Select experience level</option>
                                <?php $experienceOptions = ['Entry Level', 'Mid Level', 'Senior Level', 'Manager', 'Director']; foreach ($experienceOptions as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (($job['experience_level'] ?? '') === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="education_level" class="form-label fw-semibold">Education Level</label>
                            <select class="form-select" id="education_level" name="education_level" required>
                                <option value="">Select education level</option>
                                <?php $educationOptions = ['High School', 'Diploma', 'Bachelor\'s Degree', 'Master\'s Degree', 'Doctorate', 'Any']; foreach ($educationOptions as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (($job['education_level'] ?? '') === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="skills" class="form-label fw-semibold">Required Skills</label>
                            <textarea class="form-control" id="skills" name="skills" rows="3" required><?php echo htmlspecialchars($job['skills'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Job Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($job['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="responsibilities" class="form-label fw-semibold">Responsibilities</label>
                            <textarea class="form-control" id="responsibilities" name="responsibilities" rows="4" required><?php echo htmlspecialchars($job['responsibilities'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="requirements" class="form-label fw-semibold">Requirements</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4" required><?php echo htmlspecialchars($job['requirements'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label for="benefits" class="form-label fw-semibold">Benefits <small class="text-muted">(optional)</small></label>
                            <textarea class="form-control" id="benefits" name="benefits" rows="3"><?php echo htmlspecialchars($job['benefits'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Job Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Select status</option>
                                <option value="Draft"<?php echo (($job['status'] ?? '') === 'Draft') ? ' selected' : ''; ?>>Draft</option>
                                <option value="Open"<?php echo (($job['status'] ?? '') === 'Open') ? ' selected' : ''; ?>>Open</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom mt-4">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const fixedFields = document.getElementById('salary-fixed-fields');
    const salaryInputs = document.querySelectorAll('input[name="salary_type"]');
    const salaryAmount = document.getElementById('salary_amount');
    const salaryPeriod = document.getElementById('salary_period');

    function toggleSalaryFields() {
        const selected = document.querySelector('input[name="salary_type"]:checked');
        const isFixed = selected && selected.value === 'fixed';
        if (!fixedFields) {
            return;
        }

        fixedFields.style.display = isFixed ? 'block' : 'none';
        if (salaryAmount) {
            salaryAmount.disabled = !isFixed;
            salaryAmount.required = isFixed;
            if (!isFixed) {
                salaryAmount.value = '';
            }
        }
        if (salaryPeriod) {
            salaryPeriod.disabled = !isFixed;
            salaryPeriod.required = isFixed;
            if (!isFixed) {
                salaryPeriod.value = '';
            }
        }
    }

    salaryInputs.forEach((input) => input.addEventListener('change', toggleSalaryFields));
    toggleSalaryFields();
})();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
