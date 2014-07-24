<?php
/**
 * Utils for news2kindle
 * @author jwest <jwest@jwest.pl>
 */
class Utils
{
    /**
     * URL encode
     * @param string $url
     * @return string $ar_url
     */
    public static function urlencode( $url )
    {
        $ar_url = explode( '/', $url );

        foreach ( $ar_url as $key => $val )
        {
            $ar_url[ $key ] = urlencode( $val );
        }

        return implode('/', $ar_url );
    }

    /**
     * Prepare ID for google rss article    
     * @param string $id
     * @return string
     */
    public static function prepare_id($id)
    {
        $char_in = array('/', '.', ',', ':');
        $id = str_replace($char_in, '-', $id);
        
        return $id;
    }
}