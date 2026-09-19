# Softgenioius Auth System

A secure PHP authentication and user management system built with **raw PHP, MySQL, PDO, and JavaScript**. The system includes role-based access control, an admin dashboard, CRUD functionality, a REST API, secure file uploads, PDF report generation, and QR code generation.

## Overview

The Softgenioius Auth System is a full-stack PHP application designed to demonstrate practical implementation of authentication, authorization, database operations, API development, file management, and web security practices.

The system supports different user roles and provides protected functionality for authenticated users and administrators.

## Features

### Authentication & Authorization

* User registration
* Secure login and logout
* Session-based authentication
* Password hashing
* Password verification
* Role-based access control
* Protected routes
* User and administrator permissions

### Admin Dashboard

* Administrative dashboard
* User management
* CRUD operations
* Search and filtering
* Protected administrative functionality
* Dashboard statistics

### Security

* PDO prepared statements
* CSRF protection
* Server-side validation
* Password hashing
* Session management
* Authorization checks
* Secure file-upload validation
* Randomized uploaded filenames
* Custom error handling
* Error logging

### File Management

The system supports controlled file uploads, including:

* PDF
* JPG
* PNG
* DOC
* DOCX

Uploaded files are validated and assigned randomized filenames to reduce filename-related security risks.

### PDF & QR Code Generation

The application includes:

* PDF report generation using **TCPDF**
* QR code generation using **Endroid QR Code**

### REST API

The system includes a REST API supporting:

* JSON responses
* Bearer-token authentication
* Protected API endpoints
* Database operations through API endpoints

### User Interface

* Responsive interface
* Dashboard
* Forms and validation
* Search and filtering
* AJAX/jQuery functionality
* User interface
* Admin interface

## Technologies Used

| Technology      | Purpose                        |
| --------------- | ------------------------------ |
| PHP 8.2+        | Backend application            |
| MySQL/MariaDB   | Database                       |
| PDO             | Secure database access         |
| HTML5           | Application structure          |
| CSS3            | Styling                        |
| JavaScript      | Client-side functionality      |
| jQuery          | AJAX and frontend interactions |
| Apache          | Web server                     |
| Composer        | PHP dependency management      |
| TCPDF           | PDF generation                 |
| Endroid QR Code | QR code generation             |

## Project Structure

Softgenioius-Auth-System/
│
├── admin/
├── api/
├── auth/
├── config/
├── includes/
├── uploads/
├── reports/
├── users/
│
├── vendor/
│
├── index.php
├── composer.json
├── composer.lock
└── README.md

## Requirements

To run the project locally, you need:

* PHP 8.2 or later
* Apache
* MySQL or MariaDB
* Composer
* A modern web browser

**XAMPP** can be used to provide Apache, PHP, and MySQL/MariaDB on Windows.

## Installation

### 1. Clone the repository

git clone https://github.com/SoftgeniousDev/SoftgeniousDev254.git

### 2. Enter the project directory

cd SoftgeniousDev254

### 3. Install Composer dependencies

composer install

### 4. Create the database

Create a MySQL/MariaDB database using phpMyAdmin or the MySQL command line.

Import the project's database SQL file into the new database.

### 5. Configure the database

Update the application's database configuration with your local database credentials.

Example:

Host: localhost
Database: your_database
Username: root
Password: your_password


**Never commit production passwords or other sensitive credentials to GitHub.**

### 6. Start the application

If using XAMPP:

1. Start Apache.
2. Start MySQL.
3. Place the project inside the XAMPP `htdocs` directory.
4. Open the application in your browser.

Example:


http://localhost/SoftgeniousDev254/

## API Authentication

Protected API endpoints use **Bearer-token authentication**.

Example:

Authorization: Bearer YOUR_TOKEN


API responses are returned in JSON format.

## Security Considerations

This project demonstrates practical web-security techniques including:

* Password hashing
* Prepared SQL statements
* CSRF protection
* Input validation
* Authentication checks
* Authorization checks
* Session management
* File-type validation
* Randomized upload filenames
* Error logging

For production deployment, additional security hardening should be applied, including HTTPS, secure environment configuration, appropriate security headers, restricted file permissions, secure session-cookie configuration, and production-safe error handling.

## Project Status

**Project 1 — Softgenioius Auth System**

The core authentication, authorization, administration, API, file-management, PDF, QR-code, and security functionality has been implemented and tested locally.

The project is maintained as part of the **Softgenioius** portfolio.

## Future Improvements

Potential improvements include:

* Email verification
* Password reset functionality
* Two-factor authentication
* More granular permissions
* API documentation
* Automated testing
* Improved audit logging
* Production deployment
* Additional security hardening

## Author

**Softgenioius**

Technology projects focused on learning, creating, and growing.

## License

This project is currently presented as a portfolio and learning project.
