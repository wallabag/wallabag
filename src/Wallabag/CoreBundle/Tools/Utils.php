<?php

namespace Wallabag\CoreBundle\Tools;

class Utils
{
    /**
     * Generate a token used for RSS
     *
     * @return string
     */
    public static function generateToken()
    {
        if (ini_get('open_basedir') === '') {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // alternative to /dev/urandom for Windows
                $token = substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
            } else {
                $token = substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
            }
        } else {
            $token = substr(base64_encode(uniqid(mt_rand(), true)), 0, 20);
        }

        return str_replace('+', '', $token);
    }
}
