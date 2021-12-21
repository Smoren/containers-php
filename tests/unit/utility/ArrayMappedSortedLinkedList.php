<?php


namespace Smoren\Structs\tests\unit\utility;


use Smoren\Structs\structs\SortedMappedLinkedList;

/**
 * Class ArrayMappedSortedLinkedList
 */
class ArrayMappedSortedLinkedList extends SortedMappedLinkedList
{
    /**
     * @inheritDoc
     */
    protected function getComparator(): callable
    {
        return function(array $lhs, array $rhs) {
            return $lhs['id'] > $rhs['id'];
        };
    }
}