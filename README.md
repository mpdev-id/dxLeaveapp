# DX-Leave Management App

A modern, API-driven leave management system built with Laravel 11. It provides a comprehensive suite of tools for employees and administrators to handle leave requests, entitlements, and approval workflows efficiently.

## Key Features

- **Admin Dashboard**: At-a-glance statistics for users, departments, and leave requests.
- **Interactive Charts**: A monthly leave report chart that can be toggled between a stacked bar chart and a line chart, with filters for month and year.
- **Leave & Holiday Calendar**: A FullCalendar implementation showing all approved leaves and public holidays.
- **Role-Based Access Control**: Granular permissions for Super Admin, Manager, and Employee roles using `spatie/laravel-permission`.
- **Dynamic Approval Workflows**: Create custom, multi-step approval chains for different leave types.
- **Leave Management**: Full CRUD functionality for leave types, employee entitlements, and leave requests.
- **User Management**: Manage employees, departments, and their roles.
- **API Driven**: Built with a robust API backend powered by Laravel Sanctum for authentication.
- **Modern Frontend**: A responsive frontend built with Blade, Alpine.js, Tailwind CSS, and DaisyUI.

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Blade, Alpine.js, Tailwind CSS, DaisyUI, Chart.js, FullCalendar
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Development**: Vite

## Prerequisites

- PHP >= 8.2
- Composer
- Node.js & npm
- A MySQL database

## Installation Guide

1.  **Clone the repository:**
    ```sh
    git clone https://github.com/mnprasetya/CutikuyApp.git
    cd CutikuyApp
    ```

2.  **Install dependencies:**
    ```sh
    composer install
    npm install
    ```

3.  **Setup environment file:**
    ```sh
    cp .env.example .env
    ```

4.  **Generate application key:**
    ```sh
    php artisan key:generate
    ```

5.  **Configure your `.env` file:**
    Update the following lines with your local database credentials:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

6.  **Run database migrations and seeders:**
    This command will create all necessary tables and populate them with default roles, permissions, and user accounts.
    ```sh
    php artisan migrate --seed
    ```

7.  **Build frontend assets:**
    ```sh
    npm run dev
    ```

8.  **Run the development server:**
    ```sh
    php artisan serve
    ```
    The application will be available at `http://127.0.0.1:8000`.

## Usage

After running the migrations and seeders, you can log in with the default Super Admin account:

-   **Email:** `mnprasetya@posco.net`
-   **Password:** `@mnprasetya12`

You can access the admin dashboard by navigating to `/login`.
