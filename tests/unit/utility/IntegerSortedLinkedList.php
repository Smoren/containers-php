<?php


namespace Smoren\Structs\tests\unit\utility;


use Smoren\Structs\Structs\SortedLinkedList;

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