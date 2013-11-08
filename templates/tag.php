<?php if (empty($items)): ?>
    <p class="alert alert-info"><?php echo t('No entry for this tag') ?></p>
<?php else: ?>

    <div class="page-header">
        <h2><?php echo t('Tag') ?> <?php echo $tag['value'] ?> (<?php echo $nb_items ?>) <a href="<?php echo Helper\get_current_base_url().'feed.php?token='.urlencode($feed_token) . '&amp;status=tag&amp;value='.$tag['id'] ?>" >rss</a></h2>
    </div>

    <section class="items" id="listing">
        <?php foreach ($items as $item): ?>
            <?php echo \PicoTools\Template\load('item', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'hide' => false)) ?>
        <?php endforeach ?>

        <?php echo \PicoTools\Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction)) ?>
    </section>

<?php endif ?>