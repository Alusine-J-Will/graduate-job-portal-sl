<?php
$termsModalContent = [
    'graduate' => [
        'title' => 'Graduate Terms',
        'sections' => [
            [
                'heading' => 'Graduate Account',
                'body' => 'By creating a GradConnect SL account, the graduate agrees to provide accurate and truthful information about themselves. The graduate is responsible for keeping their account information up to date and protecting their login credentials.'
            ],
            [
                'heading' => 'Profile Information',
                'body' => 'Graduates may provide information including: full name, contact information, university, degree/program, graduation year, skills, work experience, internship experience, volunteer experience, projects, final-year project/thesis, CV, and location. Graduates agree that information they intentionally provide for recruitment purposes may be accessible to authorized employers using GradConnect SL.'
            ],
            [
                'heading' => 'Profile Visibility and Recruitment',
                'body' => 'Information included in a graduate\'s recruitment profile may be viewed by authorized employers for the purpose of identifying, evaluating, and contacting suitable candidates. Account credentials such as passwords are private and must not be accessible to employers. GradConnect SL does not claim that every item in the graduate\'s account is publicly visible.'
            ],
            [
                'heading' => 'Job Applications',
                'body' => 'Graduates understand that submitting an application does not guarantee an interview, selection, a job offer, or employment. The final recruitment decision belongs to the employer. Graduates must provide truthful information in applications and must not submit fraudulent or misleading documents.'
            ],
            [
                'heading' => 'CVs and Supporting Documents',
                'body' => 'Graduates agree that CVs and other documents uploaded for recruitment purposes may be accessed by authorized employers reviewing their applications. Graduates should only upload documents that they are permitted to share.'
            ],
            [
                'heading' => 'Prohibited Graduate Activities',
                'body' => 'Graduates must not create fraudulent accounts, impersonate another person, submit false qualifications, upload fraudulent documents, misuse employer information, harass employers or other users, attempt unauthorized access, or use GradConnect SL for illegal activities.'
            ]
        ]
    ],
    'employer' => [
        'title' => 'Employer Terms',
        'sections' => [
            [
                'heading' => 'Employer Account',
                'body' => 'Employers agree to provide accurate and truthful information about their organization. This may include the organization/company name, industry, location, website, company description, and contact information. Employers are responsible for maintaining accurate organization information.'
            ],
            [
                'heading' => 'Genuine Job Opportunities',
                'body' => 'Employers must only post genuine employment opportunities. Job postings must accurately represent the available position. Employers must not use GradConnect SL to advertise fraudulent jobs, collect information for scams, mislead graduates, impersonate another organization, or advertise illegal activities.'
            ],
            [
                'heading' => 'Job Posting Responsibilities',
                'body' => 'Employers are responsible for ensuring that their job postings contain accurate information, including job title, job description, requirements, responsibilities, location, employment type, salary/compensation information, and application requirements. Employers should update or remove job postings when positions are no longer available.'
            ],
            [
                'heading' => 'Access to Graduate Information',
                'body' => 'Employers understand that GradConnect SL may provide them access to graduate recruitment information for legitimate hiring purposes. This may include graduate profile information, education, skills, experience, projects, CVs, application information, and other recruitment-related information intentionally provided by the graduate. Employers agree to use this information only for legitimate recruitment purposes.'
            ],
            [
                'heading' => 'Candidate Privacy',
                'body' => 'Employers must handle graduate information responsibly. Employers must not sell graduate information, misuse candidate information, unnecessarily share candidate information with unauthorized persons, use candidate information for unrelated purposes, or harass candidates.'
            ],
            [
                'heading' => 'Recruitment Decisions',
                'body' => 'Employers are responsible for their own recruitment and hiring decisions. GradConnect SL does not guarantee that an employer will hire a particular graduate or that an applicant will be successful.'
            ],
            [
                'heading' => 'Employer Account Approval',
                'body' => 'Employer accounts may be subject to administrator verification or approval before full employer functionality is available. Employers agree to provide genuine organization information during the verification process.'
            ],
            [
                'heading' => 'Prohibited Employer Activities',
                'body' => 'Employers must not post fraudulent vacancies, impersonate organizations, discriminate unlawfully, harass graduates, request inappropriate information from candidates, misuse CVs or candidate information, attempt unauthorized access, misuse the platform, or engage in illegal activities through GradConnect SL.'
            ]
        ]
    ]
];

$generalTerms = [
    ['heading' => 'Account Security', 'body' => 'Users are responsible for protecting their login credentials. Users should immediately report suspected unauthorized access to their account through the available GradConnect SL support/contact channel.'],
    ['heading' => 'Platform Use', 'body' => 'GradConnect SL is an online platform intended to connect graduates with employment opportunities. Users agree to use the platform responsibly and for legitimate employment-related purposes.'],
    ['heading' => 'Accuracy of Information', 'body' => 'Users are responsible for the accuracy of information they provide. GradConnect SL may take appropriate action where information is found to be fraudulent, misleading, or abusive.'],
    ['heading' => 'Account Suspension', 'body' => 'GradConnect SL administrators may suspend or deactivate accounts that violate these Terms & Conditions or misuse the platform.'],
    ['heading' => 'Platform Availability', 'body' => 'GradConnect SL may occasionally be unavailable because of maintenance, technical problems, or other circumstances.'],
    ['heading' => 'Changes to the Terms', 'body' => 'GradConnect SL may update these Terms & Conditions when necessary. Users should review the latest version when using the platform.'],
    ['heading' => 'Acceptance', 'body' => 'By checking the agreement checkbox during registration, the user confirms that they have read and agreed to the applicable GradConnect SL Terms & Conditions and Privacy Notice.']
];

$currentRole = $_SESSION['registration_role'] ?? 'graduate';
$roleTerms = $termsModalContent[$currentRole] ?? $termsModalContent['graduate'];
?>

<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">GradConnect SL Terms &amp; Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Welcome to GradConnect SL. These Terms &amp; Conditions set out the responsibilities of graduates, employers, and users of the platform. Please read them before accepting the registration agreement.</p>

                <div class="terms-role-section" data-role="graduate" hidden>
                    <h6 class="fw-bold mt-3">Graduate Terms</h6>
                    <?php foreach ($termsModalContent['graduate']['sections'] as $section): ?>
                        <div class="mb-3">
                            <h6 class="fw-semibold mb-1"><?php echo htmlspecialchars($section['heading'], ENT_QUOTES, 'UTF-8'); ?></h6>
                            <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($section['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="terms-role-section" data-role="employer" hidden>
                    <h6 class="fw-bold mt-3">Employer Terms</h6>
                    <?php foreach ($termsModalContent['employer']['sections'] as $section): ?>
                        <div class="mb-3">
                            <h6 class="fw-semibold mb-1"><?php echo htmlspecialchars($section['heading'], ENT_QUOTES, 'UTF-8'); ?></h6>
                            <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($section['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="terms-role-section" data-role="general" hidden>
                    <h6 class="fw-bold mt-4">General GradConnect SL Terms</h6>
                    <?php foreach ($generalTerms as $section): ?>
                        <div class="mb-3">
                            <h6 class="fw-semibold mb-1"><?php echo htmlspecialchars($section['heading'], ENT_QUOTES, 'UTF-8'); ?></h6>
                            <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($section['body'], ENT_QUOTES, 'UTF-8')); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privacyModalLabel">GradConnect SL Privacy Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">This Privacy Notice explains how GradConnect SL handles the information users provide while using the platform.</p>

                <div class="mb-3">
                    <h6 class="fw-semibold mb-1">Information We Collect</h6>
                    <p class="mb-0 text-muted">Depending on the account type, GradConnect SL may collect account information, profile information, education information, employment and experience information, CVs and supporting documents, job postings, applications, and company information.</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-semibold mb-1">How Information Is Used</h6>
                    <p class="mb-0 text-muted">Information is used to create and manage accounts, provide job-search and recruitment services, allow graduates to apply for jobs, allow employers to review applicants, communicate with users, provide notifications, and maintain and improve the platform.</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-semibold mb-1">Who Can Access Information</h6>
                    <p class="mb-0 text-muted">Graduate recruitment information may be accessible to authorized employers when it is provided for recruitment purposes. Employer and job information may be displayed to graduates as part of the recruitment service. Passwords and authentication credentials are not shared with employers or other users.</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-semibold mb-1">User Responsibility</h6>
                    <p class="mb-0 text-muted">Users are responsible for ensuring that information they upload is appropriate and that they have permission to share documents they provide.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
