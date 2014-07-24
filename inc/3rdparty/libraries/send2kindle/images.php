<?php

/**
 * Get images from content and prepare to save in articles
 */
class Images {

    /**
     * JPER quality for resize
     */
    const JPEG_QUALITY = 80;

    /**
     * Articles content
     * @var string
     */
    private $_content;

    /**
     * Storage to keep images
     * @var object Storage
     */
    private $_storage;

    /**
     * images from content
     * @var array
     */
    private $_images_from_content = array();

    /**
     * Prepare get images
     * @param Strage $storage
     * @param string $article_content
     */
    public function __construct(Storage $storage, $article_content)
    {
        $this->_storage = $storage;
        $this->_content = $article_content;
        $this->_images_from_content = $this->_get_images_from_content($article_content);
    }

    /**
     * get images from url
     * @param string $content
     * @return array images hashtable
     */
    private function _get_images_from_content($content)
    {
        $result = array();
        preg_match_all('/src=\"([a-zA-Z0-9\.\/\-\_\?\+\%\~\&\;\=\:]+)\"/i', $content, $result);

        return $result[1];
    }

    /**
     * Start conversion
     * @return string converted content
     */
    public function convert()
    {
        foreach ( $this->_images_from_content as $n => $image_url )
        {
            $image = $this->_get_image($image_url);
            $this->_content = str_replace($image_url, '" recindex="'.(int)basename($image), $this->_content);
        }

        return $this->_content;
    }

    /**
     * Resize image
     * @param string $file path
     * @param int $new_width max width
     */
    private function _resize($file, $new_width = 500)
    {
        list($width, $height) = getimagesize($file);

        $new_height = 0;

        //setup the new size of the image
        if( $width > $new_width )
        {
            $ratio = $height/$width;
            $new_height = $new_width * $ratio;
        }
        else
        {
            $new_width = $width;
            $new_height = $height;
        }

        // resample the image        
        $new_image = imagecreatetruecolor($new_width, $new_height);        
        
        $type = exif_imagetype ( $file );

        switch ( $type )
        {
            case IMAGETYPE_JPEG:
                $old_image = imagecreatefromjpeg($file);
                imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($new_image, $file, self::JPEG_QUALITY);
                break;
            case IMAGETYPE_PNG:
                $old_image = imagecreatefrompng($file);
                imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagepng($new_image, $file);
                break;
            case IMAGETYPE_GIF:
                $old_image = imagecreatefromgif($file); 
                imagecopyresampled($new_image, $old_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagegif($new_image, $file);
                break;
        }
    }

    /**
     * Resize image
     * @return string image path
     */
    private function _get_image($url)
    {
        $image_data = @file_get_contents($url);

        if ( $image_data !== false )
        {
            $image_name = $this->_storage->save_image($image_data);
            $this->_resize($image_name);

            return $image_name;
        }
    }

}