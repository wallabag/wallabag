<?php

/**
 * Permet de générer l'URL de poche pour le bookmarklet
 */
function get_poche_url()
{
    $protocol = "http";
    if(isset($_SERVER['HTTPS'])) {
        if($_SERVER['HTTPS'] != "off" && $_SERVER['HTTPS'] != "") {
            $protocol = "https";
        }
    }

    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// function define to retrieve url content
function get_external_file($url)
{
    $timeout = 15;
    // spoofing FireFox 18.0
    $useragent="Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0";

    if  (in_array  ('curl', get_loaded_extensions())) {
        // Fetch feed from URL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        // FeedBurner requires a proper USER-AGENT...
        curl_setopt($curl, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($curl, CURLOPT_ENCODING, "gzip, deflate");
        curl_setopt($curl, CURLOPT_USERAGENT, $useragent);

        $data = curl_exec($curl);

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $httpcodeOK = isset($httpcode) and ($httpcode == 200 or $httpcode == 301);

        curl_close($curl);
    } else {

        // create http context and add timeout and user-agent
        $context = stream_context_create(array('http'=>array('timeout' => $timeout,'header'=> "User-Agent: ".$useragent,/*spoot Mozilla Firefox*/'follow_location' => true)));

        // only download page lesser than 4MB
        $data = @file_get_contents($url, false, $context, -1, 4000000); // We download at most 4 MB from source.

        if(isset($http_response_header) and isset($http_response_header[0])) {
            $httpcodeOK = isset($http_response_header) and isset($http_response_header[0]) and ((strpos($http_response_header[0], '200 OK') !== FALSE) or (strpos($http_response_header[0], '301 Moved Permanently') !== FALSE));
        }
    }

    // if response is not empty and response is OK
    if (isset($data) and isset($httpcodeOK) and $httpcodeOK ) {

        // take charset of page and get it
        preg_match('#<meta .*charset=.*>#Usi', $data, $meta);

        // if meta tag is found
        if (!empty($meta[0])) {
            // retrieve encoding in $enc
            preg_match('#charset="?(.*)"#si', $meta[0], $enc);

            // if charset is found set it otherwise, set it to utf-8
            $html_charset = (!empty($enc[1])) ? strtolower($enc[1]) : 'utf-8';

        } else {
            $html_charset = 'utf-8';
            $enc[1] = '';
        }

        // replace charset of url to charset of page
        $data = str_replace('charset='.$enc[1], 'charset='.$html_charset, $data);

        return $data;
    }
    else {
        return FALSE;
    }
}

/**
 * Préparation de l'URL avec récupération du contenu avant insertion en base
 */
function prepare_url($url)
{
    $parametres = array();
    $url        = html_entity_decode(trim($url));

    // We remove the annoying parameters added by FeedBurner and GoogleFeedProxy (?utm_source=...)
    // from shaarli, by sebsauvage
    $i=strpos($url,'&utm_source='); if ($i!==false) $url=substr($url,0,$i);
    $i=strpos($url,'?utm_source='); if ($i!==false) $url=substr($url,0,$i);
    $i=strpos($url,'#xtor=RSS-'); if ($i!==false) $url=substr($url,0,$i);

    $title = $url;
    if (!preg_match('!^https?://!i', $url))
        $url = 'http://' . $url;

    $html = Encoding::toUTF8(get_external_file($url,15));
    if (isset($html) and strlen($html) > 0)
    {
        $r = new Readability($html, $url);
        $r->convertLinksToFootnotes = CONVERT_LINKS_FOOTNOTES;
        if($r->init())
        {
            $content = $r->articleContent->innerHTML;
            $parametres['title'] = $r->articleTitle->innerHTML;
            $parametres['content'] = $content;
            return $parametres;
        }
    }

    logm('error during url preparation');
    return FALSE;
}

/**
 * On modifie les URLS des images dans le corps de l'article
 */
function filtre_picture($content, $url, $id)
{
    $matches = array();
    preg_match_all('#<\s*(img)[^>]+src="([^"]*)"[^>]*>#Si', $content, $matches, PREG_SET_ORDER);
    foreach($matches as $i => $link)
    {
        $link[1] = trim($link[1]);
        if (!preg_match('#^(([a-z]+://)|(\#))#', $link[1]) )
        {
            $absolute_path = get_absolute_link($link[2],$url);
            $filename = basename(parse_url($absolute_path, PHP_URL_PATH));
            $directory = create_assets_directory($id);
            $fullpath = $directory . '/' . $filename;
            download_pictures($absolute_path, $fullpath);
            $content = str_replace($matches[$i][2], $fullpath, $content);
        }

    }

    return $content;
}

/**
 * Retourne le lien absolu
 */
function get_absolute_link($relative_link, $url)
{
    /* return if already absolute URL */
    if (parse_url($relative_link, PHP_URL_SCHEME) != '') return $relative_link;

    /* queries and anchors */
    if ($relative_link[0]=='#' || $relative_link[0]=='?') return $url . $relative_link;

    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($url));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($relative_link[0] == '/') $path = '';

    /* dirty absolute URL */
    $abs = $host . $path . '/' . $relative_link;

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
}

/**
 * Téléchargement des images
 */

function download_pictures($absolute_path, $fullpath)
{
    $rawdata = get_external_file($absolute_path);

    if(file_exists($fullpath)) {
        unlink($fullpath);
    }
    $fp = fopen($fullpath, 'x');
    fwrite($fp, $rawdata);
    fclose($fp);
}

/**
 * Crée un répertoire de médias pour l'article
 */
function create_assets_directory($id)
{
    $assets_path = ABS_PATH;
    if(!is_dir($assets_path)) {
        mkdir($assets_path, 0705);
    }

    $article_directory = $assets_path . $id;
    if(!is_dir($article_directory)) {
        mkdir($article_directory, 0705);
    }

    return $article_directory;
}

/**
 * Suppression du répertoire d'images
 */
function remove_directory($directory)
{
    if(is_dir($directory)) {
        $files = array_diff(scandir($directory), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? remove_directory("$directory/$file") : unlink("$directory/$file");
        }
        return rmdir($directory);
    }
}

function display_view($view, $id = 0, $full_head = 'yes')
{
    global $tpl;

    switch ($view)
    {
        case 'view':
            $entry = get_article($id);

            if ($entry != NULL) {
                $tpl->assign('id', $entry[0]['id']);
                $tpl->assign('url', $entry[0]['url']);
                $tpl->assign('title', $entry[0]['title']);
                $tpl->assign('content', $entry[0]['content']);
                $tpl->assign('is_fav', $entry[0]['is_fav']);
                $tpl->assign('is_read', $entry[0]['is_read']);
                $tpl->assign('load_all_js', 0);
                $tpl->draw('view');
            }
            else {
                logm('error in view call : entry is NULL');
            }

            logm('view link #' . $id);
            break;
        default: # home view
            $entries = get_entries($view);

            $tpl->assign('entries', $entries);

            if ($full_head == 'yes') {
                $tpl->assign('load_all_js', 1);
                $tpl->draw('head');
                $tpl->draw('home');
            }

            $tpl->draw('entries');

            if ($full_head == 'yes') {
                $tpl->draw('js');
                $tpl->draw('footer');
            }
            break;
    }
}

/**
 * Appel d'une action (mark as fav, archive, delete)
 */
function action_to_do($action, $url, $id = 0)
{
    global $db;

    switch ($action)
    {
        case 'add':
            if ($url == '')
                continue;

            if($parametres_url = prepare_url($url)) {
                $sql_action     = 'INSERT INTO entries ( url, title, content ) VALUES (?, ?, ?)';
                $params_action  = array($url, $parametres_url['title'], $parametres_url['content']);
            }

            logm('add link ' . $url);
            break;
        case 'delete':
            remove_directory(ABS_PATH . $id);
            $sql_action     = "DELETE FROM entries WHERE id=?";
            $params_action  = array($id);
            logm('delete link #' . $id);
            break;
        case 'toggle_fav' :
            $sql_action     = "UPDATE entries SET is_fav=~is_fav WHERE id=?";
            $params_action  = array($id);
            logm('mark as favorite link #' . $id);
            break;
        case 'toggle_archive' :
            $sql_action     = "UPDATE entries SET is_read=~is_read WHERE id=?";
            $params_action  = array($id);
            logm('archive link #' . $id);
            break;
        default:
            break;
    }

    try
    {
        # action query
        if (isset($sql_action))
        {
            $query = $db->getHandle()->prepare($sql_action);
            $query->execute($params_action);
            # if we add a link, we have to download pictures
            if ($action == 'add') {
                $last_id = $db->getHandle()->lastInsertId();
                if (DOWNLOAD_PICTURES) {
                    $content        = filtre_picture($parametres_url['content'], $url, $last_id);
                    $sql_update     = "UPDATE entries SET content=? WHERE id=?";
                    $params_update  = array($content, $last_id);
                    $query_update   = $db->getHandle()->prepare($sql_update);
                    $query_update->execute($params_update);
                }
            }
        }
    }
    catch (Exception $e)
    {
        logm('action query error : '.$e->getMessage());
    }
}

/**
 * Détermine quels liens afficher : home, fav ou archives
 */
function get_entries($view)
{
    global $db;

    switch ($_SESSION['sort'])
    {
        case 'ia':
            $order = 'ORDER BY id';
            break;
        case 'id':
            $order = 'ORDER BY id DESC';
            break;
        case 'ta':
            $order = 'ORDER BY lower(title)';
            break;
        case 'td':
            $order = 'ORDER BY lower(title) DESC';
            break;
        default:
            $order = 'ORDER BY id';
            break;
    }

    switch ($view)
    {
        case 'archive':
            $sql    = "SELECT * FROM entries WHERE is_read=? " . $order;
            $params = array(-1);
            break;
        case 'fav' :
            $sql    = "SELECT * FROM entries WHERE is_fav=? " . $order;
            $params = array(-1);
            break;
        default:
            $sql    = "SELECT * FROM entries WHERE is_read=? " . $order;
            $params = array(0);
            break;
    }

    # view query
    try
    {
        $query  = $db->getHandle()->prepare($sql);
        $query->execute($params);
        $entries = $query->fetchAll();
    }
    catch (Exception $e)
    {
        logm('view query error : '.$e->getMessage());
    }

    return $entries;
}

/**
 * Récupère un article en fonction d'un ID
 */
function get_article($id)
{
    global $db;

    $entry  = NULL;
    $sql    = "SELECT * FROM entries WHERE id=?";
    $params = array(intval($id));

    # view article query
    try
    {
        $query  = $db->getHandle()->prepare($sql);
        $query->execute($params);
        $entry = $query->fetchAll();
    }
    catch (Exception $e)
    {
        logm('get article query error : '.$e->getMessage());
    }

    return $entry;
}

function logm($message)
{
    $t = strval(date('Y/m/d_H:i:s')).' - '.$_SERVER["REMOTE_ADDR"].' - '.strval($message)."\n";
    file_put_contents('./log.txt',$t,FILE_APPEND);
}