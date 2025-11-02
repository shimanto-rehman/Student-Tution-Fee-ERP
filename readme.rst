##########################
Pioneer Dental - Fee Management System
##########################

**Pioneer Dental** is a comprehensive student fee management system designed for educational institutions (specifically dental colleges). This system helps you manage student fees, track payments, generate monthly bills automatically, and provides a REST API for accessing billing information.

******************
What This System Does
******************

This system helps you:

- **Manage Student Fees**: Create and track monthly fees for students
- **Track Payments**: Record and monitor student payments
- **View Due Bills**: See which students have unpaid bills
- **View Paid Bills**: Check payment history for students
- **Generate Monthly Bills**: Automatically create bills for all students
- **Calculate Late Fees**: Automatically calculate late fees for overdue bills
- **Access via API**: Integrate with other systems using REST API
- **Dashboard**: View statistics and overview of all fees and payments

*******************
System Requirements
*******************

Before installing, make sure you have:

1. **XAMPP** (for Windows/Mac/Linux)
   - Download from: https://www.apachefriends.org/
   - This includes Apache web server, MySQL database, and PHP
   
2. **PHP Version**: 5.6 or newer (included with XAMPP)

3. **MySQL Database**: 5.5 or newer (included with XAMPP)

4. **Web Browser**: Any modern browser (Chrome, Firefox, Safari, Edge)

******************
Installation Steps
******************

Follow these steps carefully:

**Step 1: Install XAMPP**

1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP following the installation wizard
3. Make sure to install Apache and MySQL components

**Step 2: Start XAMPP Services**

1. Open **XAMPP Control Panel**
2. Click **Start** button next to **Apache**
3. Click **Start** button next to **MySQL**
4. Both should show green/active status

**Step 3: Place Project Files**

1. Copy the entire ``pioneer-dental`` folder
2. Paste it into your XAMPP ``htdocs`` folder:
   
   - **Windows**: ``C:\xampp\htdocs\pioneer-dental``
   - **Mac**: ``/Applications/XAMPP/xamppfiles/htdocs/pioneer-dental``
   - **Linux**: ``/opt/lampp/htdocs/pioneer-dental``

**Step 4: Create Database**

1. Open your web browser
2. Go to: ``http://localhost/phpmyadmin``
3. Click on **New** (on the left sidebar) to create a new database
4. Enter database name: ``pioneer_dental_db``
5. Select **utf8_general_ci** as collation
6. Click **Create**

**Step 5: Import Database**

1. In phpMyAdmin, click on **pioneer_dental_db** database (left sidebar)
2. Click on **Import** tab (top menu)
3. Click **Choose File** button
4. Select ``pioneer_dental_db.sql`` file from your project folder
5. Click **Go** button at the bottom
6. Wait for "Import has been successfully finished" message

**Step 6: Configure Database Connection**

1. Open the file: ``application/config/database.php``
2. Find these lines (around line 10-13):
   
   .. code-block:: php
   
      'hostname' => 'localhost',
      'username' => 'root',
      'password' => '',
      'database' => 'pioneer_dental_db',

3. If your MySQL password is not empty, update the ``'password' => ''`` part
4. Save the file

**Step 7: Configure Base URL**

1. Open the file: ``application/config/config.php``
2. Find this line (around line 26):
   
   .. code-block:: php
   
      $config['base_url'] = 'http://localhost/pioneer-dental/';

3. If your project is in a different location, update this URL
4. Save the file

*************
How to Run
*************

**Step 1: Start XAMPP Services**

1. Open XAMPP Control Panel
2. Make sure **Apache** and **MySQL** are running (green status)

**Step 2: Open in Browser**

1. Open your web browser
2. Go to: ``http://localhost/pioneer-dental/``
3. You should see the Dashboard page

**Step 3: Access the System**

- **Dashboard**: ``http://localhost/pioneer-dental/``
- **Student Fees**: ``http://localhost/pioneer-dental/student_fees``
- **Payments**: ``http://localhost/pioneer-dental/payments``
- **API Testing Page**: ``http://localhost/pioneer-dental/test_due_bills_api.html``

******************
Basic Usage Guide
******************

**View Dashboard**

The dashboard shows:
- Total number of students
- Total fees generated
- Unpaid bills count and amount
- Paid bills count and amount
- Partial payments count and amount

**Manage Student Fees**

1. Go to **Student Fees** section
2. Click **Create New Fee** to add a monthly fee for a student
3. Select student, enter amount, month, year, and due date
4. Click **Save**

**Record Payments**

1. Go to **Payments** section
2. Click **Create Payment**
3. Select the bill to pay
4. Enter payment amount and date
5. Click **Save**

**Generate Monthly Fees Automatically**

You can automatically generate fees for all students:

1. Use the API endpoint: ``http://localhost/pioneer-dental/api/generate-monthly-fees``
2. Or set up a cron job (see API Documentation)

*****************
API Information
*****************

This system includes a REST API for accessing billing information.

**Base URL**: ``http://localhost/pioneer-dental/api``

**Main Endpoints**:

- **Check Due Bills**: ``/api/due-bills/check``
- **Get Paid Bills**: ``/api/paid-bills/check``
- **Generate Monthly Fees**: ``/api/generate-monthly-fees``

**Quick Test**:

Open this in your browser:
``http://localhost/pioneer-dental/test_due_bills_api.html``

This gives you a web interface to test all API endpoints!

**Full API Documentation**: See ``API_DOCUMENTATION.md`` file

**API Authentication**: 

All API requests require an API key. The API key is configured in:
``application/config/config.php``

Look for: ``$config['api_secret_key']``

**Important**: Change the API key to a secure random string before using in production!

******************
Project Structure
******************

::

    pioneer-dental/
    │
    ├── application/              # Main application folder
    │   ├── config/              # Configuration files
    │   │   ├── config.php      # Main config (base URL, etc.)
    │   │   └── database.php     # Database connection settings
    │   ├── controllers/        # Application controllers
    │   │   ├── Dashboard.php
    │   │   ├── Student_fees.php
    │   │   ├── Payments.php
    │   │   └── Api/            # API controllers
    │   ├── models/             # Database models
    │   ├── views/              # HTML templates
    │   └── logs/               # Error and system logs
    │
    ├── assets/                  # CSS, JavaScript, images
    ├── system/                  # CodeIgniter framework files
    ├── index.php               # Main entry point
    ├── pioneer_dental_db.sql   # Database structure file
    │
    └── Documentation files:
        ├── API_DOCUMENTATION.md   # Complete API guide
        ├── QUICK_START.md         # Quick testing guide
        └── TEST_API.md            # API testing examples

******************
Troubleshooting
******************

**Problem: Cannot access the website (404 error)**

- **Solution**: Make sure Apache is running in XAMPP Control Panel
- **Solution**: Check that files are in the correct ``htdocs`` folder
- **Solution**: Verify the URL is correct: ``http://localhost/pioneer-dental/``

**Problem: Database connection error**

- **Solution**: Make sure MySQL is running in XAMPP Control Panel
- **Solution**: Check database credentials in ``application/config/database.php``
- **Solution**: Verify database ``pioneer_dental_db`` exists in phpMyAdmin

**Problem: Pages showing blank/white screen**

- **Solution**: Check error logs in ``application/logs/`` folder
- **Solution**: Make sure PHP errors are enabled in ``application/config/config.php``
- **Solution**: Verify all files are uploaded correctly

**Problem: API not working**

- **Solution**: Check that API key is set in ``application/config/config.php``
- **Solution**: Verify API endpoints are correct (check routes)
- **Solution**: Check browser console or API response for error messages

**Problem: Late fees not calculating**

- **Solution**: Go to Dashboard - it automatically updates late fees when loaded
- **Solution**: Make sure system date is correct on your server

******************
Important Notes
******************

1. **Security**: This is a development version. Before using in production:
   - Change the API secret key
   - Use HTTPS instead of HTTP
   - Set proper database passwords
   - Enable proper security settings

2. **Database Backup**: Regularly backup your database using phpMyAdmin Export feature

3. **Updates**: The system automatically calculates late fees when you visit the Dashboard

4. **API Testing**: Use ``test_due_bills_api.html`` for easy API testing without coding

******************
Support & Documentation
******************

- **API Documentation**: See ``API_DOCUMENTATION.md``
- **Quick Start Guide**: See ``QUICK_START.md``
- **API Test Cases**: See ``API_TEST_CASES.md``
- **Testing Interface**: ``http://localhost/pioneer-dental/test_due_bills_api.html``

******************
Technical Details
******************

- **Framework**: CodeIgniter 3.x
- **PHP Version**: 5.6+ (recommended)
- **Database**: MySQL 5.5+
- **Server**: Apache (via XAMPP)

******************
License
******************

This project uses CodeIgniter framework, which is released under the MIT License.

See ``license.txt`` for full license information.

******************
Getting Help
******************

If you encounter issues:

1. Check the **Troubleshooting** section above
2. Review error logs in ``application/logs/`` folder
3. Check documentation files (API_DOCUMENTATION.md, QUICK_START.md)
4. Verify all installation steps were completed correctly

---

**Ready to Start?**

1. Make sure XAMPP is running (Apache + MySQL)
2. Open: ``http://localhost/pioneer-dental/``
3. You're ready to go!