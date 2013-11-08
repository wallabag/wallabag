<?php if ($item['status'] == 'unread'): ?>
    <a
        id="status-<?php echo $item['id'] ?>"
        href="?action=mark-item-read&amp;id=<?php echo $item['id'] ?>&amp;offset=<?php echo $offset ?>&amp;redirect=<?php echo $redirect ?>"
        data-action="mark-read"
        data-item-id="<?php echo $item['id'] ?>"
        data-reverse-label="<?php echo t('mark as unread') ?>"
    >
        <?php echo t('mark as read') ?></a> |
<?php else: ?>
    <a
        id="status-<?php echo $item['id'] ?>"
        href="?action=mark-item-unread&amp;id=<?php echo $item['id'] ?>&amp;offset=<?php echo $offset ?>&amp;redirect=<?php echo $redirect ?>"
        data-action="mark-unread"
        data-item-id="<?php echo $item['id'] ?>"
        data-reverse-label="<?php echo t('mark as read') ?>"
    >
        <?php echo t('mark as unread') ?></a> |
<?php endif ?>
<span class="hide-mobile">
    <a
        href="?action=mark-item-removed&amp;id=<?php echo $item['id'] ?>&amp;offset=<?php echo $offset ?>&amp;redirect=<?php echo $redirect ?>"
    >
        <?php echo t('remove') ?></a> |
</span>