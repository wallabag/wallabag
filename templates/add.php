<div class="page-header">
    <h2><?= t('New link') ?></h2>
</div>

<form method="post" action="?action=insert" autocomplete="off">
    <?= Helper\form_label(t('URL'), 'url') ?>
    <?= Helper\form_text('url', $values, array(), array('required', 'tabindex=1', 'placeholder="'.t('http://test.com/article').'"')) ?>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?= t('Add') ?></button>
    </div>
</form>
