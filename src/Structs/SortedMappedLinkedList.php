<?php


namespace Smoren\Structs\Structs;


use Closure;
use Countable;
use Exception;
use IteratorAggregate;
use Smoren\Structs\Exceptions\LinkedListException;
use Smoren\Structs\Exceptions\MappedCollectionException;
use Smoren\Structs\Exceptions\MappedLinkedListException;
use Smoren\Structs\Exceptions\SortedLinkedListException;

/**
 * Class SortedMappedLinkedList
 */
class SortedMappedLinkedList implements IteratorAggregate, Countable
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
        return $this->list->pushAfter($this->findLeftPosition($id, $data), $id, $data);
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
     * Returns source list
     * @return MappedLinkedList
     */
    public function getList(): MappedLinkedList
    {
        return $this->list;
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
     * Returns element with target element ID
     * @param string $id target element ID
     * @return mixed element data value
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function get(string $id)
    {
        return $this->list->get($id);
    }

    /**
     * Returns element position from target element position
     * @param string $id target element ID
     * @return LinkedListItem position of element
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function getPosition(string $id): LinkedListItem
    {
        return $this->list->getPosition($id);
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
    protected function getComparator(): callable
    {
        return function($lhs, $rhs, LinkedListItem $lhsPos, LinkedListItem $rhsPos) {
            return $lhsPos->getExtra() > $rhsPos->getExtra();
        };
    }

    /**
     * Returns position max element which is less than argument (using comparator)
     * @param string $id element ID
     * @param mixed $data element data value
     * @return LinkedListItem|null
     * @throws MappedLinkedListException|MappedCollectionException
     */
    protected function findLeftPosition(string $id, $data): ?string
    {
        $position = null;
        $possiblePosition = new LinkedListItem($data, null, null, $id);
        foreach($this->list as $id => $val) {
            if(!($this->comparator)($data, $val, $possiblePosition, $this->getPosition($id))) {
                break;
            }
            $position = $id;
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