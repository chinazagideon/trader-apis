#!/bin/bash

# Cleanup old scheduled events
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan event:cleanup-scheduled
php artisan events:process-scheduled
php artisan events:cleanup-scheduled
php artisan events:process-scheduled
