<?php

class Plugin
{
    private static $enabled_menu = array();
    private static $disabled_menu = array();


    // Load all plugins
    public static function loadPlugins()
    {
        if (file_exists(PLUGIN_DIRECTORY)) {

            $dir = new \DirectoryIterator(PLUGIN_DIRECTORY);

            foreach ($dir as $fileinfo) {

                if (! $fileinfo->isDot() && $fileinfo->isDir()) {

                    $file = PLUGIN_DIRECTORY.'/'.$fileinfo->getFilename().'/plugin.php';

                    if (file_exists($file)) {
                        require $file;
                        if (is_null(Model\get_plugin_option($fileinfo->getFilename() . '_enabled'))) {
                            self::$disabled_menu[$fileinfo->getFilename()] = $fileinfo->getFilename();
                        }
                        else {
                            self::$enabled_menu[$fileinfo->getFilename()] = $fileinfo->getFilename();
                        }
                    }
                }
            }
        }
    }


    public static function addMenu($status, $name)
    {
        if ($status === 'enabled') {
            self::$enabled_menu[$name] = $name;
        }
        else {
            self::$disabled_menu[$name] = $name;
        }
    }


    public static function delMenu($menu, $name)
    {
        if (isset(self::$$menu[$name])) {
            unset(self::$$menu[$name]);
        }
    }


    public static function buildMenu($status)
    {
        $html = '<ul>';
        if ($status === 'enabled') {
            foreach (self::$enabled_menu as $name) {
                $html .= '<li>';
                $get_description = ucfirst($name) . "\\get_description";
                $html .= '<a href="?action='.$name.'">' . $name.'</a>: ' . $get_description() . ' <a href="?action=disable-plugin&amp;plugin_name='.$name.'" title="Disable this plugin">✘</a>';
                $html .= '</li>';
            }
        }
        else {
            foreach (self::$disabled_menu as $name) {
                $html .= '<li>';
                $get_description = ucfirst($name) . "\\get_description";
                $html .= $name. ': ' . $get_description() . '   <a href="?action=enable-plugin&amp;plugin_name='.$name.'" title="Enable this plugin">✓</a>';
                $html .= '</li>';
            }
        }

        $html .= '</ul>';

        return $html;
    }
}