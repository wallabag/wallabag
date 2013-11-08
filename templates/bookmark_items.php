<?php if ($item['bookmark']): ?>
    <a
        id="bookmark-<?php echo $item['id'] ?>"
        href="?action=bookmark&amp;value=0&amp;id=<?php echo $item['id'] ?>&amp;menu=<?php echo $menu ?>&amp;offset=<?php echo $offset ?>&amp;source=<?php echo $source ?>"
        data-action="bookmark"
        data-item-id="<?php echo $item['id'] ?>"
        data-reverse-label="<?php echo t('bookmark') ?>"
    >
        <?php echo t('remove bookmark') ?></a> |
<?php else: ?>
    <a
        id="bookmark-<?php echo $item['id'] ?>"
        href="?action=bookmark&amp;value=1&amp;id=<?php echo $item['id'] ?>&amp;menu=<?php echo $menu ?>&amp;offset=<?php echo $offset ?>&amp;source=<?php echo $source ?>"
        data-action="bookmark"
        data-item-id="<?php echo $item['id'] ?>"
        data-reverse-label="<?php echo t('remove bookmark') ?>"
    >
        <?php echo t('bookmark') ?></a> |
<?php endif ?>
