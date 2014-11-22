<?php
/**
 * File MultiArrayFactory.php
 */

namespace Tebru\Factory;

use Tebru\MultiArray;

/**
 * Class MultiArrayFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MultiArrayFactory
{
    /**
     * Make a MultiArray
     *
     * @param string|array $jsonOrArray
     * @param string $delimiter
     *
     * @return MultiArray
     */
    public function make($jsonOrArray, $delimiter = '.')
    {
        return new MultiArray($jsonOrArray, $delimiter);
    }
} 
