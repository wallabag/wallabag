<?php

namespace PicoFarad\Session;

const SESSION_LIFETIME = 2678400;


function open($base_path = '/')
{
    session_set_cookie_params(
        SESSION_LIFETIME,
        $base_path,
        null,
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        true
    );

    session_start();
}


function close()
{
    session_destroy();
}


function flash($message)
{
    $_SESSION['flash_message'] = $message;
}


function flash_error($message)
{
    $_SESSION['flash_error_message'] = $message;
}