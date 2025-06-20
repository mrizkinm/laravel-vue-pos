# Laravel Vue POS System

A modern Point of Sale (POS) system built with Laravel, Vue.js, and Inertia.js. This application provides a comprehensive solution for managing sales, inventory, purchases, suppliers, and customers.

## Features

- **Dashboard**
  - Real-time sales analytics
  - Visual data representation using Chart.js
  - Key performance indicators

- **Product Management**
  - Product categories
  - Product inventory tracking
  - Product pricing
  - Stock management

- **Sales Management**
  - Create and process sales
  - Sales history
  - Customer management
  - Sales reports

- **Purchase Management**
  - Create purchase orders
  - Manage suppliers
  - Purchase history
  - Stock updates

- **User Management**
  - Role-based access control
  - User authentication
  - Profile management

- **Settings**
  - Company settings
  - System preferences
  - Business configuration

## Tech Stack

- **Backend**
  - Laravel 12
  - PHP 8.2+
  - PostgreSQL

- **Frontend**
  - Vue.js 3
  - Inertia.js
  - TailwindCSS

## Prerequisites

- PHP >= 8.1
- Composer
- Node.js & NPM
- PostgreSQL
- Git

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd laravel-vue-pos
```

2. Install PHP dependencies
```bash
composer install
```

3. Install NPM dependencies
```bash
npm install
```

4. Create and configure .env file
```bash
cp .env.example .env
php artisan key:generate
```

5. Configure your database in .env file
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_SCHEMA=public
```

6. Run database migrations
```bash
php artisan migrate
```

7. Build assets
```bash
npm run build
```

## Development

1. Start the Laravel development server
```bash
php artisan serve
```

2. Start the Vite development server
```bash
npm run dev
```

## Usage

1. Access the application at `http://localhost:8000`
2. Login with your credentials
3. Start managing your business through the intuitive dashboard

