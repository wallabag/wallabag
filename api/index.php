<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag plug-in
 * @author     Winter Faulk <winter@faulk.me>
 * @copyright  2014
 * @license    http://www.wtfpl.net/ see COPYING file
 */

require_once( '../inc/poche/global.inc.php' );
require_once( 'users.config.php' );

$api_ver = "1.1";

$db = new Database();

if( isset( $_REQUEST['r'] ) && ( isset( $_REQUEST['apikey'] ) && strlen( $_REQUEST['apikey'] ) > 0 ) )
{
    $valid = FALSE;
    $apikey = $_REQUEST['apikey'];
    foreach( $config as $c )
    {
        if( $c['key'] == $apikey )
        {
            $uid = $c['uid'];
            $valid = TRUE;
        }
    }
    if( isset( $_REQUEST['o'] ) )
    {
        $opt = $_REQUEST['o'];
    }

    if( $valid === TRUE )
    {
        switch( $_REQUEST['r'] )
        {
            // GET API Calls
            case "get":
                switch( $opt )
                {
                    case "all": // Get all but archived
                        echo json_encode( array_reverse( $db->getEntriesByView( "", $uid ) ) );
                        break;
                    case "fav": // Get only favorites
                        echo json_encode( array_reverse( $db->getEntriesByView( "fav", $uid ) ) );
                        break;
                    case "archive": // Get only archived
                        echo json_encode( array_reverse( $db->getEntriesByView( "archive", $uid ) ) );
                        break;
                }
                break;
            // CHANGE API Calls
            case "change":
                $id = $_REQUEST['id'];
                switch( $opt )
                {
                    case "fav": // Mark as favorite
                        $db->favoriteById( $id, $uid );
                        echo '{"error":0}';
                        break;
                    case "archive": // Archive it
                        $db->archiveById( $id, $uid );
                        echo '{"error":0}';
                        break;
                }
                break;
            // DELETE API Call
            case "delete":
                $id = $_REQUEST['id'];
                $db->deleteById( $id, $uid );
                echo '{"error":0}';
                break;
            // ADD API Call
            case "add":
                $de_url = urldecode($_REQUEST['url']);
                $url = new Url( base64_encode( $de_url ) );
                $content = getPageContent( $url );
                $description = $content['rss']['channel']['item']['description'];
                $title = $content['rss']['channel']['title'];
                if( strlen( $title ) == 0 )
                {
                    $title = $de_url;
                }
                $id = $db->add( $url->getUrl(), $title, $description, $uid );
                echo '{"error":0, "id":' . $id . '}';
                break;
        }
    }
}
elseif( isset($_REQUEST['o']) )
{
    if( $_REQUEST['o'] == 'check' )
    {
        echo '{"api": true, "apiVersion": "' . $api_ver . '"}';
    }
}

function getPageContent(Url $url)
{
    // Saving and clearing context
    $REAL = array();
    foreach( $GLOBALS as $key => $value ) {
        if( $key != 'GLOBALS' && $key != '_SESSION' && $key != 'HTTP_SESSION_VARS' ) {
            $GLOBALS[$key] = array();
            $REAL[$key] = $value;
        }
    }
    // Saving and clearing session
    if ( isset($_SESSION) ) {
        $REAL_SESSION = array();
        foreach( $_SESSION as $key => $value ) {
            $REAL_SESSION[$key] = $value;
            unset($_SESSION[$key]);
        }
    }

    // Running code in different context
    $scope = function() {
        extract( func_get_arg(1) );
        $_GET = $_REQUEST = array(
                    "url" => $url->getUrl(),
                    "max" => 5,
                    "links" => "preserve",
                    "exc" => "",
                    "format" => "json",
                    "submit" => "Create Feed"
        );
        ob_start();
        require func_get_arg(0);
        $json = ob_get_contents();
        ob_end_clean();
        return $json;
    };
    $json = $scope( "../inc/3rdparty/makefulltextfeed.php", array("url" => $url) );

    // Clearing and restoring context
    foreach( $GLOBALS as $key => $value ) {
        if( $key != "GLOBALS" && $key != "_SESSION" ) {
            unset($GLOBALS[$key]);
        }
    }
    foreach( $REAL as $key => $value ) {
        $GLOBALS[$key] = $value;
    }
    // Clearing and restoring session
    if ( isset($REAL_SESSION) ) {
        foreach( $_SESSION as $key => $value ) {
            unset($_SESSION[$key]);
        }
        foreach( $REAL_SESSION as $key => $value ) {
            $_SESSION[$key] = $value;
        }
    }

    return json_decode($json, true);
}
?>