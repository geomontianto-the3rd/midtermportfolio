<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';

$admin = require_admin();
$errors = [];
$username = $admin['username'];
$email = $admin['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'details') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            $errors['details'][] = 'Username must be 3-50 characters: letters, numbers, dot, dash or underscore.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $errors['details'][] = 'Enter a valid email address.';
        }
        if (empty($errors['details'])) {
            try {
                $stmt = db()->prepare('UPDATE admins SET username = :u, email = :e WHERE id = :id');
                $stmt->execute([':u' => $username, ':e' => $email, ':id' => $admin['id']]);
                flash_set('ok', 'Account details updated.');
                redirect('account.php');
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {      // duplicate username or email
                    $errors['details'][] = 'That username or email is already in use.';
                } else {
                    throw $ex;
                }
            }
        }
    }

    if ($action === 'password') {
        $cur = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $conf = (string) ($_POST['confirm_password'] ?? '');

        $stmt = db()->prepare('SELECT password_hash FROM admins WHERE id = :id');
        $stmt->execute([':id' => $admin['id']]);
        $hash = (string) $stmt->fetchColumn();

        if (!password_verify($cur, $hash)) {
            $errors['password'][] = 'Your current password is incorrect.';
        }
        if (strlen($new) < 10) {
            $errors['password'][] = 'The new password must be at least 10 characters.';
        }
        if ($new !== $conf) {
            $errors['password'][] = 'The new passwords do not match.';
        }
        if ($new !== '' && $new === $cur) {
            $errors['password'][] = 'Choose a password different from the current one.';
        }
        if (empty($errors['password'])) {
            $up = db()->prepare('UPDATE admins SET password_hash = :h WHERE id = :id');
            $up->execute([':h' => password_hash($new, PASSWORD_DEFAULT), ':id' => $admin['id']]);
            session_regenerate_id(true);
            flash_set('ok', 'Password changed.');
            redirect('account.php');
        }
    }
}

admin_header('My account', 'account.php');
?>
<div class="two-col">
    <form method="post" class="panel form" novalidate>
        <h2>Administrator profile</h2>
        <?= csrf_field() ?><input type="hidden" name="action" value="details">
        <?php foreach ($errors['details'] ?? [] as $err): ?><div class="alert alert-error" role="alert"><?= e($err) ?></div><?php endforeach; ?>
        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($username) ?>" maxlength="50" required>
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" maxlength="150" required>
        </div>
        <div class="actions"><button class="btn btn-primary" type="submit">Save details</button></div>
    </form>

    <form method="post" class="panel form" novalidate autocomplete="off">
        <h2>Change password</h2>
        <?= csrf_field() ?><input type="hidden" name="action" value="password">
        <?php foreach ($errors['password'] ?? [] as $err): ?><div class="alert alert-error" role="alert"><?= e($err) ?></div><?php endforeach; ?>
        <div class="field">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
        </div>
        <div class="field">
            <label for="new_password">New password (min. 10 characters)</label>
            <input type="password" id="new_password" name="new_password" minlength="10" maxlength="200" autocomplete="new-password" required>
        </div>
        <div class="field">
            <label for="confirm_password">Confirm new password</label>
            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required>
        </div>
        <div class="actions"><button class="btn btn-primary" type="submit">Change password</button></div>
    </form>
</div>
<?php admin_footer();
