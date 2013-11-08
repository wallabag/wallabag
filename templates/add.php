<div class="page-header">
    <h2><?php echo t('New link') ?></h2>
</div>

<form method="post" action="?action=insert" autocomplete="off">
    <?php echo Helper\form_label(t('URL'), 'url') ?>
    <?php echo Helper\form_text('url', $values, array(), array('required', 'tabindex=1', 'placeholder="'.t('http://test.com/article').'"')) ?>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue"><?php echo t('Add') ?></button>
    </div>
</form>
