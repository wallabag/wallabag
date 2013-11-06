<div class="page-header">
    <h2><?= t('Edit tag') ?></h2>
</div>

<section class="items" id="listing">
    <ul>
    <?php foreach ($tags as $tag): ?>
        <li><?php echo $tag['value'] ?> <a href="?action=remove-tags&amp;entry_id=<?=$item['id']?>&amp;tag_id=<?=$tag['id']?>">✘</a></li>
    <?php endforeach; ?>
    </ul>

    <form method="post" action="?action=edit-tags">
        <?= Helper\form_label(t('New tags'), 'value') ?>
        <?= Helper\form_text('value', array(), array(), array('required')) ?>
        <?= Helper\form_hidden('entry_id', array('entry_id' => $item['id'])) ?>
        <?= t('you can type several tags, separated by space'); ?>
        <div class="form-actions">
            <input type="submit" value="<?= t('Add tags') ?>" class="btn btn-blue"/>
        </div>
    </form>

</section>