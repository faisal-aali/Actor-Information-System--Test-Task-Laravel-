# Actor Information Submission System

A Laravel 12 application that allows users to submit actor information through a form, processes the data using OpenAI API, and displays all submissions in a table format.

## Features

- **Actor Information Form**: Submit email and actor description
- **OpenAI Integration**: Automatically extracts structured data from descriptions
- **Data Validation**: Ensures required fields (first name, last name, address) are present
- **Submissions Table**: View all submitted actor information
- **API Endpoint**: GET `/api/actors/prompt-validation` returns JSON response
- **Responsive Design**: Bootstrap 5 styling for modern UI

## Requirements

- PHP 8.2+
- Laravel 12
- MySQL 5.7+ or MySQL 8.0+
- OpenAI API Key
- Composer

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd test-task
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Environment Variables**
   Update the `.env` file with your database and OpenAI API settings:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=actor_information_system
   DB_USERNAME=your_mysql_username
   DB_PASSWORD=your_mysql_password
   
   OPENAI_API_KEY=your_openai_api_key_here
   ```

5. **Create MySQL Database**
   ```bash
   mysql -u root -p
   CREATE DATABASE actor_information_system;
   ```

6. **Run Database Migrations**
   ```bash
   php artisan migrate
   ```

7. **Start the Development Server**
   ```bash
   php artisan serve
   ```

## Usage

1. **Access the Application**
   - Visit `http://localhost:8000` (redirects to actor form)
   - Or directly visit `http://localhost:8000/actors/form`

2. **Submit Actor Information**
   - Enter a valid email address
   - Provide a detailed actor description including:
     - First name and last name
     - Address
     - Optional: Height, weight, gender, age
   - Click "Submit Actor Information"

3. **View Submissions**
   - After successful submission, you'll be redirected to the submissions table
   - Or visit `http://localhost:8000/actors/submissions`

4. **API Endpoint**
   - GET `http://localhost:8000/api/actors/prompt-validation`
   - Returns: `{"message": "text_prompt"}`

## Testing

Run the test suite:
```bash
php artisan test
```

## Project Structure

- `app/Models/Actor.php` - Actor model with fillable fields
- `app/Http/Controllers/ActorController.php` - Main controller handling form and API
- `app/Services/OpenAIService.php` - OpenAI API integration service
- `resources/views/actor/` - Blade templates for form and submissions
- `database/migrations/` - Database schema for actors table
- `tests/Feature/ActorTest.php` - Feature tests for the application

## Database Schema

The `actors` table includes:
- `id` - Primary key
- `email` - Unique email address
- `description` - Original actor description
- `first_name` - Extracted first name
- `last_name` - Extracted last name
- `address` - Extracted address
- `height` - Extracted height (optional)
- `weight` - Extracted weight (optional)
- `gender` - Extracted gender (optional)
- `age` - Extracted age (optional)
- `created_at` / `updated_at` - Timestamps

## Error Handling

- **Validation Errors**: Form validation for required fields and unique email
- **OpenAI Errors**: Graceful handling of API failures
- **Missing Required Fields**: Specific error message when first name, last name, or address is missing

## Troubleshooting

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
