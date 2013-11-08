<?php
/**
 * Helpers
 *
 * Little functions for poche 
 * @package poche
 * @subpackage helpers
 * @license    http://www.gnu.org/licenses/agpl-3.0.html  GNU Affero GPL
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 */
namespace Helper;


/**
 * Return the good css file. 
 * It depend on theme user preference. 
 *
 * @return string path of css file
 */
function css()
{
    $theme = isset($_SESSION['user']['theme']) ? $_SESSION['user']['theme'] : 'original';

    if ($theme !== 'original') {

        $css_file = THEME_DIRECTORY.'/'.$theme.'/css/app.css';

        if (file_exists($css_file)) {
            return $css_file.'?version='.filemtime($css_file);
        }
    }

    return 'assets/css/app.css?version='.filemtime('assets/css/app.css');
}


/**
 * Calculate the estimating reading of an article
 * 
 * @param  string $text content of an article
 * @return int number of minutes to read the article
 */
function reading_time($text) {
    $word = str_word_count(strip_tags($text));
    $minutes = floor($word / 200);

    return $minutes;
}