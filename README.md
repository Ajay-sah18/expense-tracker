# Expense Tracker

A PHP-based Expense Tracker web application that helps users manage and monitor their daily expenses.

## About the Project

Expense Tracker is a learning project built using PHP, MySQL, PDO, HTML, CSS, and JavaScript.

Users can register, log in, record expenses, edit or delete their expenses, and view spending summaries through a dashboard.

The application uses session-based authentication, prepared SQL statements, and CSRF protection to improve security.

## Features

- User registration and login
- Secure password hashing and verification
- Session-based authentication
- Automatic session timeout after inactivity
- Add new expenses
- View all personal expenses
- Edit and delete expenses
- Search and filter expenses
- Sort and paginate expense records
- Export expenses to CSV
- Dashboard with spending summaries
- Monthly expense overview
- Category-wise expense breakdown
- Responsive user interface
- CSRF protection
- Input validation and output escaping
- PDO prepared statements for database operations

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP 8.2 | Backend development |
| MySQL | Database management |
| PDO | Database connectivity |
| HTML5 | Page structure |
| CSS3 | Styling and responsive design |
| JavaScript | Interactive features |
| Chart.js | Dashboard charts |
| Composer | PHP dependency management |
| PHPUnit | Existing project test suite |
| Git and GitHub | Version control |

## Requirements

To run this project locally, you need:

- PHP 8.2 or later
- MySQL
- Apache server (XAMPP recommended)
- Composer
- Git
- A web browser

## Installation and Setup

Follow these steps to run the project on your local machine.

### 1. Clone the Repository

```bash
git clone https://github.com/Ajay-sah18/expense-tracker.git
cd expense-tracker
```

### 2. Install Dependencies

Install the required PHP packages using Composer:

```bash
composer install
```

### 3. Create the Database

1. Start Apache and MySQL using XAMPP.
2. Open phpMyAdmin at `http://localhost/phpmyadmin`.
3. Create a new database named `expense_tracker`.
4. Select the database and open the **Import** tab.
5. Import the `database/schema.sql` file from the project folder.

### 4. Configure Environment Variables

Create your own `.env` file by copying `.env.example`:

```bash
copy .env.example .env
```

Open `.env` and update the database credentials according to your local MySQL configuration:

```env
DB_HOST=localhost
DB_NAME=expense_tracker
DB_USER=root
DB_PASS=
```

**Important:** Never commit your `.env` file or share your database credentials publicly.

### 5. Run the Application

If you are using XAMPP, place the project inside the `htdocs` directory or configure Apache to serve the project folder.

Open the login page in your browser:

```text
http://localhost/PHP-Learning/expense-tracker/auth/login.php
```

Register a new account and start managing your expenses.

## Project Structure

```text
expense-tracker/
|-- assets/          # CSS, JavaScript and other assets
|-- auth/            # Login and registration
|-- config/          # Database and authentication configuration
|-- database/        # Database schema
|-- src/             # Reusable PHP classes
|-- tests/           # Existing project tests
|-- vendor/          # Composer dependencies
|-- .env.example     # Environment configuration template
|-- composer.json    # Composer configuration
|-- composer.lock    # Locked dependency versions
|-- README.md        # Project documentation
```
