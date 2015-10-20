<?php

namespace Wallabag\CoreBundle\Tools;

class Utils
{
    /**
     * Generate a token used for RSS.
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

        // remove character which can broken the url
        return str_replace(array('+', '/'), '', $token);
    }

    /**
     * @param $words
     * @return float
     */
    public static function convertWordsToMinutes($words)
    {
        return floor($words / 200);
    }

    /**
     * For a given text, we calculate reading time for an article
     * based on 200 words per minute.
     *
     * @param $text
     *
     * @return float
     */
    public static function getReadingTime($text)
    {
        return self::convertWordsToMinutes(str_word_count(strip_tags($text)));
    }
}
