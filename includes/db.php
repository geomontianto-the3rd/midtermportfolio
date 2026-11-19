<?php
declare(strict_types=1);

/** Read a value from includes/config.local.php (never exposed to the browser). */
function config(string $key, $default = null)
{
    static $cfg = null;
    if ($cfg === null) {
        $file = __DIR__ . '/config.local.php';
        $cfg = is_file($file) ? (require $file) : [];
        if (!is_array($cfg)) {
            $cfg = [];
        }
    }
    return $cfg[$key] ?? $default;
}

/** Show a generic error page. Details go to the server log, never to the visitor. */
function fail_safely(int $code, string $message): void
{
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store');
    }
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<meta name="robots" content="noindex"><title>Error</title></head>'
       . '<body style="font-family:system-ui,sans-serif;max-width:32rem;margin:15vh auto;padding:0 1rem">'
       . '<h1 style="font-size:1.4rem">Sorry</h1><p>'
       . htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
       . '</p></body></html>';
    exit;
}

/** One shared PDO connection. Prepared statements everywhere = no SQL injection. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            config('db_host', 'localhost'),
            config('db_name', ''),
            config('db_charset', 'utf8mb4')
        );
        $pdo = new PDO($dsn, (string) config('db_user', ''), (string) config('db_pass', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
        ]);
    } catch (PDOException $e) {
        error_log('DB connection failed: ' . $e->getMessage());
        fail_safely(503, 'The site is temporarily unavailable. Please try again later.');
    }
    return $pdo;
}
