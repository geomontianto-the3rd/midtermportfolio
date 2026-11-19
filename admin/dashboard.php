<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';

$admin = require_admin();

$stats = db()->query(
    'SELECT
        (SELECT COUNT(*) FROM projects)     AS projects,
        (SELECT COUNT(*) FROM skills)       AS skills,
        (SELECT COUNT(*) FROM education)    AS education,
        (SELECT COUNT(*) FROM experience)   AS experience,
        (SELECT COUNT(*) FROM achievements) AS achievements,
        (SELECT COUNT(*) FROM messages)     AS messages,
        (SELECT COUNT(*) FROM messages WHERE is_read = 0) AS unread'
)->fetch();

$recent = db()->query('SELECT id, name, subject, is_read, created_at FROM messages ORDER BY created_at DESC, id DESC LIMIT 5')->fetchAll();

$cards = [
    ['Projects',      $stats['projects'],     'projects.php'],
    ['Skills',        $stats['skills'],       'skills.php'],
    ['Education',     $stats['education'],    'education.php'],
    ['Experience',    $stats['experience'],   'experience.php'],
    ['Achievements',  $stats['achievements'], 'achievements.php'],
    ['Messages',      $stats['messages'],     'messages.php'],
];

admin_header('Dashboard', 'dashboard.php');
?>
<p class="muted">Welcome back, <strong><?= e($admin['username']) ?></strong>.
    <?php if (!empty($admin['last_login_at'])): ?>Last login: <?= e($admin['last_login_at']) ?>.<?php endif; ?></p>

<div class="stat-grid">
    <?php foreach ($cards as [$label, $count, $link]): ?>
        <a class="stat panel" href="<?= e($link) ?>">
            <span class="stat-num"><?= (int) $count ?></span>
            <span class="stat-label">Total <?= e(strtolower($label)) ?></span>
        </a>
    <?php endforeach; ?>
    <a class="stat panel stat-alert" href="messages.php?filter=unread">
        <span class="stat-num"><?= (int) $stats['unread'] ?></span>
        <span class="stat-label">Unread messages</span>
    </a>
</div>

<div class="two-col">
    <section class="panel">
        <h2>Recent messages</h2>
        <?php if (!$recent): ?>
            <p class="muted">No messages yet.</p>
        <?php else: ?>
            <ul class="plain-list">
                <?php foreach ($recent as $m): ?>
                    <li>
                        <a href="messages.php#m<?= (int) $m['id'] ?>"><?= e(truncate($m['subject'], 50)) ?></a>
                        <?php if (!(int) $m['is_read']): ?><span class="badge">new</span><?php endif; ?>
                        <br><span class="muted"><?= e($m['name']) ?> &middot; <?= e($m['created_at']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="panel">
        <h2>Quick actions</h2>
        <p class="actions-col">
            <a class="btn btn-primary" href="projects.php?new=1">+ Add project</a>
            <a class="btn btn-ghost" href="skills.php?new=1">+ Add skill</a>
            <a class="btn btn-ghost" href="profile.php">Edit personal info</a>
            <a class="btn btn-ghost" href="account.php">Administrator profile</a>
            <a class="btn btn-ghost" href="../index.php" target="_blank" rel="noopener">View website ↗</a>
        </p>
    </section>
</div>
<?php admin_footer();
