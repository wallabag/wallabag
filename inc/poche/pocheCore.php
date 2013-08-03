<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

function encode_string($string) 
{
    return sha1($string . SALT);
}

function get_external_file($url)
{
    $timeout = 15;
    $useragent = "Mozilla/5.0 (Windows NT 5.1; rv:18.0) Gecko/20100101 Firefox/18.0";

    if  (in_array ('curl', get_loaded_extensions())) {
        # Fetch feed from URL
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        # for ssl, do not verified certificate
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE );

        # FeedBurner requires a proper USER-AGENT...
        curl_setopt($curl, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($curl, CURLOPT_ENCODING, "gzip, deflate");
        curl_setopt($curl, CURLOPT_USERAGENT, $useragent);

        $data = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $httpcodeOK = isset($httpcode) and ($httpcode == 200 or $httpcode == 301);
        curl_close($curl);
    } else {
        # create http context and add timeout and user-agent
        $context = stream_context_create(
            array(
                'http' => array(
                    'timeout' => $timeout,
                    'header' => "User-Agent: " . $useragent,
                    'follow_location' => true
                ),
                'ssl' => array(
                    'verify_peer' => false,
                    'allow_self_signed' => true
                )
            )
        );

        # only download page lesser than 4MB
        $data = @file_get_contents($url, false, $context, -1, 4000000); 

        if (isset($http_response_header) and isset($http_response_header[0])) {
            $httpcodeOK = isset($http_response_header) and isset($http_response_header[0]) and ((strpos($http_response_header[0], '200 OK') !== FALSE) or (strpos($http_response_header[0], '301 Moved Permanently') !== FALSE));
        }
    }

    # if response is not empty and response is OK
    if (isset($data) and isset($httpcodeOK) and $httpcodeOK) {

        # take charset of page and get it
        preg_match('#<meta .*charset=.*>#Usi', $data, $meta);

        # if meta tag is found
        if (!empty($meta[0])) {
            preg_match('#charset="?(.*)"#si', $meta[0], $encoding);
            # if charset is found set it otherwise, set it to utf-8
            $html_charset = (!empty($encoding[1])) ? strtolower($encoding[1]) : 'utf-8';
        } else {
            $html_charset = 'utf-8';
            $encoding[1] = '';
        }

        # replace charset of url to charset of page
        $data = str_replace('charset=' . $encoding[1], 'charset=' . $html_charset, $data);

        return $data;
    }
    else {
        return FALSE;
    }
}

function fetch_url_content($url)
{
    $url = base64_decode($url);
    if (pocheTools::isUrl($url)) {
        $url = pocheTools::cleanURL($url);
        $html = Encoding::toUTF8(get_external_file($url));

        # if get_external_file if not able to retrieve HTTPS content, try the same URL with HTTP protocol
        if (!preg_match('!^https?://!i', $url) && (!isset($html) || strlen($html) <= 0)) {
            $url = 'http://' . $url;
            $html = Encoding::toUTF8(get_external_file($url));
        }

        if (function_exists('tidy_parse_string')) {
            $tidy = tidy_parse_string($html, array(), 'UTF8');
            $tidy->cleanRepair();
            $html = $tidy->value;
        }

        $parameters = array();
        if (isset($html) and strlen($html) > 0)
        {
            $readability = new Readability($html, $url);
            $readability->convertLinksToFootnotes = CONVERT_LINKS_FOOTNOTES;
            $readability->revertForcedParagraphElements = REVERT_FORCED_PARAGRAPH_ELEMENTS;

            if($readability->init())
            {
                $content = $readability->articleContent->innerHTML;
                $parameters['title'] = $readability->articleTitle->innerHTML;
                $parameters['content'] = $content;

                return $parameters;
            }
        }
    }
    else {
        #$msg->add('e', _('error during url preparation : the link is not valid'));
        pocheTools::logm($url . ' is not a valid url');
    }

    return FALSE;
}

function get_tpl_file($view)
{
    $tpl_file = 'home.twig';
    switch ($view)
    {
        case 'install':
            $tpl_file = 'install.twig';
            break;
        case 'import';
            $tpl_file = 'import.twig';
            break;
        case 'export':
            $tpl_file = 'export.twig';
            break;
        case 'config':
            $tpl_file = 'config.twig';
            break;
        case 'view':
            $tpl_file = 'view.twig';
            break;
        default:
        break;
    }
    return $tpl_file;
}

function display_view($view, $id = 0)
{
    global $store;

    $tpl_vars = array();

    switch ($view)
    {
        case 'install':
            pocheTools::logm('install mode');
            break;
        case 'import';
            pocheTools::logm('import mode');
            break;
        case 'export':
            $entries = $store->retrieveAll();
            $tpl->assign('export', pocheTools::renderJson($entries));
            $tpl->draw('export');
            pocheTools::logm('export view');
            break;
        case 'config':
            pocheTools::logm('config view');
            break;
        case 'view':
            $entry = $store->retrieveOneById($id);
            if ($entry != NULL) {
                pocheTools::logm('view link #' . $id);
                $tpl->assign('id', $entry['id']);
                $tpl->assign('url', $entry['url']);
                $tpl->assign('title', $entry['title']);
                $content = $entry['content'];
                if (function_exists('tidy_parse_string')) {
                    $tidy = tidy_parse_string($content, array('indent'=>true, 'show-body-only' => true), 'UTF8');
                    $tidy->cleanRepair();
                    $content = $tidy->value;
                }
                $tpl->assign('content', $content);
                $tpl->assign('is_fav', $entry['is_fav']);
                $tpl->assign('is_read', $entry['is_read']);
                $tpl->assign('load_all_js', 0);
                $tpl->draw('view');
            }
            else {
                pocheTools::logm('error in view call : entry is NULL');
            }
            break;
        default: # home view
            $entries = $store->getEntriesByView($view);
            $tpl_vars = array(
                'entries' => $entries,
            );
            break;
    }

    return $tpl_vars;
}

/**
 * Call action (mark as fav, archive, delete, etc.)
 */
function action_to_do($action, $url, $id = 0)
{
    global $store;

    switch ($action)
    {
        case 'add':
            if($parametres_url = fetch_url_content($url)) {
                if ($store->add($url, $parametres_url['title'], $parametres_url['content'])) {
                    pocheTools::logm('add link ' . $url);
                    $last_id = $store->getLastId();
                    if (DOWNLOAD_PICTURES) {
                        $content = filtre_picture($parametres_url['content'], $url, $last_id);
                    }
                    #$msg->add('s', _('the link has been added successfully'));
                }
                else {
                    #$msg->add('e', _('error during insertion : the link wasn\'t added'));
                    pocheTools::logm('error during insertion : the link wasn\'t added');
                }
            }
            else {
                #$msg->add('e', _('error during url preparation : the link wasn\'t added'));
                pocheTools::logm('error during content fetch');
            }
            break;
        case 'delete':
            if ($store->deleteById($id)) {
                if (DOWNLOAD_PICTURES) {
                    remove_directory(ABS_PATH . $id);
                }
                #$msg->add('s', _('the link has been deleted successfully'));
                pocheTools::logm('delete link #' . $id);
            }
            else {
                #$msg->add('e', _('the link wasn\'t deleted'));
                pocheTools::logm('error : can\'t delete link #' . $id);
            }
            break;
        case 'toggle_fav' :
            $store->favoriteById($id);
            pocheTools::logm('mark as favorite link #' . $id);
            break;
        case 'toggle_archive' :
            $store->archiveById($id);
            pocheTools::logm('archive link #' . $id);
            break;
        default:
            break;
    }
}
