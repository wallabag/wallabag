<?php

namespace PicoFeed;

class Logging
{
    public static $messages = array();

    public static function log($message)
    {
        self::$messages[] = '['.date('Y-m-d H:i:s').'] '.$message;
    }
}