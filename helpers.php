<?php

namespace Helper;


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

function reading_time($text) {
    $word = str_word_count(strip_tags($text));
    $minutes = floor($word / 200);

    return $minutes;
}