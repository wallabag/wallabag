<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

class YarnItem
{
    public $score;
    public $status = FALSE;

    public function checkItem($url, $id) {
        $this->_cacheYarnFile($url, $id);
        $yarnResponse = file_get_contents(CACHE . "/yarn/" . $id . ".cache");
        Tools::logm("yarnResponse : " . $yarnResponse);
        if ($yarnResponse != FALSE) {
            $result = json_decode($yarnResponse);
            if (isset($result->page->overallScore)) {
                $this->status = TRUE;
                $this->score = $result->page->overallScore;
            }
        }
    }


    private function _connectYarnAPI($url) {
        if (in_array ('curl', get_loaded_extensions())) {
            $timeout = 15;
            # Fetch feed from URL
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, YARNAPIURL);
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
            if (!ini_get('open_basedir') && !ini_get('safe_mode')) {
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded', 'X-Mashape-Key: ' . YARNAPITOKEN));
            curl_setopt($curl, CURLOPT_POSTFIELDS, "fields=page&url=" . $url);

            $data = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $httpcodeOK = isset($httpcode) and ($httpcode == 200 or $httpcode == 301);
            curl_close($curl);
            return $data;
        } else {
            return FALSE;
        }
    }

    private function _cacheYarnFile($url, $id)
    {
        if (!is_dir(CACHE . '/yarn')) {
            mkdir(CACHE . '/yarn', 0777);
        }

        // if a cache yarn file for this url already exists and it's been less than one day than it have been updated, see in /cache
        if (!file_exists(CACHE . "/yarn/".$id.".cache")) {
            $askForYarn = $this->_connectYarnAPI($url);
            $yarnCacheFile = fopen(CACHE . "/yarn/".$id.".cache", 'w+');
            fwrite($yarnCacheFile, $askForYarn);
            fclose($yarnCacheFile);
        }
    }
}
