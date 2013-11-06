<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= t('Help') ?></title>
        <link href="<?= Helper\css() ?>" rel="stylesheet" media="screen">
        <script type="text/javascript" src="assets/js/popup.js?version=<?= filemtime('assets/js/popup.js') ?>" defer></script>
    </head>
    <body id="help-page">
        <section class="page">
            <div class="page-header">
                <h2><?= t('Help') ?></h2>
            </div>
            <section>
                <?= \PicoTools\Template\load('keyboard_shortcuts') ?>
            </section>
        </section>
    </body>
</html>