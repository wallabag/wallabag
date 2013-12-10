<?php
// Require login for admin access
// Author: Keyvan Minoukadeh
// Copyright (c) 2012 Keyvan Minoukadeh
// License: AGPLv3
// Date: 2012-08-30
// More info: http://fivefilters.org/content-only/
// Help: http://help.fivefilters.org

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Usage
// -----
// This file is included on pages which require admin privileges - e.g. updating the software.
// The username is 'admin' by default and the password should be set in the custom_config.php file.
session_start();
require_once(dirname(dirname(__FILE__)).'/config.php');

if (isset($_GET['logout'])) $_SESSION['auth'] = 0;

if (!isset($_SESSION['auth']) || $_SESSION['auth'] != 1) {
	if (isset($admin_page)) {
		header('Location: login.php?redirect='.$admin_page);
	} else {
		header('Location: login.php');
	}
	exit;
}

/* HTTP DIGEST authentication - doesn't work without server tweaks in FastCGI environments

$realm = 'Restricted area';

//user => password
$users = array($options->admin_credentials['username'] => $options->admin_credentials['password']);

if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Digest realm="'.$realm.
           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');

    die('If you can\'t remember your admin credentials, open your custom_config.php and you\'ll find them in there.');
}


// analyze the PHP_AUTH_DIGEST variable
if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
    !isset($users[$data['username']]))
    die('Wrong credentials!');


// generate the valid response
$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

if ($data['response'] != $valid_response)
    die('Wrong credentials!');

// ok, valid username & password
// echo 'Thanks! You are now logged in.';
unset($realm, $users, $data, $A1, $A2, $valid_response);

// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));

    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }

    return $needed_parts ? false : $data;
}
*/
?>