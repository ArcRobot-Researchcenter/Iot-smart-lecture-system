# IoT Smart Lecture System

**Developed by ARC ROBOTICS**

An Internet of Things (IoT) solution designed to improve lecture timetable communication and coordination in universities and other tertiary institutions.

## 1. Project Overview

The IoT Smart Lecture System combines a web-based lecture timetable management application, an ESP32 microcontroller, a P10 LED display board, and an automated SMS notification service.

The system is designed to help students access lecture information more easily while reminding lecturers about their scheduled classes.

The project addresses common challenges associated with printed timetables, manual announcements, delayed communication, and missed lecture reminders.

## 2. Problem Statement

Universities manage multiple courses, lecturers, classrooms, and lecture schedules. When timetable information changes, communicating those changes to everyone concerned can be difficult.

Printed notices and manually forwarded messages may become outdated or fail to reach lecturers on time.

The IoT Smart Lecture System aims to improve this process through digital timetable presentation and automated lecturer notifications.

## 3. Proposed Solution

The system consists of three main components:

### A. Web-Based Timetable Management

A web application developed using PHP and MySQL allows authorised administrators to manage lecture timetable information.

The planned timetable records include:

- Day and date of lecture
- Lecture start and end times
- Course code and course title
- Lecturer's name
- Lecture venue
- Semester or academic session

### B. Digital Lecture Timetable Display

An ESP32 microcontroller controls a P10 LED display board intended to present scheduled lecture information at a lecture venue.

The display is designed to make relevant timetable information visible to students and university staff.

### C. Automated SMS Reminders

The notification component uses an SMS gateway API to send reminders to lecturers before their scheduled lectures.

The reminder process is intended to reduce dependence on manual notifications and help lecturers prepare for their classes.

## 4. How It Works

1. An administrator enters or updates a lecture timetable through the web application.
2. The application stores timetable records in the MySQL database.
3. The system retrieves the relevant lecture information for display.
4. The ESP32 controls the P10 LED board to present scheduled lecture details.
5. The scheduled notification process identifies upcoming lectures.
6. An SMS gateway sends reminders to the assigned lecturers.
7. Test logs are reviewed to identify errors and improve reliability.

**Note:** The complete workflow requires successful integration and testing of the web application, display hardware, and SMS service.

## 5. Technology Stack

| Component                  | Technology                               |
| -------------------------- | ---------------------------------------- |
| Backend                    | PHP                                      |
| Database                   | MySQL                                    |
| Database administration    | phpMyAdmin                               |
| Web server for development | XAMPP / Apache                           |
| Microcontroller            | ESP32                                    |
| Digital display            | P10 LED matrix display                   |
| Notifications              | SMS gateway API                          |
| Hardware programming       | Arduino IDE / compatible ESP32 toolchain |

## 6. Current Development Status

The project is being developed as a practical university-focused IoT solution.

The timetable management interface and SMS reminder functionality have been demonstrated during development. Integration and validation of the complete hardware-software workflow remain development priorities unless otherwise confirmed by the latest tests.

Planned validation includes:

- Checking timetable record accuracy.
- Verifying that displayed information matches the database.
- Testing timetable changes and synchronisation.
- Measuring SMS delivery success and delays.
- Testing the system under temporary network interruptions.
- Evaluating reliability in a university pilot environment.

## 7. Planned Features

- Secure administrator login.
- Lecture timetable creation and editing.
- Course and lecturer management.
- Digital timetable display.
- Automated SMS reminders.
- Timetable change handling.
- Notification and system logs.
- Support for additional lecture venues.
- Improved reliability monitoring.

Features will be marked as completed only after implementation and testing.

## 8. Development Setup

The web application is intended to run in a PHP/MySQL environment.

For local development:

1. Install XAMPP.
2. Start Apache and MySQL.
3. Place the project folder inside the XAMPP `htdocs` directory.
4. Import the supplied database SQL file into phpMyAdmin.
5. Configure the database connection using the provided configuration template.
6. Configure the SMS gateway credentials through environment variables or a private configuration file.
7. Open the application through the local Apache server.

Detailed installation instructions will be updated when the source code and database schema are added to this repository.

## 9. Security

- Do not commit SMS API keys, database passwords, access tokens, or private phone numbers.
- Use a sample configuration file without real credentials.
- Protect administrative functions with authentication and appropriate access controls.
- Validate timetable inputs and use secure database queries.
- Test the system before deploying it in a live university environment.

## 10. Project Roadmap

- [x] Establish the project concept and system architecture.
- [x] Develop the timetable management interface.
- [x] Demonstrate SMS reminder functionality.
- [x] Complete and validate ESP32-to-P10 display integration.
- [x] Verify synchronisation between the database and display.
- [x] Conduct end-to-end tests of timetable updates and SMS reminders.
- [x] Document performance and reliability test results.
- [x] Conduct a pilot in a university environment.

The checklist reflects the current development status and will be updated as additional features are verified.

## 11. Intellectual Property and Contribution

The project is developed under ARC ROBOTICS. Original source code and documentation are intended to be protected under applicable intellectual property laws.

Please contact the project team before reusing proprietary materials. A formal open-source licence will be added only if the project owners decide to permit redistribution and modification.

## 12. Project Team

**Project Lead:** Abdulrahman Raji
**Organisation:** ARC ROBOTICS
**Institutional Affiliation:** Confluence University of Science and Technology (CUSTECH), Osara, Nigeria

## 13. Contact

For collaboration, testing, institutional pilot opportunities, or enquiries about the project, contact the ARC ROBOTICS project team.

**Project:** IoT Smart Lecture System
**Organisation:** ARC ROBOTICS

## 14. Vision

To make lecture coordination more reliable through accessible IoT technology, digital timetable displays, and timely communication between universities and their lecturers.
