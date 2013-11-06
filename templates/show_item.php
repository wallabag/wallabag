<?php if (empty($item)): ?>

    <p class="alert alert-info"><?= t('Item not found') ?></p>

<?php else: ?>
    <article
        class="item"
        id="current-item"
        data-item-id="<?= $item['id'] ?>"
        data-item-status="<?= $item['status'] ?>"
        data-item-bookmark="<?= $item['bookmark'] ?>"
        data-item-page="<?= $menu ?>"
    >
        <h1>
            <a href="<?= $item['url'] ?>" rel="noreferrer" target="_blank" id="original-<?= $item['id'] ?>">
                <?= Helper\escape($item['title']) ?>
            </a>
        </h1>

        <p class="infos">
            <?php if ($item['bookmark']): ?>
                <a
                    id="bookmark-<?=$item['id'] ?>"
                    href="?action=bookmark&amp;value=0&amp;id=<?= $item['id'] ?>&amp;source=show&amp;menu=<?= $menu ?>"
                    title="<?= t('remove bookmark') ?>"
                    class="bookmark-icon"
                    data-action="bookmark"
                    data-item-id="<?= $item['id'] ?>"
                >★</a>
            <?php else: ?>
                <a
                    id="bookmark-<?=$item['id'] ?>"
                    href="?action=bookmark&amp;value=1&amp;id=<?= $item['id'] ?>&amp;source=show&amp;menu=<?= $menu ?>"
                    title="<?= t('bookmark') ?>"
                    class="bookmark-icon"
                    data-action="bookmark"
                    data-item-id="<?= $item['id'] ?>"
                >☆</a>
            <?php endif ?> |

            <span class="hide-mobile"><?= dt('%e %B %Y %k:%M', $item['updated']) ?> |</span>

            <?php if (count($tags) > 0) : ?>
                tags
            <?php foreach ($tags as $tag) : ?>
                <a href="?action=tag&amp;id=<?= $tag['id'] ?>"><?php echo $tag['value'] ?></a>
            <?php endforeach; ?>
            <?php endif; ?>
            <a href="?action=edit-tags&amp;id=<?= $item['id'] ?>" title="<?= t('Edit tags'); ?>">✎</a>
        </p>

        <div id="item-content">
            <?= $item['content'] ?>
        </div>

        <?php if (isset($item_nav)): ?>
        <nav>
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['previous']['id'] ?>" id="previous-item" title="<?= t($item_nav['previous']['title']) ?>">« <?= t('Previous') ?></a>
                <?php else: ?>
                    « <?= t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item_nav['next']['id'] ?>" id="next-item" title="<?= t($item_nav['next']['title']) ?>"><?= t('Next') ?> »</a>
                <?php else: ?>
                    <?= t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>
<?php endif ?>
