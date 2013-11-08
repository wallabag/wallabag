    <div class="page-header">
        <h2><?php echo t('Search') ?></h2>
    </div>

    <section class="items" id="listing">
        <form method="post" action="?action=search">
            <?php echo Helper\form_label(t('Query'), 'query') ?>
            <?php echo Helper\form_text('query', array(), array(), array('required', 'autofocus')) ?>
            <div class="form-actions">
                <input type="submit" value="<?php echo t('Search') ?>" class="btn btn-blue"/>
            </div>
        </form>
    </section>

    <section class="items" id="listing">
        <?php if (isset($items)) : ?>
        <?php foreach ($items as $item): ?>
            <?php echo \PicoTools\Template\load('item', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'hide' => true)) ?>
        <?php endforeach ?>
        <?php endif; ?>
    </section>
