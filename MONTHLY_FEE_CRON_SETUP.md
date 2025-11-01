# Monthly Fee Generation - Cron Job Setup Guide

## Overview

This system automatically generates monthly student fee records on the **1st day of each month** at **3:00 AM**.

**Key Details:**
- **Run Date:** 1st of every month
- **Run Time:** 3:00 AM (configurable)
- **Bill Month:** Current month (e.g., Jan 1st → generates January bills)
- **Due Date:** 20th of the bill month
- **Execution:** Linux cron job (production-ready)

---

## Quick Setup (5 Minutes)

### Step 1: Update Secret Key

**IMPORTANT:** Change the secret key for security before setting up the cron job.

1. Open `application/controllers/Cron.php`
2. Find line 20:
   ```php
   private $cron_secret_key = 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION';
   ```
3. Replace with a strong random key:
   ```php
   private $cron_secret_key = 'your_strong_random_secret_key_here';
   ```

4. Open `cron_monthly_fees.sh`
5. Find line 18:
   ```bash
   SECRET_KEY="CHANGE_THIS_SECRET_KEY_IN_PRODUCTION"
   ```
6. Replace with the SAME key:
   ```bash
   SECRET_KEY="your_strong_random_secret_key_here"
   ```

**Generate a strong key using:**
```bash
openssl rand -base64 32
```

### Step 2: Update File Paths (if needed)

Edit `cron_monthly_fees.sh` and verify these paths:

```bash
PROJECT_DIR="/home/galib/personal/pioneer-dental"  # Line 14
PHP_BIN="/usr/bin/php"  # Line 15
```

**Find your PHP path:**
```bash
which php
```

### Step 3: Test the Setup

#### Test 1: PHP CLI Execution
```bash
cd /home/galib/personal/pioneer-dental
php index.php cron test your_secret_key_here
```

**Expected output:**
```json
{
    "success": true,
    "message": "Cron controller is working correctly",
    "timestamp": "2025-01-15 14:30:00",
    ...
}
```

#### Test 2: Bash Script Execution
```bash
cd /home/galib/personal/pioneer-dental
./cron_monthly_fees.sh
```

Check the log file:
```bash
cat logs/cron_monthly_fees.log
```

#### Test 3: Manual Fee Generation (Optional)
```bash
php index.php cron generate_monthly_fees your_secret_key_here
```

This will generate fees for the current month. Review the output to ensure it works correctly.

### Step 4: Install Cron Job

1. Open crontab for editing:
   ```bash
   crontab -e
   ```

2. Add this line at the end:
   ```cron
   0 3 1 * * /home/galib/personal/pioneer-dental/cron_monthly_fees.sh >> /home/galib/personal/pioneer-dental/logs/cron_monthly_fees.log 2>&1
   ```

3. Save and exit (in nano: `Ctrl+X`, then `Y`, then `Enter`)

4. Verify cron job is installed:
   ```bash
   crontab -l
   ```

**Done!** The system will now automatically generate fees on the 1st of each month at 3 AM.

---

## Cron Schedule Explanation

```
0 3 1 * * command
│ │ │ │ │
│ │ │ │ └─── Day of week (0-7, where 0 and 7 are Sunday)
│ │ │ └───── Month (1-12)
│ │ └─────── Day of month (1-31)
│ └───────── Hour (0-23)
└─────────── Minute (0-59)
```

**Current schedule:** `0 3 1 * *`
- Minute: 0
- Hour: 3 (3:00 AM)
- Day: 1 (first day of month)
- Month: * (every month)
- Day of week: * (any day)

**Alternative schedules:**

- Run at 2:00 AM on the 1st: `0 2 1 * *`
- Run at midnight on the 1st: `0 0 1 * *`
- Run at 6:00 AM on the 1st: `0 6 1 * *`

---

## How It Works

### 1. Automatic Triggers (3 ways)

#### A. Cron Job (Recommended for Production)
- Runs via Linux cron on the 1st at 3 AM
- Doesn't require website traffic
- Most reliable method
- Logs to `logs/cron_monthly_fees.log`

#### B. Hook-Based Trigger (Backup)
- Runs automatically when someone visits the website on the 1st
- Defined in `application/hooks/AutoFeeHook.php`
- Logs to CodeIgniter logs
- Works as a safety net if cron fails

#### C. Manual Trigger (Testing/Emergency)
Execute via command line:
```bash
php index.php cron generate_monthly_fees YOUR_SECRET_KEY
```

Or via web browser (not recommended for production):
```
http://yourdomain.com/cron/generate_monthly_fees?key=YOUR_SECRET_KEY
```

### 2. The Process Flow

```
Cron triggers on 1st at 3 AM
        ↓
Calls cron_monthly_fees.sh script
        ↓
Script executes: php index.php cron generate_monthly_fees SECRET_KEY
        ↓
Cron.php controller validates API key
        ↓
Calls Student_fee_model::generate_monthly_fees()
        ↓
Model checks for existing bills (avoids duplicates)
        ↓
Generates bills for all active students
        ↓
Inserts in batches of 100 records
        ↓
Returns detailed status report
        ↓
Logs results to file and database
```

### 3. Database Records Created

For each active student, a record is inserted into `student_fees` table:

```sql
INSERT INTO student_fees (
    student_id,
    fee_id,
    month_year,
    bill_month,
    bill_year,
    due_date,
    payment_status,
    base_amount,
    late_fee,
    total_amount,
    status,
    created,
    bill_unique_id,
    comments
) VALUES (
    123,                        -- student ID
    2,                          -- fee ID (from fees_master)
    '2025-01',                  -- January 2025
    1,                          -- January
    2025,                       -- Year
    '2025-01-20',              -- Due date (20th)
    'Unpaid',                   -- Initial status
    5000.00,                    -- Base amount
    0.00,                       -- No late fee initially
    5000.00,                    -- Total amount
    '1',                        -- Active
    '2025-01-01 03:00:15',     -- Created timestamp
    202501000123,               -- Unique bill ID
    'Auto-generated monthly bill'
);
```

---

## Monitoring & Logs

### Check Cron Execution Logs
```bash
tail -f /home/galib/personal/pioneer-dental/logs/cron_monthly_fees.log
```

### Check CodeIgniter Application Logs
```bash
tail -f /home/galib/personal/pioneer-dental/application/logs/log-$(date +%Y-%m-%d).php
```

### View Last Execution
```bash
tail -100 /home/galib/personal/pioneer-dental/logs/cron_monthly_fees.log
```

### Check Cron Status
```bash
# View installed cron jobs
crontab -l

# Check if cron service is running
systemctl status cron   # Ubuntu/Debian
systemctl status crond  # CentOS/RHEL
```

### Database Verification

After execution, verify records were created:

```sql
-- Check bills generated this month
SELECT COUNT(*) as total_bills,
       SUM(total_amount) as total_amount
FROM student_fees
WHERE bill_month = MONTH(CURDATE())
  AND bill_year = YEAR(CURDATE())
  AND status = '1';

-- View latest generated bills
SELECT sf.*, s.name, s.reg_no
FROM student_fees sf
JOIN student s ON s.id = sf.student_id
WHERE bill_month = MONTH(CURDATE())
  AND bill_year = YEAR(CURDATE())
  AND status = '1'
ORDER BY sf.created DESC
LIMIT 10;
```

---

## Troubleshooting

### Issue 1: Cron Job Not Running

**Symptoms:** No new bills on the 1st, no log entries

**Solutions:**

1. Check if cron service is running:
   ```bash
   systemctl status cron  # or crond
   ```

2. Verify cron job is installed:
   ```bash
   crontab -l | grep monthly_fees
   ```

3. Check cron execution permissions:
   ```bash
   ls -la /home/galib/personal/pioneer-dental/cron_monthly_fees.sh
   # Should show: -rwxr-xr-x (executable)
   ```

4. Run script manually to test:
   ```bash
   /home/galib/personal/pioneer-dental/cron_monthly_fees.sh
   ```

### Issue 2: "Invalid API Key" Error

**Solutions:**

1. Ensure SECRET_KEY matches in both files:
   - `application/controllers/Cron.php` (line 20)
   - `cron_monthly_fees.sh` (line 18)

2. Check for extra spaces or quotes

3. Test with explicit key:
   ```bash
   php index.php cron generate_monthly_fees "your_exact_key"
   ```

### Issue 3: PHP Command Not Found

**Error:** `PHP binary not found`

**Solutions:**

1. Find PHP location:
   ```bash
   which php
   # Output: /usr/bin/php or /usr/local/bin/php
   ```

2. Update `cron_monthly_fees.sh` line 15:
   ```bash
   PHP_BIN="/usr/local/bin/php"  # Use your actual path
   ```

### Issue 4: Duplicate Bills Generated

**Symptoms:** Multiple bills for the same student/month

**This shouldn't happen** - the system checks for duplicates. If it does:

1. Check logs for errors during duplicate check
2. Verify `bill_month`, `bill_year`, `fee_id` fields are properly set
3. Run this query to find duplicates:
   ```sql
   SELECT student_id, bill_month, bill_year, COUNT(*) as count
   FROM student_fees
   WHERE status = '1'
   GROUP BY student_id, bill_month, bill_year
   HAVING count > 1;
   ```

### Issue 5: No Students Found

**Error:** "No active students found"

**Solutions:**

1. Check if students exist and are active:
   ```sql
   SELECT COUNT(*) FROM student WHERE status = 1;
   ```

2. Activate students if needed:
   ```sql
   UPDATE student SET status = 1 WHERE status = 0;
   ```

### Issue 6: Monthly Fee Not Configured

**Error:** "Monthly fee not configured in fees_master"

**Solutions:**

1. Check fees_master table:
   ```sql
   SELECT * FROM fees_master WHERE fee_type = 'monthly' AND status = 1;
   ```

2. If missing, insert a monthly fee record:
   ```sql
   INSERT INTO fees_master (fee_type, fee_name, fee_amount, status)
   VALUES ('monthly', 'Monthly Tuition Fee', 5000.00, 1);
   ```

---

## Advanced Configuration

### Change Generation Time

Edit crontab to change execution time:

```bash
# 2:00 AM on the 1st
0 2 1 * * /path/to/cron_monthly_fees.sh >> /path/to/logs/cron_monthly_fees.log 2>&1

# Midnight on the 1st
0 0 1 * * /path/to/cron_monthly_fees.sh >> /path/to/logs/cron_monthly_fees.log 2>&1
```

### Email Notifications

Add email notification to cron:

1. Install mail utility:
   ```bash
   sudo apt-get install mailutils  # Ubuntu/Debian
   ```

2. Update crontab:
   ```cron
   MAILTO="admin@yourdomain.com"
   0 3 1 * * /path/to/cron_monthly_fees.sh >> /path/to/logs/cron_monthly_fees.log 2>&1
   ```

### Multiple Execution Modes

You can run the fee generation in different ways:

```bash
# 1. Via cron script (recommended)
./cron_monthly_fees.sh

# 2. Direct PHP CLI
php index.php cron generate_monthly_fees YOUR_KEY

# 3. Via curl (web request)
curl "http://yourdomain.com/cron/generate_monthly_fees?key=YOUR_KEY"

# 4. Via wget
wget -O - "http://yourdomain.com/cron/generate_monthly_fees?key=YOUR_KEY"
```

---

## Security Best Practices

1. **Strong Secret Key**
   - Use at least 32 characters
   - Mix of letters, numbers, symbols
   - Generate with: `openssl rand -base64 32`

2. **File Permissions**
   ```bash
   chmod 600 application/controllers/Cron.php
   chmod 700 cron_monthly_fees.sh
   chmod 700 logs/
   ```

3. **Restrict Web Access**

   Add to `.htaccess` or Apache config:
   ```apache
   <Files "Cron.php">
       Order Deny,Allow
       Deny from all
       Allow from 127.0.0.1
   </Files>
   ```

4. **Use Environment Variables** (Optional)

   Store secret key in environment instead of hardcoding:
   ```bash
   export CRON_SECRET_KEY="your_secret_key"
   ```

---

## Testing Before Production

### Step 1: Test Fee Generation Logic

Run a test generation:
```bash
php index.php cron generate_monthly_fees YOUR_KEY
```

Review the output carefully:
- Check generated count
- Check skipped count
- Verify execution time

### Step 2: Verify Database Records

```sql
-- Check the latest bill
SELECT * FROM student_fees
ORDER BY created DESC LIMIT 1;

-- Verify bill_unique_id format
-- Should be: YYYYMMSSSSSS (e.g., 202501000123)
```

### Step 3: Test Duplicate Prevention

Run the generation twice:
```bash
php index.php cron generate_monthly_fees YOUR_KEY
php index.php cron generate_monthly_fees YOUR_KEY
```

Second run should skip all students (already generated).

### Step 4: Test Cron Schedule

Set a test cron for the next minute:
```bash
# If current time is 14:30, set for 14:31
31 14 * * * /path/to/cron_monthly_fees.sh >> /path/to/test_cron.log 2>&1
```

Wait and check the log at 14:32.

---

## Migration from Old System

If you were using the old `autoInsertMonthlyFees()` method that ran on the 30th:

### What Changed:

1. **Execution Date:** 30th → 1st
2. **Bill Month:** Next month → Current month
3. **Due Date:** Last day → 20th
4. **Method:** `autoInsertMonthlyFees()` → `generate_monthly_fees()`
5. **Execution:** Hook-based → Cron job

### Transition Steps:

1. **Before the 1st of next month:**
   - Set up and test the new cron job
   - Update the secret key
   - Run a test execution

2. **On the 1st:**
   - Monitor the cron execution
   - Verify bills are generated correctly
   - Check logs for any errors

3. **After successful execution:**
   - You can optionally deprecate old methods
   - Keep hook-based trigger as backup

---

## Support

For issues or questions:

1. Check logs first
2. Review troubleshooting section
3. Run test commands
4. Verify database configuration

**Log locations:**
- Cron: `/home/galib/personal/pioneer-dental/logs/cron_monthly_fees.log`
- Application: `/home/galib/personal/pioneer-dental/application/logs/`

---

## Summary Checklist

- [ ] Update secret key in `Cron.php`
- [ ] Update secret key in `cron_monthly_fees.sh`
- [ ] Update file paths in script (if needed)
- [ ] Test: `php index.php cron test YOUR_KEY`
- [ ] Test: `./cron_monthly_fees.sh`
- [ ] Test: Manual fee generation
- [ ] Install cron job: `crontab -e`
- [ ] Verify cron job: `crontab -l`
- [ ] Monitor first execution on the 1st
- [ ] Verify database records created
- [ ] Set up log monitoring

---

**Last Updated:** January 2025
**System Version:** Pioneer Dental v1.0
