# Database Setup and Testing Guide

## Prerequisites
- Docker and docker-compose installed
- PHP 8.3+ installed (or use Docker PHP container)

## Step 1: Start Docker Services
```bash
cd /Volumes/MacM4Ext/Projects/PictureThis/PictureThis
docker-compose up -d
```

## Step 2: Create Database User
Access Adminer at http://localhost:8080 and run:
- Server: 127.0.0.1:3306
- Username: root
- Password: (leave empty)
- Database: picturethis_dev

Execute the SQL from `create_pt_user.sql`:
```sql
CREATE USER IF NOT EXISTS 'pt_user'@'%' IDENTIFIED BY 'pt_pass';
GRANT ALL PRIVILEGES ON picturethis_dev.* TO 'pt_user'@'%';
GRANT ALL PRIVILEGES ON cfoxcozj_PictureThis.* TO 'pt_user'@'%';
FLUSH PRIVILEGES;
```

## Step 3: Create Database and Tables
In Adminer, execute the SQL from `setup_database.sql` to create the database schema.

## Step 4: Test Database Connection
Run the test script:
```bash
cd picfePHPMYSQL/cpanel-html-mysql-app
php test_db_simple.php
```

Or using Docker PHP:
```bash
docker run --rm -v $(pwd):/app -w /app --network host php:8.3-cli php test_db_simple.php
```

## Expected Results
- ✅ Development user connection successful (pt_user@127.0.0.1:3306)
- Should show user count from database
- If pt_user fails, root user should work as fallback

## Configuration Summary
- **Development**: pt_user/pt_pass @ 127.0.0.1:3306 → picturethis_dev
- **Production**: cfoxcozj_picThisdb/[password] @ 127.0.0.1 → cfoxcozj_PictureThis
- **Config switching**: Change `IS_PRODUCTION` in config/config.php