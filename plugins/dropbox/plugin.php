<?php

namespace Dropbox;

require_once "lib/Dropbox/autoload.php";

use \PicoFarad\Router;
use \PicoFarad\Response;
use \PicoFarad\Request;
use \PicoFarad\Session;
use \PicoTools\Template;
use \Model;
use \Dropbox as dbx;

function enable() {
    if (!Model\get_plugin_option('dropbox_enabled')) {
        Model\add_plugin_option('dropbox_enabled', '1');
    }
}

function disable() {
    Model\remove_plugin_option('dropbox_enabled', '1');
}

function get_description() {
    return 'backup your poche datas in your Dropbox account.';
}


Router\get_action('dropbox', function() {

    if (!Model\get_plugin_option('dropbox_enabled')) {
        Session\flash_error(t('This plugin is disabled.'));
        Response\redirect('?action=config');
    }

    $appInfo = dbx\AppInfo::loadFromJsonFile(PLUGIN_DIRECTORY . "/dropbox/app.json");
    $webAuth = new dbx\WebAuthNoRedirect($appInfo, "PHP-Example/1.0");
    $authorizeUrl = $webAuth->start();

    Response\html(Template\layout('dropbox/templates/dropbox', array(
        'authorizeUrl' => $authorizeUrl,
        'title' => t('Export database to Dropbox')
    )));
});

Router\get_action('dropbox-import', function() {

    if (!Model\get_plugin_option('dropbox_enabled')) {
        Session\flash_error(t('This plugin is disabled.'));
        Response\redirect('?action=config');
    }

    $appInfo = dbx\AppInfo::loadFromJsonFile(PLUGIN_DIRECTORY . "/dropbox/app.json");
    $webAuth = new dbx\WebAuthNoRedirect($appInfo, "PHP-Example/1.0");
    $authCode = 'TpZ12_jq0ugAAAAAAAAAAfgSjftmPWLH4y7_PRpOaR8';

    list($accessToken, $dropboxUserId) = $webAuth->finish($authCode);

    $dbxClient = new dbx\Client($accessToken, "PHP-Example/1.0");
    $accountInfo = $dbxClient->getAccountInfo();
    $f = fopen(DB_FILENAME, "rb");
    $result = $dbxClient->uploadFile('/' . DB_FILENAME, dbx\WriteMode::add(), $f);
    fclose($f);

    Response\html(Template\layout('dropbox/templates/dropbox-import', array(
        'title' => t('Export database to Dropbox')
    )));
});