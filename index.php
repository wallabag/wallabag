<?php
require 'common.php';
require 'vendor/PicoTools/Template.php';
require 'vendor/PicoTools/Helper.php';
require 'vendor/PicoFarad/Response.php';
require 'vendor/PicoFarad/Request.php';
require 'vendor/PicoFarad/Session.php';
require 'vendor/PicoFarad/Router.php';
require 'helpers.php';
require 'plugin.php';

use PicoFarad\Router;
use PicoFarad\Response;
use PicoFarad\Request;
use PicoFarad\Session;
use PicoTools\Template;

if (SESSION_SAVE_PATH !== '') session_save_path(SESSION_SAVE_PATH);
Session\open(dirname($_SERVER['PHP_SELF']));
Plugin::loadPlugins();

// Called before each action
Router\before(function($action) {

    $ignore_actions = array('js', 'login', 'google-auth', 'google-redirect-auth', 'mozilla-auth');

    if (! isset($_SESSION['user']) && ! in_array($action, $ignore_actions)) {
        Response\redirect('?action=login');
    }

    // Load translations
    $language = isset($_SESSION['user']['language']) ?$_SESSION['user']['language']: 'en_US';
    if ($language !== 'en_US') PicoTools\Translator\load($language);

    // HTTP secure headers
    $frame_src = \PicoFeed\Filter::$iframe_whitelist;
    $frame_src[] = 'https://login.persona.org';

    Response\csp(array(
        'media-src' => '*',
        'img-src' => '*',
        'frame-src' => $frame_src
    ));

    Response\xframe();
    Response\xss();
    Response\nosniff();
});


// Javascript assets
Router\get_action('js', function() {

    $data = file_get_contents('assets/js/app.js');
    $data .= file_get_contents('assets/js/item.js');
    $data .= file_get_contents('assets/js/event.js');
    $data .= file_get_contents('assets/js/nav.js');
    $data .= file_get_contents('assets/js/functions.js');
    $data .= 'poche.App.Run();';

    Response\js($data);
});


// Logout and destroy session
Router\get_action('logout', function() {

    Session\close();
    Response\redirect('?action=login');
});


// Display form login
Router\get_action('login', function() {

    if (isset($_SESSION['user'])) Response\redirect('?action=unread');

    Response\html(Template\load('login', array(
        'errors' => array(),
        'values' => array()
    )));
});


// Check credentials and redirect to unread items
Router\post_action('login', function() {

    $values = Request\values();
    list($valid, $errors) = Model\validate_login($values);

    if ($valid) Response\redirect('?action=unread');

    Response\html(Template\load('login', array(
        'errors' => $errors,
        'values' => $values
    )));
});


// Show help
Router\get_action('show-help', function() {

    Response\html(Template\load('show_help'));
});


// Show item
Router\get_action('show', function() {

    $id = Request\param('id');
    $menu = Request\param('menu');
    $item = Model\get_item($id, Model\get_user_id());
    $tags = Model\get_tags_by_item($id);

    switch ($menu) {
        case 'unread':
            $nav = Model\get_nav_item($item, Model\get_user_by_id(Model\get_user_id()));
            $nb_unread_items = Model\count_entries('unread', Model\get_user_id());
            break;
        case 'history':
            $nav = Model\get_nav_item($item, Model\get_user_by_id(Model\get_user_id()), array('read'));
            break;
        case 'bookmarks':
            $nav = Model\get_nav_item($item, Model\get_user_by_id(Model\get_user_id()), array('unread', 'read'), array(1));
            break;
    }

    Response\html(Template\layout('show_item', array(
        'nb_unread_items' => isset($nb_unread_items) ? $nb_unread_items : null,
        'item' => $item,
        'tags' => $tags,
        'item_nav' => isset($nav) ? $nav : null,
        'menu' => $menu,
        'title' => $item['title']
    )));
});


// Mark item as read and redirect to the listing page
Router\get_action('mark-item-read', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'unread');
    $offset = Request\int_param('offset', 0);

    Model\set_item_read($id, Model\get_user_id());

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'#item-'.$id);
});


// Mark item as unread and redirect to the listing page
Router\get_action('mark-item-unread', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);

    Model\set_item_unread($id, Model\get_user_id());

    Response\Redirect('?action='.$redirect.'&offset='.$offset.'#item-'.$id);
});


// Mark item as removed and redirect to the listing page
Router\get_action('mark-item-removed', function() {

    $id = Request\param('id');
    $redirect = Request\param('redirect', 'history');
    $offset = Request\int_param('offset', 0);

    Model\set_item_removed($id, Model\get_user_id());

    Response\Redirect('?action='.$redirect.'&offset='.$offset);
});


// Ajax call to mark item read
Router\post_action('mark-item-read', function() {

    $id = Request\param('id');
    Model\set_item_read($id, Model\get_user_id());
    Response\json(array('Ok'));
});


// Ajax call to mark item unread
Router\post_action('mark-item-unread', function() {

    $id = Request\param('id');
    Model\set_item_unread($id, Model\get_user_id());
    Response\json(array('Ok'));
});


// Ajax call change item status
Router\post_action('change-item-status', function() {

    $id = Request\param('id', Model\get_user_id());

    Response\json(array(
        'item_id' => $id,
        'status' => Model\switch_item_status($id, Model\get_user_id())
    ));
});


// Ajax call to add or remove a bookmark
Router\post_action('bookmark', function() {

    $id = Request\param('id');
    $value = Request\int_param('value');

    Model\set_bookmark_value($id, $value, Model\get_user_id());

    Response\json(array('id' => $id, 'value' => $value));
});


// Add new bookmark
Router\get_action('bookmark', function() {

    $id = Request\param('id');
    $menu = Request\param('menu', 'unread');
    $source = Request\param('source', 'unread');
    $offset = Request\int_param('offset', 0);

    Model\set_bookmark_value($id, Request\int_param('value'), Model\get_user_id());

    if ($source === 'show') {
        Response\Redirect('?action=show&menu='.$menu.'&id='.$id);
    }

    Response\Redirect('?action='.$menu.'&offset='.$offset.'#item-'.$id);
});


// Display search form
Router\get_action('search', function() {

    Response\html(Template\layout('search', array(

    )));
});


// Display search form
Router\post_action('search', function() {

    $values = Request\values();

    Response\html(Template\layout('search', array(
        'items' => Model\search_items($values['query'])
    )));
});


// Display history page
Router\get_action('history', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\count_entries('read', Model\get_user_id());

    Response\html(Template\layout('history', array(
        'items' => Model\get_items(
            'read',
            Model\get_user_id(),
            $offset,
            $_SESSION['user']['items_per_page'],
            'updated',
            $_SESSION['user']['items_sorting_direction']
        ),
        'order' => '',
        'direction' => '',
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => $_SESSION['user']['items_per_page'],
        'menu' => 'history',
        'title' => t('History').' ('.$nb_items.')'
    )));
});


// Display tags page
Router\get_action('tags', function() {

    $tags = Model\get_tags();
    $nb_items = count($tags);

    Response\html(Template\layout('tags', array(
        'tags' => $tags,
        'menu' => 'tags',
        'title' => t('Tags') .' ('.$nb_items.')',
        'nb_items' => $nb_items
    )));
});


// Display entries of a tag
Router\get_action('tag', function() {

    $offset = Request\int_param('offset', 0);
    $id = Request\int_param('id', 0);
    $tag = Model\get_tag($id);
    $items = Model\get_entries_by_tag($id, Model\get_user_id());
    $nb_items = count($items);

    Response\html(Template\layout('tag', array(
        'tag' => $tag,
        'items' => $items,
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => $_SESSION['user']['items_per_page'],
        'menu' => 'tags',
        'feed_token' => $_SESSION['user']['feed_token'],
        'title' => t('Tag').' ' . $tag['value'] .' ('.$nb_items.')'
    )));
});


// Edit tags of an entry
Router\get_action('remove-tags', function() {

    $entry_id = Request\int_param('entry_id', 0);
    $tag_id = Request\int_param('tag_id', 0);
    Model\remove_tag($tag_id, $entry_id);

    Response\redirect('?action=edit-tags&id=' . $entry_id);
});


// Update tags for an entry
Router\post_action('edit-tags', function() {

    $values = Request\values();
    $menu = Request\param('menu', 'unread');
    list($valid, $errors) = Model\validate_tags($values);

    if ($valid) {

        if (Model\save_tags($values)) {
            Session\flash(t('Tags are updated.'));
        }
        else {
            Session\flash_error(t('Unable to update tags.'));
        }  
    }
    Response\redirect('?action=show&menu=' . $menu . '&id=' . $values['entry_id']);
});

// Edit tags of an entry
Router\get_action('edit-tags', function() {

    $id = Request\int_param('id', 0);
    $item = Model\get_item($id, Model\get_user_id());
    $tags = Model\get_tags_by_item($id);

    Response\html(Template\layout('edit-tags', array(
        'tags' => $tags,
        'item' => $item,
        'menu' => 'tags',
        'title' => t('Edit tags')
    )));
});


// Display bookmarks page
Router\get_action('bookmarks', function() {

    $offset = Request\int_param('offset', 0);
    $nb_items = Model\count_bookmarks(Model\get_user_id());

    Response\html(Template\layout('bookmarks', array(
        'order' => '',
        'direction' => '',
        'items' => Model\get_bookmarks(
            Model\get_user_id(),
            $offset,
            $_SESSION['user']['items_per_page'],
            $_SESSION['user']['items_sorting_direction']),
        'nb_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => $_SESSION['user']['items_per_page'],
        'menu' => 'bookmarks',
        'title' => t('Bookmarks').' ('.$nb_items.')'
    )));
});


// Mark all unread items as read
Router\get_action('mark-as-read', function() {

    Model\mark_as_read(Model\get_user_id());
    Response\redirect('?action=unread');
});


// Mark sent items id as read (Ajax request)
Router\post_action('mark-items-as-read', function(){

    Model\mark_items_as_read(Request\values(), Model\get_user_id());
    Response\json(array('OK'));
});


// Display all links
Router\get_action('links', function() {

    Response\html(Template\layout('no-items', array(
        'menu' => 'unread',
        'nothing_to_read' => Request\int_param('nothing_to_read')
    )));
});


// Display form to add one link
Router\get_action('add', function() {

    Response\html(Template\layout('add', array(
        'values' => array(),
        'errors' => array(),
        'menu' => 'add',
        'title' => t('New link')
    )));
});


// Add an entry with the form or directly from the url, it can be used by a bookmarklet by example
Router\action('insert', function() {

    if (Request\param('url')) {
        $values = array();
        $url = Request\param('url');
    }
    else {
        $values = Request\values();
        $url = isset($values['url']) ? $values['url'] : '';
    }

    $url = trim($url);
    $result = Model\add_link($url, Model\get_user_id());

    if ($result) {

        Session\flash(t('Link poched successfully.'));
        Response\redirect('?action=add');
    }
    else {

        Session\flash_error(t('Unable to poche this link.'));
    }

    Response\html(Template\layout('add', array(
        'values' => array('url' => $url),
        'menu' => 'links',
        'title' => t('Links')
    )));
});


// Re-generate tokens
Router\get_action('generate-tokens', function() {

    Model\new_tokens();
    Response\redirect('?action=config#api');
});


// Optimize the database manually
Router\get_action('optimize-db', function() {

    \PicoTools\singleton('db')->getConnection()->exec('VACUUM');
    Response\redirect('?action=config');
});


// Download the compressed database
Router\get_action('download-db', function() {

    Response\force_download('db.sqlite.gz');
    Response\binary(gzencode(file_get_contents(DB_FILENAME)));
});


// Flush console messages
Router\get_action('flush-console', function() {

    @unlink(DEBUG_FILENAME);
    Response\redirect('?action=console');
});


// Display console
Router\get_action('console', function() {

    Response\html(Template\layout('console', array(
        'content' => @file_get_contents(DEBUG_FILENAME),
        'title' => t('Console')
    )));
});


Router\get_action('enable-plugin', function() {

    $plugin = Request\param('plugin_name');
    $function = ucfirst($plugin) . "\\enable";
    $function();
    Plugin::addMenu('enabled', $plugin);
    Plugin::delMenu('disabled', $plugin);
    Session\flash(t('Plugin "'.$plugin.'" successfully enabled.'));
    Response\redirect('?action=config');
});


Router\get_action('disable-plugin', function() {

    $plugin = Request\param('plugin_name');
    $function = ucfirst($plugin) . "\\disable";
    $function();
    Plugin::addMenu('disabled', $plugin);
    Plugin::delMenu('enabled', $plugin);
    Session\flash(t('Plugin "'.$plugin.'" successfully disabled.'));
    Response\redirect('?action=config');
});


// Display preferences page
Router\get_action('config', function() {

    Response\html(Template\layout('config', array(
        'errors' => array(),
        'values' => $_SESSION['user'],
        'db_size' => filesize(DB_FILENAME),
        'languages' => Model\get_languages(),
        'paging_options' => Model\get_paging_options(),
        'theme_options' => Model\get_themes(),
        'sorting_options' => Model\get_sorting_directions(),
        'menu' => 'config',
        'title' => t('Preferences')
    )));
});


// Update preferences
Router\post_action('config', function() {

    $values = Request\values();
    list($valid, $errors) = Model\validate_config_update($values);

    if ($valid) {

        if (Model\save_config($values)) {
            Session\flash(t('Your preferences are updated.'));
        }
        else {
            Session\flash_error(t('Unable to update your preferences.'));
        }

        Response\redirect('?action=config');
    }

    Response\html(Template\layout('config', array(
        'errors' => $errors,
        'values' => $values,
        'db_size' => filesize(DB_FILENAME),
        'languages' => Model\get_languages(),
        'paging_options' => Model\get_paging_options(),
        'theme_options' => Model\get_themes(),
        'sorting_options' => Model\get_sorting_directions(),
        'menu' => 'config',
        'title' => t('Preferences')
    )));
});


// Link to a Google Account (redirect)
Router\get_action('google-redirect-link', function() {

    require 'vendor/PicoTools/AuthProvider.php';
    Response\Redirect(AuthProvider\google_get_url(Helper\get_current_base_url(), '?action=google-link'));
});


// Link to a Google Account (association)
Router\get_action('google-link', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\google_validate();

    if ($valid) {
        $_SESSION['user']['auth_google_token'] = $token;
        Model\save_auth_token('google', $token);
        Session\flash(t('Your Google Account is linked to poche.'));
    }
    else {
        Session\flash_error(t('Unable to link poche to your Google Account.'));
    }

    Response\redirect('?action=config');
});


// Authenticate with a Google Account (redirect)
Router\get_action('google-redirect-auth', function() {

    require 'vendor/PicoTools/AuthProvider.php';
    Response\Redirect(AuthProvider\google_get_url(Helper\get_current_base_url(), '?action=google-auth'));
});


// Authenticate with a Google Account (callback url)
Router\get_action('google-auth', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\google_validate();
    $user = Model\get_user_by_config('auth_google_token', $token);

    if ($valid && $user != null) {
        unset($user['password']);
        $_SESSION['user'] = $user;

        Response\redirect('?action=unread');
    }
    else {

        Response\html(Template\load('login', array(
            'errors' => array('login' => t('Unable to authenticate with Google. Maybe you have to activate it in poche preferences.')),
            'values' => array()
        )));
    }
});


// Authenticate with a Mozilla Persona (ajax check)
Router\post_action('mozilla-auth', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\mozilla_validate(Request\value('token'));
    $user = Model\get_user_by_config('auth_mozilla_token', $token);

    if ($valid && $user != null) {
        unset($user['password']);
        $_SESSION['user'] = $user;

        Response\text('?action=unread');
    }
    else {
        Response\text("?action=login");
    }
});


// Link poche to a Mozilla Account (ajax check)
Router\post_action('mozilla-link', function() {

    require 'vendor/PicoTools/AuthProvider.php';

    list($valid, $token) = AuthProvider\mozilla_validate(Request\value('token'));
    $user = Model\get_user_by_config('auth_mozilla_token', $token);

    if ($valid && $user != null) {
        $_SESSION['user']['auth_mozilla_token'] = $token;
        Model\save_auth_token('mozilla', $token);
        Session\flash(t('Your Mozilla Persona Account is linked to poche.'));
    }
    else {
        Session\flash_error(t('Unable to link poche to your Mozilla Persona Account. Maybe you have to activate it in poche preferences.'));
    }

    Response\text("?action=config");
});


// Remove account link
Router\get_action('unlink-account-provider', function() {
    Model\remove_auth_token(Request\param('type'));
    Response\redirect('?action=config');
});


// Display unread items
Router\notfound(function() {
    $order = Request\param('order', 'updated');
    $direction = Request\param('direction', $_SESSION['user']['items_sorting_direction']);
    $offset = Request\int_param('offset', 0);
    $items = Model\get_items('unread', Model\get_user_id(), $offset, $_SESSION['user']['items_per_page'], $order, $direction);
    $nb_items = Model\count_entries('unread', Model\get_user_id());

    if ($nb_items === 0) Response\redirect('?action=links&nothing_to_read=1');

    Response\html(Template\layout('unread_items', array(
        'order' => $order,
        'direction' => $direction,
        'items' => $items,
        'nb_items' => $nb_items,
        'nb_unread_items' => $nb_items,
        'offset' => $offset,
        'items_per_page' => $_SESSION['user']['items_per_page'],
        'title' => 'poche ('.$nb_items.')',
        'menu' => 'unread'
    )));
});
