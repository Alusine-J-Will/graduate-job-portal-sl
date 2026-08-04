# Project Development Journal

**Project Title:** Design and Implementation of an Online Job Portal for Graduates in Sierra Leone

**Developer:** Alusine J. Will

**Supervisor (AI Assistant):** ChatGPT

---

# Session 1 – Project Planning

## Activities Completed

- Selected the project topic:
  **Design and Implementation of an Online Job Portal for Graduates in Sierra Leone.**

- Defined the project problem:
  Graduates in Sierra Leone face challenges finding graduate-level job opportunities. Employers also lack a centralized platform to recruit fresh graduates efficiently.

- Identified project objectives.

- Defined project scope and limitations.

- Identified key stakeholders:
  - Graduates
  - Employers
  - System Administrator

- Defined the project's unique features:
  - Focus on graduate-level jobs
  - Graduate profiles with education, skills, projects, and internships
  - Employer profiles
  - Lightweight design suitable for slower internet connections
  - Localized platform for Sierra Leone

---

# Session 2 – Requirements Analysis

## Activities Completed

Developed:

- Functional Requirements

Examples:

- Graduate registration
- Employer registration
- Login
- Job posting
- Job application
- CV upload
- Applicant shortlist
- Admin management

Developed:

- Non-functional Requirements

Examples:

- Security
- Usability
- Reliability
- Performance
- Scalability
- Maintainability

---

# Session 3 – System Design

## Activities Completed

Designed:

- Use Case Diagram
- Entity Relationship Diagram (ERD)
- Database Schema
- Navigation Structure

Defined relationships between:

- Users
- Graduates
- Employers
- Jobs
- Applications
- Skills
- Notifications

---

# Session 4 – Database Design

## Activities Completed

Created the MySQL database.

Created all project tables.

Established foreign key relationships.

Configured database connection.

Tested successful database connection using PHP.

---

# Session 5 – Project Setup

## Activities Completed

Created the complete project folder structure.

Configured:

- XAMPP
- Apache
- MySQL

Created reusable project folders including:

- admin
- auth
- graduate
- employer
- config
- includes
- assets
- uploads

Configured:

- config.php
- constants.php
- database.php
- session.php

Successfully tested PHP environment.

Successfully tested MySQL connection.

---

# Session 6 – Homepage Development

## Activities Completed

Developed Version 1 of the homepage.

Implemented:

- Navigation Bar
- Hero Section
- Statistics
- Featured Jobs
- Why Choose Us
- How It Works
- Partner Companies
- Testimonials
- Footer

Integrated Bootstrap 5.

Integrated Font Awesome.

---

# Session 7 – UI Improvements

## Activities Completed

Improved homepage branding.

Updated Hero Section.

Added:

- Better typography
- Improved spacing
- Professional layout

Implemented responsive design.

Created reusable page structure.

Standardized:

- Header
- Navbar
- Footer

---

# Session 8 – Design System

## Activities Completed

Created a global UI design system.

Implemented:

- CSS Variables
- Global Theme
- Standard Card Components
- Standard Button Components
- Improved Shadows
- Rounded Cards
- Hover Effects
- Responsive Components

Added:

- Password Strength Meter (Frontend)
- Password Requirement Indicator
- Email Availability UI (Placeholder)
- Form Progress Indicator

---

# Session 9 – Version Control

## Activities Completed

Initialized Git repository.

Uploaded project to GitHub.

Prepared development documentation including:

- README.md
- Development Checklist
- Project Journal

---

# Current Progress

Completed:

✔ Project Planning

✔ Requirements Analysis

✔ System Design

✔ Database Design

✔ Project Structure

✔ Configuration

✔ Homepage

✔ Responsive Design

✔ UI Design System

✔ GitHub Repository

---

# Next Development Session

Authentication Module

## session 10 Graduate Registration Module

Completed: 8 July 2026.

- Graduate registration interface
- Client-side validation
- Server-side validation
- Password hashing
- Duplicate email prevention
- Database insertion into users table
- Database insertion into graduates table
- Transaction handling
- Redirect to login page

Issues encountered:

- Incorrect primary key (id vs user_id)
- Database column mismatch (first_name vs full_name)
- Missing profile_completion column
- Blank login page due to placeholder file

Resolution:
Updated backend to match the database schema and confirmed successful registration.

## Authentication Module

Completed:

- Graduate registration
- Password hashing
- Duplicate email validation
- Login interface
- Login authentication
- Session management
- Role-based redirection
- Flash messaging

Issues solved:

- id vs user_id mismatch
- first_name vs full_name mismatch
- Missing profile_completion column
- Placeholder login page
- Flash message helper mismatch
- Pending account login issue

Status:

Authentication module completed successfully.

- Employer Registration
- Login
- Forgot Password
- Logout

After authentication:

- Graduate Dashboard
  Graduate Dashboard Module

Completed

Graduate dashboard
Sidebar navigation
Welcome panel
Dynamic logged-in user information
Profile card
Profile completion section
Statistics cards
Notifications panel
Recommended jobs section
Quick Actions
Responsive layout

Testing

Graduate login successful
Dashboard authentication successful
Session data displayed correctly
Sidebar navigation working
Responsive layout verified

- Employer Dashboard
  Module Completed

Employer Registration & Employer Dashboard Module

Objectives Achieved
Completed Employer Registration page.
Implemented Employer Registration processing.
Added server-side validation.
Added secure password hashing.
Successfully inserted employer records into both the users and employers tables.
Integrated employer login with the existing authentication system.
Implemented Employer Dashboard.
Implemented Company Profile page.
Added Edit Company Profile functionality.
Added role-based access control for employer pages.
Connected employer dashboard navigation.
Improved registration workflow.
Added password strength meter.
Added show/hide password functionality.
Added live password validation.
Added live email availability check.
Corrected company verification status display.
Ensured consistent UI and UX between Graduate and Employer registration pages.
Bugs Encountered
Bug 1

Employer registration remained on the registration page after clicking Register.

Cause

Form was missing method="POST" and action.

Solution

Added the correct form method and action attributes.
Bug 2

Employer account was not created.

Cause

Registration process was using graduate registration logic.

Solution

Created a dedicated employer registration process.
Inserted records into both the users and employers tables.
Bug 3

Employer login returned "Invalid email or password."

Cause

No employer record existed in the database.

Solution

Fixed the registration process and verified database inserts.
Bug 4

Company status displayed Pending.

Cause

Company verification status defaults to pending.

Solution

Updated the company verification status to approved for testing.
Bug 5

Employer Registration page lacked password strength meter and password visibility toggle.

Solution

Added password strength meter.
Added show/hide password functionality.
Added real-time password validation.
Testing Performed
✅ Employer Registration
✅ Employer Login
✅ Employer Dashboard
✅ Company Profile
✅ Edit Company Profile
✅ Password Strength Meter
✅ Password Visibility Toggle
✅ Email Availability Check
✅ Database Record Verification
✅ Role-Based Access Control
✅ Dashboard Navigation

Lessons Learned
Maintain separate registration logic for different user roles.
Reuse shared UI components and validation logic to keep the experience consistent.
Verify database inserts before troubleshooting authentication issues.
Test every module thoroughly before moving on to the next stage.
Small UI improvements can significantly improve usability.

29 July, 2026
Completed Employer Job Management Part 1.
Successfully implemented job posting functionality.
Fixed jobs table structure mismatch.
Verified records are saved in the jobs table.
Tested Manage Jobs functionality.
Confirmed successful redirect after job creation.
Committed stable version before Edit Job implementation.

Date: [31 July, 2026]

Module: Employer Job Management & Job Application System

Part 1 – Post Job & Manage Jobs
Created employer job posting page.
Added job creation form with validation.
Connected form to database.
Created manage jobs page.
Employers can view all jobs they have posted.
Fixed job creation issues and database insertion errors.
Part 2 – Edit Job & Update Job
Implemented edit job functionality.
Employers can update existing job details.
Added validation for updated job information.
Ensured updated records are saved correctly in the jobs table.
Part 3 – Delete Job
Implemented job deletion feature.
Added confirmation prompt before deletion.
Verified jobs are removed correctly from the database.
Restricted deletion to the job owner.
Part 4 – Job Details / Preview
Created job details page.
Employers can preview complete job information.
Added navigation between manage jobs and job details pages.
Verified all job information displays correctly.
Part 5 – Graduate Job Search & Filtering
Created graduate job listing page.
Added job search functionality.
Added filtering options.
Integrated job details page for graduates.
Verified graduates can browse available jobs.
Part 6 – Job Application System
Implemented job application functionality.
Created application submission workflow.
Added My Applications page for graduates.
Added applicant management page for employers.
Enabled application status tracking.
Fixed Apply button integration.
Fixed dashboard Recent Applications section to display live application data.
Verified applications are saved successfully to the database.
Verified employers can view submitted applications.
Verified application status updates work correctly.
Testing Summary

✅ Job posting works

✅ Job editing works

✅ Job deletion works

✅ Job details page works

✅ Job search and filtering works

✅ Job applications save correctly

✅ Employer applicant review works

✅ Dashboard statistics update correctly

✅ Recent Applications section now displays actual records

- Administrator Dashboard

# Admin Module Development Summary

## Part 1: Admin Dashboard

### Features Completed

- Admin Dashboard created.
- Admin-only access control implemented.
- Dashboard statistics cards added.
- Total Graduates counter.
- Total Employers counter.
- Total Jobs counter.
- Total Applications counter.
- Recent activity sections added.
- Quick action buttons implemented.
- System overview section created.

### Testing Results

- Admin login successful.
- Dashboard loads correctly.
- Statistics display accurate values.
- Access control works correctly.

**Status:** Completed

---

## Part 2: Manage Graduates

### Features Completed

- Graduate listing page created.
- Graduate details page created.
- Graduate account status management implemented.
- Search functionality added.
- Status filters added.
- Pagination implemented.
- Account activation/deactivation implemented.

### Testing Results

- View all graduates.
- Search functionality operational.
- Filters operational.
- Graduate details page loads.
- Status changes save correctly.
- Deactivated graduates cannot log in.
- Reactivated graduates can log in.

**Status:** Completed

---

## Part 3: Manage Employers

### Features Completed

- Employer listing page created.
- Employer details page created.
- Employer account status management implemented.
- Company verification management implemented.
- Advanced search functionality added.
- Status filters added.
- Pagination implemented.
- Company approval/rejection controls implemented.

### Testing Results

- View all employers.
- Search by company name.
- Search by employer name.
- Search by email.
- Search by job title.
- Filters operational.
- Employer details page loads.
- Company approval/rejection works.
- Employer activation/deactivation works.

**Status:** Completed

---

## Part 4: Manage Jobs

### Features Completed

- Jobs management page created.
- Job details page created.
- Job status management implemented.
- Job deletion functionality implemented.
- Advanced search added.
- Category filters added.
- Employment type filters added.
- Pagination implemented.

### Testing Results

- View all jobs.
- Search by title.
- Search by company.
- Search by category.
- Filters operational.
- Pagination operational.
- Job details page loads.
- Status updates work.
- Job deletion works.
- Deleted jobs removed from listings.

**Status:** Completed

---

## Part 5: Manage Applications

### Features Completed

- Applications management page created.
- Application details page created.
- Application status management implemented.
- Search functionality added.
- Status filters added.
- Statistics cards implemented.
- Pagination implemented.

### Testing Results

- View all applications.
- Search functionality operational.
- Filters operational.
- Application details page loads.
- Status updates work.
- Statistics display correctly.
- Updates reflected across employer and graduate modules.

**Status:** Completed

---

## Part 6: Reports & Analytics

### Features Completed

- Reports dashboard created.
- User statistics reporting implemented.
- Job statistics reporting implemented.
- Application statistics reporting implemented.
- Employer statistics reporting implemented.
- Top employers report implemented.
- Top job categories report implemented.
- Recent activity reports implemented.
- CSV export functionality implemented.

### Testing Results

- Reports page loads correctly.
- User statistics accurate.
- Job statistics accurate.
- Application statistics accurate.
- Employer statistics accurate.
- Top employers displayed correctly.
- Top categories displayed correctly.
- Recent activity displayed correctly.
- CSV exports operational.
- Admin-only access verified.

**Status:** Completed

---

# Admin Module Completion Status

All Admin Module components have been successfully developed, integrated, tested, and verified.

Completed:

- Admin Dashboard
- Manage Graduates
- Manage Employers
- Manage Jobs
- Manage Applications
- Reports & Analytics

Module Status: COMPLETED

# Notification System Development Summary

## Part 1: Notification Center & Notification Bell

### Features Completed

- Notification Center created.
- Notification Bell added to navigation.
- Unread notification counter implemented.
- Notification listing page created.
- Read/Unread notification styling implemented.
- Mark notification as read functionality implemented.
- Mark all notifications as read functionality implemented.
- Notification filtering implemented.
- Pagination implemented.
- Role-based notification access implemented.

### Testing Results

- Notification bell displays correctly.
- Unread counts update correctly.
- Notifications page loads successfully.
- Users only see their own notifications.
- Mark as read works correctly.
- Mark all as read works correctly.
- Filters work correctly.
- Pagination works correctly.

**Status:** Completed

---

## Part 2: Job Application Notifications

### Features Completed

- Employer notified when a graduate applies for a job.
- Graduate notified when application status changes.
- Reviewed notifications implemented.
- Shortlisted notifications implemented.
- Accepted notifications implemented.
- Rejected notifications implemented.
- Notification count updates integrated.

### Testing Results

- Employer receives application notifications.
- Graduate receives status update notifications.
- Notification bell updates correctly.
- Notifications appear in Notification Center.
- Read/unread functionality remains operational.

**Status:** Completed

---

## Part 3: Employer Notifications

### Features Completed

- New applicant notifications implemented.
- Company approval notifications implemented.
- Company rejection notifications implemented.
- Employer account status notifications implemented.
- Job expiry notifications implemented.
- Employer dashboard recent notifications widget implemented.

### Testing Results

- Employer receives applicant notifications.
- Employer receives approval notifications.
- Employer receives rejection notifications.
- Employer receives status change notifications.
- Notification dashboard widget works correctly.
- Notification bell updates correctly.

**Status:** Completed

---

## Part 4: Admin Notifications

### Features Completed

- New employer registration notifications implemented.
- Pending company approval notifications implemented.
- New job posting notifications implemented.
- High application activity notifications implemented.
- Administrative summary notifications implemented.
- Admin dashboard notification widget implemented.

### Testing Results

- Admin receives employer registration notifications.
- Admin receives verification review notifications.
- Admin receives new job notifications.
- High activity alerts function correctly.
- Dashboard notification widget works.
- Notification bell updates correctly.
- Admin-only visibility enforced.

**Status:** Completed

---

## Part 5: Job Match Notifications

### Features Completed

- Profile-based job matching implemented.
- Skill matching logic implemented.
- Education matching logic implemented.
- Category matching logic implemented.
- Recommended jobs integration implemented.
- Notification-to-job navigation implemented.
- Duplicate notification prevention implemented.

### Testing Results

- Matching graduates receive notifications.
- Non-matching graduates do not receive notifications.
- Notification bell updates correctly.
- Notifications appear in Notification Center.
- Graduates can open jobs directly from notifications.
- Recommended jobs display correctly.
- Duplicate notifications prevented.

**Status:** Completed

---

## Part 6: Email Notifications

### Features Completed

- Email configuration layer created.
- Reusable email helper system created.
- Welcome email integration implemented.
- Job application email integration implemented.
- Application status email integration implemented.
- Company approval/rejection email integration implemented.
- Job match email integration implemented.
- Error handling and logging implemented.

### Files Created

- config/email_config.php
- includes/email_helper.php

### Testing Results

- No PHP errors introduced.
- Registration remains functional.
- Login remains functional.
- Job applications remain functional.
- Status updates remain functional.
- Email failures do not interrupt application workflows.

### Notes

- PHPMailer integration supported.
- SMTP configuration required for production email delivery.
- Email system operates alongside in-app notifications.

**Status:** Completed

---

# Notification System Completion Status

Completed:

- Notification Center
- Notification Bell
- Job Application Notifications
- Employer Notifications
- Admin Notifications
- Job Match Notifications
- Email Notifications

Module Status: COMPLETED
