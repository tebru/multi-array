<?php
/**
 * File JsonObject.php 
 */

namespace Tebru;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use OutOfBoundsException;
use Traversable;

/**
 * Class JsonObject
 *
 * Attempts to ease access to multidimensional arrays. Created with the intention of
 * accessing json response values.
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonObject implements IteratorAggregate, JsonSerializable
{
    /**
     * How keys will be delimited
     *
     * @var string $keyDelimiter
     */
    private $keyDelimiter;

    /**
     * Stores array object was created with
     * @var array $storage
     */
    private $storage = [];

    /**
     * A cache of keys that have been verified and values
     *
     * @var array $cache
     */
    private $cache = [];

    /**
     * Constructor
     *
     * @param array|string $jsonOrArray An array or string. If a string is provided, attempts
     *     to json_decode() it into an associative array.
     * @param string $keyDelimiter How array key access will be delimited
     */
    public function __construct($jsonOrArray, $keyDelimiter = '.')
    {
        if (is_string($jsonOrArray)) {
            $jsonOrArray = json_decode($jsonOrArray, true);

            if (null === $jsonOrArray) {
                throw new InvalidArgumentException('Could not decode json string into array.');
            }
        }

        if (!is_array($jsonOrArray)) {
            throw new InvalidArgumentException('Expected array or string, got ' . gettype($jsonOrArray));
        }

        $this->storage = $jsonOrArray;
        $this->keyDelimiter = $keyDelimiter;
    }

    /**
     * Determine if a key exists
     *
     * @param string $keyString
     *
     * example:
     *     $jsonObject->exists('key1.key2');
     *
     * @return bool
     */
    public function exists($keyString)
    {
        if (true === $this->inCache($keyString)) {
            return true;
        }

        try {
            $this->get($keyString);
        } catch (OutOfBoundsException $e) {
            return false;
        }

        return true;
    }

    /**
     * Attempt to get a value by key
     *
     * @param $keyString
     *
     * example:
     *     $jsonObject->get('key1.key2');
     *
     * @return mixed
     * @throws OutOfBoundsException If the key does not exist
     */
    public function get($keyString)
    {
        if (true === $this->inCache($keyString)) {
            return $this->cache[$keyString];
        }

        $keys = explode($this->keyDelimiter, $keyString);
        $value = $this->getValue($keys, $this->storage);
        $this->cache[$keyString] = $value;

        return $value;
    }

    /**
     * Recursive method to get a value
     *
     * Will continue to call method until $keys array is empty, then returns
     * the current value.
     *
     * @param array $keys
     * @param mixed $element
     *
     * @return mixed
     */
    private function getValue(array &$keys, $element)
    {
        $checkKey = array_shift($keys);

        if (!isset($element[$checkKey])) {
            throw new OutOfBoundsException('Could not find key in array.');
        }

        if (empty($keys)) {
            return $element[$checkKey];
        }

        return $this->getValue($keys, $element[$checkKey]);
    }

    /**
     * Check if the key is currently in the cache
     *
     * @param string $keyString
     *
     * @return bool
     */
    private function inCache($keyString)
    {
        return isset($this->cache[$keyString]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->storage);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return $this->storage;
    }
}
