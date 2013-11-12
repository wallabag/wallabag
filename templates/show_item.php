<?php if (empty($item)): ?>

    <p class="alert alert-info"><?php echo t('Item not found') ?></p>

<?php else: ?>
    <article
        class="item"
        id="current-item"
        data-item-id="<?php echo $item['id'] ?>"
        data-item-status="<?php echo $item['status'] ?>"
        data-item-bookmark="<?php echo $item['bookmark'] ?>"
        data-item-page="<?php echo $menu ?>"
    >
        <h1>
            <a href="<?php echo $item['url'] ?>" rel="noreferrer" target="_blank" id="original-<?php echo $item['id'] ?>">
                <?php echo Helper\escape($item['title']) ?>
            </a>
        </h1>

        <p class="infos">
            <?php if ($item['bookmark']): ?>
                <a
                    id="bookmark-<?php echo $item['id'] ?>"
                    href="?action=bookmark&amp;value=0&amp;id=<?php echo $item['id'] ?>&amp;source=show&amp;menu=<?php echo $menu ?>"
                    title="<?php echo t('remove bookmark') ?>"
                    class="bookmark-icon"
                    data-action="bookmark"
                    data-item-id="<?php echo $item['id'] ?>"
                >★</a>
            <?php else: ?>
                <a
                    id="bookmark-<?php echo $item['id'] ?>"
                    href="?action=bookmark&amp;value=1&amp;id=<?php echo $item['id'] ?>&amp;source=show&amp;menu=<?php echo $menu ?>"
                    title="<?php echo t('bookmark') ?>"
                    class="bookmark-icon"
                    data-action="bookmark"
                    data-item-id="<?php echo $item['id'] ?>"
                >☆</a>
            <?php endif ?> |
            <?php if ($item['status'] == 'unread'): ?>
                <a
                    id="status-<?php echo $item['id'] ?>"
                    href="?action=mark-item-read&amp;id=<?php echo $item['id'] ?>&amp;offset=<?php echo $offset ?>&amp;redirect=<?php echo $redirect ?>"
                    data-action="mark-read"
                    data-item-id="<?php echo $item['id'] ?>"
                    data-reverse-label="<?php echo t('mark as unread') ?>"
                >
                    <?php echo t('mark as read') ?></a> |
            <?php else: ?>
                <a
                    id="status-<?php echo $item['id'] ?>"
                    href="?action=mark-item-unread&amp;id=<?php echo $item['id'] ?>&amp;offset=<?php echo $offset ?>&amp;redirect=<?php echo $redirect ?>"
                    data-action="mark-unread"
                    data-item-id="<?php echo $item['id'] ?>"
                    data-reverse-label="<?php echo t('mark as read') ?>"
                >
                    <?php echo t('mark as unread') ?></a> |<?php endif ?>

            <a
                href="<?php echo $item['url'] ?>"
                id="original-<?php echo $item['id'] ?>"
                rel="noreferrer"
                target="_blank"
                data-item-id="<?php echo $item['id'] ?>"
            >
                <?php echo Helper\get_host_from_url($item['url']) ?></a> |

            <span class="hide-mobile"><?php echo dt('%e %b %Y %k:%M', $item['updated']) ?> |</span>

            <?php if (count($tags) > 0) : ?>
                tags
            <?php foreach ($tags as $tag) : ?>
                <a href="?action=tag&amp;id=<?php echo $tag['id'] ?>"><?php echo $tag['value'] ?></a>
            <?php endforeach; ?>
            <?php endif; ?>
            <a href="?action=edit-tags&amp;id=<?php echo $item['id'] ?>" title="<?php echo t('Edit tags'); ?>">✎</a>
        </p>

        <div id="item-content">
            <?php echo $item['content'] ?>
        </div>

        <?php if (isset($item_nav)): ?>
        <nav>
            <span class="nav-left">
                <?php if ($item_nav['previous']): ?>
                    <a href="?action=show&amp;menu=<?php echo $menu ?>&amp;id=<?php echo $item_nav['previous']['id'] ?>" id="previous-item" title="<?php echo t($item_nav['previous']['title']) ?>">« <?php echo t('Previous') ?></a>
                <?php else: ?>
                    « <?php echo t('Previous') ?>
                <?php endif ?>
            </span>

            <span class="nav-right">
                <?php if ($item_nav['next']): ?>
                    <a href="?action=show&amp;menu=<?php echo $menu ?>&amp;id=<?php echo $item_nav['next']['id'] ?>" id="next-item" title="<?php echo t($item_nav['next']['title']) ?>"><?php echo t('Next') ?> »</a>
                <?php else: ?>
                    <?php echo t('Next') ?> »
                <?php endif ?>
            </span>
        </nav>
        <?php endif ?>
    </article>
<?php endif ?>
