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

## Setup Instructions

1. **Clone the Repository**: Download or clone the repository to your local machine or server.

2. **Database Configuration**: Update the `config/config.php` file with your MySQL database connection details.

3. **Database Setup**: Execute the SQL commands in `sql/schema.sql` to create the necessary database tables.

4. **File Permissions**: Ensure that the necessary files and directories have the correct permissions for the web server to access them.

5. **Access the Application**: Navigate to the `public/index.php` file in your web browser to access the application.

## Usage Guidelines

- The application is designed to be modular, with separate directories for controllers, models, and views.
- You can extend the functionality by adding new controllers, models, and views as needed.
- Ensure that any new features maintain the existing design and functionality for a consistent user experience.

## License

This project is open-source and available for modification and distribution under the terms of the MIT License.