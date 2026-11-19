<?php
declare(strict_types=1);

const LOGIN_MAX_ATTEMPTS    = 5;     // failures allowed per IP ...
const LOGIN_MAX_PER_USER    = 10;    // ... or per username
const LOGIN_WINDOW_MINUTES  = 15;    // ... inside this time window
const SESSION_IDLE_SECONDS  = 1800;  // auto logout after 30 minutes of inactivity

/* ---------------- Brute-force protection ---------------- */

/** Seconds the visitor must still wait, or 0 if login is allowed. */
function login_lock_seconds(string $ip, string $username): int
{
    $window = LOGIN_WINDOW_MINUTES * 60;
    $wait = 0;

    $checks = [
        ['ip_address', $ip, LOGIN_MAX_ATTEMPTS],
        ['username', strtolower($username), LOGIN_MAX_PER_USER],
    ];
    foreach ($checks as [$column, $value, $limit]) {
        $sql = 'SELECT COUNT(*) AS c, TIMESTAMPDIFF(SECOND, MIN(attempted_at), NOW()) AS age
                FROM login_attempts
                WHERE ' . $column . ' = :v AND attempted_at > (NOW() - INTERVAL ' . LOGIN_WINDOW_MINUTES . ' MINUTE)';
        $stmt = db()->prepare($sql);
        $stmt->execute([':v' => $value]);
        $row = $stmt->fetch();
        if ($row && (int) $row['c'] >= $limit) {
            $wait = max($wait, max(1, $window - (int) $row['age']));
        }
    }
    return $wait;
}

function login_failures_left(string $ip): int
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM login_attempts
        WHERE ip_address = :ip AND attempted_at > (NOW() - INTERVAL ' . LOGIN_WINDOW_MINUTES . ' MINUTE)');
    $stmt->execute([':ip' => $ip]);
    return max(0, LOGIN_MAX_ATTEMPTS - (int) $stmt->fetchColumn());
}

function record_failed_login(string $ip, string $username): void
{
    $stmt = db()->prepare('INSERT INTO login_attempts (ip_address, username) VALUES (:ip, :u)');
    $stmt->execute([':ip' => $ip, ':u' => strtolower(substr($username, 0, 150))]);
    // housekeeping: forget old attempts
    db()->exec('DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 1 DAY)');
}

function clear_failed_logins(string $ip, string $username): void
{
    $stmt = db()->prepare('DELETE FROM login_attempts WHERE ip_address = :ip OR username = :u');
    $stmt->execute([':ip' => $ip, ':u' => strtolower($username)]);
}

/* ---------------- Session handling ---------------- */

function destroy_admin_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 3600, 'path' => $p['path'], 'domain' => $p['domain'],
            'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax',
        ]);
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

/** Call right after the password was verified. */
function admin_login(array $admin): void
{
    session_regenerate_id(true);               // stops session fixation
    $_SESSION = [];
    $_SESSION['admin_id']      = (int) $admin['id'];
    $_SESSION['admin_ua']      = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $_SESSION['last_activity'] = time();
    csrf_token();                              // fresh CSRF token for the new session
}

/** Returns the logged-in admin (id, username, email) or null. */
function current_admin(): ?array
{
    static $admin = false;
    if ($admin !== false) {
        return $admin;
    }
    $admin = null;

    $id = (int) ($_SESSION['admin_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    $ua = hash('sha256', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    $idle = time() - (int) ($_SESSION['last_activity'] ?? 0);
    if (!hash_equals((string) ($_SESSION['admin_ua'] ?? ''), $ua) || $idle > SESSION_IDLE_SECONDS) {
        destroy_admin_session();
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, email, last_login_at FROM admins WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if (!$row) {
        destroy_admin_session();
        return null;
    }
    $_SESSION['last_activity'] = time();
    $admin = $row;
    return $admin;
}

/** Put this at the top of EVERY admin page except login/setup. */
function require_admin(): array
{
    require_https_if_configured();
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Robots-Tag: noindex, nofollow');

    $admin = current_admin();
    if ($admin === null) {
        redirect('login.php');
    }
    return $admin;
}
