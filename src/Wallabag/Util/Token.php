<?php

namespace Wallabag\Util;

class Token
{
    /**
     * Generates a new token
     *
     * @return string a token generated with /dev/urandom or mt_rand()
     */
    public static function generateToken()
    {
        if (ini_get('open_basedir') === '') {
            return substr(base64_encode(file_get_contents('/dev/urandom', false, null, 0, 20)), 0, 15);
        }
        else {
            return substr(base64_encode(uniqid(mt_rand(), true)), 0, 15);
        }
    }

}
