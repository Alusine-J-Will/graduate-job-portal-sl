<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireRole('employer');
$conn = $GLOBALS['conn'];
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$employerStmt = $conn->prepare(
    'SELECT e.company_id
     FROM employers e
     WHERE e.user_id = ?
     LIMIT 1'
);
$employerStmt->bind_param('i', $userId);
$employerStmt->execute();
$employerResult = $employerStmt->get_result();
$employer = $employerResult->fetch_assoc();
$employerStmt->close();

$companyId = $employer['company_id'] ?? null;
if (!$companyId) {
    $_SESSION['error'] = 'Unable to create a job without a linked company profile. Please complete your company profile first.';
    redirect('profile.php');
}

$categoriesStmt = $conn->prepare('SELECT category_id, category_name FROM job_categories ORDER BY category_name ASC');
$categoriesStmt->execute();
$categoriesResult = $categoriesStmt->get_result();
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);
$categoriesStmt->close();

$formData = $_SESSION['post_job_data'] ?? [];
unset($_SESSION['post_job_data']);

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
                        <h1 class="h4 fw-bold mb-1">Post a New Job</h1>
                        <p class="text-muted mb-0">Create a job posting for your company and manage it from your employer dashboard.</p>
                    </div>
                    <a href="manage_jobs.php" class="btn btn-outline-custom">Manage Jobs</a>
                </div>

                <form id="post-job-form" method="post" action="post_job_process.php" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label fw-semibold">Job Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($formData['title'] ?? ''); ?>" placeholder="e.g. Software Engineer" required>
                            <div class="invalid-feedback">Job title is required and must be shorter than 255 characters.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold">Job Category</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>"<?php echo (isset($formData['category_id']) && $formData['category_id'] == $category['category_id']) ? ' selected' : ''; ?>><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a job category.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="employment_type" class="form-label fw-semibold">Employment Type</label>
                            <select class="form-select" id="employment_type" name="employment_type" required>
                                <option value="">Select employment type</option>
                                <?php
                                $employmentOptions = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Temporary'];
                                foreach ($employmentOptions as $option):
                                ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (isset($formData['employment_type']) && $formData['employment_type'] === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select an employment type.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="work_mode" class="form-label fw-semibold">Work Mode</label>
                            <select class="form-select" id="work_mode" name="work_mode" required>
                                <option value="">Select work mode</option>
                                <?php
                                $workModeOptions = ['On-site', 'Remote', 'Hybrid'];
                                foreach ($workModeOptions as $option):
                                ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (isset($formData['work_mode']) && $formData['work_mode'] === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a work mode.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="location" class="form-label fw-semibold">Job Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($formData['location'] ?? ''); ?>" placeholder="e.g. Freetown, Sierra Leone" required>
                            <div class="invalid-feedback">Job location is required and must be shorter than 150 characters.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="min_salary" class="form-label fw-semibold">Minimum Salary</label>
                            <input type="number" class="form-control" id="min_salary" name="min_salary" value="<?php echo htmlspecialchars($formData['min_salary'] ?? ''); ?>" min="0" placeholder="Optional">
                            <div class="invalid-feedback">Minimum salary must be a valid number.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="max_salary" class="form-label fw-semibold">Maximum Salary</label>
                            <input type="number" class="form-control" id="max_salary" name="max_salary" value="<?php echo htmlspecialchars($formData['max_salary'] ?? ''); ?>" min="0" placeholder="Optional">
                            <div class="invalid-feedback">Maximum salary must be a valid number.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="deadline" class="form-label fw-semibold">Application Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" value="<?php echo htmlspecialchars($formData['deadline'] ?? ''); ?>" required>
                            <div class="invalid-feedback">Please enter a valid deadline date.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="vacancies" class="form-label fw-semibold">Number of Vacancies</label>
                            <input type="number" class="form-control" id="vacancies" name="vacancies" value="<?php echo htmlspecialchars($formData['vacancies'] ?? ''); ?>" min="1" required>
                            <div class="invalid-feedback">Please enter the number of vacancies.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="experience_level" class="form-label fw-semibold">Experience Level</label>
                            <select class="form-select" id="experience_level" name="experience_level" required>
                                <option value="">Select experience level</option>
                                <?php
                                $experienceOptions = ['Entry Level', 'Mid Level', 'Senior Level', 'Manager', 'Director'];
                                foreach ($experienceOptions as $option):
                                ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (isset($formData['experience_level']) && $formData['experience_level'] === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select an experience level.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="education_level" class="form-label fw-semibold">Education Level</label>
                            <select class="form-select" id="education_level" name="education_level" required>
                                <option value="">Select education level</option>
                                <?php
                                $educationOptions = ['High School', 'Diploma', 'Bachelor\'s Degree', 'Master\'s Degree', 'Doctorate', 'Any'];
                                foreach ($educationOptions as $option):
                                ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"<?php echo (isset($formData['education_level']) && $formData['education_level'] === $option) ? ' selected' : ''; ?>><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select an education level.</div>
                        </div>

                        <div class="col-12">
                            <label for="skills" class="form-label fw-semibold">Required Skills</label>
                            <input type="text" class="form-control" id="skills" name="skills" value="<?php echo htmlspecialchars($formData['skills'] ?? ''); ?>" placeholder="Type a skill and press Enter" required>
                            <div class="form-text">Add skills such as PHP, MySQL, HTML and press Enter to create tags.</div>
                            <div class="invalid-feedback">Please list the required skills.</div>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label fw-semibold">Job Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Describe the role and responsibilities" required><?php echo htmlspecialchars($formData['description'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Job description is required.</div>
                        </div>
                        <div class="col-12">
                            <label for="responsibilities" class="form-label fw-semibold">Responsibilities</label>
                            <textarea class="form-control" id="responsibilities" name="responsibilities" rows="4" placeholder="Outline key responsibilities" required><?php echo htmlspecialchars($formData['responsibilities'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please describe the responsibilities.</div>
                        </div>
                        <div class="col-12">
                            <label for="requirements" class="form-label fw-semibold">Requirements</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4" placeholder="List the job requirements" required><?php echo htmlspecialchars($formData['requirements'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Please list the requirements.</div>
                        </div>
                        <div class="col-12">
                            <label for="benefits" class="form-label fw-semibold">Benefits <small class="text-muted">(optional)</small></label>
                            <textarea class="form-control" id="benefits" name="benefits" rows="3" placeholder="List benefits if available"><?php echo htmlspecialchars($formData['benefits'] ?? ''); ?></textarea>
                            <div class="invalid-feedback">Benefits must be shorter than 5000 characters.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Job Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Select status</option>
                                <option value="Draft"<?php echo (isset($formData['status']) && $formData['status'] === 'Draft') ? ' selected' : ''; ?>>Draft</option>
                                <option value="Open"<?php echo (isset($formData['status']) && $formData['status'] === 'Open') ? ' selected' : ''; ?>>Open</option>
                            </select>
                            <div class="invalid-feedback">Please choose a job status.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-custom mt-4">Publish Job</button>
                </form>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script>
(function () {
    'use strict';
    const form = document.getElementById('post-job-form');
    const skillsInput = document.getElementById('skills');

    if (skillsInput) {
        new Tagify(skillsInput, {
            delimiters: ',',
            keepInvalidTags: false,
            dropdown: {
                enabled: 0
            }
        });
    }

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
