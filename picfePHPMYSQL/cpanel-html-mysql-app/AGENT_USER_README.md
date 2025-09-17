# PictureThis Agent User Setup Guide

## Overview
This guide explains how to set up and use the dedicated database agent user (`pt_agent`) for the PictureThis application instead of using the root user.

## Why Use an Agent User?
- **Security**: Limits database access to specific databases and operations
- **Best Practices**: Avoids using root user for application connections
- **Isolation**: Separate user for development and production environments
- **Auditing**: Easier to track application-specific database activity

## Agent User Credentials
```
Username: pt_agent
Password: agent_secure_2025
Host: localhost and % (any host)
Databases: picturethis_dev, cfoxcozj_PictureThis
```

## Quick Setup

### Option 1: Automated Setup (Recommended)
```bash
cd picfePHPMYSQL/cpanel-html-mysql-app
./setup_agent.sh
```

### Option 2: Manual Setup
```bash
# Connect to MySQL as root
mysql -u root

# Run the SQL commands from setup_agent_user.sql
source setup_agent_user.sql
```

### Option 3: Using Docker MySQL
```bash
# If using Docker MySQL
docker exec -i mysql-container mysql -u root < setup_agent_user.sql
```

## Configuration Files Updated

### Development Configuration
```php
// config/development.php
'database' => [
    'host' => '127.0.0.1:3306',
    'user' => 'pt_agent',        // ✅ Agent user
    'pass' => 'agent_secure_2025', // ✅ Agent password
    'name' => 'picturethis_dev',
],
```

### Production Configuration
```php
// config/production.php
'database' => [
    'host' => '127.0.0.1',
    'user' => 'pt_agent',        // ✅ Agent user
    'pass' => 'agent_secure_2025', // ✅ Agent password
    'name' => 'cfoxcozj_PictureThis',
],
```

## Testing the Setup

### Database Connection Test
Visit: `http://localhost:8000/test_db_simple.php`

This will test:
- ✅ Agent user connection (pt_agent)
- ✅ Localhost connection
- ✅ Root user fallback
- ✅ PHP extensions
- ✅ Database access

### Application Test
Visit: `http://localhost:8000/login`

Should now connect using the agent user without permission errors.

## Troubleshooting

### "Access denied for user 'pt_agent'"
**Solution**: Run the setup script again
```bash
./setup_agent.sh
```

### "Database connection failed"
**Solution**: Check if MySQL is running
```bash
# Check MySQL status
sudo systemctl status mysql

# Or for macOS
brew services list | grep mysql
```

### "Table doesn't exist"
**Solution**: Import the database schema
```bash
mysql -u pt_agent -p picturethis_dev < setup_database.sql
```

## Security Notes

- The agent user has full access to the PictureThis databases
- Password should be changed in production environments
- Consider restricting permissions to SELECT, INSERT, UPDATE, DELETE only
- Regular password rotation is recommended

## Files Modified

- `config/development.php` - Updated database credentials
- `config/production.php` - Updated database credentials
- `setup_agent_user.sql` - SQL script to create agent user
- `setup_agent.sh` - Automated setup script
- `test_db_simple.php` - Updated test file for agent user
- `AGENT_USER_README.md` - This documentation

## Next Steps

1. ✅ Run `./setup_agent.sh` to create the agent user
2. ✅ Test with `http://localhost:8000/test_db_simple.php`
3. ✅ Verify application works with `http://localhost:8000/login`
4. 🔄 Update password in production for security

---
**Status**: Agent user configuration complete! 🎉