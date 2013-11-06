<?php

namespace PicoFarad\Response;


function force_download($filename)
{
    header('Content-Disposition: attachment; filename="'.$filename.'"');
}


function status($status_code)
{
    if (strpos(php_sapi_name(), 'apache') !== false) {

        header('HTTP/1.0 '.$status_code);
    }
    else {

        header('Status: '.$status_code);
    }
}


function redirect($url)
{
    header('Location: '.$url);
    exit;
}


function json(array $data, $status_code = 200)
{
    status($status_code);

    header('Content-Type: application/json');
    echo json_encode($data);

    exit;
}


function text($data, $status_code = 200)
{
    status($status_code);

    header('Content-Type: text/plain; charset=utf-8');
    echo $data;

    exit;
}


function html($data, $status_code = 200)
{
    status($status_code);

    header('Content-Type: text/html; charset=utf-8');
    echo $data;

    exit;
}


function xml($data, $status_code = 200)
{
    status($status_code);

    header('Content-Type: text/xml; charset=utf-8');
    echo $data;

    exit;
}


function js($data, $status_code = 200)
{
    status($status_code);

    header('Content-Type: text/javascript; charset=utf-8');
    echo $data;

    exit;
}


function binary($data, $status_code = 200)
{
    status($status_code);

    header('Content-Transfer-Encoding: binary');
    header('Content-Type: application/octet-stream');
    echo $data;

    exit;
}


function csp(array $policies = array())
{
    $policies['default-src'] = "'self'";
    $values = '';

    foreach ($policies as $policy => $hosts) {

        if (is_array($hosts)) {

            $acl = '';

            foreach ($hosts as &$host) {

                if ($host === '*' || $host === 'self' || strpos($host, 'http') === 0) {
                    $acl .= $host.' ';
                }
            }
        }
        else {

            $acl = $hosts;
        }

        $values .= $policy.' '.trim($acl).'; ';
    }

    header('Content-Security-Policy: '.$values);
}


function nosniff()
{
    header('X-Content-Type-Options: nosniff');
}


function xss()
{
    header('X-XSS-Protection: 1; mode=block');
}


function hsts()
{
    header('Strict-Transport-Security: max-age=31536000');
}


function xframe($mode = 'DENY', array $urls = array())
{
    header('X-Frame-Options: '.$mode.' '.implode(' ', $urls));
}