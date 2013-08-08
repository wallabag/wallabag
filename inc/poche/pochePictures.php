<?php
/**
 * poche, a read it later open source system
 *
 * @category   poche
 * @author     Nicolas Lœuillet <support@inthepoche.com>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

/**
 * On modifie les URLS des images dans le corps de l'article
 */
function filtre_picture($content, $url, $id)
{
    $matches = array();
    preg_match_all('#<\s*(img)[^>]+src="([^"]*)"[^>]*>#Si', $content, $matches, PREG_SET_ORDER);
    foreach($matches as $i => $link) {
        $link[1] = trim($link[1]);
        if (!preg_match('#^(([a-z]+://)|(\#))#', $link[1])) {
            $absolute_path = get_absolute_link($link[2],$url);
            $filename = basename(parse_url($absolute_path, PHP_URL_PATH));
            $directory = create_assets_directory($id);
            $fullpath = $directory . '/' . $filename;
            download_pictures($absolute_path, $fullpath);
            $content = str_replace($matches[$i][2], $fullpath, $content);
        }

    }

    return $content;
}

/**
 * Retourne le lien absolu
 */
function get_absolute_link($relative_link, $url) {
    /* return if already absolute URL */
    if (parse_url($relative_link, PHP_URL_SCHEME) != '') return $relative_link;

    /* queries and anchors */
    if ($relative_link[0]=='#' || $relative_link[0]=='?') return $url . $relative_link;

    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($url));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($relative_link[0] == '/') $path = '';

    /* dirty absolute URL */
    $abs = $host . $path . '/' . $relative_link;

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
}

/**
 * Téléchargement des images
 */
function download_pictures($absolute_path, $fullpath)
{
    $rawdata = Tools::getFile($absolute_path);

    if(file_exists($fullpath)) {
        unlink($fullpath);
    }
    $fp = fopen($fullpath, 'x');
    fwrite($fp, $rawdata);
    fclose($fp);
}

/**
 * Crée un répertoire de médias pour l'article
 */
function create_assets_directory($id)
{
    $assets_path = ABS_PATH;
    if(!is_dir($assets_path)) {
        mkdir($assets_path, 0705);
    }

    $article_directory = $assets_path . $id;
    if(!is_dir($article_directory)) {
        mkdir($article_directory, 0705);
    }

    return $article_directory;
}

/**
 * Suppression du répertoire d'images
 */
function remove_directory($directory)
{
    if(is_dir($directory)) {
        $files = array_diff(scandir($directory), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$directory/$file")) ? remove_directory("$directory/$file") : unlink("$directory/$file");
        }
        return rmdir($directory);
    }
}