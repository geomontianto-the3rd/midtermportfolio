<?php
declare(strict_types=1);
require __DIR__ . '/_layout.php';

require_admin();

$filter = (($_GET['filter'] ?? 'all') === 'unread') ? 'unread' : 'all';

/* ---------- actions: mark read / unread / delete (POST + CSRF only) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $action = (string) ($_POST['action'] ?? '');
    $back = 'messages.php' . ($filter === 'unread' ? '?filter=unread' : '');

    if ($id > 0) {
        if ($action === 'mark_read' || $action === 'mark_unread') {
            $stmt = db()->prepare('UPDATE messages SET is_read = :r WHERE id = :id');
            $stmt->execute([':r' => $action === 'mark_read' ? 1 : 0, ':id' => $id]);
            flash_set('ok', $action === 'mark_read' ? 'Marked as read.' : 'Marked as unread.');
        } elseif ($action === 'delete') {
            $stmt = db()->prepare('DELETE FROM messages WHERE id = :id');
            $stmt->execute([':id' => $id]);
            flash_set('ok', 'Message deleted.');
        }
    }
    redirect($back);
}

$sql = 'SELECT * FROM messages ' . ($filter === 'unread' ? 'WHERE is_read = 0 ' : '') . 'ORDER BY created_at DESC, id DESC LIMIT 200';
$rows = db()->query($sql)->fetchAll();

admin_header('Messages', 'messages.php');
?>
<p class="toolbar">
    <a class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="messages.php">All</a>
    <a class="btn <?= $filter === 'unread' ? 'btn-primary' : 'btn-ghost' ?> btn-sm" href="messages.php?filter=unread">Unread only</a>
    <span class="muted"><?= count($rows) ?> shown</span>
</p>

<?php if (!$rows): ?>
    <div class="panel empty">No messages <?= $filter === 'unread' ? 'unread' : 'yet' ?>.</div>
<?php endif; ?>

<?php foreach ($rows as $m): $unread = !(int) $m['is_read']; ?>
    <details class="panel msg<?= $unread ? ' msg-unread' : '' ?>" id="m<?= (int) $m['id'] ?>">
        <summary>
            <span class="msg-subject"><?= e($m['subject']) ?></span>
            <?php if ($unread): ?><span class="badge">new</span><?php endif; ?>
            <span class="muted msg-meta"><?= e($m['name']) ?> &middot; <?= e($m['created_at']) ?></span>
        </summary>
        <dl class="msg-info">
            <div><dt>From</dt><dd><?= e($m['name']) ?> &lt;<a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>&gt;</dd></div>
            <div><dt>Received</dt><dd><?= e($m['created_at']) ?></dd></div>
        </dl>
        <p class="msg-body"><?= nl2br(e($m['message'])) ?></p>
        <div class="actions">
            <form method="post" class="inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                <input type="hidden" name="action" value="<?= $unread ? 'mark_read' : 'mark_unread' ?>">
                <button class="btn btn-ghost btn-sm" type="submit">Mark as <?= $unread ? 'read' : 'unread' ?></button>
            </form>
            <form method="post" class="inline" data-confirm="Delete this message permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
            </form>
        </div>
    </details>
<?php endforeach; ?>
<?php admin_footer();
