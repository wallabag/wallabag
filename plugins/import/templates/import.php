    <div class="page-header">
        <h2><?= t('Import') ?></h2>
    </div>

    <section class="plugins" id="listing">
        <form method="post" action="?action=import">
            <?= Helper\form_label(t('From which application do you want to import your datas?'), 'application') ?>
            <?= Helper\form_radios('application', $values, array(), true, $errors) ?>
            <div class="form-actions">
                <input type="submit" value="<?= t('Import') ?>" class="btn btn-blue"/>
            </div>
        </form>
    </section>
