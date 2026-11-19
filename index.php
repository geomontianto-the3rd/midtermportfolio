<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$S = settings();
$P = profile();
$pdo = db();

$skillRows   = $pdo->query('SELECT * FROM skills ORDER BY sort_order, id')->fetchAll();
$projects    = $pdo->query('SELECT * FROM projects ORDER BY sort_order, id')->fetchAll();
$education   = $pdo->query('SELECT * FROM education ORDER BY sort_order, id')->fetchAll();
$experience  = $pdo->query('SELECT * FROM experience ORDER BY sort_order, id')->fetchAll();
$achievements = $pdo->query('SELECT * FROM achievements ORDER BY sort_order, id')->fetchAll();

// group skills by category, keeping the admin's order
$skillGroups = [];
foreach ($skillRows as $s) {
    $skillGroups[$s['category']][] = $s;
}

// typing-effect words
$words = array_values(array_filter(array_map('trim', explode(',', (string) ($P['role_text'] ?? '')))));
if (!$words) {
    $words = ['Web Developer'];
}

// contact form state: what happened on the last submit
$flash = $_SESSION['contact_flash'] ?? null;
$old   = $_SESSION['contact_old'] ?? [];
unset($_SESSION['contact_flash'], $_SESSION['contact_old']);
$_SESSION['form_started'] = time();   // used by the "too fast = bot" check
$formErrors = $flash['errors'] ?? [];

$theme = in_array($S['default_theme'], ['light', 'dark'], true) ? $S['default_theme'] : 'system';
$logoImg = img_url($S['logo_image'] ?? '');
$portrait = img_url($P['profile_image'] ?? '');
$name = $P['display_name'] ?? 'Portfolio';
$hasExperienceSection = $education || $experience || $achievements;

function field_attr(array $errors, string $id): string
{
    return isset($errors[$id]) ? ' aria-invalid="true"' : '';
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light" data-default="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($S['site_title']) ?></title>
    <meta name="description" content="<?= e($S['meta_description']) ?>">
    <meta name="theme-color" content="#e9f0ed">
    <?php if ($logoImg !== ''): ?><link rel="icon" href="<?= e($logoImg) ?>"><?php endif; ?>

    <!-- Apply the saved (or default) theme before first paint to avoid a flash -->
    <script src="assets/js/theme-init.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Manrope:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        :root { --accent: <?= e($S['accent_light']) ?>; --chip: <?= e(hex_to_rgba($S['accent_light'], 0.12)) ?>; }
        :root[data-theme="dark"] { --accent: <?= e($S['accent_dark']) ?>; --chip: <?= e(hex_to_rgba($S['accent_dark'], 0.16)) ?>; }
    </style>
</head>

<body>

<a class="skip-link" href="#main">Skip to content</a>

<!-- ================= NAVIGATION ================= -->
<header class="site-header">
    <div class="container">
        <div class="navbar glass">

            <a href="#home" class="logo" aria-label="<?= e($name) ?>, back to top">
                <?php if ($logoImg !== ''): ?>
                    <img class="logo-img" src="<?= e($logoImg) ?>" alt="<?= e($S['logo_text']) ?>">
                <?php else: ?>
                    <?= e($S['logo_text']) ?><span>.</span>
                <?php endif; ?>
            </a>

            <nav class="nav" id="navMenu" aria-label="Main">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <?php if ($skillGroups): ?><a href="#skills">Skills</a><?php endif; ?>
                <?php if ($projects): ?><a href="#projects">Projects</a><?php endif; ?>
                <?php if ($hasExperienceSection): ?><a href="#experience">Experience</a><?php endif; ?>
                <a href="#contact">Contact</a>
            </nav>

            <button class="icon-btn theme-btn" id="themeBtn" type="button" aria-label="Switch to dark mode" aria-pressed="false">
                <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            </button>

            <button class="icon-btn menu-btn" id="menuBtn" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="navMenu">
                <svg class="i-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg class="i-close" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>

        </div>
    </div>
</header>

<main id="main">

    <!-- ================= HOME ================= -->
    <section id="home" class="hero">
        <div class="container hero-grid">

            <div class="hero-text">
                <h1>Hi, I'm <?= e($name) ?>.</h1>

                <p class="role">
                    <span class="sr-only"><?= e(implode(', ', $words)) ?></span>
                    <span id="typing" data-words="<?= e(implode('|', $words)) ?>" aria-hidden="true"><?= e($words[0]) ?></span>
                </p>

                <p class="lead"><?= e($P['intro'] ?? '') ?></p>

                <div class="hero-buttons">
                    <?php if ($projects): ?><a href="#projects" class="btn btn-primary">View projects</a><?php endif; ?>
                    <a href="#contact" class="btn btn-ghost">Contact me</a>
                </div>
            </div>

            <?php if ($portrait !== ''): ?>
            <figure class="portrait glass">
                <img src="<?= e($portrait) ?>" width="540" height="960" alt="<?= e($P['image_alt'] ?? '') ?>">
                <?php if (!empty($P['study_value'])): ?>
                <figcaption class="chip glass">
                    <span><?= e($P['study_label'] ?? '') ?></span>
                    <strong><?= e($P['study_value']) ?></strong>
                </figcaption>
                <?php endif; ?>
            </figure>
            <?php endif; ?>

        </div>
    </section>

    <!-- ================= ABOUT ================= -->
    <section id="about" class="section">
        <div class="container">

            <div class="section-head">
                <h2>About</h2>
                <p>My background, education, goals, and interests.</p>
            </div>

            <div class="panel glass about-panel">

                <div class="about-copy">
                    <h3><?= e($P['about_heading'] ?? '') ?></h3>
                    <?php if (!empty($P['about_text1'])): ?><p><?= nl2br(e($P['about_text1'])) ?></p><?php endif; ?>
                    <?php if (!empty($P['about_text2'])): ?><p><?= nl2br(e($P['about_text2'])) ?></p><?php endif; ?>
                </div>

                <dl class="facts">
                    <?php if (!empty($P['fact_education'])): ?><div><dt>Education</dt><dd><?= e($P['fact_education']) ?></dd></div><?php endif; ?>
                    <?php if (!empty($P['fact_focus'])): ?><div><dt>Focus</dt><dd><?= e($P['fact_focus']) ?></dd></div><?php endif; ?>
                    <?php if (!empty($P['fact_interests'])): ?><div><dt>Interests</dt><dd><?= e($P['fact_interests']) ?></dd></div><?php endif; ?>
                </dl>

            </div>
        </div>
    </section>

    <!-- ================= SKILLS ================= -->
    <?php if ($skillGroups): ?>
    <section id="skills" class="section">
        <div class="container">

            <div class="section-head">
                <h2>Skills</h2>
                <p>Technical skills I'm building through school and personal projects.</p>
            </div>

            <div class="panel glass">
                <div class="skill-groups">
                    <?php foreach ($skillGroups as $category => $items): ?>
                    <div class="skill-group">
                        <h3><?= e($category) ?></h3>
                        <?php foreach ($items as $s): $lvl = max(0, min(100, (int) $s['level'])); ?>
                        <div class="skill" style="--level:<?= $lvl ?>%">
                            <div class="skill-head"><span><?= e($s['name']) ?></span><span class="pct"><?= $lvl ?>%</span></div>
                            <?php if ($s['description'] !== ''): ?><p><?= e($s['description']) ?></p><?php endif; ?>
                            <div class="bar" aria-hidden="true"><span></span></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ================= PROJECTS ================= -->
    <?php if ($projects): ?>
    <section id="projects" class="section">
        <div class="container">

            <div class="section-head">
                <h2>Projects</h2>
                <p>A selection of my academic and personal work.</p>
            </div>

            <div class="project-grid">
                <?php foreach ($projects as $i => $p): $img = img_url($p['image'] ?? null); $link = safe_url($p['project_url']); ?>
                <article class="card glass">
                    <div class="thumb tone-<?= ($i % 4) + 1 ?>">
                        <?php if ($img !== ''): ?>
                            <img class="thumb-img" src="<?= e($img) ?>" alt="Screenshot of <?= e($p['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <?= placeholder_svg((string) $p['placeholder'], $p['title']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($p['category'] !== ''): ?><p class="kicker"><?= e($p['category']) ?></p><?php endif; ?>
                        <h3><?= e($p['title']) ?></h3>
                        <p><?= e($p['description']) ?></p>
                        <?php if ($tags = tags_list((string) $p['tags'])): ?>
                        <ul class="tags"><?php foreach ($tags as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul>
                        <?php endif; ?>
                        <?php if ($link !== ''): ?>
                        <p class="card-link"><a href="<?= e($link) ?>" target="_blank" rel="noopener noreferrer">View project &rarr;</a></p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ================= EXPERIENCE ================= -->
    <?php if ($hasExperienceSection): ?>
    <section id="experience" class="section">
        <div class="container">

            <div class="section-head">
                <h2>Experience and education</h2>
                <p>My academic and technical development so far.</p>
            </div>

            <?php foreach ([['Education', $education], ['Experience', $experience], ['Achievements', $achievements]] as [$heading, $rows]): ?>
                <?php if ($rows): ?>
                <h3 class="subhead"><?= e($heading) ?></h3>
                <div class="experience-grid">
                    <?php foreach ($rows as $i => $r): ?><?= render_info_card($r, $i) ?><?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>

        </div>
    </section>
    <?php endif; ?>

    <!-- ================= CONTACT ================= -->
    <section id="contact" class="section">
        <div class="container">

            <div class="section-head">
                <h2>Contact</h2>
                <p>Have a question or an opportunity? Send me a message.</p>
            </div>

            <div class="contact-grid">

                <div class="contact-info">
                    <h3>Let's connect.</h3>
                    <p>You can reach me by email or on Facebook.</p>

                    <dl class="contact-list">
                        <?php if (!empty($P['email'])): ?>
                        <div><dt>Email</dt><dd><a href="mailto:<?= e($P['email']) ?>"><?= e($P['email']) ?></a></dd></div>
                        <?php endif; ?>
                        <?php if (safe_url($P['facebook_url'] ?? '') !== ''): ?>
                        <div><dt>Facebook</dt><dd><a href="<?= e($P['facebook_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(preg_replace('~^https?://(www\.)?~i', '', $P['facebook_url'])) ?></a></dd></div>
                        <?php endif; ?>
                        <?php if (!empty($P['phone'])): ?>
                        <div><dt>Phone</dt><dd><?= e($P['phone']) ?></dd></div>
                        <?php endif; ?>
                        <?php if (!empty($P['location'])): ?>
                        <div><dt>Location</dt><dd><?= e($P['location']) ?></dd></div>
                        <?php endif; ?>
                    </dl>
                </div>

                <form id="contactForm" class="glass" method="post" action="contact.php" novalidate>
                    <?= csrf_field() ?>

                    <!-- Honeypot: real visitors never see or fill this; bots do -->
                    <div class="hp" aria-hidden="true">
                        <label for="website">Leave this field empty</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="field">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" maxlength="100" value="<?= e($old['name'] ?? '') ?>" placeholder="Your name" autocomplete="name" aria-describedby="name-error"<?= field_attr($formErrors, 'name') ?>>
                        <span class="error" id="name-error"><?= e($formErrors['name'] ?? '') ?></span>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" maxlength="150" value="<?= e($old['email'] ?? '') ?>" placeholder="you@example.com" autocomplete="email" aria-describedby="email-error"<?= field_attr($formErrors, 'email') ?>>
                        <span class="error" id="email-error"><?= e($formErrors['email'] ?? '') ?></span>
                    </div>

                    <div class="field">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" maxlength="150" value="<?= e($old['subject'] ?? '') ?>" placeholder="What is this about?" aria-describedby="subject-error"<?= field_attr($formErrors, 'subject') ?>>
                        <span class="error" id="subject-error"><?= e($formErrors['subject'] ?? '') ?></span>
                    </div>

                    <div class="field">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="6" maxlength="2000" placeholder="Write your message" aria-describedby="message-error"<?= field_attr($formErrors, 'message') ?>><?= e($old['message'] ?? '') ?></textarea>
                        <span class="error" id="message-error"><?= e($formErrors['message'] ?? '') ?></span>
                    </div>

                    <p id="formMessage" class="form-status<?= ($flash['type'] ?? '') === 'error' ? ' is-error' : '' ?>" role="status" aria-live="polite"><?= e($flash['message'] ?? '') ?></p>

                    <button type="submit" class="btn btn-primary">Send message</button>

                </form>

            </div>
        </div>
    </section>

</main>

<!-- ================= FOOTER ================= -->
<footer class="site-footer">
    <div class="container footer-row">
        <a href="#home" class="logo" aria-label="<?= e($name) ?>, back to top"><?= e($S['logo_text']) ?><span>.</span></a>
        <p>&copy; <span id="year"><?= date('Y') ?></span> <?= e($S['footer_text'] !== '' ? $S['footer_text'] : $name) ?>. All rights reserved.</p>
    </div>
</footer>

<button class="to-top glass" id="scrollTop" type="button" aria-label="Back to top">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<script src="assets/js/script.js"></script>
</body>
</html>
