GEO MONTIANTO - DYNAMIC PORTFOLIO PROJECT

FOLDER STRUCTURE
- index.php, contact.php, portfolio.sql: public website and database import
- includes/: PHP shared files and database configuration
- admin/: administrator login, dashboard, and content-management pages
- assets/css/: public and admin stylesheets
- assets/js/: public and admin JavaScript
- assets/images/: seeded portfolio images
- uploads/: destination for images uploaded through the dashboard

LOCAL SETUP (XAMPP)
1. Copy this entire folder into C:\xampp\htdocs\midtermportfolio
2. In phpMyAdmin, create a database named geo_portfolio.
3. Import portfolio.sql into that database.
4. Copy includes/config.sample.php to includes/config.php.
5. Edit includes/config.php and set:
   db_host = localhost
   db_name = geo_portfolio
   db_user = root
   db_pass = '' (blank for a default XAMPP installation)
   setup_key = a long random secret string
   force_https = false
   debug = true (local development only)
6. Start Apache and MySQL in XAMPP.
7. Open http://localhost/midtermportfolio/
8. Create the first administrator at
   http://localhost/midtermportfolio/admin/setup.php
   Enter the setup_key configured in includes/config.php.
9. Sign in at http://localhost/midtermportfolio/admin/login.php

IMPORTANT
- Never upload includes/config.php or real database credentials to GitHub.
- Do not delete the original static portfolio until the PHP version is tested.
- GitHub Pages does not run PHP. A PHP/MySQL host is required for the dynamic live site.
- The original static site files were not included in the supplied ZIP, so add/retain
  your existing images and any other original assets when merging this project.
- Before public deployment, set debug=false and force_https=true, use a strong
  database password, and configure the hosting provider's database credentials.
