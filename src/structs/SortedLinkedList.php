<?php


namespace Smoren\Structs\structs;


use Closure;
use Countable;
use Exception;
use IteratorAggregate;
use Smoren\Structs\exceptions\LinkedListException;
use Smoren\Structs\exceptions\SortedLinkedListException;

abstract class SortedLinkedList implements IteratorAggregate, Countable
{
    /**
     * @var LinkedList data source
     */
    protected LinkedList $list;
    /**
     * @var Closure|callable comparator
     */
    protected Closure $comparator;

    /**
     * SortedLinkedList constructor.
     * @param array|LinkedList $input default data list
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

    /**
     * Inserts element into collection
     * @param mixed $data element data value
     * @return LinkedListItem
     */
    public function insert($data): LinkedListItem
    {
        return $this->list->pushAfter($this->findLeftPosition($data), $data);
    }

    /**
     * Converts collection to array
     * @return array
     */
    public function toArray(): array
    {
        return $this->list->toArray();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->list->count();
    }

    /**
     * @inheritDoc
     * @return LinkedListIterator
     */
    public function getIterator(): LinkedListIterator
    {
        return $this->list->getIterator();
    }

    /**
     * Returns source list
     * @return LinkedList
     */
    public function getList(): LinkedList
    {
        return $this->list;
    }

    /**
     * Removes element from the front of list
     * @return mixed data value of removed element
     * @throws LinkedListException
     */
    public function popFront()
    {
        return $this->list->popFront();
    }

    /**
     * Removes element from the back of list
     * @return mixed data value of removed element
     * @throws LinkedListException
     */
    public function popBack()
    {
        return $this->list->popBack();
    }

    /**
     * Removes element from target element position
     * @param LinkedListItem $item target element position
     * @return LinkedListItem old position of element
     */
    public function pop(LinkedListItem $item): LinkedListItem
    {
        return $this->list->pop($item);
    }

    /**
     * Clears list
     * @return $this
     */
    public function clear(): self
    {
        $this->list->clear();
        return $this;
    }

    /**
     * Returns comparator function for sorting and position search
     * @return callable
     */
    abstract protected function getComparator(): callable;

    /**
     * Returns position max element which is less than argument (using comparator)
     * @param mixed $data element data value
     * @return LinkedListItem|null
     */
    protected function findLeftPosition($data): ?LinkedListItem
    {
        $position = null;
        foreach($this->list as $pos => $val) {
            if(!($this->comparator)($data, $val)) {
                break;
            }
            $position = $pos;
        }

        return $position;
    }

    /**
     * Clones object
     */
    public function __clone()
    {
        $this->list = clone $this->list;
    }
}