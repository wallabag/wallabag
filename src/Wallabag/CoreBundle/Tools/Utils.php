<?php

namespace Wallabag\CoreBundle\Tools;

class Utils
{
    public const DEFAULT_WORDS_PER_MINUTE = 200;

    private const WORDS_PATTERN = '~([^\p{L}\p{N}\']+|(\p{Han}|\p{Hiragana}|\p{Katakana}|\p{Hangul}){1,2})~u';

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
     * @return float
     */
    public static function getReadingTime($text)
    {
        $text = strip_tags($text);

        $words = preg_split(self::WORDS_PATTERN, $text);

        if (false === $words) {
            $words = preg_split(self::WORDS_PATTERN, mb_convert_encoding($text, 'UTF-8', 'UTF-8'));
        }

        if (false === $words) {
            return 0.0;
        }

        return floor(\count($words) / self::DEFAULT_WORDS_PER_MINUTE);
    }
}
