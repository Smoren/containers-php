<?php


namespace Smoren\Structs\structs;


use Closure;
use Countable;
use Exception;
use IteratorAggregate;
use Smoren\Structs\exceptions\LinkedListException;
use Smoren\Structs\exceptions\MappedCollectionException;
use Smoren\Structs\exceptions\MappedLinkedListException;
use Smoren\Structs\exceptions\SortedLinkedListException;

/**
 * Class SortedMappedLinkedList
 */
abstract class SortedMappedLinkedList implements IteratorAggregate, Countable
{
    /**
     * @var MappedLinkedList data source
     */
    protected MappedLinkedList $list;
    /**
     * @var Closure|callable comparator
     */
    protected Closure $comparator;

    /**
     * SortedLinkedList constructor.
     * @param array|MappedLinkedList $input default data list
     * @throws Exception
     */
    public function __construct($input = [])
    {
        if($input instanceof MappedLinkedList) {
            $this->list = $input;
        } elseif(is_array($input)) {
            $this->list = new MappedLinkedList($input);
        } else {
            $linkedListType = MappedLinkedList::class;
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
     * @param string $id element ID
     * @param mixed $data element data value
     * @return LinkedListItem
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function insert(string $id, $data): LinkedListItem
    {
        return $this->list->pushAfter($this->findLeftPosition($data), $id, $data);
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
     * @return MappedLinkedListIterator
     */
    public function getIterator(): MappedLinkedListIterator
    {
        return $this->list->getIterator();
    }

    /**
     * Removes element from the front of list
     * @return array [id, value]
     * @throws MappedLinkedListException|MappedCollectionException|LinkedListException
     */
    public function popFront(): array
    {
        return $this->list->popFront();
    }

    /**
     * Removes element from the back of list
     * @return array [id, value]
     * @throws MappedLinkedListException|MappedCollectionException|LinkedListException
     */
    public function popBack(): array
    {
        return $this->list->popBack();
    }

    /**
     * Removes element from target element position
     * @param string $id target element ID
     * @return LinkedListItem old position of element
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function pop(string $id): LinkedListItem
    {
        return $this->list->pop($id);
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
    protected function findLeftPosition($data): ?string
    {
        $position = null;
        foreach($this->list as $id => $val) {
            if(!($this->comparator)($data, $val)) {
                break;
            }
            $position = $id;
        }

        return $position;
    }
}