# cPanel HTML MySQL Application

This project is a simple web application designed to run in a cPanel environment using Apache and MySQL. It provides a structured approach to developing a web application with a focus on maintaining a consistent look and feel.

## Project Structure

```
cpanel-html-mysql-app
├── public
│   ├── index.php          # Main entry point for the application
│   ├── css
│   │   └── style.css      # CSS styles for the application
│   └── js
│       └── main.js        # JavaScript for client-side functionality
├── src
│   ├── controllers
│   │   └── HomeController.php  # Handles requests for the home page
│   ├── models
│   │   └── UserModel.php       # Interacts with the MySQL database for user operations
│   ├── views
│   │   ├── header.php          # HTML for the header section
│   │   └── footer.php          # HTML for the footer section
│   └── lib
│       └── db.php              # Functions for connecting to the MySQL database
├── config
│   └── config.php              # Configuration settings for the application
├── sql
│   └── schema.sql              # SQL schema for setting up the database
├── .htaccess                    # Apache server configurations
├── composer.json                # Composer dependencies
└── README.md                    # Project documentation
```

## Environment Configuration

This application uses environment variables to securely store sensitive configuration data. The configuration is split between development and production environments.

### Local Development Setup

1. **Copy the environment file**:
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` file** with your local credentials:
   ```bash
   # Database
   DB_HOST=127.0.0.1:3306
   DB_USER=your_db_user
   DB_PASS=your_db_password
   DB_NAME=your_db_name

   # PayFast
   PAYFAST_MERCHANT_ID=your_merchant_id
   PAYFAST_MERCHANT_KEY=your_merchant_key
   PAYFAST_PASSPHRASE=your_passphrase

   # OpenRouter API
   OPENROUTER_API_KEY=your_api_key

   # Email/SMTP
   SMTP_HOST=your_smtp_host
   SMTP_USERNAME=your_smtp_username
   SMTP_PASSWORD=your_smtp_password
   SMTP_PORT=587
   SMTP_FROM_EMAIL=your_email@domain.com
   ```

3. **Run the setup script**:
   ```bash
   php setup_env.php
   ```

### cPanel Production Setup

#### Option 1: Using .env file (Recommended)
1. **Upload your project** to cPanel's `public_html` directory
2. **Create `.env` file** in your home directory (`/home/username/.env`):
   ```bash
   # Via cPanel File Manager or SSH
   nano /home/username/.env
   ```
3. **Add your production credentials** to the `.env` file

#### Option 2: Using cPanel Environment Variables
1. **Go to cPanel** → **Software** → **MultiPHP INI Editor**
2. **Add environment variables** with `PICTURETHIS_` prefix:
   ```
   PICTURETHIS_DB_PASS=your_production_db_password
   PICTURETHIS_PAYFAST_MERCHANT_ID=your_merchant_id
   PICTURETHIS_OPENROUTER_API_KEY=your_api_key
   ```

### Security Notes

- ✅ **Never commit** `.env` files to version control
- ✅ **Store `.env` outside** your web root directory
- ✅ **Use strong passwords** for all services
- ✅ **Regularly rotate** API keys and credentials
- ✅ **Test configuration** after deployment

### Configuration Files

- `config/config.php` - Main configuration loader
- `config/development.php` - Development environment settings
- `config/production.php` - Production environment settings
- `.env.example` - Example environment file template

## Usage Guidelines

- The application is designed to be modular, with separate directories for controllers, models, and views.
- You can extend the functionality by adding new controllers, models, and views as needed.
- Ensure that any new features maintain the existing design and functionality for a consistent user experience.

## License

This project is open-source and available for modification and distribution under the terms of the MIT License.