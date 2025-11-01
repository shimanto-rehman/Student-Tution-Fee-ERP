# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Pioneer Dental is a Student Payment Management System built with CodeIgniter 3 (PHP MVC framework). It manages student fees, monthly billing, and payment processing with MTB payment gateway integration.

**Tech Stack:**
- PHP (5.6+) with CodeIgniter 3
- MySQL/MariaDB database (`pioneer_dental_db`)
- Bootstrap 5.3.0, jQuery 3.6.0, Chart.js
- PHPUnit for testing

## Development Commands

### Running the Application
```bash
# Start PHP built-in server
php -S localhost:8000

# Or configure Apache with document root pointing to this directory
# Ensure mod_rewrite is enabled for clean URLs
```

### Testing
```bash
# Run tests with coverage
composer run-script test:coverage

# Direct PHPUnit execution
phpunit --configuration tests/travis/sqlite.phpunit.xml
```

### Database
- Database name: `pioneer_dental_db`
- Configuration: `application/config/database.php`
- Default connection: localhost/root (no password)

## Architecture

### MVC Structure
```
application/
├── controllers/        # Request handlers
│   ├── Dashboard.php      # Main dashboard with statistics
│   ├── Student_fees.php   # Fee CRUD operations
│   ├── Payments.php       # Payment processing
│   └── Api/Mtb_gateway.php # Payment gateway API
├── models/            # Data access layer
│   ├── Student_model.php
│   ├── Student_fee_model.php
│   └── Payment_model.php
└── views/             # UI templates (Bootstrap-based)
```

### Key Database Tables
- **student** - Student information (id, name, reg_no, phone, status)
- **student_fees** - Monthly fee records (student_id, month, year, amount, payment_status)
- **payments** - Payment transactions (student_fee_id, amount_paid, payment_date, payment_mode)
- **fees_master** - Fee type definitions

### Payment Status Flow
1. **Unpaid** - No payments received
2. **Partial** - Some payment received (Total Amount > Sum of Payments)
3. **Paid** - Full payment received

Status is automatically updated when payments are created.

## Important Business Logic

### Automatic Monthly Fee Generation
The system generates monthly fees for all active students on the 1st of each month.

**Automatic Execution (on user visits):**
- **Location:** `AutoFeeHook::checkMonthlyFee()` (application/hooks/AutoFeeHook.php)
- **Trigger:** Automatically runs when any user visits the site on the 1st
- **Method:** `Student_fee_model::generate_monthly_fees()`
- **Implementation:** Uses `insert_batch()` in chunks of 100 records for performance
- **Bill Month:** Current month (e.g., Jan 1st → generates January bills)
- **Due Date:** 20th of the current month
- **Duplicate Prevention:** Automatically skips students who already have bills for the month

**Manual Execution (via UI button):**
- **Location:** Student Fees page header (green "Generate Monthly Fees" button)
- **Route:** `/student-fees/generate-all`
- **Controller:** `Student_fees::generate_all_monthly_fees()`
- **Features:**
  - Confirmation modal before generation
  - Shows current month/year being generated
  - AJAX-based with loading spinner
  - Displays success statistics (generated, skipped, total)
  - Always visible for admin convenience
- **Use Cases:** Manual triggering for testing, missed auto-runs, or regeneration

### Late Fee Calculation
- **Method:** `Student_fee_model::update_all_late_fees()`
- Called before dashboard/fee listing
- Adds penalties for overdue payments

### MTB Payment Gateway Integration
- **Controller:** `Api/Mtb_gateway.php`
- **Authentication:** Bearer token required
- **Endpoints:**
  - GET `/api/mtb/billing-info` - Fetch billing information
  - POST `/api/mtb/payment-callback` - Handle payment confirmation
  - GET `/api/mtb/payment-status/{id}` - Check payment status
- Merchant ID: `PIONEER_DENTAL_001`

## Configuration Files

### Core Config
- **application/config/config.php** - Base URL, session, general settings
- **application/config/database.php** - Database connection
- **application/config/routes.php** - URL routing (default: `dashboard`)
- **application/config/autoload.php** - Auto-loaded libraries (database, url helper)

### Routes Structure
- Dashboard: `/` (default controller)
- Student Fees: `/student-fees`, `/student-fees/create`, `/student-fees/edit/{id}`, `/student-fees/generate-all`
- Payments: `/payments`, `/payments/create/{fee_id}`, `/payments/process/{fee_id}`
- API: `/api/mtb/*`

## Data Consistency

### Transactions
Models use database transactions for critical operations:
- Payment creation automatically updates fee status
- Monthly fee generation uses batch inserts
- Check `Payment_model::create()` and `Student_fee_model::generate_monthly_fees()` for examples

### Balance Calculation
```php
Balance = Total Amount - Sum(Payments where MTB status = 'success')
```
Only successful payments count toward fee balance.

## Code Patterns

### Controller Pattern
```php
// controllers/Example.php
class Example extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('example_model');
    }

    public function index() {
        $data['items'] = $this->example_model->get_all();
        $this->load->view('templates/header');
        $this->load->view('example/index', $data);
        $this->load->view('templates/footer');
    }
}
```

### Model Pattern
```php
// models/Example_model.php
class Example_model extends CI_Model {
    public function get_all($limit = 10, $offset = 0) {
        return $this->db->limit($limit, $offset)
                        ->where('status', 1)
                        ->get('table_name')
                        ->result();
    }
}
```

### Using Transactions
```php
$this->db->trans_start();
// Database operations here
$this->db->trans_complete();

if ($this->db->trans_status() === FALSE) {
    // Handle error
}
```

## Testing

- **Framework:** PHPUnit 4.x/5.x/9.x
- **Bootstrap:** `tests/Bootstrap.php`
- **Mocks:** Use `tests/mocks/` for CodeIgniter core mocks
- **Virtual Filesystem:** vfsStream for isolated testing

## Working with Dates

The system heavily uses month/year for fee tracking:
- Fees are generated monthly (automatically on 1st when users visit, or manually via button)
- Date format: `Y-m-d` (MySQL standard)
- Month/year stored separately in `student_fees` table for easier querying
- Bills generated on the 1st are for the current month with due date on the 20th

## Security Notes

- Use CodeIgniter's query builder to prevent SQL injection
- API endpoints require Bearer token authentication
- Soft deletes implemented (status = 0 instead of DELETE)
- Session management via CI's session library
- CORS headers configured for API endpoints
