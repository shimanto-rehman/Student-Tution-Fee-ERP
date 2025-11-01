#!/bin/bash

###############################################################################
# Monthly Fee Generation Cron Script
#
# This script automatically generates monthly student fees on the 1st of each month
#
# Usage:
#   ./cron_monthly_fees.sh
#
# Crontab Entry (runs at 3:00 AM on the 1st of every month):
#   0 3 1 * * /home/galib/personal/pioneer-dental/cron_monthly_fees.sh >> /home/galib/personal/pioneer-dental/logs/cron_monthly_fees.log 2>&1
#
###############################################################################

# Configuration
PROJECT_DIR="/home/galib/personal/pioneer-dental"
PHP_BIN="/usr/bin/php"  # Change this if your PHP binary is in a different location
LOG_DIR="$PROJECT_DIR/logs"
LOG_FILE="$LOG_DIR/cron_monthly_fees.log"
SECRET_KEY="CHANGE_THIS_SECRET_KEY_IN_PRODUCTION"  # Must match the key in Cron.php

# Ensure log directory exists
mkdir -p "$LOG_DIR"

# Start logging
echo "========================================" | tee -a "$LOG_FILE"
echo "Monthly Fee Generation Cron Job" | tee -a "$LOG_FILE"
echo "Started at: $(date '+%Y-%m-%d %H:%M:%S')" | tee -a "$LOG_FILE"
echo "========================================" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Change to project directory
cd "$PROJECT_DIR" || {
    echo "ERROR: Failed to change to project directory: $PROJECT_DIR" | tee -a "$LOG_FILE"
    exit 1
}

echo "Project directory: $PROJECT_DIR" | tee -a "$LOG_FILE"
echo "PHP binary: $PHP_BIN" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check if PHP is available
if ! command -v "$PHP_BIN" &> /dev/null; then
    echo "ERROR: PHP binary not found at $PHP_BIN" | tee -a "$LOG_FILE"
    echo "Please update PHP_BIN variable in this script" | tee -a "$LOG_FILE"
    exit 1
fi

echo "PHP Version: $($PHP_BIN -v | head -n 1)" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Execute the cron job
echo "Executing monthly fee generation..." | tee -a "$LOG_FILE"
echo "Command: $PHP_BIN index.php cron generate_monthly_fees $SECRET_KEY" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Run the PHP script and capture output
OUTPUT=$($PHP_BIN index.php cron generate_monthly_fees "$SECRET_KEY" 2>&1)
EXIT_CODE=$?

# Log the output
echo "$OUTPUT" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

# Check exit code
if [ $EXIT_CODE -eq 0 ]; then
    echo "SUCCESS: Monthly fee generation completed successfully" | tee -a "$LOG_FILE"
else
    echo "ERROR: Monthly fee generation failed with exit code: $EXIT_CODE" | tee -a "$LOG_FILE"
fi

echo "" | tee -a "$LOG_FILE"
echo "Finished at: $(date '+%Y-%m-%d %H:%M:%S')" | tee -a "$LOG_FILE"
echo "========================================" | tee -a "$LOG_FILE"
echo "" | tee -a "$LOG_FILE"

exit $EXIT_CODE
