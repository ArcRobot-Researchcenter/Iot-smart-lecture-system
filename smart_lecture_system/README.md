# Smart Lecture Timetable and Automated Lecturer Notification System

## Overview
A complete web-based system for managing lecture timetables and automatically sending SMS reminders to lecturers using eBulkSMS API.

## Technology Stack
- PHP 8+
- MySQL Database
- Bootstrap 5
- JavaScript/AJAX
- eBulkSMS REST API

## Installation Instructions

### Step 1: Extract Files
Extract the `smart_lecture_system.zip` file to your XAMPP `htdocs` folder:
```
C:\xampp\htdocs\smart_lecture_system\
```

### Step 2: Create Database
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `smart_lecture_system`
3. Import the `database/database.sql` file

### Step 3: Configure Database
Edit `config.php` if your database credentials differ from default:
- Default username: `root`
- Default password: `` (empty)

### Step 4: Configure SMS API
1. Sign up at https://www.ebulksms.com
2. Get your API username and API key
3. Go to Settings -> SMS Configuration
4. Enter your eBulkSMS credentials

### Step 5: Run the System
Open your browser and navigate to:
```
http://localhost/smart_lecture_system/
```

### Default Login
- **Username:** admin
- **Password:** admin123

## System Features
- Secure Authentication (Login/Logout/Password Change)
- Role-Based Access Control (Super Admin, Department Officer, Class Representative)
- Lecturer Management (CRUD + Photo Upload)
- Course Management (CRUD)
- Timetable Management (CRUD + Filters + Print)
- Automatic SMS Reminders (Configurable: 5/10/15/30/60 min before)
- Manual SMS Sending per entry
- Today's Reminders (Bulk Send)
- Broadcast SMS (All/Department/Selected)
- SMS Logs (View/Search/Export/Delete)
- Holiday Mode Toggle
- System Settings
- Dashboard with Charts
- Backup & Restore
- Responsive Black & Gold Theme

## File Structure
```
smart_lecture_system/
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   ├── images/
│   └── uploads/
├── admin/
│   ├── includes/header.php, footer.php
│   ├── api/sms_api.php, auto_reminder.php
│   ├── dashboard.php, lecturers.php, courses.php
│   ├── timetable.php, sms_logs.php, broadcast_sms.php
│   ├── todays_reminders.php, settings.php, reports.php
│   ├── backup.php, profile.php, notifications.php
│   └── logout.php
├── database/
│   └── database.sql
├── config.php
├── index.php
└── forgot_password.php
```

## SMS Configuration
To enable SMS functionality:
1. Register at eBulkSMS.com
2. Add credits to your account
3. Configure API credentials in Settings
4. Set reminder time (default: 30 minutes)

## Automatic Reminders Setup
### Windows (Task Scheduler):
Create a task that runs every minute:
```
Program: php.exe
Arguments: C:\xampp\htdocs\smart_lecture_system\admin\api\auto_reminder.php
```

### Linux (Cron):
```
* * * * * /usr/bin/php /var/www/html/smart_lecture_system/admin/api/auto_reminder.php >> /var/log/sms_reminders.log 2>&1
```

## Security Features
- Password hashing (bcrypt)
- SQL injection protection (prepared statements)
- XSS protection (htmlspecialchars)
- Session timeout (1 hour)
- Role-based access control

## License
This project is for educational purposes.


## Emergency Password Fix (If Login Still Fails)

If you still get "Invalid password" after importing the database, run this SQL in phpMyAdmin:

```sql
UPDATE users SET password = '$2y$10$pk2jTASxkR/W0.k81KeZSOHAVD4MI2Q6O2iCBudOz3hjI.Is4c.1S' WHERE username = 'admin';
```

Or use the included `reset_password.php` file:
1. Go to `http://localhost/smart_lecture_system/reset_password.php`
2. Enter any new password
3. Login with that password
4. **DELETE `reset_password.php` after use for security!**
