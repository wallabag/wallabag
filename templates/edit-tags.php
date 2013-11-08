<div class="page-header">
    <h2><?php echo t('Edit tag') ?></h2>
</div>

<section class="items" id="listing">
    <ul>
    <?php foreach ($tags as $tag): ?>
        <li><?php echo $tag['value'] ?> <a href="?action=remove-tags&amp;entry_id=<?php echo $item['id']?>&amp;tag_id=<?php echo $tag['id']?>">✘</a></li>
    <?php endforeach; ?>
    </ul>

    <form method="post" action="?action=edit-tags">
        <?php echo Helper\form_label(t('New tags'), 'value') ?>
        <?php echo Helper\form_text('value', array(), array(), array('required')) ?>
        <?php echo Helper\form_hidden('entry_id', array('entry_id' => $item['id'])) ?>
        <?php echo t('you can type several tags, separated by space'); ?>
        <div class="form-actions">
            <input type="submit" value="<?php echo t('Add tags') ?>" class="btn btn-blue"/>
        </div>
    </form>

</section>