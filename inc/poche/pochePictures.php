<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://www.wtfpl.net/ see COPYING file
 */

/**
 * On modifie les URLS des images dans le corps de l'article
 */
function filtre_picture($content, $url, $id)
{
    $matches = array();
    $processing_pictures = array(); // list of processing image to avoid processing the same pictures twice
    preg_match_all('#<\s*(img)[^>]+src="([^"]*)"[^>]*>#Si', $content, $matches, PREG_SET_ORDER);
    foreach($matches as $i => $link) {
        $link[1] = trim($link[1]);
        if (!preg_match('#^(([a-z]+://)|(\#))#', $link[1])) {
            $absolute_path = get_absolute_link($link[2],$url);
            $filename = basename(parse_url($absolute_path, PHP_URL_PATH));
            $directory = create_assets_directory($id);
            $fullpath = $directory . '/' . $filename;
            
            if (in_array($absolute_path, $processing_pictures) === true) {
                // replace picture's URL only if processing is OK : already processing -> go to next picture
                continue;
            }
            
            if (download_pictures($absolute_path, $fullpath) === true) {
                $content = str_replace($matches[$i][2], $fullpath, $content);
            }
            
            $processing_pictures[] = $absolute_path;
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
 * 
 * @return bool true if the download and processing is OK, false else
 */
function download_pictures($absolute_path, $fullpath)
{
    $rawdata = Tools::getFile($absolute_path);
    $fullpath = urldecode($fullpath);

    if(file_exists($fullpath)) {
        unlink($fullpath);
    }
    
    // check extension
    $file_ext = strrchr($fullpath, '.');
    $whitelist = array(".jpg",".jpeg",".gif",".png"); 
    if (!(in_array($file_ext, $whitelist))) {
        Tools::logm('processed image with not allowed extension. Skipping ' . $fullpath);
        return false;
    }
    
    // check headers
    $imageinfo = getimagesize($absolute_path);
    if ($imageinfo['mime'] != 'image/gif' && $imageinfo['mime'] != 'image/jpeg'&& $imageinfo['mime'] != 'image/jpg'&& $imageinfo['mime'] != 'image/png') {
        Tools::logm('processed image with bad header. Skipping ' . $fullpath);
        return false;
    }
    
    // regenerate image
    $im = imagecreatefromstring($rawdata);
    if ($im === false) {
        Tools::logm('error while regenerating image ' . $fullpath);
        return false;
    }
    
    switch ($imageinfo['mime']) {
        case 'image/gif':
            $result = imagegif($im, $fullpath);
            break;
        case 'image/jpeg':
        case 'image/jpg':
            $result = imagejpeg($im, $fullpath, REGENERATE_PICTURES_QUALITY);
            break;
        case 'image/png':
            $result = imagepng($im, $fullpath, ceil(REGENERATE_PICTURES_QUALITY / 100 * 9));
            break;
    }
    imagedestroy($im);
    
    return $result;
}

/**
 * Crée un répertoire de médias pour l'article
 */
function create_assets_directory($id)
{
    $assets_path = ABS_PATH;
    if(!is_dir($assets_path)) {
        mkdir($assets_path, 0715);
    }

    $article_directory = $assets_path . $id;
    if(!is_dir($article_directory)) {
        mkdir($article_directory, 0715);
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
