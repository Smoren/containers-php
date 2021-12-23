<?php


namespace Smoren\Containers\Tests\Unit\Utility;


use Smoren\Containers\Structs\SortedLinkedList;

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