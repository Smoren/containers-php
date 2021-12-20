<?php


namespace Smoren\Structs\tests\unit;


use Smoren\Structs\structs\SortedLinkedList;

class IntegerSortedLinkedList extends SortedLinkedList
{
    protected function getComparator(): callable
    {
        return function(int $lhs, int $rhs) {
            return $lhs > $rhs;
        };
    }
}