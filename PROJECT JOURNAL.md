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

- Administrator Dashboard
