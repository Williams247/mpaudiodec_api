# Laravel commands to start up the application

## Create a Laravel application using Laravel
### composer create-project laravel/laravel mybackend-app

## Run migrations after installation
### php artisan migrate

## Run server locally to be sure that things works
### php artisan serve

## Add sample db props (FOR SQLITE)
### DB_USERNAME=root
### DB_PASSWORD=root
### DB_DATABASE=database/database.sqlite

## Run migrarions again
### php artisan migrate

## Get a token manager
### composer require laravel/sanctum

## Run token manager
### php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

## Run migrations again to register token manager
### php artisan migrate

## Create an auth controller
### php artisan make:controller AuthController

## Create notes table
### php artisan make:model Notes -m

## Install php api
### php artisan install:api

## After the installation of php api, put your routes in /routes/api.php

## List the available routes
### php artisan route:list

## Clear cache
### php artisan route:clear
### php artisan cache:clear

## Query db/system via the terminal
### php artisan tinker 

## NOTE: Any time you make modifications to a model please run migrations

## Refresh database (You lose all your data)
### php artisan migrate:fresh

## Create a middleware
### php artisan make:middleware EnsureAuthenticated

## Check current php version
### php artisan --version

## Fresh migration
### php artisan migrate:fresh
### Use when you update the migration and models

## Clears cache
### php artisan optimize:clear

## Create a mailer
## php artisan make:mail SendOtpMail
