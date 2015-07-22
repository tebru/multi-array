<?php
/**
 * File MultiArrayTest.php
 */

namespace Tebru\Test;

use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;
use Tebru\MultiArray;

/**
 * Class MultiArrayTest
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MultiArrayTest extends PHPUnit_Framework_TestCase
{
    public function testCanCreateFromJson()
    {
        $array = $this->getMultiArray(true);
        $jsonObject = new MultiArray($array);
        $this->assertTrue($jsonObject instanceof MultiArray);
    }

    public function testCanCreateFromArray()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $this->assertTrue($jsonObject instanceof MultiArray);
    }

    public function testCanAccessKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $value = $jsonObject->get('key3');
        $this->assertEquals('value3', $value);
    }

    public function testCanAccessKeyOneLevel()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $value = $jsonObject->get('key1.key1-1');
        $this->assertEquals('value1', $value);
    }

    public function testCanAccessKeyTwoLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $value = $jsonObject->get('key2.key2-2.key2-3');
        $this->assertEquals('value2', $value);
    }

    public function testCanAccessWithPhp()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $value = $jsonObject['key2.key2-2.key2-3'];
        $this->assertEquals('value2', $value);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testInvalidKeyWillThrowException()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->get('key1.key3');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function getWithPhpWillThrowException()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject['key1.key3'];
    }

    public function testExistsTrue()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $exists = $jsonObject->exists('key2.key2-2.key2-3');
        $this->assertTrue($exists);
    }

    public function testExistsFalse()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $exists = $jsonObject->exists('key2.key5');
        $this->assertFalse($exists);
    }

    public function testPhpExists()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $exists = isset($jsonObject['key2.key2-2.key2-3']);
        $this->assertTrue($exists);
    }

    public function testCanIterate()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
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
        $jsonObject = new MultiArray($array);
        $jsonObjectSerialized = json_encode($jsonObject);
        $this->assertSame($json, $jsonObjectSerialized);
    }

    public function testWillUseCacheForGet()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->get('key1.key1-1');

        $cacheProperty = new ReflectionProperty(MultiArray::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('value1', $cacheValue['key1.key1-1']);
    }

    public function testWillUseCacheForExists()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->exists('key1.key1-1');

        $cacheProperty = new ReflectionProperty(MultiArray::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('value1', $cacheValue['key1.key1-1']);
    }

    public function testCanUseDifferentDelimiter()
    {
        $array = $this->getMultiArray(true);
        $jsonObject = new MultiArray($array, ':');
        $exists = $jsonObject->exists('key1:key1-1');
        $this->assertTrue($exists);
    }

    public function testSetKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->set('key3', 'test');
        $this->assertEquals('test', $jsonObject->get('key3'));
        $this->assertEquals('value3', $array['key3']);
    }

    public function testSetKeyTwoLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->set('key1.key1-1', 'test');
        $this->assertEquals('test', $jsonObject->get('key1.key1-1'));
        $this->assertEquals('value1', $array['key1']['key1-1']);
    }

    public function testSetKeyAddedToCache()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->set('key3', 'test');

        $cacheProperty = new ReflectionProperty(MultiArray::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('test', $cacheValue['key3']);
    }

    public function testNewKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->set('key2.key2-2.test', 'test');
        $this->assertEquals('test', $jsonObject->get('key2.key2-2.test'));
        $this->assertEquals('value2', $jsonObject->get('key2.key2-2.key2-3'));
    }

    public function testTwoNewKeyLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->set('key1.test.test2', 'test');
        $this->assertEquals('test', $jsonObject->get('key1.test.test2'));
    }

    public function testAllNewKeys()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->set('test.test2.test3', 'test');
        $this->assertEquals('test', $jsonObject->get('test.test2.test3'));
    }

    public function testNewKeysPhp()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject['test.test2.test3'] = 'test';
        $this->assertEquals('test', $jsonObject['test.test2.test3']);
    }

    public function testNewKeyAddedToCache()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->set('key2.key2-2.test', 'test');

        $cacheProperty = new ReflectionProperty(MultiArray::class, 'cache');
        $cacheProperty->setAccessible(true);
        $cacheValue = $cacheProperty->getValue($jsonObject);

        $this->assertEquals('test', $cacheValue['key2.key2-2.test']);
    }

    public function testUnsetKey()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $this->assertTrue($jsonObject->exists('key3'));
        $jsonObject->remove('key3');
        $this->assertFalse($jsonObject->exists('key'));
    }

    public function testUnsetKeyTwoLevels()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $this->assertTrue($jsonObject->exists('key1.key1-1'));
        $jsonObject->remove('key1.key1-1');
        $this->assertFalse($jsonObject->exists('key1.key1-1'));
    }

    public function testUnsetWithPhp()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $this->assertTrue(isset($jsonObject['key1.key1-1']));
        unset($jsonObject['key1.key1-1']);
        $this->assertFalse(isset($jsonObject['key1.key1-1']));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testUnsetWillThrowException()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        $jsonObject->remove('key4');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testUnsetPhpWillThrowException()
    {
        $array = $this->getMultiArray();
        $jsonObject = new MultiArray($array);
        unset($jsonObject['key']);
    }

    public function testSetKeyInDiffOrder()
    {

        $multiArray1 = new MultiArray(array());
        $multiArray1->set('key1.key2.key3.key4', 'value1');
        $multiArray1->set('key1.key2.key5', 'value2');
        $multiArray1->set('key1.key2', 'value3');

        $multiArray2 = new MultiArray(array());
        $multiArray2->set('key1.key2', 'value3');
        $multiArray2->set('key1.key2.key5', 'value2');
        $multiArray2->set('key1.key2.key3.key4', 'value1');

        $this->assertEquals($multiArray1->get('key1.key2'), $multiArray2->get('key1.key2'));
        $this->assertEquals($multiArray1->get('key1.key2.key5'), $multiArray2->get('key1.key2.key5'));
        $this->assertEquals($multiArray1->get('key1.key2.key3.key4'), $multiArray2->get('key1.key2.key3.key4'));

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