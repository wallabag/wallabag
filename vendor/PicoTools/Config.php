<?php

/*
 * This file is part of PicoTools.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PicoTools;


/**
 * Handle configuration parameters
 *
 * @author Frédéric Guillot
 */
class Config
{
    /**
     * Container
     *
     * @access private
     * @static
     * @var array
     */
    private static $container = array();


    /**
     * Set a new configuration parameter
     *
     * @access public
     * @static
     * @param string $name Parameter name
     * @param string $value Parameter value
     */
    public static function set($name, $value)
    {
        self::$container[$name] = $value;
    }


    /**
     * Fetch a parameter value
     *
     * @access public
     * @static
     * @param string $name Parameter name
     * @param string $defaultValue Default parameter value
     */
    public static function get($name, $defaultValue = null)
    {
        return isset(self::$container[$name]) ? self::$container[$name] : $defaultValue;
    }


    /**
     * Load a PHP config file
     *
     * @access public
     * @static
     */
    public static function load($env = null)
    {
        if ($env !== null) {

            $filename = 'config/'.$env.'.php';

            if (file_exists($filename)) {

                require $filename;
            }
            else {

                throw new \RuntimeException('Unable to load the config file: '.$filename);
            }
        }
        else {

            if (file_exists('config/prod.php')) {

                require 'config/prod.php';
            }
            else if (file_exists('config/dev.php')) {

                require 'config/dev.php';
            }
            else {

                throw new \RuntimeException('No config file loaded.');
            }
        }
    }
}