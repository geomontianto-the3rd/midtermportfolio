<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

/* Receives the public contact form. All input is validated on the SERVER;
   the JavaScript checks are only a convenience for the visitor. */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php#contact');
}

function contact_back(string $type, string $message, array $errors = [], array $old = []): void
{
    $_SESSION['contact_flash'] = ['type' => $type, 'message' => $message, 'errors' => $errors];
    $_SESSION['contact_old']   = $old;
    redirect('index.php#contact');
}

// 1) CSRF token
if (!csrf_valid()) {
    contact_back('error', 'Your session expired. Please try sending the message again.');
}

// 2) Honeypot: bots fill the hidden field. Pretend success so they learn nothing.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    contact_back('ok', 'Thank you! Your message has been sent.');
}

// 3) Timing check: a human needs more than 3 seconds to fill the form
$started = (int) ($_SESSION['form_started'] ?? 0);
if ($started === 0 || (time() - $started) < 3) {
    contact_back('error', 'That was very quick. Please wait a moment and send again.');
}

// 4) Clean + validate
$clean = function (string $key, bool $multiline = false): string {
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) {
        return '';
    }
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $v) ?? '';
    $v = str_replace(["\r\n", "\r"], "\n", $v);
    $v = trim($v);
    if (!$multiline) {
        $v = trim(preg_replace('/\s+/u', ' ', $v) ?? $v);
    }
    return $v;
};

$name    = $clean('name');
$email   = $clean('email');
$subject = $clean('subject');
$message = $clean('message', true);
$old = ['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message];

$errors = [];
if (str_length($name) < 2 || str_length($name) > 100) {
    $errors['name'] = 'Enter your name (2-100 characters).';
}
if ($email === '' || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Enter a valid email address, like you@example.com.';
}
if (str_length($subject) < 3 || str_length($subject) > 150) {
    $errors['subject'] = 'Enter a subject (3-150 characters).';
}
if (str_length($message) < 10 || str_length($message) > 2000) {
    $errors['message'] = 'Write a message of 10 to 2000 characters.';
}
if (!$errors && preg_match_all('~https?://|www\.~i', $message . ' ' . $subject) > 2) {
    $errors['message'] = 'Please remove some links from your message.';
}
if ($errors) {
    contact_back('error', 'Fix the highlighted fields and try again.', $errors, $old);
}

// 5) Rate limit: max 3 messages per IP in 10 minutes
$ip = client_ip();
$stmt = db()->prepare('SELECT COUNT(*) FROM messages WHERE ip_address = :ip AND created_at > (NOW() - INTERVAL 10 MINUTE)');
$stmt->execute([':ip' => $ip]);
if ((int) $stmt->fetchColumn() >= 3) {
    contact_back('error', 'You have sent several messages recently. Please try again in a few minutes.', [], $old);
}

// 6) Save (prepared statement)
$stmt = db()->prepare(
    'INSERT INTO messages (name, email, subject, message, ip_address) VALUES (:n, :e, :s, :m, :ip)'
);
$stmt->execute([':n' => $name, ':e' => $email, ':s' => $subject, ':m' => $message, ':ip' => $ip]);

unset($_SESSION['form_started']);
contact_back('ok', 'Thank you! Your message has been sent.');
