# Graduate Job Portal SL (GradConnect SL)

A web-based graduate recruitment and job management system developed to connect graduates, employers, and administrators through a centralized online platform.

## Project Overview

Graduate Job Portal SL is a final-year project designed to simplify the recruitment process for graduates and employers. The system enables graduates to create profiles, search for suitable job opportunities, and apply online, while employers can publish vacancies, manage applications, and communicate with candidates. Administrators can oversee the platform, manage users, and monitor activities.

## Key Features

- Secure user registration and login
- Role-based access for graduates, employers, and administrators
- Graduate profile management
- Employer company and job posting management
- Job search and filtering
- Online job application system
- Notifications and email alerts
- Admin dashboard and reporting features
- User documentation and technical documentation

## Modules Implemented

- Authentication System
- Graduate Module
- Employer Module
- Job Management
- Job Search and Filtering
- Job Application System
- Admin Module
- Reports and Analytics
- Notification System
- Email Notification System

## Technology Stack

- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript
- Backend: PHP 8
- Database: MySQL
- Environment: XAMPP
- Editor: Visual Studio Code

## Project Structure

- admin/: Administrative dashboard and management pages
- auth/: Registration, login, logout, and authentication flows
- assets/: CSS, JavaScript, images, and other frontend resources
- config/: Core configuration and database/session settings
- database/: SQL schema and database-related files
- docs/: Project reports, user manual, and technical documentation
- employer/: Employer-specific pages and dashboards
- graduate/: Graduate-specific pages and dashboards
- includes/: Shared PHP functions, layout components, and helpers
- layouts/: Reusable layout templates
- uploads/: Uploaded files such as CVs, photos, and company logos
- vendor/: Composer dependencies (if used in future)

## Installation and Setup

1. Place the project folder inside your XAMPP htdocs directory.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open the project folder in Visual Studio Code.
4. Create a MySQL database for the project.
5. Import the SQL file from the database folder.
6. Update the database connection settings in config/database.php if required.
7. Open the project in your browser using:
   http://localhost/graduate-job-portal-sl/

## Usage

- Register as a graduate to create a profile and apply for jobs.
- Register as an employer to post vacancies and manage applications.
- Log in as an administrator to manage users, jobs, and system activity.

## Documentation

Project documentation is available in the docs folder:

- docs/user_manual.md
- docs/technical_documentation.md
- docs/qa_review_report.md
- docs/defense_preparation_package.md

## Notes

This project is intended for academic, educational, and demonstration purposes. It can be extended with additional features such as AI-based job recommendations, real-time chat, mobile support, and cloud deployment in the future.

## License

This project is provided for academic and educational use. Please contact the project author for commercial use or redistribution permissions.
