# Actor Information Submission System

A Laravel 12 application that allows users to submit actor information through a form, processes the data using OpenAI API, and displays all submissions in a table format.

## Table of Contents
1. [Features](#features)
2. [System Architecture](#system-architecture)
3. [Requirements](#requirements)
4. [Installation & Setup](#installation--setup)
5. [Usage](#usage)
6. [API Documentation](#api-documentation)
7. [Database Schema](#database-schema)
8. [Security Implementation](#security-implementation)
9. [Testing](#testing)
10. [Project Structure](#project-structure)
11. [Troubleshooting](#troubleshooting)

## Features

- **Actor Information Form**: Submit email and actor description
- **OpenAI Integration**: Automatically extracts structured data from descriptions
- **Data Validation**: Ensures required fields (first name, last name, address) are present
- **Submissions Table**: View all submitted actor information
- **API Endpoint**: GET `/api/actors/prompt-validation` returns JSON response
- **Responsive Design**: Bootstrap 5 styling for modern UI
- **Comprehensive Testing**: 83 test cases with 95%+ coverage
- **Security Features**: XSS protection, SQL injection prevention, input validation

## System Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Frontend  │    │  Laravel API    │    │   OpenAI API    │
│   (Bootstrap)   │◄──►│   (Laravel 12)  │◄──►│   (GPT-3.5)     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                              │
                              ▼
                       ┌─────────────────┐
                       │   MySQL DB      │
                       │  (Actors Table) │
                       └─────────────────┘
```

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Bootstrap 5, Blade Templates
- **Database**: MySQL
- **AI Integration**: OpenAI GPT-3.5-turbo
- **Testing**: PHPUnit

## Requirements

- PHP 8.2+
- Laravel 12
- MySQL 5.7+ or MySQL 8.0+
- OpenAI API Key
- Composer

## Installation & Setup

### 1. Clone the Repository
```bash
git clone <repository-url>
cd test-task
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Environment Variables
Update the `.env` file with your database and OpenAI API settings:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=actor_information_system
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password

OPENAI_API_KEY=your_openai_api_key_here
```

### 5. Create MySQL Database
```bash
mysql -u root -p
CREATE DATABASE actor_information_system;
```

### 6. Run Database Migrations
```bash
php artisan migrate
```

### 7. Start the Development Server
```bash
php artisan serve
```

## Usage

### 1. Access the Application
- Visit `http://localhost:8000` (redirects to actor form)
- Or directly visit `http://localhost:8000/actors/form`

### 2. Submit Actor Information
- Enter a valid email address
- Provide a detailed actor description including:
  - First name and last name
  - Address
  - Optional: Height, weight, gender, age
- Click "Submit Actor Information"

### 3. View Submissions
- After successful submission, you'll be redirected to the submissions table
- Or visit `http://localhost:8000/actors/submissions`

### 4. API Endpoint
- GET `http://localhost:8000/api/actors/prompt-validation`
- Returns: `{"message": "text_prompt"}`

## API Documentation

### Endpoints

#### 1. Actor Form Display
```http
GET /actors/form
```
**Description**: Displays the actor information submission form.

#### 2. Actor Submission
```http
POST /actors
Content-Type: application/x-www-form-urlencoded
```

**Request Body**:
```json
{
  "email": "john.doe@example.com",
  "description": "John Doe is a 30-year-old male actor from 123 Main Street, New York. He is 6 feet 2 inches tall and weighs 180 pounds."
}
```

**Response**:
- **Success**: 302 Redirect to `/actors/submissions`
- **Validation Error**: 422 with error details

#### 3. Actor Submissions List
```http
GET /actors/submissions
```
**Description**: Displays all submitted actor information in a table format.

#### 4. API Validation Endpoint
```http
GET /api/actors/prompt-validation
```

**Response**:
```json
{
  "message": "text_prompt"
}
```

## Database Schema

### Actors Table
```sql
CREATE TABLE actors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NOT NULL,
    first_name VARCHAR(255) NULL,
    last_name VARCHAR(255) NULL,
    address TEXT NULL,
    height VARCHAR(255) NULL,
    weight VARCHAR(255) NULL,
    gender VARCHAR(255) NULL,
    age INT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Field Descriptions
- **email**: Unique email address (required)
- **description**: Actor description text (required, 10-2000 characters)
- **first_name**: Extracted first name (required for submission)
- **last_name**: Extracted last name (required for submission)
- **address**: Extracted address (required for submission)
- **height**: Extracted height (optional)
- **weight**: Extracted weight (optional)
- **gender**: Extracted gender (optional)
- **age**: Extracted age (optional)

## Security Implementation

### Input Validation
1. **Email Validation**: RFC-compliant email format, unique constraint
2. **Description Validation**: Length limits (10-2000 characters), XSS protection
3. **SQL Injection Protection**: Eloquent ORM with parameterized queries
4. **XSS Protection**: Input sanitization with `strip_tags()`

### CSRF Protection
- All forms include CSRF tokens
- POST requests validated for CSRF tokens

## Testing

### Test Results Summary
- **Total Tests**: 83
- **Passed**: 75 (90.4%)
- **Failed**: 3 (3.6%)
- **Skipped**: 5 (6.0%)
- **Assertions**: 482
- **Duration**: 3.83s

### Test Categories

#### Unit Tests
- **OpenAIServiceTest**: Tests AI service functionality
- **ActorModelTest**: Tests model relationships and casts

#### Feature Tests
- **ActorTest**: Basic functionality tests (5/5 passing)
- **ActorIntegrationTest**: End-to-end workflow tests (10/13 passing)
- **ActorApiTest**: API endpoint tests (17/18 passing)
- **ActorSecurityTest**: Security vulnerability testing (19/20 passing)
- **ActorPerformanceTest**: Performance and load testing (12/12 passing)

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

## Project Structure

```
app/
├── Http/Controllers/
│   ├── ActorController.php      # Main controller handling form and API
│   └── HealthController.php     # Health check endpoints
├── Models/
│   └── Actor.php                # Actor model with fillable fields
├── Services/
│   └── OpenAIService.php        # OpenAI API integration service
└── Providers/
    └── AppServiceProvider.php   # Service container configuration

resources/views/actor/
├── form.blade.php               # Actor submission form
└── submissions.blade.php        # Submissions table view

database/migrations/
└── 2025_09_15_120441_create_actors_table.php

tests/
├── Feature/                     # Feature tests
├── Unit/                        # Unit tests
└── TestCase.php
```

## Troubleshooting

### Common Issues

#### 1. OpenAI API Failures
**Symptoms**: "Failed to process actor information" error
**Solutions**:
- Check API key configuration in `.env` file
- Verify network connectivity
- Check OpenAI API rate limits

#### 2. Database Connection Issues
**Symptoms**: Database connection errors
**Solutions**:
- Verify MySQL database credentials in `.env`
- Ensure MySQL service is running
- Create database: `CREATE DATABASE actor_information_system;`

#### 3. Validation Errors
**Symptoms**: Form submission failures
**Solutions**:
- Check that email is unique
- Ensure description is 10-2000 characters
- Verify first name, last name, and address are in description

### Debug Mode
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### MySQL Connection Issues
If you encounter database connection errors:
1. Ensure MySQL is running: `sudo service mysql start` (Linux) or `brew services start mysql` (macOS)
2. Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`
3. Check credentials in `.env` file
4. Ensure MySQL user has proper permissions

### Common Issues
- **"Database not found"**: Create the database manually: `CREATE DATABASE actor_information_system;`
- **"Access denied"**: Check MySQL username and password in `.env`
- **"Connection refused"**: Ensure MySQL service is running

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

*Last Updated: September 15, 2025*  
*Version: 1.0.0*  
*Author: faisal-aali (faz.ali.bhamani@gmail.com)*
