<div id="items-paging">
<?php if ($offset > 0): ?>
    <a id="previous-page" href="?action=<?php echo $menu ?>&amp;offset=<?php echo ($offset - $items_per_page) ?>&amp;order=<?php echo $order ?>&amp;direction=<?php echo $direction ?><?php echo isset($feed_id) ? '&amp;feed_id='.$feed_id : '' ?>">« <?php echo t('Previous page') ?></a>
<?php endif ?>
&nbsp;
<?php if (($nb_items - $offset) > $items_per_page): ?>
    <a id="next-page" href="?action=<?php echo $menu ?>&amp;offset=<?php echo ($offset + $items_per_page) ?>&amp;order=<?php echo $order ?>&amp;direction=<?php echo $direction ?><?php echo isset($feed_id) ? '&amp;feed_id='.$feed_id : '' ?>"><?php echo t('Next page') ?> »</a>
<?php endif ?>
</div>