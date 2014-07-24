<?php
/**
 * Storage for news2Kindle
 * @author jwest <jwest@jwest.pl>
 */
class Storage {

    /**
     * Newspapper name
     * @var string
     */
    private $_name;

    /**
     * Default dir
     * @var string
     */
    private $_default_dir = 'data/';

    /**
     * Info about newspapper
     * @var array
     */
    private $_info;

    /**
     * Prepare storage
     * @param string $newspapper
     * @param string $default_dir
     */
    public function __construct($newspapper, $default_dir = NULL)
    {
        $this->_name = $newspapper;

        if ( $default_dir !== NULL )
        {
            $this->_default_dir = $default_dir;
        }

        $this->_info = $this->_get_info();
    }

    /**
     * save info before exit from script
     */
    public function __destruct()
    {
        $this->_save_info($this->_info);
    }

    /**
     * Get path
     * @param bool $newspapper path to newspapper
     * @return string path to repository
     */
    public function get_path($newspapper = true)
    {
        $path = $this->_default_dir;

        if ( $newspapper ) 
        {
            $path .= $this->_name . '/';
        }

        return $path;
    }

    /**
     * Get info about newspapper
     * @return array newspapper info
     */
    private function _get_info()
    {        
        $content = unserialize( $this->_get_file('info.dat') );

        if( $content === FALSE )
        {
            $content = (object) array
            (
                'images_count' => 0,
                'articles_count' => 0,
                'images' => array(),
            );

            $this->_save_info($content);
        }

        return $content;
    }

    /**
     * Get info key
     * @param $key
     * @return mixed
     */
    public function info($key)
    {
        return $this->_info->$key;
    }

    /**
     * Save info
     * @param array $info info from class
     */
    private function _save_info($info)
    {
        $this->_save_file( 'info.dat', serialize($info) );
    }

    /**
     * Save image in storage
     * @param resource $image
     * @param string $name image name, if null create auto name
     * @return string image name
     */
    public function save_image($image, $name = NULL)
    {
        if ( $name === NULL )
        {
            $image_name = (string) $this->_info->images_count;
                    
            for ($i=strlen($image_name); $i<6; $i++)
            {
                $image_name = '0'.$image_name;
            }

            $this->_info->images_count++;
            $this->_info->images[$this->_info->images_count] = $image_name;

            $name = $image_name;
        }

        $this->_save_file($name, $image);

        return $this->_default_dir . $this->_name . '/' . $name;
    }

    /**
     * Get image
     * @param  string $name image name
     * @return resource
     */
    public function get_image($name)
    {
        return $this->_get_file($name);
    }

    /**
     * Put article contents
     * @param string $id unique id for article
     * @param string $title
     * @param string $content article content
     * @param string $url url for article
     * @param object $website
     */
    public function add_content($id, $title, $content, $url, $website)
    {
        $articles = unserialize( $this->_get_file('articles.dat') );
        
        $articles[$id] = (object) array
        (
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'url' => $url,
            'website' => $website,
        );

        $this->_save_file( 'articles.dat', serialize($articles) );
    }

    /**
     * Get articles contents
     * @param string $file_name
     * @param string $file_content
     */
    public function get_contents()
    {
        return unserialize( $this->_get_file( 'articles.dat' ) );
    }

    /**
     * Get file content
     * @param string $file_name
     * @param string $file_content
     */
    private function _save_file($file_name, $file_content)
    {
        if( ! file_exists( $this->_default_dir . $this->_name ) )
        {
            mkdir( $this->_default_dir . $this->_name, 0777, TRUE );
        }

        file_put_contents($this->_default_dir . $this->_name . '/' . $file_name, $file_content);
    }

    /**
     * Get file content
     * @param string $file_name
     * @param string $default_file_content (if file not exists)
     * @return string file content
     */
    private function _get_file($file_name, $default_file_content = NULL)
    {
        $content = @file_get_contents($this->_default_dir . $this->_name . '/' . $file_name);

        if ($content === FALSE)
        {
            return $default_file_content;
        }

        return $content;
    }

    /**
     * Clean newspapper after convert to mobi
     */
    public function clean()
    {
        $files_to_remove = glob($this->_default_dir . $this->_name . '/*');

        foreach ( $files_to_remove as $file )
        {
            unlink( $file );
        }

        rmdir( $this->_default_dir . $this->_name );
    }

}