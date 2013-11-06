<?php if ($item['bookmark']): ?>
    <a
        id="bookmark-<?= $item['id'] ?>"
        href="?action=bookmark&amp;value=0&amp;id=<?= $item['id'] ?>&amp;menu=<?= $menu ?>&amp;offset=<?= $offset ?>&amp;source=<?= $source ?>"
        data-action="bookmark"
        data-item-id="<?= $item['id'] ?>"
        data-reverse-label="<?= t('bookmark') ?>"
    >
        <?= t('remove bookmark') ?></a> |
<?php else: ?>
    <a
        id="bookmark-<?= $item['id'] ?>"
        href="?action=bookmark&amp;value=1&amp;id=<?= $item['id'] ?>&amp;menu=<?= $menu ?>&amp;offset=<?= $offset ?>&amp;source=<?= $source ?>"
        data-action="bookmark"
        data-item-id="<?= $item['id'] ?>"
        data-reverse-label="<?= t('remove bookmark') ?>"
    >
        <?= t('bookmark') ?></a> |
<?php endif ?>
