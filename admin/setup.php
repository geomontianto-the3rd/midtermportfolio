<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';

/*
 * One-time page that creates the FIRST administrator.
 * - Needs the "setup_key" from includes/config.local.php
 * - Refuses to run once any administrator exists
 * - DELETE THIS FILE from the server after you use it.
 */
require_https_if_configured();
header('Cache-Control: no-store');

$count = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$setupKey = (string) config('setup_key', '');

if ($count > 0) {
    http_response_code(403);
    auth_header('Setup already completed');
    echo '<p>An administrator already exists. For safety, delete <code>admin/setup.php</code> from the server.</p>'
       . '<p><a class="btn btn-primary" href="login.php">Go to login</a></p>';
    auth_footer();
    exit;
}
if ($setupKey === '' || strlen($setupKey) < 12) {
    http_response_code(403);
    auth_header('Setup disabled');
    echo '<p>Set a long <code>setup_key</code> (at least 12 characters) in <code>includes/config.local.php</code> to enable this page.</p>';
    auth_footer();
    exit;
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $ip = client_ip();
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');
    $key = (string) ($_POST['setup_key'] ?? '');

    if (login_lock_seconds($ip, '__setup__') > 0) {
        $errors[] = 'Too many attempts. Please wait a few minutes.';
    } elseif (!hash_equals($setupKey, $key)) {
        record_failed_login($ip, '__setup__');
        $errors[] = 'The setup key is incorrect.';
    } else {
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            $errors[] = 'Username must be 3-50 characters: letters, numbers, dot, dash or underscore.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors[] = 'Enter a valid email address.';
        }
        if (strlen($password) < 10) {
            $errors[] = 'Password must be at least 10 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'The two passwords do not match.';
        }
        if (!$errors) {
            $stmt = db()->prepare('INSERT INTO admins (username, email, password_hash) VALUES (:u, :e, :h)');
            $stmt->execute([':u' => $username, ':e' => $email, ':h' => password_hash($password, PASSWORD_DEFAULT)]);
            flash_set('ok', 'Administrator created. Now DELETE admin/setup.php from the server, then log in.');
            redirect('login.php');
        }
    }
}

auth_header('Create the first administrator');
foreach ($errors as $err) {
    echo '<div class="alert alert-error" role="alert">' . e($err) . '</div>';
}
render_flash();
?>
<form method="post" class="form" autocomplete="off">
    <?= csrf_field() ?>
    <div class="field">
        <label for="setup_key">Setup key (from includes/config.local.php)</label>
        <input type="password" id="setup_key" name="setup_key" required>
    </div>
    <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" maxlength="50" required>
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="150" required>
    </div>
    <div class="field">
        <label for="password">Password (min. 10 characters)</label>
        <input type="password" id="password" name="password" minlength="10" maxlength="200" required>
    </div>
    <div class="field">
        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" minlength="10" maxlength="200" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Create administrator</button>
</form>
<?php auth_footer();
