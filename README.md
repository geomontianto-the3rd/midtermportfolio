# Dynamic Personal Portfolio

A PHP and MySQL portfolio website with an authenticated administration dashboard.

## Features

- Dynamic profile, skills, projects, education, experience, and achievements
- CRUD administration for every portfolio section
- Secure login, logout, password hashing, session protection, and login throttling
- Contact form validation, CSRF protection, spam controls, and message management
- Validated image uploads with server-generated filenames
- Responsive public and administrative interfaces
- Light, dark, and system theme support

## Requirements

- PHP 8.0 or newer
- MySQL 5.7+ or MariaDB 10.4+
- Apache or another PHP-capable web server
- Required PHP extensions: PDO MySQL, fileinfo, and GD or built-in image support

## Local Installation

1. Copy the project into `C:\xampp\htdocs\midtermportfolio`.
2. Start Apache and MySQL in XAMPP.
3. Create a database, for example `geo_portfolio`.
4. Import `database/portfolio.sql` in phpMyAdmin or with:
   `mysql -u root -p geo_portfolio < database/portfolio.sql`
5. Copy `includes/config.sample.php` to `includes/config.php`.
6. Set the database credentials and a strong setup key in `includes/config.php`.
7. Open `http://localhost/midtermportfolio/admin/setup.php` and create the first administrator.
8. After setup, delete `admin/setup.php` from the deployed server.

`includes/config.php` is ignored by Git and must never be committed.

## Deployment

- Use HTTPS and set `force_https` to `true`.
- Set `debug` to `false`.
- Use a dedicated database account with only the permissions the application requires.
- Keep `admin/setup.php` inaccessible or delete it after the first administrator is created.
- Disable PHP execution in `uploads/` and in all public upload locations.
- Preserve the database across deployments and take regular SQL backups.

## Repository Structure

- `index.php` - Dynamic public website
- `contact.php` - Validated contact-form handler
- `admin/` - Authentication and content-management pages
- `includes/` - Database, security, authentication, and shared helpers
- `assets/css/` - Public and administrative styles
- `assets/js/` - Public and administrative JavaScript
- `assets/images/` - Repository-managed images
- `uploads/` - Administrator-uploaded images
- `database/portfolio.sql` - Database schema and sample content
