#!/bin/bash

# Setup the project
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan event:cache --clear
php artisan route:cache
php artisan view:cache --clear
php artisan config:cache --clear
php artisan optimize --clear
php artisan optimize:clear --clear
php artisan queue:restart --clear
php artisan queue:listen --clear
php artisan queue:work --queue=default --clear
php artisan queue:work --queue=default --clear
