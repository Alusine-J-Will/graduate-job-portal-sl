-- ============================================================
-- Graduate Job Portal for Sierra Leone
-- MySQL Database Schema
-- Author: Final Year Computer Science Project
-- ============================================================

-- ------------------------------------------------------------
-- 1. Drop database if it already exists
-- ------------------------------------------------------------
DROP DATABASE IF EXISTS graduate_job_portal_db;

-- ------------------------------------------------------------
-- 2. Create database
-- ------------------------------------------------------------
CREATE DATABASE graduate_job_portal_db
CHARACTER
SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. Use the database
-- ------------------------------------------------------------
USE graduate_job_portal_db;

-- ------------------------------------------------------------
-- 4. Create all tables
-- ------------------------------------------------------------

CREATE TABLE users
(
    user_id INT
    AUTO_INCREMENT,
    full_name VARCHAR
    (150) NOT NULL,
    email VARCHAR
    (150) NOT NULL,
    phone VARCHAR
    (30) DEFAULT NULL,
    password VARCHAR
    (255) NOT NULL,
    role ENUM
    ('graduate', 'employer', 'admin') NOT NULL,
    status ENUM
    ('pending', 'active', 'inactive') NOT NULL DEFAULT 'pending',
    terms_accepted TINYINT(1) NOT NULL DEFAULT 0,
    terms_accepted_at DATETIME DEFAULT NULL,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    email_verification_token CHAR(64) DEFAULT NULL,
    email_verification_expires DATETIME DEFAULT NULL,
    email_verification_sent_at DATETIME DEFAULT NULL,
    password_reset_token CHAR(64) DEFAULT NULL,
    password_reset_expires DATETIME DEFAULT NULL,
    password_reset_sent_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
    UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY
    (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE graduates
    (
        graduate_id INT
        AUTO_INCREMENT,
    user_id INT NOT NULL,
    location VARCHAR
        (150) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    cv VARCHAR
        (255) DEFAULT NULL,
    profile_picture VARCHAR
        (255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
        UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY
        (graduate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE education
        (
            education_id INT
            AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    institution VARCHAR
            (255) NOT NULL,
    degree VARCHAR
            (150) NOT NULL,
    field_of_study VARCHAR
            (150) DEFAULT NULL,
    graduation_year YEAR DEFAULT NULL,
    grade VARCHAR
            (50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
            (education_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE experience
            (
                experience_id INT
                AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    organization VARCHAR
                (255) NOT NULL,
    position VARCHAR
                (150) NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
                (experience_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE projects
                (
                    project_id INT
                    AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    title VARCHAR
                    (255) NOT NULL,
    description TEXT DEFAULT NULL,
    technologies VARCHAR
                    (255) DEFAULT NULL,
    completion_date DATE DEFAULT NULL,
    project_link VARCHAR
                    (255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
                    (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                    CREATE TABLE skills
                    (
                        skill_id INT
                        AUTO_INCREMENT,
    skill_name VARCHAR
                        (100) NOT NULL,
    PRIMARY KEY
                        (skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                        CREATE TABLE graduate_skills
                        (
                            graduate_id INT NOT NULL,
                            skill_id INT NOT NULL,
                            PRIMARY KEY (graduate_id, skill_id)
                        )
                        ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                        CREATE TABLE companies
                        (
                            company_id INT
                            AUTO_INCREMENT,
    company_name VARCHAR
                            (255) NOT NULL,
    logo VARCHAR
                            (255) DEFAULT NULL,
    industry VARCHAR
                            (150) DEFAULT NULL,
    company_size VARCHAR
                            (100) DEFAULT NULL,
    location VARCHAR
                            (150) DEFAULT NULL,
    website VARCHAR
                            (255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    founded_year YEAR DEFAULT NULL,
    verification_status ENUM
                            ('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    verified_by INT DEFAULT NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
                            UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY
                            (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                            CREATE TABLE employers
                            (
                                employer_id INT
                                AUTO_INCREMENT,
    user_id INT NOT NULL,
    company_id INT DEFAULT NULL,
    job_title VARCHAR
                                (150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
                                (employer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                CREATE TABLE job_categories
                                (
                                    category_id INT
                                    AUTO_INCREMENT,
    category_name VARCHAR
                                    (100) NOT NULL,
    PRIMARY KEY
                                    (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                    CREATE TABLE jobs
                                    (
                                        job_id INT
                                        AUTO_INCREMENT,
    company_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR
                                        (255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT DEFAULT NULL,
    location VARCHAR
                                        (150) NOT NULL,
    employment_type VARCHAR
                                        (100) DEFAULT NULL,
    experience_level VARCHAR
                                        (100) DEFAULT NULL,
    work_mode VARCHAR
                                        (100) DEFAULT NULL,
    salary VARCHAR
                                        (100) DEFAULT NULL,
    salary_type ENUM
                                        ('negotiable', 'competitive', 'not_disclosed', 'fixed') NOT NULL DEFAULT 'negotiable',
    salary_amount DECIMAL
                                        (12, 2) DEFAULT NULL,
    salary_period ENUM
                                        ('monthly', 'annual') DEFAULT NULL,
    vacancies INT DEFAULT NULL,
    education_level VARCHAR
                                        (100) DEFAULT NULL,
    skills TEXT DEFAULT NULL,
    responsibilities TEXT DEFAULT NULL,
    benefits TEXT DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    status ENUM
                                        ('Draft', 'Open', 'Closed', 'Expired') NOT NULL DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
                                        UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY
                                        (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                        CREATE TABLE applications
                                        (
                                            application_id INT
                                            AUTO_INCREMENT,
    graduate_id INT NOT NULL,
    job_id INT NOT NULL,
    cv_path VARCHAR
                                            (255) NOT NULL,
    cover_letter_path VARCHAR
                                            (255) DEFAULT NULL,
    application_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM
                                            ('pending', 'under_review', 'shortlisted', 'interview_scheduled', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    interview_date DATE DEFAULT NULL,
    interview_time TIME DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
                                            UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY
                                            (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                            CREATE TABLE notifications
                                            (
                                                notification_id INT
                                                AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR
                                                (100) DEFAULT NULL,
    title VARCHAR
                                                (255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR
                                                (255) DEFAULT NULL,
    is_read TINYINT
                                                (1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
                                                (notification_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                                CREATE TABLE saved_jobs
                                                (
                                                    graduate_id INT NOT NULL,
                                                    job_id INT NOT NULL,
                                                    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                    PRIMARY KEY (graduate_id, job_id)
                                                )
                                                ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                                CREATE TABLE activity_logs
                                                (
                                                    log_id INT
                                                    AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity TEXT NOT NULL,
    ip_address VARCHAR
                                                    (45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
                                                    (log_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                                    CREATE TABLE password_resets
                                                    (
                                                        reset_id INT
                                                        AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR
                                                        (255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT
                                                        (1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY
                                                        (reset_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                                                        -- ------------------------------------------------------------
                                                        -- 5. Create primary keys
                                                        -- ------------------------------------------------------------
                                                        -- All tables already declare their primary keys during creation.
                                                        -- Composite primary keys are defined for junction tables above.

                                                        -- ------------------------------------------------------------
                                                        -- 6. Create foreign keys
                                                        -- ------------------------------------------------------------
                                                        ALTER TABLE graduates
    ADD CONSTRAINT fk_graduates_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE;

                                                        ALTER TABLE education
    ADD CONSTRAINT fk_education_graduate
    FOREIGN KEY (graduate_id) REFERENCES graduates(graduate_id)
    ON DELETE CASCADE ON UPDATE CASCADE;

                                                        ALTER TABLE experience
    ADD CONSTRAINT fk_experience_graduate
    FOREIGN KEY (graduate_id) REFERENCES graduates(graduate_id)
    ON DELETE CASCADE ON UPDATE CASCADE;

                                                        ALTER TABLE projects
    ADD CONSTRAINT fk_projects_graduate
    FOREIGN KEY (graduate_id) REFERENCES graduates(graduate_id)
    ON DELETE CASCADE ON UPDATE CASCADE;

                                                        ALTER TABLE graduate_skills
    ADD CONSTRAINT fk_graduate_skills_graduate
    FOREIGN KEY (graduate_id) REFERENCES graduates(graduate_id)
    ON DELETE CASCADE ON UPDATE CASCADE
                                                        ,
                                                        ADD CONSTRAINT fk_graduate_skills_skill
    FOREIGN KEY
                                                        (skill_id) REFERENCES skills
                                                        (skill_id)
    ON
                                                        DELETE CASCADE ON
                                                        UPDATE CASCADE;

                                                        ALTER TABLE companies
    ADD CONSTRAINT fk_companies_verified_by
    FOREIGN KEY (verified_by) REFERENCES users(user_id)
    ON DELETE SET NULL ON UPDATE CASCADE;

                                                        ALTER TABLE employers
    ADD CONSTRAINT fk_employers_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE
                                                        ,
                                                        ADD CONSTRAINT fk_employers_company
    FOREIGN KEY
                                                        (company_id) REFERENCES companies
                                                        (company_id)
    ON
                                                        DELETE
                                                        SET NULL
                                                        ON
                                                        UPDATE CASCADE;

                                                        ALTER TABLE jobs
    ADD CONSTRAINT fk_jobs_company
    FOREIGN KEY (company_id) REFERENCES companies(company_id)
    ON DELETE CASCADE ON UPDATE CASCADE
                                                        ,
                                                        ADD CONSTRAINT fk_jobs_category
    FOREIGN KEY
                                                        (category_id) REFERENCES job_categories
                                                        (category_id)
    ON
                                                        DELETE RESTRICT ON
                                                        UPDATE CASCADE;

                                                        ALTER TABLE applications
    ADD CONSTRAINT fk_applications_graduate
    FOREIGN KEY (graduate_id) REFERENCES graduates(graduate_id)
    ON DELETE CASCADE ON UPDATE CASCADE
                                                        ,
                                                        ADD CONSTRAINT fk_applications_job
    FOREIGN KEY
                                                        (job_id) REFERENCES jobs
                                                        (job_id)
    ON
                                                        DELETE CASCADE ON
                                                        UPDATE CASCADE;

                                                        ALTER TABLE notifications
    ADD CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE;

                                                        ALTER TABLE saved_jobs
    ADD CONSTRAINT fk_saved_jobs_graduate
    FOREIGN KEY (graduate_id) REFERENCES graduates(graduate_id)
    ON DELETE CASCADE ON UPDATE CASCADE
                                                        ,
                                                        ADD CONSTRAINT fk_saved_jobs_job
    FOREIGN KEY
                                                        (job_id) REFERENCES jobs
                                                        (job_id)
    ON
                                                        DELETE CASCADE ON
                                                        UPDATE CASCADE;

                                                        ALTER TABLE activity_logs
    ADD CONSTRAINT fk_activity_logs_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE;

                                                        ALTER TABLE password_resets
    ADD CONSTRAINT fk_password_resets_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE;

                                                        -- ------------------------------------------------------------
                                                        -- 7. Create indexes
                                                        -- ------------------------------------------------------------
                                                        CREATE INDEX idx_users_email ON users(email);
                                                        CREATE INDEX idx_users_role ON users(role);
                                                        CREATE INDEX idx_companies_company_name ON companies(company_name);
                                                        CREATE INDEX idx_jobs_title ON jobs(title);
                                                        CREATE INDEX idx_jobs_category_id ON jobs(category_id);
                                                        CREATE INDEX idx_jobs_location ON jobs(location);
                                                        CREATE INDEX idx_jobs_deadline ON jobs(deadline);
                                                        CREATE INDEX idx_jobs_status ON jobs(status);
                                                        CREATE INDEX idx_education_graduation_year ON education(graduation_year);
                                                        CREATE INDEX idx_applications_status ON applications(status);
                                                        CREATE INDEX idx_notifications_is_read ON notifications(is_read);
                                                        CREATE INDEX idx_saved_jobs_job_id ON saved_jobs(job_id);

                                                        -- ------------------------------------------------------------
                                                        -- 8. Create unique constraints
                                                        -- ------------------------------------------------------------
                                                        ALTER TABLE users
    ADD CONSTRAINT uq_users_email UNIQUE (email);

                                                        ALTER TABLE job_categories
    ADD CONSTRAINT uq_job_categories_name UNIQUE (category_name);

                                                        ALTER TABLE skills
    ADD CONSTRAINT uq_skills_name UNIQUE (skill_name);

                                                        ALTER TABLE applications
    ADD CONSTRAINT uq_applications_graduate_job UNIQUE (graduate_id, job_id);

                                                        -- ------------------------------------------------------------
                                                        -- 9. Insert lookup data
                                                        -- ------------------------------------------------------------
                                                        INSERT INTO job_categories
                                                            (category_name)
                                                        VALUES
                                                            ('Information Technology'),
                                                            ('Banking'),
                                                            ('Finance'),
                                                            ('Engineering'),
                                                            ('Healthcare'),
                                                            ('Education'),
                                                            ('Agriculture'),
                                                            ('NGO'),
                                                            ('Government'),
                                                            ('Telecommunications'),
                                                            ('Marketing'),
                                                            ('Sales');

                                                        INSERT INTO skills
                                                            (skill_name)
                                                        VALUES
                                                            ('PHP'),
                                                            ('HTML'),
                                                            ('CSS'),
                                                            ('JavaScript'),
                                                            ('MySQL'),
                                                            ('Python'),
                                                            ('Java'),
                                                            ('C#'),
                                                            ('Git'),
                                                            ('Communication'),
                                                            ('Teamwork'),
                                                            ('Problem Solving'),
                                                            ('Microsoft Excel'),
                                                            ('Microsoft Word');

                                                        -- ------------------------------------------------------------
                                                        -- 10. Insert a default administrator account
                                                        -- ------------------------------------------------------------
                                                        -- IMPORTANT: Replace this placeholder password with a value generated
                                                        -- using PHP password_hash() before deploying the application to production.
                                                        INSERT INTO users
                                                            (user_id, full_name, email, phone, password, role, status)
                                                        VALUES
                                                            (
                                                                1,
                                                                'System Administrator',
                                                                'admin@graduatejobportal.sl',
                                                                '+232000000000',
                                                                'PLACEHOLDER_HASHED_PASSWORD',
                                                                'admin',
                                                                'active'
);

-- ------------------------------------------------------------
-- End of schema
-- ------------------------------------------------------------
