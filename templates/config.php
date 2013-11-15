<div class="page-header">
    <h2><?php echo t('Preferences') ?></h2>
</div>
<section>
    <?php if (DEMO_MODE): ?><div class="alert alert-error">In demo mode, you can't change the password.</div><?php endif;?>
<form method="post" action="?action=config" autocomplete="off">

    <?php echo Helper\form_label(t('Username'), 'username') ?>
    <?php echo Helper\form_text('username', $values, $errors, array('required', 'readonly')) ?><br/>

    <?php echo Helper\form_label(t('Password'), 'password') ?>
    <?php echo Helper\form_password('password', $values, $errors, (!DEMO_MODE?array():array('disabled'))) ?><br/>

    <?php echo Helper\form_label(t('Confirmation'), 'confirmation') ?>
    <?php echo Helper\form_password('confirmation', $values, $errors, (!DEMO_MODE?array():array('disabled'))) ?><br/>

    <?php echo Helper\form_label(t('Language'), 'language') ?>
    <?php echo Helper\form_select('language', $languages, $values, $errors) ?><br/>

    <?php echo Helper\form_label(t('Items per page'), 'items_per_page') ?>
    <?php echo Helper\form_select('items_per_page', $paging_options, $values, $errors) ?><br/>

    <?php echo Helper\form_label(t('Default sorting order for items'), 'items_sorting_direction') ?>
    <?php echo Helper\form_select('items_sorting_direction', $sorting_options, $values, $errors) ?><br/>

    <?php echo Helper\form_label(t('Theme'), 'theme') ?>
    <?php echo Helper\form_select('theme', $theme_options, $values, $errors) ?><br/>

    <ul>
        <li>
            <?php if ($values['auth_google_token']): ?>
                <?php echo t('Your Google Account is linked to poche') ?>, <a href="?action=unlink-account-provider&amp;type=google"><?php echo t('remove') ?></a>
            <?php else: ?>
                <a href="?action=google-redirect-link"><?php echo t('Link poche to my Google account') ?></a>
            <?php endif ?>
        </li>
        <li>
            <?php if ($values['auth_mozilla_token']): ?>
                <?php echo t('Your Mozilla Persona Account is linked to poche') ?>, <a href="?action=unlink-account-provider&amp;type=mozilla"><?php echo t('remove') ?></a>
            <?php else: ?>
                <a href="#" data-action="mozilla-link"><?php echo t('Link poche to my Mozilla Persona account') ?></a>
            <?php endif ?>
        </li>
    </ul>

    <div class="form-actions">
        <input type="submit" value="<?php echo t('Save') ?>" class="btn btn-blue"/>
    </div>
</form>
</section>

<div class="page-section">
    <h2><?php echo t('Plugins') ?></h2>
</div>
<section>
    <div class="alert alert-normal">
        <h3 id="enabled-plugins"><?php echo t('Enabled plugins') ?></h3>
        <?php echo Plugin::buildMenu('enabled') ?>
    </div>
    <div class="alert alert-normal">
        <h3 id="disabled-plugins"><?php echo t('Disabled plugins') ?></h3>
        <?php echo Plugin::buildMenu('disabled') ?>
    </div>
</section>

<div class="page-section">
    <h2><?php echo t('More informations') ?></h2>
</div>
<section>
    <div class="alert alert-normal">
        <h3 id="api"><?php echo t('API') ?></h3>
        <ul>
            <li>
                <?php echo t('Bookmarklet:') ?>
                <a href="javascript:location.href='<?php echo Helper\get_current_base_url() ?>?action=insert&amp;url='+encodeURIComponent(location.href)"><?php echo t('poche it!') ?></a> (<?php echo t('Drag and drop this link to your bookmarks') ?>)
            <li>
                <?php echo t('Unread RSS Feed:') ?>
                <a href="<?php echo Helper\get_current_base_url().'feed.php?id='.$values['id'].'&amp;token='.urlencode($values['feed_token']).'&amp;status=unread' ?>" target="_blank"><?php echo Helper\get_current_base_url().'feed.php?id='.$values['id'].'&amp;token='.urlencode($values['feed_token']).'&amp;status=unread' ?></a>
            </li>
            <li>
                <?php echo t('Bookmarks RSS Feed:') ?>
                <a href="<?php echo Helper\get_current_base_url().'feed.php?id='.$values['id'].'&amp;token='.urlencode($values['feed_token']).'&amp;status=bookmarks' ?>" target="_blank"><?php echo Helper\get_current_base_url().'feed.php?id='.$values['id'].'&amp;token='.urlencode($values['feed_token']).'&amp;status=bookmarks' ?></a>
            </li>
            <li><?php echo t('API endpoint:') ?> <strong><?php echo Helper\get_current_base_url().'jsonrpc.php' ?></strong></li>
            <li><?php echo t('API global username:') ?> <strong><?php echo Helper\escape(API_USER) ?></strong></li>
            <li><?php echo t('API global token:') ?> <strong><?php echo Helper\escape(Model\get_config_value('api_token')) ?></strong></li>
            <li><?php echo t('Your API username:') ?> <strong><?php echo Helper\escape($values['username']) ?></strong></li>
            <li><?php echo t('Your API token:') ?> <strong><?php echo Helper\escape($values['api_token']) ?></strong></li>
            <li><a href="?action=generate-tokens"><?php echo t('Generate new tokens') ?></a></li>
        </ul>
    </div>
    <div class="alert alert-normal">
        <h3><?php echo t('Database') ?></h3>
        <ul>
            <li><?php echo t('Database size:') ?> <strong><?php echo Helper\format_bytes($db_size) ?></strong></li>
            <li><a href="?action=optimize-db"><?php echo t('Optimize the database') ?></a> <?php echo t('(VACUUM command)') ?></li>
            <li><a href="?action=download-db"><?php echo t('Download the entire database') ?></a> <?php echo t('(Gzip compressed Sqlite file)') ?></li>
        </ul>
    </div>
    <?php echo \PicoTools\Template\load('keyboard_shortcuts') ?>
    <div class="alert alert-normal">
        <h3><?php echo t('About') ?></h3>
        <ul>
            <li><?php echo t('poche version:') ?> <strong><?php echo APP_VERSION ?></strong></li>
            <li><?php echo t('Official website:') ?> <a href="http://inthepoche.com" target="_blank">http://inthepoche.com</a></li>
            <li><a href="?action=console"><?php echo t('Console') ?></a></li>
        </ul>
    </div>
</section>

<script type="text/javascript" src="assets/js/persona.js" async></script>