<?php if ($item['status'] == 'unread'): ?>
    <a
        id="status-<?= $item['id'] ?>"
        href="?action=mark-item-read&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>&amp;redirect=<?= $redirect ?>"
        data-action="mark-read"
        data-item-id="<?= $item['id'] ?>"
        data-reverse-label="<?= t('mark as unread') ?>"
    >
        <?= t('mark as read') ?></a> |
<?php else: ?>
    <a
        id="status-<?= $item['id'] ?>"
        href="?action=mark-item-unread&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>&amp;redirect=<?= $redirect ?>"
        data-action="mark-unread"
        data-item-id="<?= $item['id'] ?>"
        data-reverse-label="<?= t('mark as read') ?>"
    >
        <?= t('mark as unread') ?></a> |
<?php endif ?>
<span class="hide-mobile">
    <a
        href="?action=mark-item-removed&amp;id=<?= $item['id'] ?>&amp;offset=<?= $offset ?>&amp;redirect=<?= $redirect ?>"
    >
        <?= t('remove') ?></a> |
</span>