<?php

namespace AuthProvider;

function google_get_url($realm, $return_path)
{
    $return_to = $realm.$return_path;
    $url = 'https://accounts.google.com/o/openid2/auth?';
    $params = array();
    $params['openid.ns'] = 'http://specs.openid.net/auth/2.0';
    $params['openid.mode'] = 'checkid_setup';
    $params['openid.return_to'] = $return_to;
    $params['openid.realm'] = $realm;
    $params['openid.identity'] = 'http://specs.openid.net/auth/2.0/identifier_select';
    $params['openid.claimed_id'] = 'http://specs.openid.net/auth/2.0/identifier_select';

    return $url.http_build_query($params, '', '&');
}

function google_validate()
{
    $identity = '';

    if (! ini_get('allow_url_fopen')) {
        die('You must have "allow_url_fopen=On" to use this feature!');
    }

    if (! isset($_GET['openid_mode']) || $_GET['openid_mode'] !== 'id_res') {
        return array(false, $identity);
    }

    $params = array();
    $params['openid.ns'] = 'http://specs.openid.net/auth/2.0';
    $params['openid.mode'] = 'check_authentication';
    $params['openid.assoc_handle'] = $_GET['openid_assoc_handle'];
    $params['openid.signed'] = $_GET['openid_signed'];
    $params['openid.sig'] = $_GET['openid_sig'];

    foreach (explode(',', $_GET['openid_signed']) as $item) {
        $params['openid.'.$item] = $_GET['openid_' . str_replace('.', '_', $item)];
    }

    $context = stream_context_create(array(
        'http'=>array(
        'method'=> 'POST',
        'header'=> implode("\r\n", array(
            'Content-type: application/x-www-form-urlencoded',
            'Accept: application/xrds+xml, */*'
        )),
        'content' => http_build_query($params, '', '&')
    )));

    $response = file_get_contents('https://www.google.com/accounts/o8/ud', false, $context);
    $identity = $_GET['openid_identity'];

    return array(strpos($response, 'is_valid:true') !== false, $identity);
}


function mozilla_validate($token)
{
    if (! ini_get('allow_url_fopen')) {
        die('You must have "allow_url_fopen=On" to use this feature!');
    }

    $params = array(
        'assertion' => $token,
        'audience' => (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']
    );

    $context = stream_context_create(array(
        'http'=> array(
        'method'=> 'POST',
        'header'=> implode("\r\n", array(
            'Content-type: application/x-www-form-urlencoded',
        )),
        'content' => http_build_query($params, '', '&')
    )));

    $body = @file_get_contents('https://verifier.login.persona.org/verify', false, $context);
    $response = json_decode($body, true);

    if (! $response) {
        return array(
            false,
            ''
        );
    }

    return array(
        $response['status'] === 'okay',
        $response['email']
    );
}
