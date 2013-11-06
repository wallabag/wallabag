<?php

namespace Import;

use \PicoFarad\Session;
use \Model;


function importFromReadability($targetFile)
{
    $file_content = file_get_contents($targetFile);
    $data = json_decode($file_content, true);
    $count = 0;

    foreach ($data as $key => $value) {
        $url = null;
        $favorite = false;
        $archive = false;
        foreach ($value as $attr => $attr_value) {
            if ($attr == 'article__url') {
                $url = $attr_value;
            }
            if ($attr_value == 'true') {
                if ($attr == 'favorite') {
                    $favorite = true;
                }
                if ($attr == 'archive') {
                    $archive = true;
                }
            }
        }

        # we can add the url
        if (!is_null($url)) {
            $id = Model\add_link($url, $_SESSION['user']['id'], false);
            $count++;
            if ($favorite) {
                Model\set_bookmark_value(array($id), '1', $_SESSION['user']['id']);
            }
            if ($archive) {
                Model\mark_items_as_read(array($id), $_SESSION['user']['id']);
            }
        }
    }

    return true;
}


function importFromInstapaper($targetFile)
{
    $row = 1;
    if (($handle = fopen($targetFile, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            if ($row > 1) {
                $url = $data[0];
                $id = Model\add_link($url, $_SESSION['user']['id'], false);
                if ($data[3] == 'Archive') {
                    Model\mark_items_as_read(array($id), $_SESSION['user']['id']);
                }
            }
            $row++;
        }
        fclose($handle);
    }

    return true;
}

function importFromPocket($targetFile)
{
    $html = new simple_html_dom();
    $html->load_file($targetFile);

    $read = 0;
    $errors = array();
    foreach($html->find('ul') as $ul)
    {
        foreach($ul->find('li') as $li)
        {
            $a = $li->find('a');

            $url = trim($a[0]->href);
            $id = Model\add_link($url, $_SESSION['user']['id'], false);

            if ($read == '1') {
                Model\mark_items_as_read(array($id), $_SESSION['user']['id']);
            }
        }
        
        # the second <ul> is for read links
        $read = 1;
    }
    
    return true;
}


function importFromPoche($targetFile)
{
    $file_content = file_get_contents($targetFile);
    $data = json_decode($file_content, true);
    $count = 0;
    
    foreach ($data as $key => $value) {
        $url = null;
        $favorite = false;
        $archive = false;
        foreach ($value as $attr => $attr_value) {
            if ($attr == 'url') {
                $url = $attr_value;
            }
            $sequence = '';

            if ($attr_value == '1') {
                if ($attr == 'is_fav') {
                    $favorite = true;
                }
                if ($attr == 'is_read') {
                    $archive = true;
                }
            }
        }

        # we can add the url
        if (!is_null($url)) {
            $id = Model\add_link($url, $_SESSION['user']['id'], false);
            $count++;
            if ($favorite) {
                Model\set_bookmark_value(array($id), '1', $_SESSION['user']['id']);
            }
            if ($archive) {
                Model\mark_items_as_read(array($id), $_SESSION['user']['id']);
            }
        }
    }

    return true;
}


function executeImport($values)
{
    $provider = $values['application'];
    $function = 'Import\importFrom' . ucfirst($provider);

    $targetDefinition = 'IMPORT_' . strtoupper($provider) . '_FILE';
    $targetFile = constant($targetDefinition);

    if (! defined($targetDefinition)) {
        Session\flash_error(t('Please define "' . $targetDefinition . '" in import.php.'));
        return false;
    }
    
    if (! file_exists($targetFile)) {
        Session\flash_error(t('Could not find required "' . $targetFile . '" import file.'));
        return false;
    }

    if (! $function($targetFile)) {
        return false;
    }

    return true;
}