# Environment Configuration System

This application uses a unified configuration system that allows you to switch between development and production environments by changing a single setting in `.htaccess`.

## How It Works

### 1. Environment Detection
The system automatically detects the environment based on the `APP_ENV` setting in `.htaccess`:
- `APP_ENV = "development"` → Loads `config/development.php`
- `APP_ENV = "production"` → Loads `config/production.php`

### 2. Configuration Hierarchy
Values are loaded in this order (later sources override earlier ones):
1. **Base config file** (`config/development.php` or `config/production.php`)
2. **Environment variables** (from `.htaccess` SetEnv or server environment)
3. **Local overrides** (from `.env` file, if present)

## Switching Between Environments

### For Development (Local Coding)
In `.htaccess`, ensure this line is active:
```apache
SetEnv APP_ENV "development"
```

### For Production (Deployment)
In `.htaccess`, change to:
```apache
SetEnv APP_ENV "production"
```

And uncomment/configure the production environment variables:
```apache
# Database Configuration (Production)
SetEnv DB_HOST "your-production-db-host"
SetEnv DB_USER "your-production-db-user"
SetEnv DB_PASS "your-production-db-password"
SetEnv DB_NAME "your-production-db-name"

# PayFast Configuration (Production)
SetEnv PAYFAST_MERCHANT_ID "your-production-merchant-id"
SetEnv PAYFAST_MERCHANT_KEY "your-production-merchant-key"
SetEnv PAYFAST_PASSPHRASE "your-production-passphrase"

# OpenRouter Configuration (Production)
SetEnv OPENROUTER_API_KEY "your-production-api-key"

# Email Configuration (Production)
SetEnv SMTP_PASSWORD "your-production-smtp-password"
```

## File Structure

```
config/
├── config.php          # Main configuration loader
├── development.php     # Development environment settings
└── production.php      # Production environment settings

.htaccess               # Environment flag and production overrides
.env                    # Local development overrides (optional)
```

## Configuration Files

### config.php
- **Purpose**: Main configuration loader that selects the appropriate environment config
- **Behavior**: Automatically loads `development.php` or `production.php` based on `APP_ENV`
- **Override Logic**: Environment variables can override any config value

### development.php
- **Purpose**: Contains all default development settings
- **Usage**: Used when `APP_ENV = "development"`
- **Security**: Contains test credentials and local settings

### production.php
- **Purpose**: Contains production defaults with empty sensitive values
- **Usage**: Used when `APP_ENV = "production"`
- **Security**: Sensitive values should be set via `.htaccess` SetEnv

### .htaccess
- **Purpose**: Single point of control for environment switching
- **Usage**: Change `APP_ENV` to switch environments
- **Security**: Contains production credentials (should not be in version control)

### .env (Optional)
- **Purpose**: Local development overrides
- **Usage**: Only needed if your local setup differs from `development.php` defaults
- **Security**: Should be in `.gitignore` and never committed

## Deployment Checklist

### Before Deployment:
1. ✅ Change `APP_ENV` to `"production"` in `.htaccess`
2. ✅ Uncomment and configure all production environment variables in `.htaccess`
3. ✅ Update production database credentials
4. ✅ Update production PayFast credentials
5. ✅ Update production OpenRouter API key
6. ✅ Update production SMTP password
7. ✅ Update `APP_URL` to production domain

### After Deployment:
1. ✅ Test database connection
2. ✅ Test PayFast integration
3. ✅ Test email sending
4. ✅ Test OpenRouter API
5. ✅ Verify all features work in production

## Security Notes

- **Never commit `.htaccess`** with real production credentials to version control
- **Use `.env`** only for local development overrides
- **Keep production credentials** in `.htaccess` or server environment variables
- **Test thoroughly** after any configuration changes

## Troubleshooting

### Configuration Not Loading
- Check that `APP_ENV` is set correctly in `.htaccess`
- Verify the corresponding config file exists (`config/development.php` or `config/production.php`)
- Check PHP error logs for any syntax errors

### Environment Variables Not Working
- Ensure `.htaccess` SetEnv directives are properly formatted
- Check that Apache mod_env is enabled
- Verify variable names match between config files and `.htaccess`

### Database Connection Issues
- Verify database credentials in the appropriate config file
- Check that environment variables are being read correctly
- Test database connectivity from the server