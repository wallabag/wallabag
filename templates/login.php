<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="./assets/img/favicon.ico">
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="apple-touch-icon" href="./assets/img/touch-icon-iphone.png">
        <link rel="apple-touch-icon" sizes="72x72" href="./assets/img/touch-icon-ipad.png">
        <link rel="apple-touch-icon" sizes="114x114" href="./assets/img/touch-icon-iphone-retina.png">
        <link rel="apple-touch-icon" sizes="144x144" href="./assets/img/touch-icon-ipad-retina.png">
        <title>poche</title>
        <link href="<?= Helper\css() ?>" rel="stylesheet" media="screen">
        <script type="text/javascript" src="?action=js" defer></script>
        <script type="text/javascript" src="assets/js/persona.js" defer></script>
    </head>
    <body id="login-page">
        <section class="page">
            <div class="page-header">
                <h2><?= t('Sign in') ?></h2>
            </div>
            <section>

            <?php if (isset($errors['login'])): ?>
                <p class="alert alert-error"><?= Helper\escape($errors['login']) ?></p>
            <?php endif ?>

            <form method="post" action="?action=login">

                <?= Helper\form_label(t('Username'), 'username') ?>
                <?= Helper\form_text('username', $values, $errors, array('autofocus', 'required')) ?><br/>

                <?= Helper\form_label(t('Password'), 'password') ?>
                <?= Helper\form_password('password', $values, $errors, array('required')) ?>

                <p><br/><a href="?action=google-redirect-auth"><?= t('Login with my Google Account') ?></a></p>
                <p><br/><a href="#" data-action="mozilla-login"><?= t('Login with my Mozilla Persona Account') ?></a></p>

                <div class="form-actions">
                    <input type="submit" value="<?= t('Sign in') ?>" class="btn btn-blue"/>
                </div>
            </form>
            </section>
        </section>
         <footer>
            <p><?= t('powered by <a href="http://inthepoche.com">poche</a>') ?></p>
         </footer>
    </body>
</html>