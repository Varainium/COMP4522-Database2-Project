# COMP 4522 - Wellness Clinic Management System

## Description

The Wellness Clinic Management System is a PHP-based web application designed to manage and streamline operations in a clinic setting. It supports functionalities such as patient management, appointment scheduling, billing, reporting, prescription management, and staff management. This project was developed using PHP, HTML, CSS, and JavaScript, and is powered by a SQLite database.

## Project Structure

```
COMP4522-Database2-Project/
├── css/
│   └── styles.css
├── data/
│   ├── sample_data.sql
│   ├── wellness_clinic.db
│   └── wellness_clinic_DDL.sql
├── includes/
│   ├── config.inc.php
│   └── db-classes.inc.php
├── views/
│   ├── appointments.php
│   ├── patients.php
│   ├── prescription.php
│   ├── reports.php
│   ├── schedule.php
│   └── staff.php
├── index.php
├── README.md
```

## Technologies Used

- Visual Studio Code (VSCode) - Development Environment

- GitHub - Version Control

- PHP - Backend Scripting Language

- JavaScript - Client-Side Scripting

- HTML & CSS - Frontend Styling

- XAMPP - Local Server Environment (Apache, PHP, MySQL)

## Installation Instructions

1. Clone the repository from GitHub.

2. Set up XAMPP and ensure Apache and MySQL are running.

3. Place the project folder in the htdocs directory of XAMPP.

4. Import the wellness_clinic_DDL.sql file to initialize the database structure.

5. Load the sample_data.sql file to populate tables with initial data.

6. Update config.inc.php with your database connection settings.

7. Launch the application by accessing http://localhost/COMP4522-Database2-Project/index.php.

## Acknowledgments

This project was developed as part of the COMP 4522 - Database II course. Assistance was provided by ChatGPT for generating and debugging PHP code, structuring SQL queries, and ensuring the application met the project requirements.

