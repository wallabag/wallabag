<?php

namespace Wallabag\Tools;

class Utils
{
    /**
     * Generate a token used for Feeds.
     *
     * @param int $length Length of the token
     *
     * @return string
     */
    public static function generateToken($length = 15)
    {
        $token = substr(base64_encode(random_bytes($length)), 0, $length);

        // remove character which can broken the url
        return str_replace(['+', '/'], '', $token);
    }

    /**
     * For a given text, we calculate reading time for an article based on 200 words per minute.
     *
     * @param string $text
     *
     * @return int
     */
    public static function getReadingTime($text)
    {
        return (int) floor(\count(preg_split('~([^\p{L}\p{N}\']+|(\p{Han}|\p{Hiragana}|\p{Katakana}|\p{Hangul}){1,2})~u', strip_tags($text))) / 200);
    }
}
