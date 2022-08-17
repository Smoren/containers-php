# Data containers

![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/smoren/containers)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Smoren/containers-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Smoren/containers-php/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/Smoren/containers-php/badge.svg?branch=master)](https://coveralls.io/github/Smoren/containers-php?branch=master)
![Build and test](https://github.com/Smoren/containers-php/actions/workflows/test_master.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Abstract data containers and structures for PHP

### How to install as dependency to your project
```
composer require smoren/containers
```

### Unit testing
```
composer install
./vendor/bin/codecept build
./vendor/bin/codecept run unit tests/unit
```

### LinkedList

Classic implementation of linked list structure. Also can be used as stack and queue.

```php
use Smoren\Containers\Structs\LinkedList;
use Smoren\Containers\Structs\LinkedListItem;

$ll = new LinkedList([6, 4, 2]);
var_dump($ll->count()); // output: 3

$ll->pushBack(1);
$ll->pushFront(7);
var_dump($ll->count()); // output: 5
print_r($ll->toArray()); // output: [7, 6, 4, 2, 1]

$ll->sort(function($lhs, $rhs) {
    return $lhs > $rhs;
});

print_r($ll->toArray()); // output: [1, 2, 4, 6, 7]

$ll->swap($ll->getFirst(), $ll->getLast());
print_r($ll->toArray()); // output: [7, 2, 4, 6, 1]

$ll->swap($ll->getFirst()->getNext(), $ll->getLast()->getPrev());
print_r($ll->toArray()); // output: [7, 6, 4, 2, 1]

var_dump($ll->popFront()); // output: 7
var_dump($ll->popBack()); // output: 1
print_r($ll->toArray()); // output: [6, 4, 2]

$ll->delete($ll->getFirst()->getNext());
print_r($ll->toArray()); // output: [6, 2]

$llNew = LinkedList::merge(new LinkedList([-2, -1]), $ll, new LinkedList([1, 2]));
var_dump($llNew->count()); // output: 6
print_r($llNew->toArray()); // output: [-2, -1, 6, 2, 1, 2]

$ll->pushBefore($ll->getFirst(), -3);
$ll->pushAfter($ll->getLast(), 3);
print_r($llNew->toArray()); // output: [-3, -2, -1, 6, 2, 1, 2, 3]

/** 
 * @var LinkedListItem $id
 * @var mixed $item 
 */
foreach($ll as $id => $item) {
    // you can iterate collection
}

$ll->clear();
print_r($ll->toArray()); // output: []
```

### MappedCollection

Map-like data structure.

```php
use Smoren\Containers\Exceptions\MappedCollectionException;
use Smoren\Containers\Structs\MappedCollection;

$coll = new MappedCollection(['1' => ['id' => 1]]);
var_dump($coll->count()); // output: 1
print_r($coll->toArray()); // output: ['1' => ['id' => 1]]

$coll->add('2', ['id' => 2]);
var_dump($coll->count()); // output: 1
print_r($coll->toArray()); // output: ['1' => ['id' => 1], '2' => ['id' => 2]]

var_dump($coll->exist(1)); // output: true
var_dump($coll->exist(2)); // output: true
var_dump($coll->exist(3)); // output: false

try {
    $coll->add('1', ['id' => 1, 'extra' => 'test']);
} catch(MappedCollectionException $e) {
    // cannot silently replace existing value
}

print_r($coll->get(1)); // output: ['id' => 1]

$coll->replace('1', ['id' => 1, 'extra' => 'test']);
print_r($coll->get(1)); // output: ['id' => 1, 'extra' => 'test']

try {
    $coll->get('3');
} catch(MappedCollectionException $e) {
    // element with id = 3 does not exist so you cannot get it
}

/** 
 * @var string $id
 * @var mixed $item 
 */
foreach($coll as $id => $item) {
    // you can iterate collection
}

$coll->clear();
print_r($coll->toArray()); // output: []
```

### MappedLinkedList

LinkedList with mapping by id.

```php
use Smoren\Containers\Structs\MappedLinkedList;

$mll = new MappedLinkedList([]);
var_dump($mll->count()); // output: 0

$mll->pushBack(102, 2);
$mll->pushFront(101, 1);
$mll->pushBack(100, 0);
var_dump($mll->count()); // output: 4
print_r($mll->toArray()); // output: [101 => 1, 102 => 2, 100 => 0]]

$mll->sort(function($lhs, $rhs) {
    return $lhs > $rhs;
});
print_r($mll->toArray()); // output: [100 => 0, 101 => 1, 102 => 2]]

$mll->swap(100, 101);
print_r($mll->toArray()); // output: [101 => 1, 100 => 0, 102 => 2]]

var_dump($mll->popFront()); // output: [101, 1]
var_dump($mll->popBack()); // output: [102, 2]
print_r($mll->toArray()); // output: [100 => 0]

$mllNew = MappedLinkedList::merge(
    new MappedLinkedList([-999 => -99]),
    $mll,
    new MappedLinkedList([999 => 99])
);
var_dump($mllNew->count()); // output: 5
print_r($mllNew->toArray()); // output: [-999 => 99, 100 => 0, 999 => 9]

$mllNew->pushBefore(100, -101, -1);
$mllNew->pushAfter(100, 101, 1);
print_r($mllNew->toArray()); // output: [-999 => 99, -101 => -1, 100 => 0, 101 => 1, 999 => 9]

/** 
 * @var string $id
 * @var mixed $value
 */
foreach($mllNew as $id => $value) {
    // you can iterate collection
}

$mllNew->clear();
print_r($mllNew->toArray()); // output: []
```

### SortedLinkedList

```php
use Smoren\Containers\Structs\SortedLinkedList;
use Smoren\Containers\Exceptions\LinkedListException;

/**
 * Class IntegerSortedLinkedList
 */
class IntegerSortedLinkedList extends SortedLinkedList
{
    /**
     * @inheritDoc
     */
    protected function getComparator(): callable
    {
        return function(int $lhs, int $rhs) {
            return $lhs > $rhs;
        };
    }
}

$sll = new IntegerSortedLinkedList([2, 5, 1]);
var_dump($sll->count()); // output: 3
print_r($sll->toArray()); // output: [1, 2, 5]

$sll->insert(2);
$pos = $sll->insert(3);
var_dump($sll->count()); // output: 5
print_r($sll->toArray()); // output: [1, 2, 2, 3, 5]

var_dump($sll->delete($pos)); // output: 3
var_dump($sll->count()); // output: 4
print_r($sll->toArray()); // output: [1, 2, 2, 5]

var_dump($sll->popBack()); // output: 5
var_dump($sll->popFront()); // output: 1
var_dump($sll->count()); // output: 2
print_r($sll->toArray()); // output: [2, 2]

/** 
 * @var string $id
 * @var mixed $value
 */
foreach($sll as $id => $value) {
    // you can iterate collection
}

$sll->clear();
var_dump($sll->count()); // output: 0
print_r($sll->toArray()); // output: []

try {
    $sll->popBack();
} catch(LinkedListException $e) {
    // cannot pop when collection is empty
}

try {
    $sll->popFront();
} catch(LinkedListException $e) {
    // cannot pop when collection is empty
}
```

### SortedMappedLinkedList

LinkedList with presort and mapping.

```php
use Smoren\Containers\Exceptions\MappedLinkedListException;
use Smoren\Containers\Structs\LinkedListItem;
use Smoren\Containers\Structs\SortedMappedLinkedList;

$smll = new SortedMappedLinkedList([1 => -1, 10 => -10, 5 => -5]);
var_dump($smll->count()); // output: 3
print_r($smll->toArray()); // output: [1 => -1, 5 => -5, 10 => -10]

$smll->insert(3, -3);
var_dump($smll->count()); // output: 4
print_r($smll->toArray()); // output: [1 => -1, 3 => -3, 5 => -5, 10 => -10]

$smll->popBack();
$smll->popFront();
var_dump($smll->count()); // output: 2
print_r($smll->toArray()); // output: [3 => -3, 5 => -5]

/**
 * Class ArrayMappedSortedLinkedList
 */
class ArraySortedMappedLinkedList extends SortedMappedLinkedList
{
    /**
     * @inheritDoc
     */
    protected function getComparator(): callable
    {
        return function(array $lhs, array $rhs) {
            return $lhs['a'] > $rhs['a'];
        };
    }
}

$ll = new ArraySortedMappedLinkedList([
    -5 => ['a' => 5],
    -1 => ['a' => 1],
    -2 => ['a' => 2],
]);
var_dump($smll->count()); // output: 3
print_r($smll->toArray()); // output: [-1 => ['a' => 1], -2 => ['a' => 2], -5 => ['a' => 5]]

/** 
 * @var string $id
 * @var mixed $value
 */
foreach($smll as $id => $value) {
    // you can iterate collection
}

try {
    $smll->insert(-2, ['a' => 2, 'extra' => 'test']);
} catch(MappedLinkedListException $e) {
    // cannot simply replace existing element
}

/** @var LinkedListItem $pos */
$pos = $ll->delete(-1);
print_r($pos->getData()); // output: ['a' => 1]
var_dump($smll->count()); // output: 2
print_r($smll->toArray()); // output: [-2 => ['a' => 2], -5 => ['a' => 5]]

print_r($ll->popBack()); // output: [-5, ['a' => 5]]
print_r($ll->popFront()); // output: [-2, ['a' => 2]]
var_dump($smll->count()); // output: 0
print_r($smll->toArray()); // output: []
```

### Graph

Graph data structure with tools for traversing.

```php
use Smoren\Containers\Structs\Graph;

$graph = new Graph(
    [1 => 11, 2 => 22, 3 => 33, 4 => 44, 5 => 55, 6 => 66],
    [[1, 2, 'a'], [2, 3, 'a'], [3, 4, 'a'], [2, 5, 'b'], [5, 3, 'b'], [5, 6, 'c'], [6, 4, 'c']]
);

$paths = $graph->traverseBackward(4);
var_dump($paths); // output: 3
var_dump($paths[0]->toArray(true)); // output: [4, 3, 2, 1]
var_dump($paths[1]->toArray(true)); // output: [4, 3, 5, 2, 1]
var_dump($paths[2]->toArray(true)); // output: [4, 6, 5, 2, 1]

$paths = $graph->traverseForward(1);
var_dump(3, $paths); // output: 3
var_dump($paths[0]->toArray(true)); // output: [1, 2, 3, 4]
var_dump($paths[1]->toArray(true)); // output: [1, 2, 5, 3, 4]
var_dump($paths[2]->toArray(true)); // output: [1, 2, 5, 6, 4]

$paths = $graph->traverseForward(1, ['a', 'b']);
var_dump(2, $paths); // output: 
var_dump($paths[0]->toArray(true)); // output: [1, 2, 3, 4]
var_dump($paths[1]->toArray(true)); // output: [1, 2, 5, 3, 4]

$graph->insert(7, 77);
$graph->link(7, 1, 'a');

$paths = $graph->traverseBackward(7);
var_dump($paths); // output: 3
var_dump($paths[0]->toArray(true)); // output: [4, 3, 2, 1, 7]
var_dump($paths[1]->toArray(true)); // output: [4, 3, 5, 2, 1, 7]
var_dump($paths[2]->toArray(true)); // output: [4, 6, 5, 2, 1, 7]
var_dump($paths[2]->reverse()->toArray(true)); // output: [1, 2, 5, 6, 4, 7]
```
