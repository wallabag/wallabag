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
    echo 'Please execute the import script locally, it can take a very long time. <br /><a href="import.php?start">Bye bye Pocket, let\'s go !</a>';
}
else {
    $html = new simple_html_dom();
    $html->load_file('ril_export.html');

    $read = '0';
    $errors = array();
    foreach($html->find('ul') as $ul)
    {
        foreach($ul->find('li') as $li)
        {
            $a = $li->find('a');
            $url = $a[0]->href;

            # FIXME corriger cette génération d'ID
            $req = $db->getHandle()->query("SELECT id FROM entries ORDER BY id DESC");
            $id = $req->fetchColumn()+1;

            $parametres_url = prepare_url($url, $id);
            $sql_action     = 'INSERT INTO entries ( url, title, content, is_read ) VALUES (?, ?, ?, ?)';
            $params_action  = array($url, $parametres_url['title'], $parametres_url['content'], $read);
            try
            {
                # action query
                if (isset($sql_action))
                {
                    $query = $db->getHandle()->prepare($sql_action);
                    $query->execute($params_action);
                }
            }
            catch (Exception $e)
            {
                logm('error during pocket import : ' . $e->getMessage());
            }
        }
        # Pocket génère un fichier HTML avec deux <ul>
        # Le premier concerne les éléments non lus
        # Le second concerne les éléments archivés
        $read = '-1';
    }

    echo 'Import from Pocket completed. <a href="index.php">Welcome to #poche !</a>';
    logm('import from pocket completed');
}