    <div class="page-header">
        <h2><?= t('Search') ?></h2>
    </div>

    <section class="items" id="listing">
        <form method="post" action="?action=search">
            <?= Helper\form_label(t('Query'), 'query') ?>
            <?= Helper\form_text('query', array(), array(), array('required', 'autofocus')) ?>
            <div class="form-actions">
                <input type="submit" value="<?= t('Search') ?>" class="btn btn-blue"/>
            </div>
        </form>
    </section>

    <section class="items" id="listing">
        <?php if (isset($items)) : ?>
        <?php foreach ($items as $item): ?>
            <?= \PicoTools\Template\load('item', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'hide' => true)) ?>
        <?php endforeach ?>
        <?php endif; ?>
    </section>
