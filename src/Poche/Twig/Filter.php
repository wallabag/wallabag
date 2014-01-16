<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

namespace Poche\Twig;

class Filter
{
    public function getDomain($url)
    {
        return parse_url($url, PHP_URL_HOST);
    }

    public function getReadingTime($text) 
    {
        $word = str_word_count(strip_tags($text));
        $minutes = floor($word / 200);
        $seconds = floor($word % 200 / (200 / 60));
        $time = array('minutes' => $minutes, 'seconds' => $seconds);

        return $minutes;
    }

    public function getPicture($text) 
    {
        $output = preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/Ui', $text, $result);

        return $output ? $result[1] : '';
    }
}