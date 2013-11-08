<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo t('Help') ?></title>
        <link href="<?php echo Helper\css() ?>" rel="stylesheet" media="screen">
        <script type="text/javascript" src="assets/js/popup.js?version=<?php echo filemtime('assets/js/popup.js') ?>" defer></script>
    </head>
    <body id="help-page">
        <section class="page">
            <div class="page-header">
                <h2><?php echo t('Help') ?></h2>
            </div>
            <section>
                <?php echo \PicoTools\Template\load('keyboard_shortcuts') ?>
            </section>
        </section>
    </body>
</html>