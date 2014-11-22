[![Build Status](https://travis-ci.org/tebru/multi-array.svg?branch=master)](https://travis-ci.org/tebru/multi-array)

# MultiArray
This project aims to provide easier access to multidimensional arrays.  Json responses were in mind during the creation.  The goal is to make it easier to check if keys are set multiple levels deep, and retrieve the value.

## Installation

```
composer require tebru/multi-array:~0.1
```

## Usage
Create the object by either instantiating it or using the factory.  Pass in a json object or array.

```
$array = [
    'key' => 'value',
    'key2' => ['nested-key' => 'value2'],
];
$multiArray = new MultiArray($array);
$multiArray = $multiArrayFactory->make($array);

$json = json_encode($array);
$multiArray = new MultiArray($json);
$multiArray = $multiArrayFactory->make($json);
```

From here you can check if a key exists

```
$multiArray->exists('key2.nested-key'); // returns true
$multiArray->exists('key3'); // returns false
```

Get the value of a key

```
$multiArray->get('key2'); // returns ['nested-key' => 'value2']
$multiArray->get('key2.nested-key'); // returns 'value2'
$multiArray->get('key3'); // throws OutOfBoundsException
```

Set the value of a key

```
$multiArray->set('key1', 'value');
$multiArray->set('key2.nested-key', 'value');
$multiArray->set('key2.newKey', 'value');
$multiArray->set('key1.newKey', 'value'); // throws InvalidArgumentException
```

Remove the value of a key

```
$multiArray->remove('key1');
$multiArray->remove('key2.unknown'); // throws OutOfBoundsException
```

We default to delimiting keys by a period `.`, but that can be changed during instantiation

```
$multiArray = new MultiArray($array, ':');
$multiArray = $multiArrayFactory->make($array, '--');
```

You can also access the object like a normal array

```
isset($multiArray['key2.nested-key']);
$multiArray['key2.nested-key];
$multiArray['key2.nested-key'] = 'value';
unset($multiArray['key2.nested-key']);
```
