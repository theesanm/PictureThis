# Test Suite Documentation

This directory contains comprehensive tests for the Interactive Prompt Enhancement Agent feature.

## Quick Start

1. **Access the test suite**: Navigate to `/tests/` in your browser
2. **Check environment status**: Click "Environment Status" to verify your setup
3. **Update database schema**: Click "Update Schema" to apply agent table changes
4. **Run all tests**: Click "Run All Tests" to execute the complete test suite

## Directory Structure

```
tests/
├── README.md              # Complete documentation
├── index.php              # Web-accessible entry point with tool links
├── status.php             # Environment status checker
├── update_schema.php      # Database schema updater
├── bootstrap.php          # Test environment setup
├── run_tests.php          # Main test runner
├── web_runner.php         # Web-compatible test interface
├── unit/                  # Unit tests
│   └── PromptAgentControllerTest.php
├── database/              # Database schema and data tests
│   └── SchemaTest.php
├── api/                   # API integration tests
│   └── AgentApiTest.php
└── utils/                 # Test utilities and helpers
    ├── TestHelper.php
    └── DatabaseTestHelper.php
```

## Test Tools

### Environment Status (`status.php`)
- PHP environment information (version, extensions, memory limits)
- Database connection status and credentials validation
- Test file availability check with visual indicators
- Quick navigation links to other test tools

### Schema Update (`update_schema.php`)
- Applies agent schema changes to database with preview
- Safe execution with error handling and rollback capability
- Confirmation required before applying changes
- Detailed feedback on execution results

### Web Test Runner (`web_runner.php`)
- Interactive test execution with real-time progress
- Detailed results display with pass/fail indicators
- Error reporting with stack traces and debugging info
- Mobile-responsive interface for all devices

### 🔧 New Diagnostic Tools

#### Full System Diagnostics (`diagnostics.php`)
**Purpose**: Complete system health check for production deployment
**Tests**:
- Environment (PHP version, extensions, memory)
- Configuration loading and validation
- Database connectivity and table structure
- File system permissions and security
- Security settings and CSRF protection
- Email/SMTP configuration and connectivity
- API endpoints accessibility
- Agent functionality and OpenRouter integration

**Usage**: Open in browser and click "Run Full Diagnostics"

#### Database Diagnostics (`database.php`)
**Purpose**: Detailed database connectivity and structure testing
**Tests**:
- Connection establishment and authentication
- Basic query execution and performance
- Required table existence verification
- Table structure and column validation
- Query performance benchmarking

**Usage**: Open in browser and click "Run Database Tests"

#### Email Diagnostics (`email.php`)
**Purpose**: SMTP configuration and email functionality testing
**Tests**:
- SMTP settings validation
- Server connectivity testing
- PHP mail() function availability
- Manual email sending capability

**Usage**:
1. Open in browser and click "Run Email Tests"
2. Use manual email test to send actual test emails

#### API Diagnostics (`api.php`)
**Purpose**: API endpoints and static file accessibility testing
**Tests**:
- Endpoint HTTP response validation
- Static file serving verification
- Content type checking
- CORS and security headers

**Usage**: Open in browser and click "Run API Tests"

## 🚀 Deployment Process

### Step 1: Code Check-in
```bash
# In your local development environment
git add .
git commit -m "Deployment: [description]"
git push origin main
```

### Step 2: Server Update
```bash
# On your server via SSH or cPanel File Manager
cd /path/to/github/folder
git pull origin main
```

### Step 3: Web Installation
1. Copy `web_install.php` from GitHub folder to root directory
2. Open `http://yourdomain.com/web_install.php?install=confirm` in browser
3. Follow the installation wizard:
   - ✅ Environment check
   - ✅ File copying from GitHub folder
   - ✅ Configuration setup
   - ✅ Production mode activation
   - ✅ Basic configuration test
4. Delete `web_install.php` after successful installation

### Step 4: Run Comprehensive Diagnostics
1. Open `http://yourdomain.com/tests/diagnostics.php` in browser
2. Click "Run Full Diagnostics"
3. Verify all tests pass (target: 90%+ success rate)
4. Review any warnings or failures

### Step 5: Run Specialized Tests (if needed)
If full diagnostics show issues, run specific tests:
- **Database issues**: `tests/database.php`
- **Email problems**: `tests/email.php`
- **API problems**: `tests/api.php`

### Step 6: Launch Application
- If all diagnostics pass, the application is ready for production
- Access your main application at the root URL
- Monitor server logs for any runtime issues

## 📊 Test Results Guide

### ✅ PASS (Green)
- Test completed successfully
- No action required
- System component working correctly

### ❌ FAIL (Red)
- Test failed - requires immediate attention
- Check details for specific error messages
- Fix configuration or code issues before launch

### ⚠️ WARNING (Yellow)
- Test passed but with performance concerns
- May indicate optimization opportunities
- Review and address if affecting user experience

### ℹ️ INFO (Blue)
- Informational results
- No action required
- Useful for debugging and monitoring

## Prerequisites

1. **Database Setup**: Ensure your test database is properly configured
2. **Dependencies**: All required PHP extensions and composer packages
3. **Web Server**: For integration tests, ensure the application is running

## Running Tests

### Run All Tests
```bash
php tests/run_tests.php
```

### Run Specific Test Types
```bash
# Unit tests only
php tests/run_tests.php unit

# Database tests only
php tests/run_tests.php database

# Integration tests only
php tests/run_tests.php integration
```

### Run Individual Test Files
```bash
# Unit tests
php tests/unit/PromptAgentControllerTest.php

# Database tests
php tests/database/SchemaTest.php

# API tests
php tests/api/AgentApiTest.php
```

## Test Categories

### Unit Tests (`tests/unit/`)
- Test individual methods and functions in isolation
- Mock external dependencies
- Focus on business logic

### Database Tests (`tests/database/`)
- Validate database schema structure
- Test data integrity and constraints
- Verify indexes and foreign keys

### Integration Tests (`tests/api/`)
- Test actual HTTP endpoints
- Verify API responses and error handling
- Test authentication and authorization

## Test Helpers

### TestHelper
Common testing utilities and assertions:
- `assert($condition, $message)` - Basic assertion
- `assertEquals($expected, $actual, $message)` - Equality assertion
- `assertArrayHasKey($key, $array, $message)` - Array key assertion
- `generateTestSessionId()` - Generate test session IDs

### DatabaseTestHelper
Database-specific testing utilities:
- `createTestUser($overrides)` - Create test users
- `createTestAgentSession($userId, $overrides)` - Create test sessions
- `createTestAgentMessage($sessionId, $overrides)` - Create test messages
- `cleanupTestData()` - Clean up test data

## Writing New Tests

### Basic Test Structure
```php
class MyTest {
    public function testSomething() {
        // Arrange
        $expected = 'expected value';

        // Act
        $actual = someFunction();

        // Assert
        TestHelper::assertEquals($expected, $actual, 'Should return expected value');
    }

    public function runAllTests() {
        $tests = ['testSomething'];

        foreach ($tests as $test) {
            TestHelper::runTest($test, function() {
                $this->$test();
            });
        }
    }
}
```

### Database Test Example
```php
public function testUserCreation() {
    $userData = DatabaseTestHelper::createTestUser();

    // Verify user was created
    $pdo = DatabaseTestHelper::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userData['id']]);
    $result = $stmt->fetch();

    TestHelper::assertNotNull($result, 'User should be created');
    TestHelper::assertEquals($userData['email'], $result['email'], 'Email should match');
}
```

## Test Data Management

- Test data is automatically cleaned up after each test run
- Use `DatabaseTestHelper::cleanupTestData()` for manual cleanup
- Test data uses predictable prefixes (e.g., 'test_' for IDs)

## Continuous Integration

For CI/CD pipelines, you can run tests with:
```bash
# Exit with error code on failure
php tests/run_tests.php || exit 1

# Generate test reports
php tests/run_tests.php > test_results.log
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config/development.php`
   - Ensure database server is running
   - Verify user permissions

2. **Test Files Not Found**
   - Check file paths and permissions
   - Ensure you're running from the correct directory

3. **Integration Tests Failing**
   - Ensure web server is running
   - Check API endpoints are accessible
   - Verify CORS and authentication settings

### Debug Mode

Enable debug output by setting:
```php
define('TESTING', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Contributing

When adding new tests:
1. Follow the existing naming conventions
2. Include proper error messages in assertions
3. Clean up test data in `__destruct()` methods
4. Update this README if adding new test categories

## Test Coverage

Current test coverage includes:
- ✅ PromptAgentController methods
- ✅ Database schema validation
- ✅ API endpoint integration
- ✅ Data integrity and constraints
- ✅ Error handling and edge cases