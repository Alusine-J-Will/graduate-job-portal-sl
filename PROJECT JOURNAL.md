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

- Employer Registration
- Login
- Forgot Password
- Logout

After authentication:

- Graduate Dashboard
- Employer Dashboard
- Administrator Dashboard
