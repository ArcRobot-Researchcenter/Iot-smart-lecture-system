-- Smart Lecture Timetable System
-- Database: smart_lecture_system
-- Created: 2026

CREATE DATABASE IF NOT EXISTS smart_lecture_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_lecture_system;

-- Table: faculties
CREATE TABLE faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT,
    department_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE SET NULL
);

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    role ENUM('Super Admin', 'Department Officer', 'Class Representative') DEFAULT 'Class Representative',
    department_id INT,
    photo VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Table: lecturers
CREATE TABLE lecturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_id VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(50),
    full_name VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    email_address VARCHAR(255),
    department_id INT,
    position VARCHAR(100),
    status ENUM('Active', 'Inactive', 'On Leave') DEFAULT 'Active',
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Table: courses
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(50) NOT NULL UNIQUE,
    course_title VARCHAR(255) NOT NULL,
    credit_unit INT DEFAULT 3,
    department_id INT,
    level VARCHAR(50),
    semester ENUM('First', 'Second', 'Summer') DEFAULT 'First',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- Table: timetable
CREATE TABLE timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    lecturer_id INT NOT NULL,
    venue VARCHAR(100) NOT NULL,
    day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    session VARCHAR(50),
    semester ENUM('First', 'Second', 'Summer') DEFAULT 'First',
    academic_year VARCHAR(50),
    status ENUM('Active', 'Cancelled', 'Postponed') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE
);

-- Table: sms_logs
CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecturer_id INT,
    phone_number VARCHAR(20) NOT NULL,
    course_id INT,
    message TEXT NOT NULL,
    status ENUM('Sent', 'Failed', 'Pending') DEFAULT 'Pending',
    response TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE SET NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE SET NULL
);

-- Table: settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('SMS', 'System', 'Alert') DEFAULT 'System',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    status ENUM('Read', 'Unread') DEFAULT 'Unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: backup_logs
CREATE TABLE backup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    file_size VARCHAR(50),
    backup_type ENUM('Manual', 'Auto') DEFAULT 'Manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: sent_reminders
CREATE TABLE sent_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timetable_id INT NOT NULL,
    reminder_date DATE NOT NULL,
    reminder_type ENUM('Auto', 'Manual', 'Broadcast') DEFAULT 'Auto',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reminder (timetable_id, reminder_date),
    FOREIGN KEY (timetable_id) REFERENCES timetable(id) ON DELETE CASCADE
);

-- Insert Default Data
INSERT INTO faculties (faculty_name) VALUES ('Faculty of Engineering');

INSERT INTO departments (faculty_id, department_name) VALUES (1, 'Computer Engineering Department');

INSERT INTO users (username, password, full_name, email, phone, role, status) VALUES 
('admin', '$2y$10$pk2jTASxkR/W0.k81KeZSOHAVD4MI2Q6O2iCBudOz3hjI.Is4c.1S', 'System Administrator', 'admin@example.com', '08012345678', 'Super Admin', 'Active');
-- Default password: admin123

INSERT INTO settings (setting_key, setting_value) VALUES
('institution_name', 'Confluence University of Science and Technology, Osara'),
('faculty_name', 'Faculty of Engineering'),
('department_name', 'Computer Engineering Department'),
('system_name', 'Smart Lecture Timetable and Lecturer Notification System'),
('system_subtitle', 'Automated Lecture Management Platform'),
('school_logo', ''),
('faculty_logo', ''),
('footer_text', 'Smart Lecture System - All Rights Reserved'),
('developer_name', 'Smart Systems Developer'),
('project_version', '1.0.0'),
('sms_reminder_minutes', '30'),
('holiday_mode', 'OFF'),
('auto_backup', 'OFF');
