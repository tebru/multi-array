<?php
/**
 * File JsonObjectTest.php 
 */

namespace Tebru\Test;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\JsonObject;

/**
 * Class JsonObjectTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class JsonObjectTest extends PHPUnit_Framework_TestCase
{
    public function testCanCreateFromJson()
    {
        $array = $this->getMultiArray(true);
        $jsonObject = new JsonObject($array);
        $this->assertTrue($jsonObject instanceof JsonObject);
    }

    public function testCanCreateFromArray()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $this->assertTrue($jsonObject instanceof JsonObject);
    }

    public function testCanAccessKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $value = $jsonObject->get('key3');
        $this->assertEquals('value3', $value);
    }

    public function testCanAccessKeyOneLevel()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $value = $jsonObject->get('key1.key1-1');
        $this->assertEquals('value1', $value);
    }

    public function testCanAccessKeyTwoLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $value = $jsonObject->get('key2.key2-2.key2-3');
        $this->assertEquals('value2', $value);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testInvalidKeyWillThrowException()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->get('key1.key3');
    }

    public function testExistsTrue()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $exists = $jsonObject->exists('key2.key2-2.key2-3');
        $this->assertTrue($exists);
    }

    public function testExistsFalse()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $exists = $jsonObject->exists('key2.key5');
        $this->assertFalse($exists);
    }

    public function testCanIterate()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $keyCounter = 1;
        foreach ($jsonObject as $key => $element) {
            $this->assertEquals('key' . $keyCounter, $key);
            ++$keyCounter;
        }
    }

    public function testCanSerialize()
    {
        $array = $this->getMultiArray();
        $json = $this->getMultiArray(true);
        $jsonObject = new JsonObject($array);
        $jsonObjectSerialized = json_encode($jsonObject);
        $this->assertSame($json, $jsonObjectSerialized);
    }

    public function testWillUseCacheForGet()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->get('key1.key1-1');

        $cacheProperty = new ReflectionProperty(JsonObject::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('value1', $cacheValue['key1.key1-1']);
    }

    public function testWillUseCacheForExists()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->exists('key1.key1-1');

        $cacheProperty = new ReflectionProperty(JsonObject::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('value1', $cacheValue['key1.key1-1']);
    }

    public function testCanUseDifferentDelimiter()
    {
        $array = $this->getMultiArray(true);
        $jsonObject = new JsonObject($array, ':');
        $exists = $jsonObject->exists('key1:key1-1');
        $this->assertTrue($exists);
    }

    public function testSetKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('key3', 'test');
        $this->assertEquals('test', $jsonObject->get('key3'));
        $this->assertEquals('value3', $array['key3']);
    }

    public function testSetKeyTwoLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('key1.key1-1', 'test');
        $this->assertEquals('test', $jsonObject->get('key1.key1-1'));
        $this->assertEquals('value1', $array['key1']['key1-1']);
    }

    public function testSetKeyAddedToCache()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('key3', 'test');

        $cacheProperty = new ReflectionProperty(JsonObject::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('test', $cacheValue['key3']);
    }

    public function testNewKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('key2.key2-2.test', 'test');
        $this->assertEquals('test', $jsonObject->get('key2.key2-2.test'));
        $this->assertEquals('value2', $jsonObject->get('key2.key2-2.key2-3'));
    }

    public function testTwoNewKeyLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('key1.test.test2', 'test');
        $this->assertEquals('test', $jsonObject->get('key1.test.test2'));
    }

    public function testAllNewKeys()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('test.test2.test3', 'test');
        $this->assertEquals('test', $jsonObject->get('test.test2.test3'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNewKeyThrowsException()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('key1.key1-1.test', 'test');
    }

    public function testNewKeyAddedToCache()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->set('key2.key2-2.test', 'test');

        $cacheProperty = new ReflectionProperty(JsonObject::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('test', $cacheValue['key2.key2-2.test']);
    }

    public function testUnsetKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $this->assertTrue($jsonObject->exists('key3'));
        $jsonObject->remove('key3');
        $this->assertFalse($jsonObject->exists('key'));
    }

    public function testUnsetKeyTwoLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $this->assertTrue($jsonObject->exists('key1.key1-1'));
        $jsonObject->remove('key1.key1-1');
        $this->assertFalse($jsonObject->exists('key1.key1-1'));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testUnsetWillThrowException()
    {
        $array = $this->getMultiArray();
        $jsonObject = new JsonObject($array);
        $jsonObject->remove('key4');
    }

    private function getMultiArray($encode = false)
    {
        $array =[
            'key1' => [
                'key1-1' => 'value1'
            ],
            'key2' => [
                'key2-2' => [
                    'key2-3' => 'value2'
                ]
            ],
            'key3' => 'value3',
        ];

        if ($encode) {
            return json_encode($array);
        }

        return $array;
    }
}
