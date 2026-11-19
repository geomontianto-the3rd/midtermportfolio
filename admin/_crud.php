<?php
declare(strict_types=1);

require_once __DIR__ . '/_layout.php';

/* =========================================================
   Form helpers shared by every module
   ========================================================= */

/** Clean + validate all NON-image fields. Returns [values, errors]. */
function form_collect(array $fields, array $post): array
{
    $values = [];
    $errors = [];

    foreach ($fields as $name => $f) {
        $type  = $f['type'] ?? 'text';
        $label = $f['label'] ?? ucfirst($name);
        if ($type === 'image') {
            continue;
        }

        $raw = $post[$name] ?? '';
        if (!is_string($raw)) {
            $raw = '';
        }
        // remove control characters, normalise line breaks
        $raw = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $raw) ?? '';
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        if (!preg_match('//u', $raw)) {
            $values[$name] = '';
            $errors[$name] = $label . ' contains invalid characters.';
            continue;
        }

        $val = trim($raw);
        if ($type !== 'textarea') {
            $val = trim(preg_replace('/\s+/u', ' ', $val) ?? $val);   // single line
        }
        $values[$name] = $val;

        if ($val === '') {
            if (!empty($f['required'])) {
                $errors[$name] = $label . ' is required.';
            } elseif ($type === 'int') {
                $values[$name] = (int) ($f['default'] ?? 0);
            }
            continue;
        }

        $max = (int) ($f['max'] ?? 0);
        if ($max > 0 && str_length($val) > $max) {
            $errors[$name] = $label . ' must be at most ' . $max . ' characters.';
            continue;
        }

        switch ($type) {
            case 'int':
                $min = (int) ($f['min'] ?? 0);
                $maxv = (int) ($f['maxv'] ?? 1000000);
                $i = filter_var($val, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $maxv]]);
                if ($i === false) {
                    $errors[$name] = $label . ' must be a whole number from ' . $min . ' to ' . $maxv . '.';
                } else {
                    $values[$name] = $i;
                }
                break;
            case 'select':
                if (!array_key_exists($val, $f['options'] ?? [])) {
                    $errors[$name] = 'Choose a valid option for ' . $label . '.';
                }
                break;
            case 'color':
                if (!preg_match('/^#[0-9a-f]{6}$/i', $val)) {
                    $errors[$name] = $label . ' must be a colour like #17694d.';
                } else {
                    $values[$name] = strtolower($val);
                }
                break;
            case 'url':
                if (safe_url($val) === '') {
                    $errors[$name] = $label . ' must start with http:// or https://';
                }
                break;
            case 'email':
                if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    $errors[$name] = 'Enter a valid email address for ' . $label . '.';
                }
                break;
        }
    }
    return [$values, $errors];
}

/** Handle image fields. Returns [values, errors, oldFilesToDelete]. */
function form_uploads(array $fields, array $post, array $files, array $current): array
{
    $values = [];
    $errors = [];
    $old = [];

    foreach ($fields as $name => $f) {
        if (($f['type'] ?? '') !== 'image') {
            continue;
        }
        $cur = $current[$name] ?? null;
        $values[$name] = ($cur !== null && $cur !== '') ? $cur : null;

        $file = $files[$name] ?? null;
        $chosen = is_array($file) && (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);

        if ($chosen) {
            [$path, $err] = upload_image($file);
            if ($err !== null) {
                $errors[$name] = $err;
                continue;
            }
            $values[$name] = $path;
            if ($values[$name] !== $cur && $cur) {
                $old[] = $cur;
            }
        } elseif (!empty($post['remove_' . $name])) {
            $values[$name] = null;
            if ($cur) {
                $old[] = $cur;
            }
        }
    }
    return [$values, $errors, $old];
}

/** Print one labelled form control. */
function form_render_field(string $name, array $f, $value, ?string $error): void
{
    $id = 'f_' . $name;
    $label = $f['label'] ?? ucfirst($name);
    $type = $f['type'] ?? 'text';
    $req = !empty($f['required']) ? ' required' : '';
    $aria = $error ? ' aria-invalid="true" aria-describedby="' . e($id) . '-err"' : '';
    $max = !empty($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '';

    echo '<div class="field' . ($error ? ' has-error' : '') . '">';
    echo '<label for="' . e($id) . '">' . e($label) . (!empty($f['required']) ? ' <span class="req" aria-hidden="true">*</span>' : '') . '</label>';

    switch ($type) {
        case 'textarea':
            echo '<textarea id="' . e($id) . '" name="' . e($name) . '" rows="' . (int) ($f['rows'] ?? 4) . '"' . $max . $req . $aria . '>' . e($value) . '</textarea>';
            break;

        case 'select':
            echo '<select id="' . e($id) . '" name="' . e($name) . '"' . $req . $aria . '>';
            foreach ($f['options'] as $k => $text) {
                $sel = ((string) $value === (string) $k) ? ' selected' : '';
                echo '<option value="' . e($k) . '"' . $sel . '>' . e($text) . '</option>';
            }
            echo '</select>';
            break;

        case 'image':
            $src = img_url(is_string($value) ? $value : null, '../');
            if ($src !== '') {
                echo '<div class="current-image"><img src="' . e($src) . '" alt="Current image" loading="lazy">'
                   . '<label class="check"><input type="checkbox" name="remove_' . e($name) . '" value="1"> Remove this image</label></div>';
            }
            echo '<input type="file" id="' . e($id) . '" name="' . e($name) . '" accept="image/jpeg,image/png,image/webp,image/gif"' . $aria . '>';
            break;

        case 'color':
            $v = preg_match('/^#[0-9a-f]{6}$/i', (string) $value) ? (string) $value : '#000000';
            echo '<input type="color" id="' . e($id) . '" name="' . e($name) . '" value="' . e($v) . '"' . $aria . '>';
            break;

        default:
            $htmlType = in_array($type, ['url', 'email'], true) ? $type : ($type === 'int' ? 'number' : 'text');
            $extra = '';
            if ($type === 'int') {
                $extra = ' min="' . (int) ($f['min'] ?? 0) . '" max="' . (int) ($f['maxv'] ?? 1000000) . '" step="1"';
            }
            $list = '';
            if (!empty($f['suggest'])) {
                $list = ' list="dl_' . e($name) . '"';
            }
            echo '<input type="' . $htmlType . '" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '"' . $max . $extra . $list . $req . $aria . '>';
            if ($list !== '') {
                echo '<datalist id="dl_' . e($name) . '">';
                foreach ($f['suggest'] as $s) {
                    echo '<option value="' . e($s) . '">';
                }
                echo '</datalist>';
            }
    }

    if (!empty($f['help'])) {
        echo '<p class="help">' . e($f['help']) . '</p>';
    }
    if ($error) {
        echo '<p class="field-error" id="' . e($id) . '-err">' . e($error) . '</p>';
    }
    echo '</div>';
}

/* =========================================================
   The CRUD engine: one function drives every module
   ========================================================= */

function crud_find(string $table, int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM `' . $table . '` WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function crud_page(array $cfg): void
{
    require_admin();

    $table  = $cfg['table'];                 // set by us in the page file, never by the visitor
    $fields = $cfg['fields'];
    $single = !empty($cfg['single']);        // true = a single row (personal info)
    $self   = basename((string) $_SERVER['SCRIPT_NAME']);
    $isMultipart = false;
    foreach ($fields as $f) {
        if (($f['type'] ?? '') === 'image') {
            $isMultipart = true;
        }
    }

    $mode = 'list';
    $values = [];
    $errors = [];
    $editId = 0;

    /* ---------- handle POST (create / update / delete) ---------- */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verify();
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'delete' && !$single) {
            $id = (int) ($_POST['id'] ?? 0);
            $row = $id > 0 ? crud_find($table, $id) : null;
            if ($row) {
                $stmt = db()->prepare('DELETE FROM `' . $table . '` WHERE id = :id');
                $stmt->execute([':id' => $id]);
                foreach ($fields as $name => $f) {
                    if (($f['type'] ?? '') === 'image') {
                        delete_upload($row[$name] ?? null);
                    }
                }
                flash_set('ok', $cfg['singular'] . ' deleted.');
            } else {
                flash_set('error', 'That record no longer exists.');
            }
            redirect($self);
        }

        if ($action === 'save') {
            $id = $single ? 1 : (int) ($_POST['id'] ?? 0);
            $current = $id > 0 ? crud_find($table, $id) : null;
            if (!$single && $id > 0 && !$current) {
                flash_set('error', 'That record no longer exists.');
                redirect($self);
            }

            [$values, $errors] = form_collect($fields, $_POST);
            $old = [];
            if (!$errors) {
                [$imgValues, $errors, $old] = form_uploads($fields, $_POST, $_FILES, $current ?? []);
                $values += $imgValues;
            }

            if (!$errors) {
                $cols = array_keys($values);
                $params = [];
                foreach ($values as $k => $v) {
                    $params[':' . $k] = $v;
                }
                if ($current) {
                    $set = implode(', ', array_map(fn($c) => '`' . $c . '` = :' . $c, $cols));
                    $params[':__id'] = $id;
                    $stmt = db()->prepare('UPDATE `' . $table . '` SET ' . $set . ' WHERE id = :__id');
                } else {
                    if ($single) {
                        $cols[] = 'id';
                        $params[':id'] = 1;
                    }
                    $names = implode(', ', array_map(fn($c) => '`' . $c . '`', $cols));
                    $marks = implode(', ', array_map(fn($c) => ':' . $c, $cols));
                    $stmt = db()->prepare('INSERT INTO `' . $table . '` (' . $names . ') VALUES (' . $marks . ')');
                }
                $stmt->execute($params);

                foreach ($old as $path) {
                    delete_upload($path);
                }
                flash_set('ok', $single ? 'Saved. The public website is updated.' : $cfg['singular'] . ' saved.');
                redirect($self);
            }

            // validation failed: show the form again with what the admin typed
            $values = array_merge($current ?? [], $values);
            $mode = 'form';
            $editId = $current ? $id : 0;
        }
    } else {
        /* ---------- handle GET ---------- */
        if ($single) {
            $mode = 'form';
            $editId = 1;
            $values = crud_find($table, 1) ?? [];
        } elseif (isset($_GET['new'])) {
            $mode = 'form';
            foreach ($fields as $name => $f) {
                $values[$name] = $f['default'] ?? '';
            }
        } elseif (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
            $row = crud_find($table, (int) $_GET['edit']);
            if ($row) {
                $mode = 'form';
                $editId = (int) $row['id'];
                $values = $row;
            } else {
                flash_set('error', 'That record was not found.');
                redirect($self);
            }
        }
    }

    /* ---------- render ---------- */
    admin_header($cfg['title'], $self);

    if ($mode === 'form') {
        if ($errors) {
            echo '<div class="alert alert-error" role="alert">Please fix the highlighted fields.</div>';
        }
        echo '<form method="post" class="panel form"' . ($isMultipart ? ' enctype="multipart/form-data"' : '') . ' novalidate>';
        echo csrf_field() . '<input type="hidden" name="action" value="save">';
        if (!$single) {
            echo '<input type="hidden" name="id" value="' . (int) $editId . '">';
            echo '<h2>' . ($editId ? 'Edit ' : 'Add ') . e(strtolower($cfg['singular'])) . '</h2>';
        }
        foreach ($fields as $name => $f) {
            form_render_field($name, $f, $values[$name] ?? '', $errors[$name] ?? null);
        }
        echo '<div class="actions"><button class="btn btn-primary" type="submit">Save</button>';
        if (!$single) {
            echo ' <a class="btn btn-ghost" href="' . e($self) . '">Cancel</a>';
        }
        echo '</div></form>';
        admin_footer();
        return;
    }

    // list view
    $order = $cfg['order'] ?? 'sort_order ASC, id ASC';
    $rows = db()->query('SELECT * FROM `' . $table . '` ORDER BY ' . $order)->fetchAll();

    echo '<p class="toolbar"><a class="btn btn-primary" href="' . e($self) . '?new=1">+ Add ' . e(strtolower($cfg['singular'])) . '</a>'
       . '<span class="muted">' . count($rows) . ' total</span></p>';

    if (!$rows) {
        echo '<div class="panel empty">Nothing here yet. Use the button above to add the first one.</div>';
        admin_footer();
        return;
    }

    echo '<div class="panel table-wrap"><table><thead><tr>';
    foreach ($cfg['columns'] as $col => $label) {
        echo '<th scope="col">' . e($label) . '</th>';
    }
    echo '<th scope="col" class="col-actions">Actions</th></tr></thead><tbody>';

    foreach ($rows as $r) {
        echo '<tr>';
        foreach ($cfg['columns'] as $col => $label) {
            echo '<td data-label="' . e($label) . '">';
            $ftype = $fields[$col]['type'] ?? 'text';
            if ($ftype === 'image') {
                $src = img_url($r[$col] ?? null, '../');
                echo $src !== '' ? '<img class="thumb-sm" src="' . e($src) . '" alt="">' : '<span class="muted">none</span>';
            } else {
                echo e(truncate((string) ($r[$col] ?? ''), 70));
            }
            echo '</td>';
        }
        $title = (string) ($r[array_key_first($cfg['columns'])] ?? '');
        echo '<td class="col-actions"><a class="btn btn-ghost btn-sm" href="' . e($self) . '?edit=' . (int) $r['id'] . '">Edit</a> '
           . '<form method="post" class="inline" data-confirm="' . e('Delete “' . truncate($title, 40) . '” permanently?') . '">'
           . csrf_field() . '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . (int) $r['id'] . '">'
           . '<button class="btn btn-danger btn-sm" type="submit">Delete</button></form></td></tr>';
    }
    echo '</tbody></table></div>';
    admin_footer();
}
