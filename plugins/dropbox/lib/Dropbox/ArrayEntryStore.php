<?php
namespace Dropbox;

/**
 * A class that gives get/put/clear access to a single entry in an array.
 */
class ArrayEntryStore implements ValueStore
{
    private $array;
    private $key;

    /**
     * Constructor.
     *
     * @param array $array
     *    The array that we'll be accessing.
     *
     * @param object $key
     *    The key for the array element we'll be accessing.
     */
    function __construct(&$array, $key)
    {
        $this->array = &$array;
        $this->key = $key;
    }

    /**
     * Returns the entry's current value or <code>null</code> if nothing is set.
     *
     * @return object
     */
    function get()
    {
        if (isset($this->array[$this->key])) {
            return $this->array[$this->key];
        } else {
            return null;
        }
    }

    /**
     * Set the array entry to the given value.
     *
     * @param object $value
     */
    function set($value)
    {
        $this->array[$this->key] = $value;
    }

    /**
     * Clear the entry.
     */
    function clear()
    {
        unset($this->array[$this->key]);
    }
}
