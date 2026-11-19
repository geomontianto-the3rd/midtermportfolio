<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

function unread_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
}

function render_flash(): void
{
    foreach (flash_pull() as $f) {
        $class = $f['type'] === 'error' ? 'alert alert-error' : 'alert alert-ok';
        echo '<div class="' . $class . '" role="status">' . e($f['msg']) . '</div>';
    }
}

function admin_head(string $title): void
{
    $theme = in_array(settings()['default_theme'], ['light', 'dark'], true) ? settings()['default_theme'] : 'system';
    echo '<!DOCTYPE html><html lang="en" data-theme="light" data-default="' . e($theme) . '"><head>'
       . '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<meta name="robots" content="noindex,nofollow">'
       . '<title>' . e($title) . ' | Admin</title>'
       . '<script src="../assets/js/theme-init.js"></script>'
       . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Serif&family=Manrope:wght@400;600;700&display=swap">'
       . '<link rel="stylesheet" href="../assets/css/admin.css">'
       . '</head><body>';
}

/** Page shell for logged-in pages: sidebar + content area. */
function admin_header(string $title, string $active = ''): void
{
    $admin = current_admin();
    $unread = unread_count();

    $nav = [
        'dashboard.php'    => 'Dashboard',
        'profile.php'      => 'Personal info',
        'skills.php'       => 'Skills',
        'projects.php'     => 'Projects',
        'education.php'    => 'Education',
        'experience.php'   => 'Experience',
        'achievements.php' => 'Achievements',
        'messages.php'     => 'Messages',
        'settings.php'     => 'Website settings',
        'account.php'      => 'My account',
    ];

    admin_head($title);
    echo '<div class="shell">';
    echo '<header class="topbar"><button class="menu-toggle" id="adminMenuBtn" type="button" aria-expanded="false" aria-controls="adminNav">Menu</button>'
       . '<strong class="brand">Admin<span>.</span></strong></header>';
    echo '<aside class="sidebar" id="adminNav"><div class="brand brand-desktop">Admin<span>.</span></div><nav aria-label="Admin">';
    foreach ($nav as $file => $label) {
        $cls = ($active === $file) ? ' class="active" aria-current="page"' : '';
        $badge = ($file === 'messages.php' && $unread > 0) ? ' <span class="badge">' . $unread . '</span>' : '';
        echo '<a href="' . e($file) . '"' . $cls . '>' . e($label) . $badge . '</a>';
    }
    echo '<a href="../index.php" target="_blank" rel="noopener">View website ↗</a></nav>';
    echo '<div class="side-foot"><p>Signed in as<br><strong>' . e($admin['username'] ?? '') . '</strong></p>'
       . '<form method="post" action="logout.php">' . csrf_field() . '<button class="btn btn-ghost btn-sm" type="submit">Log out</button></form></div>';
    echo '</aside><main class="content" id="main"><h1>' . e($title) . '</h1>';
    render_flash();
}

function admin_footer(): void
{
    echo '</main></div><script src="../assets/js/admin.js"></script></body></html>';
}

/** Centered card for login / setup. */
function auth_header(string $title): void
{
    admin_head($title);
    echo '<main class="auth"><div class="auth-card"><div class="brand">Admin<span>.</span></div><h1>' . e($title) . '</h1>';
}

function auth_footer(): void
{
    echo '</div></main><script src="../assets/js/admin.js"></script></body></html>';
}
