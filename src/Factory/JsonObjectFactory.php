<?php
/**
 * File JsonObjectFactory.php 
 */

namespace Tebru\Factory;

use Tebru\JsonObject;

/**
 * Class JsonObjectFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonObjectFactory
{
    public function make($jsonOrArray, $delimiter = '.')
    {
        return new JsonObject($jsonOrArray, $delimiter);
    }
} 
