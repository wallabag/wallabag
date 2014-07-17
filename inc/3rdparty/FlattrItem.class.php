<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

class FlattrItem
{
    public $status;
    public $urlToFlattr;
    public $flattrItemURL;
    public $numFlattrs;

    public function checkItem($urlToFlattr, $id)
    {
        $this->_cacheFlattrFile($urlToFlattr, $id);
        $flattrResponse = file_get_contents(CACHE . "/flattr/".$id.".cache");
        if($flattrResponse != FALSE) {
            $result = json_decode($flattrResponse);
            if (isset($result->message)) {
                if ($result->message == "flattrable") {
                    $this->status = FLATTRABLE;
                }
            } 
            elseif (is_object($result) && $result->link) {
                $this->status = FLATTRED;
                $this->flattrItemURL = $result->link;
                $this->numFlattrs = $result->flattrs;
            }
            else {
                $this->status = NOT_FLATTRABLE;
            }
        }
        else {
            $this->status = "FLATTR_ERR_CONNECTION";
        }
    }

    private function _cacheFlattrFile($urlToFlattr, $id)
    {
        if (!is_dir(CACHE . '/flattr')) {
            mkdir(CACHE . '/flattr', 0777);
        }

        // if a cache flattr file for this url already exists and it's been less than one day than it have been updated, see in /cache
        if ((!file_exists(CACHE . "/flattr/".$id.".cache")) || (time() - filemtime(CACHE . "/flattr/".$id.".cache") > 86400)) {
            $askForFlattr = Tools::getFile(FLATTR_API . $urlToFlattr);
            $flattrCacheFile = fopen(CACHE . "/flattr/".$id.".cache", 'w+');
            fwrite($flattrCacheFile, $askForFlattr);
            fclose($flattrCacheFile);
        }
    }
}
