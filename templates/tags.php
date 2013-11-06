<?php if (empty($tags)): ?>
    <p class="alert alert-info"><?= t('No tag') ?></p>
<?php else: ?>

    <div class="page-header">
        <h2><?= t('Tags') ?> (<?= $nb_items ?>)</h2>
    </div>

    <section class="items" id="listing">
        <?php foreach ($tags as $tag): ?>
            <a href="?action=tag&amp;id=<?php echo $tag['id'] ?>"><?php echo $tag['value'] ?></a>
        <?php endforeach ?>
    </section>

<?php endif ?>