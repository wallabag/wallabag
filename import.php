<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

set_time_limit(0);

include dirname(__FILE__).'/inc/config.php';
include dirname(__FILE__).'/inc/simple_html_dom.php';

if (!isset($_GET['start'])) {
    echo _('Please execute the import script locally, it can take a very long time.') . '<br /><br />' . _('Please choose between Pocket & Readabilty :') . '<br /><a href="import.php?start=pocket">' . _('Bye bye Pocket, let\'s go !') . '</a><br /><a href="import.php?start=readability">' . _('Bye bye Readability, let\'s go !') . '</a>';
}
else {
    if ($_GET['start'] == 'pocket') {
        $html = new simple_html_dom();
        $html->load_file('ril_export.html');

        $read = 0;
        $errors = array();
        foreach($html->find('ul') as $ul)
        {
            foreach($ul->find('li') as $li)
            {
                $a = $li->find('a');
                $url = $a[0]->href;

                action_to_do('add', $url);
                if ($read == '1') {
                    $last_id = $db->getHandle()->lastInsertId();
                    $sql_update     = "UPDATE entries SET is_read=~is_read WHERE id=?";
                    $params_update  = array($last_id);
                    $query_update   = $db->getHandle()->prepare($sql_update);
                    $query_update->execute($params_update);
                }
            }
            # Pocket génère un fichier HTML avec deux <ul>
            # Le premier concerne les éléments non lus
            # Le second concerne les éléments archivés
            $read = 1;
        }

        echo _('Import from Pocket completed.') . '<a href="index.php">' . _('Welcome to poche !') .'</a>';
        logm('import from pocket completed');
    }
    else if ($_GET['start'] == 'readability') {
        $str_data = file_get_contents("readability");
        $data = json_decode($str_data,true);

        foreach ($data as $key => $value) {
            $url = '';
            foreach ($value as $key2 => $value2) {
                if ($key2 == 'article__url') {
                    $url = $value2;
                }
            }
            if ($url != '')
                action_to_do('add', $url);
        }

        echo _('Import from Readability completed.') . '<a href="index.php">' . _('Welcome to poche !') . '</a>';
        logm('import from Readability completed');
    }
    else {
        echo _('Error with the import.') . '<a href="index.php">' . _('Back to poche'). '</a>';
        logm('error with the import');
    }
}