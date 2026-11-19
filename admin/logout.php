<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';

// Logging out changes state, so it only works with POST + a valid CSRF token.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('dashboard.php');
}
csrf_verify();
destroy_admin_session();
redirect('login.php?out=1');
