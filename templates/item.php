<article
    id="item-<?= $item['id'] ?>"
    data-item-id="<?= $item['id'] ?>"
    data-item-status="<?= $item['status'] ?>"
    data-item-bookmark="<?= $item['bookmark'] ?>"
    data-item-page="<?= $menu ?>"
    <?= $hide ? 'data-hide="true"' : '' ?>
    >
    <h2>
        <?= $item['bookmark'] ? '<span id="bookmark-icon-'.$item['id'].'">★ </span>' : '' ?>
        <?= $item['status'] === 'read' ? '<span id="read-icon-'.$item['id'].'">☑ </span>' : '' ?>
        <a
            href="?action=show&amp;menu=<?= $menu ?>&amp;id=<?= $item['id'] ?>"
            data-item-id="<?= $item['id'] ?>"
            id="show-<?= $item['id'] ?>"
            <?= $item['status'] === 'read' ? 'class="read"' : '' ?>
        >
            <?= Helper\escape($item['title']) ?>
        </a>
    </h2>
    <p class="preview">
        <?= Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
    </p>
    <p>
        <?= Helper\get_host_from_url($item['url']) ?> |

        <span class="hide-mobile">
            <?= dt('%e %B %Y %k:%M', $item['updated']) ?> |
            <?= \PicoTools\Template\load('bookmark_items', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'source' => '')) ?>
        </span>

        <?= \PicoTools\Template\load('status_items', array('item' => $item, 'redirect' => $menu, 'offset' => $offset)) ?>

        <a
            href="<?= $item['url'] ?>"
            id="original-<?= $item['id'] ?>"
            rel="noreferrer"
            target="_blank"
            data-item-id="<?= $item['id'] ?>"
        >
            <?= t('original link') ?>
        </a> |
        ⌚ <?= \Helper\reading_time($item['content']) . ' ' . t('minutes')?>
    </p>
</article>