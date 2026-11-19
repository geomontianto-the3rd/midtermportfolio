<?php
declare(strict_types=1);
require __DIR__ . '/_crud.php';

require_admin();

// Settings are stored as key/value rows, so this page uses the same form helpers without the CRUD engine.
$fields = [
    'site_title'       => ['label' => 'Website title (browser tab)', 'required' => true, 'max' => 120],
    'meta_description' => ['label' => 'Search-engine description', 'type' => 'textarea', 'max' => 300, 'rows' => 3],
    'logo_text'        => ['label' => 'Logo text', 'required' => true, 'max' => 40, 'help' => 'Shown in the navigation bar.'],
    'logo_image'       => ['label' => 'Logo image (optional)', 'type' => 'image', 'help' => 'Replaces the logo text and is also used as the browser icon.'],
    'footer_text'      => ['label' => 'Footer name', 'max' => 80],
    'accent_light'     => ['label' => 'Accent colour (light mode)', 'type' => 'color', 'required' => true],
    'accent_dark'      => ['label' => 'Accent colour (dark mode)', 'type' => 'color', 'required' => true],
    'default_theme'    => ['label' => 'Default theme for new visitors', 'type' => 'select',
                           'options' => ['system' => 'Follow visitor’s device', 'light' => 'Light', 'dark' => 'Dark']],
];

$current = settings();
$values = $current;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    [$vals, $errors] = form_collect($fields, $_POST);
    $old = [];
    if (!$errors) {
        [$imgVals, $errors, $old] = form_uploads($fields, $_POST, $_FILES, $current);
        $vals += $imgVals;
    }

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        foreach ($vals as $key => $val) {
            $stmt->execute([':k' => $key, ':v' => (string) ($val ?? '')]);
        }
        foreach ($old as $path) {
            delete_upload($path);
        }
        flash_set('ok', 'Settings saved. The public website is updated.');
        redirect('settings.php');
    }
    $values = array_merge($current, $vals);
}

admin_header('Website settings', 'settings.php');
if ($errors) {
    echo '<div class="alert alert-error" role="alert">Please fix the highlighted fields.</div>';
}
echo '<form method="post" class="panel form" enctype="multipart/form-data" novalidate>' . csrf_field() . '<input type="hidden" name="action" value="save">';
foreach ($fields as $name => $f) {
    form_render_field($name, $f, $values[$name] ?? '', $errors[$name] ?? null);
}
echo '<div class="actions"><button class="btn btn-primary" type="submit">Save settings</button></div></form>';
admin_footer();
