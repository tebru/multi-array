[![Build Status](https://travis-ci.org/tebru/json-object.svg?branch=master)](https://travis-ci.org/tebru/json-object)

# JsonObject
This project aims to provide easier access to multidimensional arrays.  Json responses were in mind during the creation.  The goal is to make it easier to check if keys are set multiple levels deep, and retrieve the value.

## Installation

```
composer require tebru/json-object:~0.1
```

## Usage
Create the object by either instantiating it or using the factory.  Pass in a json object or array.

```
$array = [
    'key' => 'value',
    'key2' => ['nested-key' => 'value2'],
];
$jsonObject = new JsonObject($array);
$jsonObject = $jsonObjectFactory->make($array);

$json = json_encode($array);
$jsonObject = new JsonObject($json);
$jsonObject = $jsonObjectFactory->make($json);
```

From here you can check if a key exists

```
$jsonObject->exists('key2.nested-key'); // returns true
$jsonObject->exists('key3'); // returns false
```

Or get the value of a key

```
$jsonObject->get('key2'); // returns ['nested-key' => 'value2']
$jsonObject->get('key2.nested-key'); // returns 'value2'
$jsonObject->get('key3'); // throws OutOfBoundsException
```

We default to delimiting keys by a period (.), but that can be changed during instantiation

```
$jsonObject = new JsonObject($array, ':');
$jsonObject = $jsonObjectFactory->make($array, '--');
```
