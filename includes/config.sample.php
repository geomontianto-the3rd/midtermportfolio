<?php
/*
 * Copy this file to  includes/config.php  and fill in your real values.
 * config.php is listed in .gitignore, so it is never pushed to GitHub.
 */
return [
    'db_host'    => 'localhost',
    'db_name'    => 'your_database_name',
    'db_user'    => 'your_database_user',
    'db_pass'    => 'your_database_password',
    'db_charset' => 'utf8mb4',

    // Long random text. Needed once to open admin/setup.php and create the first admin.
    'setup_key'  => '',

    // true on the live site: admin pages redirect to https://
    'force_https' => false,

    // true only on your own computer while debugging
    'debug'      => false,
];
