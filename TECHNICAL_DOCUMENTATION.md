# Actor Information System - Technical Documentation

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [API Documentation](#api-documentation)
3. [Database Schema](#database-schema)
4. [Security Implementation](#security-implementation)
5. [Testing Strategy](#testing-strategy)
6. [Performance Considerations](#performance-considerations)
7. [Deployment Guide](#deployment-guide)
8. [Monitoring & Health Checks](#monitoring--health-checks)
9. [Troubleshooting](#troubleshooting)
10. [Contributing Guidelines](#contributing-guidelines)

## Architecture Overview

### System Components

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
- **Database**: MySQL (Development and Production)
- **AI Integration**: OpenAI GPT-3.5-turbo
- **Caching**: Laravel Cache (Database Driver)
- **Documentation**: OpenAPI/Swagger
- **Testing**: PHPUnit with Feature, Unit, Integration, Performance, and Security tests

## API Documentation

### Base URL
```
Development: http://localhost:8000
Production: https://your-domain.com
```

### Authentication
Currently, the API does not require authentication. All endpoints are publicly accessible.

### Endpoints

#### 1. Actor Form Display
```http
GET /actors/form
```
**Description**: Displays the actor information submission form.

**Response**: HTML form page

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

**Response**: HTML page with actor data table

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

#### 5. Health Check Endpoints

##### Overall Health
```http
GET /health
```

**Response**:
```json
{
  "status": "healthy",
  "timestamp": "2025-09-15T12:00:00.000Z",
  "version": "1.0.0",
  "services": {
    "database": {
      "status": "healthy",
      "response_time_ms": 15.5,
      "database_size_mb": 12.3
    },
    "cache": {
      "status": "healthy",
      "response_time_ms": 2.1
    },
    "openai": {
      "status": "healthy",
      "response_time_ms": 1250.8,
      "model": "gpt-3.5-turbo"
    }
  },
  "metrics": {
    "memory_usage_mb": 45.2,
    "memory_peak_mb": 67.8,
    "uptime_seconds": 3600
  },
  "response_time_ms": 1275.2
}
```

##### Individual Service Health
```http
GET /health/database
GET /health/cache
GET /health/openai
```

### Swagger Documentation
Interactive API documentation is available at:
```
http://localhost:8000/api/documentation
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

### Indexes
- **Primary Key**: `id`
- **Unique Index**: `email`
- **Performance Indexes**: `created_at` (for ordering)

### Data Types
- **Email**: VARCHAR(255) with email validation
- **Description**: TEXT with length validation (10-2000 characters)
- **Names**: VARCHAR(255) nullable
- **Address**: TEXT nullable
- **Physical Attributes**: VARCHAR(255) nullable
- **Age**: INTEGER nullable

## Security Implementation

### Input Validation
1. **Email Validation**: RFC-compliant email format, unique constraint
2. **Description Validation**: Length limits (10-2000 characters), XSS protection
3. **SQL Injection Protection**: Eloquent ORM with parameterized queries
4. **XSS Protection**: Blade template auto-escaping

### Security Headers (Recommended)
```php
// In middleware or response headers
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
```

### CSRF Protection
- All forms include CSRF tokens
- POST requests validated for CSRF tokens

### Rate Limiting (Recommended)
```php
// In routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes
});
```

## Testing Strategy

### Test Categories

#### 1. Unit Tests
- **OpenAIServiceTest**: Tests AI service functionality
- **ActorModelTest**: Tests model relationships and casts
- **ValidationTest**: Tests input validation logic

#### 2. Feature Tests
- **ActorTest**: Basic functionality tests
- **ActorIntegrationTest**: End-to-end workflow tests
- **ActorApiTest**: API endpoint tests

#### 3. Performance Tests
- **ActorPerformanceTest**: Load testing and performance benchmarks
- Memory usage testing
- Database query optimization

#### 4. Security Tests
- **ActorSecurityTest**: Security vulnerability testing
- SQL injection prevention
- XSS protection validation
- Input sanitization

### Test Coverage
- **Target**: 90%+ code coverage
- **Critical Paths**: 100% coverage for actor submission flow
- **Edge Cases**: Comprehensive error handling tests

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage

# Run performance tests
php artisan test tests/Feature/ActorPerformanceTest.php
```

## Performance Considerations

### Caching Strategy
1. **OpenAI Responses**: 1-hour cache for similar descriptions
2. **Database Queries**: Query result caching for frequently accessed data
3. **View Caching**: Blade view compilation caching

### Database Optimization
1. **Indexes**: Proper indexing on frequently queried columns
2. **Query Optimization**: Efficient Eloquent queries with MySQL query analysis
3. **Connection Pooling**: MySQL connection management
4. **MySQL Configuration**: Optimize my.cnf for performance
5. **Query Cache**: Enable MySQL query cache for frequently executed queries

### Memory Management
1. **Chunked Processing**: Large dataset processing in chunks
2. **Garbage Collection**: Proper memory cleanup
3. **Resource Limits**: Memory usage monitoring

### Performance Benchmarks
- **Form Load Time**: < 1 second
- **Submission Processing**: < 3 seconds
- **Submissions Page (1000 records)**: < 2 seconds
- **API Response Time**: < 100ms

## Deployment Guide

### Environment Setup

#### 1. Development
```bash
# Clone repository
git clone <repository-url>
cd test-task

# Install dependencies
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database setup
# Create MySQL database first
mysql -u root -p -e "CREATE DATABASE actor_information_system;"
php artisan migrate

# Start development server
php artisan serve
```

#### 2. Production

##### Docker Deployment
```dockerfile
FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    mysql-client \
    curl

# Copy application
COPY . /var/www/html
WORKDIR /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Configure permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Configure nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80
CMD ["sh", "-c", "php artisan migrate --force && php-fpm"]
```

##### Environment Variables
```env
APP_NAME="Actor Information System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=actor_information_system
DB_USERNAME=your-username
DB_PASSWORD=your-password

OPENAI_API_KEY=your-openai-api-key

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### CI/CD Pipeline

#### GitHub Actions Example
```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        
    - name: Install dependencies
      run: composer install --no-progress --prefer-dist --optimize-autoloader
      
    - name: Run tests
      run: php artisan test --coverage
      
    - name: Deploy to production
      if: github.ref == 'refs/heads/main'
      run: |
        # Deployment commands
```

## Monitoring & Health Checks

### Health Check Endpoints
- **Overall Health**: `/health`
- **Database Health**: `/health/database`
- **Cache Health**: `/health/cache`
- **OpenAI Health**: `/health/openai`

### Monitoring Metrics
1. **System Metrics**: Memory usage, disk usage, uptime
2. **Application Metrics**: Response times, error rates
3. **Service Metrics**: Database performance, cache hit rates
4. **AI Service Metrics**: OpenAI API response times, success rates

### Logging
```php
// Error logging
Log::error('Actor submission failed', [
    'email' => $request->email,
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);

// Performance logging
Log::info('Actor submission processed', [
    'email' => $request->email,
    'processing_time_ms' => $processingTime
]);
```

### Alerting (Recommended)
- Database connection failures
- OpenAI API failures
- High error rates
- Performance degradation

## Troubleshooting

### Common Issues

#### 1. OpenAI API Failures
**Symptoms**: "Failed to process actor information" error
**Solutions**:
- Check API key configuration
- Verify network connectivity
- Check API rate limits
- Review OpenAI service logs

#### 2. Database Connection Issues
**Symptoms**: Database connection errors
**Solutions**:
- Verify MySQL database credentials
- Check MySQL server status
- Ensure MySQL service is running
- Verify database exists: `SHOW DATABASES;`
- Check MySQL connection limits
- Review connection pool settings

#### 3. Performance Issues
**Symptoms**: Slow page loads, timeouts
**Solutions**:
- Check database query performance
- Review caching configuration
- Monitor memory usage
- Optimize database indexes

#### 4. Validation Errors
**Symptoms**: Form submission failures
**Solutions**:
- Check input validation rules
- Review error messages
- Verify CSRF token
- Check field length limits

### Debug Mode
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Log Files
- **Application Logs**: `storage/logs/laravel.log`
- **Error Logs**: Check web server error logs
- **Database Logs**: Enable query logging in development

## Contributing Guidelines

### Code Standards
1. **PSR-12**: Follow PHP PSR-12 coding standards
2. **Laravel Conventions**: Follow Laravel best practices
3. **Documentation**: Document all public methods and classes
4. **Testing**: Write tests for all new functionality

### Pull Request Process
1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Update documentation
6. Submit pull request

### Code Review Checklist
- [ ] Code follows PSR-12 standards
- [ ] Tests are written and passing
- [ ] Documentation is updated
- [ ] Security considerations addressed
- [ ] Performance impact assessed

### Development Workflow
```bash
# Create feature branch
git checkout -b feature/new-feature

# Make changes and commit
git add .
git commit -m "Add new feature"

# Run tests
php artisan test

# Push and create PR
git push origin feature/new-feature
```

---

## Support

For technical support or questions:
- **Email**: faz.ali.bhamani@gmail.com
- **Documentation**: See README.md for basic setup
- **API Docs**: http://localhost:8000/api/documentation
- **Health Check**: http://localhost:8000/health

---

*Last Updated: September 15, 2025*
*Version: 1.0.0*
