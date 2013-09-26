<?php
/* 
* Class for Flattr querying
*/
class FlattrItem {

    public $status;
    public $urltoflattr;
    public $flattrItemURL;
    public $numflattrs;

    public function checkItem($urltoflattr) {
        $this->cacheflattrfile($urltoflattr);
        $flattrResponse = file_get_contents(CACHE . "/flattr/".md5($urltoflattr).".cache");
        if($flattrResponse != FALSE) {
            $result = json_decode($flattrResponse);
            if (isset($result->message)){
                if ($result->message == "flattrable") {
                    $this->status = FLATTRABLE;
                }
            } 
            elseif ($result->link) {
                $this->status = FLATTRED;
                $this->flattrItemURL = $result->link;
                $this->numflattrs = $result->flattrs;
            }
            else {
                $this->status = NOT_FLATTRABLE;
            }
        }
        else {
            $this->status = "FLATTR_ERR_CONNECTION";
        }
    }

    private function cacheflattrfile($urltoflattr) {
        if (!is_dir(CACHE . '/flattr')) {
            mkdir(CACHE . '/flattr', 0777);
        }

        // if a cache flattr file for this url already exists and it's been less than one day than it have been updated, see in /cache
        if ((!file_exists(CACHE . "/flattr/".md5($urltoflattr).".cache")) || (time() - filemtime(CACHE . "/flattr/".md5($urltoflattr).".cache") > 86400)) {
            $askForFlattr = Tools::getFile(FLATTR_API . $urltoflattr);
            $flattrCacheFile = fopen(CACHE . "/flattr/".md5($urltoflattr).".cache", 'w+');
            fwrite($flattrCacheFile, $askForFlattr);
            fclose($flattrCacheFile);
        }
    }
}