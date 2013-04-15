<?php

/**
 * Permet de générer l'URL de poche pour le bookmarklet
 */
function get_poche_url()
{
    $protocol = "http";
    if(isset($_SERVER['HTTPS'])) {
        if($_SERVER['HTTPS'] != "off") {
            $protocol = "https";
        }
    }

    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// function define to retrieve url content
function get_external_file($url, $timeout)
{
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
        $context = stream_context_create(array('http'=>array('timeout' => $timeout, // Timeout : time until we stop waiting for the response.
                                                                                    'header'=> "User-Agent: ".$useragent, // spoot Mozilla Firefox
                                                                                    'follow_location' => true
                                                        )));

        // only download page lesser than 4MB
        $data = @file_get_contents($url, false, $context, -1, 4000000); // We download at most 4 MB from source.
        //  echo "<pre>http_response_header : ".print_r($http_response_header);

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
    $url    = html_entity_decode(trim($url));

    // We remove the annoying parameters added by FeedBurner and GoogleFeedProxy (?utm_source=...)
    // from shaarli, by sebsauvage
    $i=strpos($url,'&utm_source='); if ($i!==false) $url=substr($url,0,$i);
    $i=strpos($url,'?utm_source='); if ($i!==false) $url=substr($url,0,$i);
    $i=strpos($url,'#xtor=RSS-'); if ($i!==false) $url=substr($url,0,$i);

    $title  = $url;
    if (!preg_match('!^https?://!i', $url))
        $url = 'http://' . $url;

    $html = Encoding::toUTF8(get_external_file($url,15));
    if (isset($html) and strlen($html) > 0)
    {
        $r = new Readability($html, $url);
        if($r->init())
        {
            $title = $r->articleTitle->innerHTML;
        }
    }

    $parametres['title']    = $title;
    $parametres['content']  = $r->articleContent->innerHTML;

    return $parametres;
}

/**
 * Appel d'une action (mark as fav, archive, delete)
 */
function action_to_do($action, $id, $url, $token)
{
    global $db;

    switch ($action)
    {
        case 'add':
            if ($url == '')
                continue;

            $parametres_url = prepare_url($url);
            $sql_action     = 'INSERT INTO entries ( url, title, content ) VALUES (?, ?, ?)';
            $params_action  = array($url, $parametres_url['title'], $parametres_url['content']);
            break;
        case 'delete':
            if (verif_token($token)) {
                $sql_action     = "DELETE FROM entries WHERE id=?";
                $params_action  = array($id);
            }
            else die('CSRF problem');
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
        }
    }
    catch (Exception $e)
    {
        die('action query error : '.$e->getMessage());
    }
}

/**
 * Détermine quels liens afficher : home, fav ou archives
 */
function display_view($view)
{
    global $db;

    switch ($view)
    {
        case 'archive':
            $sql    = "SELECT * FROM entries WHERE is_read=? ORDER BY id desc";
            $params = array(-1);
            break;
        case 'fav' :
            $sql    = "SELECT * FROM entries WHERE is_fav=? ORDER BY id desc";
            $params = array(-1);
            break;
        default:
            $sql    = "SELECT * FROM entries WHERE is_read=? ORDER BY id desc";
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
        die('view query error : '.$e->getMessage());
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
        die('query error : '.$e->getMessage());
    }

    return $entry;
}

/**
 * Vérifie si le jeton passé en $_POST correspond à celui en session
 */
function verif_token($token)
{
    if(isset($_SESSION['token_poche']) && isset($_SESSION['token_time_poche']) && isset($token))
    {
        if($_SESSION['token_poche'] == $token)
        {
            $old_timestamp = time() - (15*60);
            if($_SESSION['token_time_poche'] >= $old_timestamp)
            {
                return TRUE;
            }
            else return FALSE;
        }
        else return FALSE;
    }
    else return FALSE;
}