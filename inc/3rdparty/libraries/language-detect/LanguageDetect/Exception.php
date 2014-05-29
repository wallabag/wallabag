<?php
class Text_LanguageDetect_Exception extends Exception
{
    /**
     * Database file could not be found
     */
    const DB_NOT_FOUND = 10;

    /**
     * Database file found, but not readable
     */
    const DB_NOT_READABLE = 11;

    /**
     * Database file is empty
     */
    const DB_EMPTY = 12;

    /**
     * Database contents is not a PHP array
     */
    const DB_NOT_ARRAY = 13;

    /**
     * Magic quotes are activated
     */
    const MAGIC_QUOTES = 14;


    /**
     * Parameter of invalid type passed to method
     */
    const PARAM_TYPE = 20;

    /**
     * Character in parameter is invalid
     */
    const INVALID_CHAR = 21;


    /**
     * Language is not in the database
     */
    const UNKNOWN_LANGUAGE = 30;


    /**
     * Error during block detection
     */
    const BLOCK_DETECTION = 40;


    /**
     * Error while clustering languages
     */
    const NO_HIGHEST_KEY = 50;
}
