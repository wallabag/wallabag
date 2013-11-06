<div id="items-paging">
<?php if ($offset > 0): ?>
    <a id="previous-page" href="?action=<?= $menu ?>&amp;offset=<?= ($offset - $items_per_page) ?>&amp;order=<?= $order ?>&amp;direction=<?= $direction ?><?= isset($feed_id) ? '&amp;feed_id='.$feed_id : '' ?>">« <?= t('Previous page') ?></a>
<?php endif ?>
&nbsp;
<?php if (($nb_items - $offset) > $items_per_page): ?>
    <a id="next-page" href="?action=<?= $menu ?>&amp;offset=<?= ($offset + $items_per_page) ?>&amp;order=<?= $order ?>&amp;direction=<?= $direction ?><?= isset($feed_id) ? '&amp;feed_id='.$feed_id : '' ?>"><?= t('Next page') ?> »</a>
<?php endif ?>
</div>