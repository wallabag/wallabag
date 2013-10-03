<?php
/* 
* Class for Flattr querying
*/
class FlattrItem {

    public $status;
    public $urltoflattr;
    public $flattrItemURL;
    public $numflattrs;

    public function checkItem($urltoflattr,$id) {
        $this->cacheflattrfile($urltoflattr, $id);
        $flattrResponse = file_get_contents(CACHE . "/flattr/".$id.".cache");
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

    private function cacheflattrfile($urltoflattr, $id) {
        if (!is_dir(CACHE . '/flattr')) {
            mkdir(CACHE . '/flattr', 0777);
        }

        // if a cache flattr file for this url already exists and it's been less than one day than it have been updated, see in /cache
        if ((!file_exists(CACHE . "/flattr/".$id.".cache")) || (time() - filemtime(CACHE . "/flattr/".$id.".cache") > 86400)) {
            $askForFlattr = Tools::getFile(FLATTR_API . $urltoflattr);
            $flattrCacheFile = fopen(CACHE . "/flattr/".$id.".cache", 'w+');
            fwrite($flattrCacheFile, $askForFlattr);
            fclose($flattrCacheFile);
        }
    }
}