<!DOCTYPE html>
<!--[if lte IE 6]><html class="no-js ie6 ie67 ie678" lang="{{ lang }}"><![endif]-->
<!--[if lte IE 7]><html class="no-js ie7 ie67 ie678" lang="{{ lang }}"><![endif]-->
<!--[if IE 8]><html class="no-js ie8 ie678" lang="{{ lang }}"><![endif]-->
<!--[if gt IE 8]><html class="no-js" lang="{{ lang }}"><![endif]-->
<html>
    <head>
        <meta name="viewport" content="initial-scale=1.0">
        <meta charset="utf-8">
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=10">
        <![endif]-->
        <link rel="icon" type="image/png" href="assets/img/favicon.ico">
        <link rel="shortcut icon" href="favicon.ico">
        <title><?= isset($title) ? Helper\escape($title) : 'poche' ?></title>
        <link href="<?= Helper\css() ?>" rel="stylesheet" media="screen">
        <script type="text/javascript" src="?action=js" defer></script>
    </head>
    <body>
        <header>
            <nav>
                <ul>
                    <li <?= isset($menu) && $menu === 'unread' ? 'class="active"' : '' ?>>
                        <a href="?action=unread"><?= t('unread') ?> <span id="nav-counter"><?= isset($nb_unread_items) ? '('.$nb_unread_items.')' : '' ?></span></a>
                    </li>
                    <li <?= isset($menu) && $menu === 'bookmarks' ? 'class="active"' : '' ?>>
                        <a href="?action=bookmarks"><?= t('bookmarks') ?></a>
                    </li>
                    <li <?= isset($menu) && $menu === 'history' ? 'class="active"' : '' ?>>
                        <a href="?action=history"><?= t('archive') ?></a>
                    </li>
                    <li <?= isset($menu) && $menu === 'tags' ? 'class="active"' : '' ?>>
                        <a href="?action=tags"><?= t('tags') ?></a>
                    </li>
                    <li <?= isset($menu) && $menu === 'add' ? 'class="active"' : '' ?>>
                        <a href="?action=add"><?= t('add') ?></a>
                    </li>
                    <li <?= isset($menu) && $menu === 'config' ? 'class="active"' : '' ?>>
                        <a href="?action=config"><?= t('preferences') ?></a>
                    </li>
                    <li>
                        <a href="?action=logout"><?= t('logout') ?></a>
                    </li>
                </ul>
            </nav>
        </header>
        <section class="page">
            <?= Helper\flash('<div class="alert alert-success">%s</div>') ?>
            <?= Helper\flash_error('<div class="alert alert-error">%s</div>') ?>
            <?= $content_for_layout ?>
        </section>
    </body>
</html>