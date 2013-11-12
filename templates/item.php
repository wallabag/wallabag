<article
    id="item-<?php echo $item['id'] ?>"
    data-item-id="<?php echo $item['id'] ?>"
    data-item-status="<?php echo $item['status'] ?>"
    data-item-bookmark="<?php echo $item['bookmark'] ?>"
    data-item-page="<?php echo $menu ?>"
    <?php echo $hide ? 'data-hide="true"' : '' ?>
    >
    <h2>
        <?php echo $item['bookmark'] ? '<span id="bookmark-icon-'.$item['id'].'">★ </span>' : '' ?>
        <?php echo $item['status'] === 'read' ? '<span id="read-icon-'.$item['id'].'">☑ </span>' : '' ?>
        <a
            href="?action=show&amp;menu=<?php echo $menu ?>&amp;id=<?php echo $item['id'] ?>"
            data-item-id="<?php echo $item['id'] ?>"
            id="show-<?php echo $item['id'] ?>"
            <?php echo $item['status'] === 'read' ? 'class="read"' : '' ?>
        >
            <?php echo Helper\escape($item['title']) ?>
        </a>
    </h2>
    <p class="preview">
        <?php echo Helper\escape(Helper\summary(strip_tags($item['content']), 50, 300)) ?>
    </p>
    <p>
        <?php echo Helper\get_host_from_url($item['url']) ?> |

        <span class="hide-mobile">
            <?php echo dt('%e %b %Y %k:%M', $item['updated']) ?> |
            <?php echo \PicoTools\Template\load('bookmark_items', array('item' => $item, 'menu' => $menu, 'offset' => $offset, 'source' => '')) ?>
        </span>

        <?php echo \PicoTools\Template\load('status_items', array('item' => $item, 'redirect' => $menu, 'offset' => $offset)) ?>

        <a
            href="<?php echo $item['url'] ?>"
            id="original-<?php echo $item['id'] ?>"
            rel="noreferrer"
            target="_blank"
            data-item-id="<?php echo $item['id'] ?>"
        >
            <?php echo t('original link') ?>
        </a> |
        ⌚ <?php echo \Helper\reading_time($item['content']) . ' ' . t('min.')?>
    </p>
</article>