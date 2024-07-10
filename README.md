# Pet Shop Backend

## Introduction
This is the backend API for the Pet Shop application built using Laravel 11.

## Prerequisites
- PHP 8.3
- Composer
- MySQL
- Node.js and npm (for running frontend tasks, optional)

## Installation

### Step 1: Clone the repository
```sh
git clone https://github.com/Dante983/pet-shop-v2.git
cd pet-shop-backend
composer install
cp .env.example .env
php artisan migrate --seed

chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

## The backend should now be running at http://127.0.0.1:8000.


### Unit and Feature Tests

php artisan test

### API Documentation

composer require darkaonline/l5-swagger
php artisan l5-swagger:generate
