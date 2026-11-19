<?php
/*
 * Copy this file to includes/config.local.php and fill in your real values.
 * config.local.php is listed in .gitignore, so it is never pushed to GitHub.
 */
return [
    // Local XAMPP defaults:
    // db_host: 'localhost', db_name: 'geo_portfolio', db_user: 'root', db_pass: ''
    //
    // InfinityFree values come from the hosting control panel:
    // db_host is usually sqlNN.infinityfree.com
    // db_name/user include the InfinityFree account prefix
    'db_host'    => 'localhost',
    'db_name'    => 'geo_portfolio',
    'db_user'    => 'root',
    'db_pass'    => '',
    'db_charset' => 'utf8mb4',

    // Long random text. Needed once to open admin/setup.php and create the first admin.
    'setup_key'  => '',

    // true on the live site: admin pages redirect to https://
    'force_https' => false,

    // true only on your own computer while debugging
    'debug'      => false,
];
