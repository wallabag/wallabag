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
        <title><?php echo isset($title) ? Helper\escape($title) : 'poche' ?></title>
        <link href="<?php echo Helper\css() ?>" rel="stylesheet" media="screen">
        <script type="text/javascript" src="?action=js" defer></script>
    </head>
    <body>
        <header>
            <nav>
                <ul>
                    <li <?php echo isset($menu) && $menu === 'unread' ? 'class="active"' : '' ?>>
                        <a href="?action=unread"><?php echo t('unread') ?> <span id="nav-counter"><?php echo isset($nb_unread_items) ? '('.$nb_unread_items.')' : '' ?></span></a>
                    </li>
                    <li <?php echo isset($menu) && $menu === 'bookmarks' ? 'class="active"' : '' ?>>
                        <a href="?action=bookmarks"><?php echo t('bookmarks') ?></a>
                    </li>
                    <li <?php echo isset($menu) && $menu === 'history' ? 'class="active"' : '' ?>>
                        <a href="?action=history"><?php echo t('archive') ?></a>
                    </li>
                    <li <?php echo isset($menu) && $menu === 'tags' ? 'class="active"' : '' ?>>
                        <a href="?action=tags"><?php echo t('tags') ?></a>
                    </li>
                    <li <?php echo isset($menu) && $menu === 'add' ? 'class="active"' : '' ?>>
                        <a href="?action=add"><?php echo t('add') ?></a>
                    </li>
                    <li <?php echo isset($menu) && $menu === 'config' ? 'class="active"' : '' ?>>
                        <a href="?action=config"><?php echo t('preferences') ?></a>
                    </li>
                    <li>
                        <a href="?action=logout"><?php echo t('logout') ?></a>
                    </li>
                </ul>
            </nav>
        </header>
        <section class="page">
            <?php echo Helper\flash('<div class="alert alert-success">%s</div>') ?>
            <?php echo Helper\flash_error('<div class="alert alert-error">%s</div>') ?>
            <?php echo $content_for_layout ?>
        </section>
    </body>
</html>