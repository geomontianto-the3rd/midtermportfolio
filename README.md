# Dynamic Personal Portfolio

A PHP and MySQL portfolio website with an authenticated administration dashboard.

The root `index.html` is a static GitHub Pages preview. It uses sample portfolio content and does not connect to the database or accept contact submissions. The dynamic site is `index.php` and must run on a PHP/MySQL host such as InfinityFree; Apache is configured to prefer `index.php`.

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
5. Copy `includes/config.local.example.php` to `includes/config.local.php`.
6. Set the database credentials and a strong setup key in `includes/config.local.php`.
7. Open `http://localhost/midtermportfolio/admin/setup.php` and create the first administrator.
8. After setup, delete `admin/setup.php` from the deployed server.

`includes/config.local.php` is ignored by Git and must never be committed.

## InfinityFree Deployment

1. In the InfinityFree control panel, create a MySQL database and note the exact
   database host, database name, username, and password shown there.
2. Upload the project contents into the website's `htdocs` directory, keeping
   the existing folders (`admin`, `assets`, `database`, `includes`, and
   `uploads`) in place. Do not upload `.git` or overwrite the host's unrelated
   files.
3. Use the hosting control panel's phpMyAdmin to select the new database and
   import `database/portfolio.sql`.
4. Create `htdocs/includes/config.local.php` by copying
   `includes/config.local.example.php`. In that file, set `db_host`, `db_name`,
   `db_user`, and `db_pass` to the exact values from the control panel.
   InfinityFree database hosts are account-specific and are not `localhost`.
5. Set a unique, random `setup_key` of at least 12 characters, keep `debug`
   set to `false`, and set `force_https` to `true` after HTTPS is enabled.
6. Visit `/admin/setup.php` on the site and create the administrator. Then
   delete `admin/setup.php` from the server.
7. Test the public site, admin login, contact form, and an image upload.

Keep the configuration at `includes/config.local.php`; do not move it to the
website root or commit it. The tracked `includes/config.local.example.php`
contains local XAMPP sample values, not production credentials; replace them
before deploying. The `.htaccess` files protect the config, SQL schema, and
uploaded files from direct access; retain them when uploading.
Set the `uploads` directory writable by PHP using the hosting file manager if
uploads fail, without making it world-writable.

## Deployment

- Use HTTPS and set `force_https` to `true`.
- Set `debug` to `false`.
- Use a dedicated database account with only the permissions the application requires.
- Keep `admin/setup.php` inaccessible or delete it after the first administrator is created.
- Disable PHP execution in `uploads/` and in all public upload locations.
- Preserve the database across deployments and take regular SQL backups.

## Repository Structure

- `index.php` - Dynamic public website
- `index.html` - Static GitHub Pages preview
- `contact.php` - Validated contact-form handler
- `admin/` - Authentication and content-management pages
- `includes/` - Database, security, authentication, and shared helpers
- `assets/css/` - Public and administrative styles
- `assets/js/` - Public and administrative JavaScript
- `assets/images/` - Repository-managed images
- `uploads/` - Administrator-uploaded images
- `database/portfolio.sql` - Database schema and sample content
