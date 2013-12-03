<?php

namespace Import;

require 'lib/model.php';
require 'lib/helper.php';
require 'lib/simple_html_dom.php';

use \PicoFarad\Router;
use \PicoFarad\Response;
use \PicoFarad\Request;
use \PicoFarad\Session;
use \PicoTools\Template;
use \Model;

define ('IMPORT_POCKET_FILE', './data/ril_export.html');
define ('IMPORT_READABILITY_FILE', './data/readability');
define ('IMPORT_INSTAPAPER_FILE', './data/instapaper-export.csv');
define ('IMPORT_POCHE_FILE', './data/poche.json');

function enable() {
    if (!Model\get_plugin_option('import_enabled')) {
        Model\add_plugin_option('import_enabled', '1');
    }
}

function disable() {
    Model\remove_plugin_option('import_enabled', '1');
}

function get_description() {
    return 'import in your poche the datas from third applications.';
}

Router\get_action('import', function() {

    if (!Model\get_plugin_option('import_enabled')) {
        Session\flash_error(t('This plugin is disabled.'));
        Response\redirect('?action=config');
    }

    $values = array(
            'pocket' => 'Pocket',
            'instapaper' => 'Instapaper',
            'readability' => 'Readability',
            'poche' => 'poche v1'
            );

    Response\html(Template\layout('import/templates/import', array(
        'errors' => array(),
        'values' => $values,
        'title' => t('Import')
    )));

});

Router\post_action('import', function() {

    if (!Model\get_plugin_option('import_enabled')) {
        Session\flash_error(t('This plugin is disabled.'));
        Response\redirect('?action=config');
    }

    $values = Request\values();
    list($valid, $errors) = validate_import($values);

    if ($valid) {

        if (executeImport($values)) {
            Session\flash(t('Import finished, execute the cron to fetch any missing content.'));
        }

        Response\redirect('?action=import');
    }

    Response\html(Template\layout('import/templates/import', array(
        'errors' => $errors,
        'values' => $values,
        'title' => t('Import')
    )));
});
