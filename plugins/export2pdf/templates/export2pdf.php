    <div class="page-header">
        <h2><?= t('Export to PDF') ?></h2>
    </div>

    <section class="plugins" id="listing">
        <h3><?=t('Unread items')?></h3>
        <form method="post" action="?action=export2pdf">
        <ul>
        <?php foreach ($unread_items as $item): ?>
            <li><?= Helper\form_checkbox('chk_unread', Helper\escape($item['title']), $item['id'], false, '') ?></li>
        <?php endforeach ?>
        </ul>
        <div class="form-actions">
            <input type="button" onclick="toggleCheckboxes('chk_unread')" value="<?= t('Select all') ?>" class="btn btn-blue"/> <input type="submit" value="<?= t('Export selected') ?>" class="btn btn-blue"/>
        </div>
        </form>

        <h3><?=t('Bookmarked items')?></h3>
        <form method="post" action="?action=import">
        <ul>
        <?php foreach ($bookmarked_items as $item): ?>
            <li><?= Helper\form_checkbox('chk_bookmarked', Helper\escape($item['title']), $item['id'], false, '') ?></li>
        <?php endforeach ?>
        </ul>
        <div class="form-actions">
            <input type="button" onclick="toggleCheckboxes('chk_bookmarked')" value="<?= t('Select all') ?>" class="btn btn-blue"/> <input type="submit" value="<?= t('Export selected') ?>" class="btn btn-blue"/>
        </div>
        </form>

        <h3><?=t('Read items')?></h3>
        <form method="post" action="?action=import">
        <ul>
        <?php foreach ($read_items as $item): ?>
            <li><?= Helper\form_checkbox('chk_read', Helper\escape($item['title']), $item['id'], false, '') ?></li>
        <?php endforeach ?>
        </ul>
        <div class="form-actions">
            <input type="button" onclick="toggleCheckboxes('chk_read')" value="<?= t('Select all') ?>" class="btn btn-blue"/> <input type="submit" value="<?= t('Export selected') ?>" class="btn btn-blue"/>
        </div>
        </form>

    </section>
