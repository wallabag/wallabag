<?php if (empty($items)): ?>
    <p class="alert alert-info"><?php echo t('No history') ?></p>
<?php else: ?>

    <div class="page-header">
        <h2><?php echo t('History') ?> (<?php echo $nb_items ?>)</h2>
    </div>

    <section class="items" id="listing">
        <?php foreach ($items as $item): ?>
            <?php echo \PicoTools\Template\load('item', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'hide' => true)) ?>
        <?php endforeach ?>

        <?php echo \PicoTools\Template\load('paging', array('menu' => $menu, 'nb_items' => $nb_items, 'items_per_page' => $items_per_page, 'offset' => $offset, 'order' => $order, 'direction' => $direction)) ?>
    </section>

<?php endif ?>
