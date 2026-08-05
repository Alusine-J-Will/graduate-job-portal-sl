# Graduate Job Portal SL (GradConnect SL)

# Technical Documentation

Student Name: [Your Name]
Department: [Department Name]
University: [University Name]
Academic Year: [Academic Year]

---

## Table of Contents

1. Introduction
2. System Analysis
3. System Architecture
4. Technology Stack
5. Database Design
6. Module Design
7. Authentication and Security
8. Notification System
9. Testing and QA
10. Challenges Encountered
11. Future Improvements
12. Conclusion

---

## 1. Introduction

Graduate Job Portal SL (GradConnect SL) is a web-based recruitment and job management system developed to support communication between graduates, employers, and administrators. The system provides a digital platform for graduates to create professional profiles, search for jobs, and apply for suitable vacancies. Employers can register their companies, publish job opportunities, review applicants, and manage the recruitment process. Administrators are responsible for overseeing platform operations, managing users, monitoring applications, and generating reports.

The purpose of this project is to simplify the process of job discovery and recruitment by providing a centralized online system. The system reduces the need for manual recruitment processes and supports better organization, faster communication, and improved candidate management.

The scope of the project includes:

- User authentication and session management
- Graduate profile management
- Employer profile and company management
- Job posting and job search
- Application submission and tracking
- Notifications for users
- Admin dashboard and reporting features

The main objectives of the system are to:

- Provide graduates with access to employment opportunities
- Enable employers to hire suitable candidates efficiently
- Improve communication between job seekers and recruiters
- Support administrators in managing platform operations

---

## 2. System Analysis

### 2.1 Existing System

Traditional job recruitment often depends on manual processes such as physical notice boards, direct interviews, or informal communication between graduates and employers. These methods are often slow, inefficient, and difficult to manage. There is usually limited visibility of job opportunities, and employers may struggle to review candidate information systematically.

### 2.2 Problems with Existing System

The existing manual recruitment environment presents several challenges:

- Limited access to job opportunities for graduates
- Poor tracking of job applications
- Difficulty in organizing applicants and employer requests
- Lack of centralized communication between stakeholders
- Delays in recruitment decisions

### 2.3 Proposed Solution

The proposed system is a web-based Graduate Job Portal that centralizes all recruitment activities within a single platform. The system allows graduates to create profiles, upload CVs, search for jobs, and apply online. Employers can register and manage job postings, review applications, and update application statuses. Administrators can monitor system activities and manage all major entities.

### 2.4 Benefits of Proposed Solution

The proposed system offers several advantages:

- Faster and more structured recruitment processes
- Centralized management of jobs and applications
- Improved visibility for graduates and employers
- Better tracking and notification of application progress
- Increased efficiency and transparency in recruitment

---

## 3. System Architecture

The system follows a three-tier architecture consisting of the presentation layer, business logic layer, and database layer.

### 3.1 Presentation Layer

The presentation layer is responsible for the user interface. It includes the web pages displayed to graduates, employers, and administrators. This layer is implemented using HTML5, CSS3, Bootstrap 5, and JavaScript. It provides forms, dashboards, navigation menus, and interactive components.

### 3.2 Business Logic Layer

The business logic layer contains the core application logic. It is implemented using PHP 8. This layer processes requests, validates form inputs, manages user authentication, controls access permissions, and connects the user interface with the database. It also handles job matching, notification creation, and application processing.

### 3.3 Database Layer

The database layer stores all application data using MySQL. This includes user records, graduate profiles, employer records, jobs, applications, and notifications. The database ensures persistent storage and retrieval of data required by the system.

### 3.4 Data Flow

The data flow in the system is as follows:

1. A user submits a request through the browser.
2. The request is sent to the server-side PHP logic.
3. The business logic validates the request and processes the operation.
4. The relevant data is retrieved from or stored in the MySQL database.
5. The results are returned to the user interface for display.

This architecture separates the user interface, business logic, and data storage, making the system easier to maintain and expand.

---

## 4. Technology Stack

The following technologies were selected for the development of the system:

| Technology  | Purpose                                                                               |
| ----------- | ------------------------------------------------------------------------------------- |
| PHP 8       | Used for server-side processing, session handling, authentication, and business logic |
| MySQL       | Used for storing and managing all system data                                         |
| Bootstrap 5 | Used to create a responsive and modern user interface                                 |
| JavaScript  | Used for form validation, interactive behavior, and UI enhancements                   |
| HTML5       | Used for creating structured web page content                                         |
| CSS3        | Used for styling and presentation of web pages                                        |
| XAMPP       | Used as the local development environment for Apache, MySQL, and PHP                  |

### 4.1 Why PHP Was Selected

PHP was chosen because it is widely used for web development and supports dynamic web application development efficiently. It integrates well with MySQL and is suitable for building secure and scalable web systems.

### 4.2 Why MySQL Was Selected

MySQL was chosen because it is a reliable relational database management system. It provides structured storage for users, profiles, jobs, applications, and notifications.

### 4.3 Why Bootstrap Was Selected

Bootstrap was selected because it supports responsive web design and speeds up the development of professional-looking interfaces.

### 4.4 Why JavaScript Was Selected

JavaScript was used to improve user interaction and provide enhanced form handling and client-side validation.

### 4.5 Why HTML and CSS Were Selected

HTML and CSS were used to structure and style the system pages clearly and consistently.

### 4.6 Why XAMPP Was Selected

XAMPP was selected as the local development environment because it provides Apache, MySQL, and PHP in a simple package that is suitable for academic and local project development.

---

## 5. Database Design

The system uses a relational database model to manage relevant entities. The major database tables include users, graduates, employers, jobs, applications, and notifications.

### 5.1 users Table

**Purpose:** Stores the authentication and account information for all users.

**Key Fields:**

- user_id
- full_name
- email
- phone
- password
- role
- status
- created_at
- updated_at

**Relationships:**

- One user can have one graduate profile or one employer profile depending on the role.

### 5.2 graduates Table

**Purpose:** Stores graduate-specific profile information.

**Key Fields:**

- graduate_id
- user_id
- location
- bio
- cv
- profile_picture
- created_at
- updated_at

**Relationships:**

- A graduate is linked to one user record.
- A graduate can have multiple education, experience, and skill records.

### 5.3 employers Table

**Purpose:** Stores employer profile details.

**Key Fields:**

- employer_id
- user_id
- company_id
- job_title
- created_at
- updated_at

**Relationships:**

- An employer is linked to one user and one company.

### 5.4 companies Table

**Purpose:** Stores company details for employer accounts.

**Key Fields:**

- company_id
- company_name
- industry
- company_size
- location
- website
- verification_status
- created_at
- updated_at

**Relationships:**

- One company may be associated with one employer profile.

### 5.5 jobs Table

**Purpose:** Stores all job postings created by employers.

**Key Fields:**

- job_id
- company_id
- title
- description
- requirements
- category_id
- location
- salary_range
- deadline
- status
- created_at
- updated_at

**Relationships:**

- A job belongs to one company.
- A job can receive multiple applications.

### 5.6 applications Table

**Purpose:** Stores all job applications submitted by graduates.

**Key Fields:**

- application_id
- graduate_id
- job_id
- cover_letter
- status
- applied_at

**Relationships:**

- An application belongs to one graduate and one job.

### 5.7 notifications Table

**Purpose:** Stores notifications sent to users.

**Key Fields:**

- notification_id
- user_id
- type
- title
- message
- link
- is_read
- created_at

**Relationships:**

- Each notification belongs to one user.

### 5.8 Other Major Tables

Additional tables may include:

- education
- experience
- skills
- graduate_skills
- job_categories

These tables support graduate profile details and job classification.

---

## 6. Module Design

### 6.1 Graduate Module

The graduate module allows users to perform the following actions:

- Register an account
- Login to the system
- Complete and update profile information
- Add education details
- Add experience details
- Add skills
- Upload profile photo and CV
- Search for jobs
- View job details
- Apply for jobs
- Track application status
- Receive notifications

### 6.2 Employer Module

The employer module supports the recruitment workflow for companies:

- Register a company account
- Complete company profile
- Post new jobs
- Edit or delete jobs
- Manage active job listings
- View applicants
- Review applications
- Update application status
- Receive notifications

### 6.3 Admin Module

The admin module provides system oversight and management:

- View dashboard statistics
- Manage graduates
- Manage employers
- Manage jobs
- Manage applications
- Review reports and analytics
- Handle notifications and alerts

---

## 7. Authentication and Security

The system includes multiple security features to protect user data and maintain platform integrity.

### 7.1 Password Hashing

User passwords are stored using secure hashing techniques rather than plain text. This ensures that passwords are protected even if the database is compromised.

### 7.2 Session Management

The system uses session handling to remember logged-in users and protect restricted pages. Sessions are initialized and managed securely during user interaction.

### 7.3 Role-Based Access Control

The platform uses role-based access control to restrict access according to user role:

- Graduates can access graduate-related pages
- Employers can access employer-related pages
- Administrators can access admin pages

This minimizes unauthorized access to sensitive information.

### 7.4 Input Validation

All user inputs are validated before being processed. This includes checks for empty fields, invalid email addresses, password strength, and malformed values.

### 7.5 Prepared Statements

Prepared statements are used for database queries to reduce the risk of SQL injection attacks. This is an important security feature in the system.

---

## 8. Notification System

The notification system is an important component of the platform because it informs users about important events and updates.

### 8.1 Notification Center

The notification center provides a centralized place where users can review all notifications received by their account.

### 8.2 Notification Bell

The notification bell displays the number of unread notifications so that users can quickly identify pending updates.

### 8.3 Job Application Notifications

Graduates and employers receive notifications when applications are submitted or reviewed.

### 8.4 Employer Notifications

Employers receive notifications about new applications, job-related updates, and system alerts.

### 8.5 Admin Notifications

Administrators receive notifications for account activity, job review actions, and important system events.

### 8.6 Job Match Notifications

Graduates may receive notifications when a job matches their skills or profile information.

### 8.7 Email Notifications

The system also supports email notifications for important actions such as registration and application updates. This improves communication and user awareness.

---

## 9. Testing and QA

The system was evaluated through several testing methods to ensure functionality and reliability.

### 9.1 Functional Testing

Functional testing was performed to verify that major features such as registration, login, profile updates, job posting, job applications, and admin actions worked as expected.

### 9.2 Security Testing

Security testing was conducted to review authentication, authorization, input validation, and SQL protection measures. The system uses role-based access control and prepared statements to improve security.

### 9.3 UI Testing

UI testing focused on navigation, forms, dashboards, buttons, and layout consistency. The design was evaluated for readability and ease of use.

### 9.4 Database Testing

Database testing was performed to ensure that user records, graduate records, employer records, jobs, applications, and notifications were stored and retrieved correctly.

### 9.5 QA Findings

The overall QA review showed that the system is functionally strong and suitable for academic demonstration. However, additional refinement is recommended in areas such as validation completeness, navigation consistency, and performance optimization.

---

## 10. Challenges Encountered

Several challenges were encountered during the development of the system, including:

### 10.1 Registration Issues

Some registration flow inconsistencies were identified and resolved to ensure that graduates and employers could use the system correctly.

### 10.2 Login Issues

Login reliability and session handling were improved to ensure stable user authentication.

### 10.3 Routing Problems

Broken or incorrect navigation links were identified and fixed to ensure that users are directed to the correct pages.

### 10.4 Notification Link Bugs

Notification links were adjusted to ensure that users were redirected to proper job or application pages.

### 10.5 Job Posting Issues

Issues related to job creation and data handling were corrected to ensure smooth operation.

These challenges were resolved through careful debugging, code review, and validation of the affected modules.

---

## 11. Future Improvements

Although the current system satisfies the required project objectives, several enhancements can be added in the future:

- SMS notifications for mobile users
- A dedicated mobile application
- AI-powered job recommendations
- Real-time chat between graduates and employers
- Online interview scheduling and video interview support
- Advanced analytics and reporting dashboards

These improvements would further enhance the practical value of the system.

---

## 12. Conclusion

Graduate Job Portal SL (GradConnect SL) is a successful web-based system that addresses the need for an organized and efficient graduate recruitment platform. The system provides important features for graduates, employers, and administrators, including registration, profile management, job posting, application management, notifications, and reporting.

The project demonstrates the effective use of PHP, MySQL, Bootstrap, JavaScript, HTML, CSS, and XAMPP in building a functional web application. It also reflects the application of software engineering principles, database design, security practices, and system analysis in a real-world context.

Overall, the project has achieved its main objectives and provides a strong foundation for future enhancement and practical deployment.
