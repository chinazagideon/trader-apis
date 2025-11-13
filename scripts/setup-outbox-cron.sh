#!/bin/bash

# Setup script for notification outbox cron job
# Run this script on your production server

set -e

echo "Setting up notification outbox cron job..."

# Get the absolute path to the project
PROJECT_PATH="/var/www/trader-apis"
LOG_FILE="/var/log/trader-apis-scheduler.log"

# Check if project directory exists
if [ ! -d "$PROJECT_PATH" ]; then
    echo "Error: Project directory $PROJECT_PATH does not exist"
    exit 1
fi

# Check if Docker container exists
if ! docker ps --format '{{.Names}}' | grep -q "^trader-apis-app$"; then
    echo "Error: Docker container 'trader-apis-app' is not running"
    exit 1
fi

# Create log file if it doesn't exist
touch "$LOG_FILE"
chmod 644 "$LOG_FILE"

# Add cron job (runs Laravel scheduler every minute)
CRON_ENTRY="* * * * * cd $PROJECT_PATH && docker exec trader-apis-app php artisan schedule:run >> $LOG_FILE 2>&1"

# Check if cron entry already exists
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    echo "Cron entry already exists. Updating..."
    # Remove old entry
    crontab -l 2>/dev/null | grep -v "schedule:run" | crontab -
fi

# Add new cron entry
(crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -

echo "✅ Cron job installed successfully!"
echo ""
echo "Cron entry:"
crontab -l | grep "schedule:run"
echo ""
echo "Log file: $LOG_FILE"
echo ""
echo "To verify it's working:"
echo "  - Check logs: tail -f $LOG_FILE"
echo "  - View cron: crontab -l"
echo "  - Test manually: cd $PROJECT_PATH && docker exec trader-apis-app php artisan schedule:run"
echo ""
echo "To remove the cron job:"
echo "  crontab -l | grep -v 'schedule:run' | crontab -"

