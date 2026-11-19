<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';

require_https_if_configured();
header('Cache-Control: no-store');

if (current_admin() !== null) {
    redirect('dashboard.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $ip = client_ip();

    $wait = login_lock_seconds($ip, $username);
    if ($wait > 0) {
        $error = 'Too many failed attempts. Please try again in ' . (int) ceil($wait / 60) . ' minute(s).';
    } elseif ($username === '' || $password === '') {
        $error = 'Enter your username (or email) and password.';
    } else {
        $stmt = db()->prepare('SELECT * FROM admins WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute([':username' => $username, ':email' => $username]);
        $admin = $stmt->fetch();

        // Always run password_verify, even for unknown users, so response time does not reveal valid usernames
        $hash = $admin['password_hash'] ?? password_hash('not-a-real-password', PASSWORD_DEFAULT);
        $valid = password_verify($password, $hash) && $admin;

        if ($valid) {
            clear_failed_logins($ip, $username);
            if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
                $re = db()->prepare('UPDATE admins SET password_hash = :h WHERE id = :id');
                $re->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $admin['id']]);
            }
            $up = db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
            $up->execute([':id' => $admin['id']]);

            admin_login($admin);          // regenerates the session ID
            redirect('dashboard.php');
        }

        record_failed_login($ip, $username);
        $left = login_failures_left($ip);
        $error = 'Incorrect username or password.'
               . ($left > 0 ? ' You have ' . $left . ' attempt(s) left before a temporary lock.' : ' Too many failed attempts: login is locked for ' . LOGIN_WINDOW_MINUTES . ' minutes.');
    }
}

auth_header('Administrator login');
if ($error !== '') {
    echo '<div class="alert alert-error" role="alert">' . e($error) . '</div>';
}
render_flash();
if (isset($_GET['out'])) {
    echo '<div class="alert alert-ok" role="status">You have been logged out.</div>';
}
?>
<form method="post" class="form" autocomplete="on">
    <?= csrf_field() ?>
    <div class="field">
        <label for="username">Username or email</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" maxlength="150" autocomplete="username" required autofocus>
    </div>
    <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" maxlength="200" autocomplete="current-password" required>
    </div>
    <button class="btn btn-primary btn-block" type="submit">Log in</button>
    <p class="muted center"><a href="../index.php">&larr; Back to website</a></p>
</form>
<?php auth_footer();
