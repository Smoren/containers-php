<?php


namespace Smoren\Structs\structs;


use Closure;
use Exception;
use Smoren\Structs\exceptions\SortedLinkedListException;

abstract class SortedLinkedList
{
    protected LinkedList $list;
    protected Closure $comparator;

    /**
     * SortedLinkedList constructor.
     * @param array|LinkedList $input
     * @throws Exception
     */
    public function __construct($input = [])
    {
        if($input instanceof LinkedList) {
            $this->list = $input;
        } elseif(is_array($input)) {
            $this->list = new LinkedList($input);
        } else {
            $linkedListType = LinkedList::class;
            $givenType = get_class($input);
            throw new SortedLinkedListException(
                "input must be instance of array or $linkedListType, given {$givenType}",
                SortedLinkedListException::STATUS_BAD_LINKED_LIST_TYPE
            );
        }

        $this->comparator = $this->getComparator();
        $this->list->sort($this->comparator);
    }

    public function insert($data): LinkedListItem
    {
        return $this->list->pushAfter($this->findLeftPosition($data), $data);
    }

    abstract protected function getComparator(): callable;

    protected function findLeftPosition($data): LinkedListItem
    {
        $position = null;
        foreach($this->list as $pos => $val) {
            if(!($this->comparator)($val, $data)) {
                break;
            }
            $position = $pos;
        }

        return $position;
    }
}