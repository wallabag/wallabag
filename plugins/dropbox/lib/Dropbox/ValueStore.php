<?php
namespace Dropbox;

/**
 * A contract for a class which provides simple get/set/clear access to a single string
 * value.  {@link ArrayEntryStore} provides an implementation of this for storing a value
 * in a single array element.
 *
 * Example implementation for a Memcache-based backing store:
 *
 * <code>
 * class MemcacheValueStore implements ValueStore
 * {
 *     private $key;
 *     private $memcache;
 *
 *     function __construct($memcache, $key)
 *     {
 *         $this->memcache = $memcache;
 *         $this->key = $key;
 *     }
 *
 *     function get()
 *     {
 *         $value = $this->memcache->get($this->getKey());
 *         return $value === false ? null : base64_decode($value);
 *     }
 *
 *     function set($value)
 *     {
 *         $this->memcache->set($this->key, base64_encode($value));
 *     }
 *
 *     function clear()
 *     {
 *         $this->memcache->delete($this->key);
 *     }
 * }
 * </code>
 */
interface ValueStore
{
    /**
     * Returns the entry's current value or <code>null</code> if nothing is set.
     *
     * @return string
     */
    function get();

    /**
     * Set the entry to the given value.
     *
     * @param string $value
     */
    function set($value);

    /**
     * Remove the value.
     */
    function clear();
}
