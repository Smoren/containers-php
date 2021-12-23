<?php


namespace Smoren\Structs\Structs;

use Countable;
use Exception;
use IteratorAggregate;
use Smoren\Structs\Exceptions\LinkedListException;
use Smoren\Structs\Exceptions\MappedCollectionException;
use Smoren\Structs\Exceptions\MappedLinkedListException;

class MappedLinkedList implements IteratorAggregate, Countable
{
    /**
     * @var LinkedList
     */
    protected LinkedList $list;
    /**
     * @var MappedCollection
     */
    protected MappedCollection $positionsMap;

    /**
     * Create new list by merging several another lists
     * @param MappedLinkedList ...$lists
     * @return MappedLinkedList
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public static function merge(MappedLinkedList ...$lists): MappedLinkedList
    {
        $result = new MappedLinkedList();

        foreach($lists as $list) {
            foreach($list as $id => $value) {
                $result->pushBack($id, $value);
            }
        }

        return $result;
    }

    /**
     * MappedLinkedList constructor.
     * @param array $inputMap
     * @param LinkedList|null $listObject
     * @param MappedCollection|null $positionMap
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function __construct(
        array $inputMap = [], ?LinkedList $listObject = null, ?MappedCollection $positionMap = null
    )
    {
        $this->list = $listObject ?? new LinkedList();
        $this->positionsMap = $positionMap ?? new MappedCollection();

        foreach($inputMap as $id => $value) {
            $this->pushBack($id, $value);
        }
    }

    /**
     * Pushes element to front of list
     * @param string $id element ID
     * @param mixed $data data value of element
     * @return LinkedListItem
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function pushFront(string $id, $data): LinkedListItem
    {
        $this->checkNotExist($id);

        $position = $this->list->pushFront($data);
        $position->setExtra($id);
        $this->positionsMap->add($id, $position);

        return $position;
    }

    /**
     * Pushes element to back of list
     * @param string $id element ID
     * @param mixed $data data value of element
     * @return LinkedListItem
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function pushBack(string $id, $data): LinkedListItem
    {
        $this->checkNotExist($id);

        $position = $this->list->pushBack($data);
        $position->setExtra($id);
        $this->positionsMap->add($id, $position);

        return $position;
    }

    /**
     * Pushes new element to after target element position
     * @param string|null $idAfter element ID
     * @param string $id new element ID
     * @param mixed $data data value of new element
     * @return LinkedListItem
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function pushAfter(?string $idAfter, string $id, $data): LinkedListItem
    {
        if($idAfter !== null) {
            $this->checkExist($idAfter);
            $position = $this->positionsMap->get($idAfter);
        } else {
            $position = null;
        }
        $this->checkNotExist($id);
        $newPosition = $this->list->pushAfter($position, $data);
        $newPosition->setExtra($id);
        $this->positionsMap->add($id, $newPosition);

        return $newPosition;
    }

    /**
     * Pushes new element to before target element position
     * @param string|null $idBefore element ID
     * @param string $id new element ID
     * @param mixed $data data value of new element
     * @return LinkedListItem
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function pushBefore(?string $idBefore, string $id, $data): LinkedListItem
    {
        if($idBefore !== null) {
            $this->checkExist($idBefore);
            $position = $this->positionsMap->get($idBefore);
        } else {
            $position = null;
        }
        $this->checkNotExist($id);
        $newPosition = $this->list->pushBefore($position, $data);
        $newPosition->setExtra($id);
        $this->positionsMap->add($id, $newPosition);

        return $newPosition;
    }

    /**
     * Removes element from the front of list
     * @return array [id, value]
     * @throws MappedLinkedListException|LinkedListException|MappedCollectionException
     */
    public function popFront(): array
    {
        $this->checkNotEmpty();

        $position = $this->list->popFrontPosition();
        $id = $position->getExtra();
        $this->positionsMap->delete($id);

        return [$id, $position->getData()];
    }

    /**
     * Removes element from the back of list
     * @return array [id, value]
     * @throws MappedLinkedListException|LinkedListException|MappedCollectionException
     */
    public function popBack(): array
    {
        $this->checkNotEmpty();

        $position = $this->list->popBackPosition();
        $id = $position->getExtra();
        $this->positionsMap->delete($id);

        return [$id, $position->getData()];
    }

    /**
     * Removes element from target element ID
     * @param string $id element ID
     * @return LinkedListItem old position of element
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function pop(string $id): LinkedListItem
    {
        $this->checkExist($id);

        $position = $this->positionsMap->get($id);
        $this->positionsMap->delete($id);

        return $this->list->pop($position);
    }

    /**
     * Returns element with target element ID
     * @param string $id element ID
     * @return mixed element data value
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function get(string $id)
    {
        return $this->getPosition($id)->getData();
    }

    /**
     * Returns element position with target element ID
     * @param string $id element ID
     * @return LinkedListItem position of element
     * @throws MappedLinkedListException|MappedCollectionException
     */
    public function getPosition(string $id): LinkedListItem
    {
        $this->checkExist($id);
        return $this->positionsMap->get($id);
    }

    /**
     * Swaps two elements
     * @param string $lhsId first element ID
     * @param string $rhsId second element ID
     * @return $this
     * @throws MappedCollectionException|MappedLinkedListException
     */
    public function swap(string $lhsId, string $rhsId): self
    {
        $this->checkExist($lhsId);
        $this->checkExist($rhsId);

        $this->list->swap($this->positionsMap->get($lhsId), $this->positionsMap->get($rhsId));

        return $this;
    }

    /**
     * Clears collection
     * @return $this
     */
    public function clear(): self
    {
        $this->list->clear();
        $this->positionsMap->clear();
        return $this;
    }

    /**
     * Sorts element via comparator callback
     * @param callable $comparator comparator callback
     * @return $this
     * @throws Exception
     */
    public function sort(callable $comparator): self
    {
        $this->list->sort($comparator);
        return $this;
    }

    /**
     * Converts list to array
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        /**
         * @var LinkedListItem $position
         * @var mixed $value
         */
        foreach($this->list as $position => $value) {
            $result[$position->getExtra()] = $value;
        }

        return $result;
    }

    /**
     * Returns true if element with such ID exists in collection
     * @param string $id element ID
     * @return bool
     */
    public function exist(string $id): bool
    {
        return $this->positionsMap->exist($id);
    }

    /**
     * Checks if element with such ID exists
     * @param string $id element ID
     * @return $this
     * @throws MappedLinkedListException
     */
    public function checkExist(string $id): self
    {
        if(!$this->exist($id)) {
            throw new MappedLinkedListException(
                "ID '{$id}' not exists", MappedLinkedListException::STATUS_ID_NOT_EXIST
            );
        }
        return $this;
    }

    /**
     * Checks if element with such ID not exists
     * @param string $id element ID
     * @return $this
     * @throws MappedLinkedListException
     */
    public function checkNotExist(string $id): self
    {
        if($this->exist($id)) {
            throw new MappedLinkedListException(
                "ID '{$id}' exists", MappedLinkedListException::STATUS_ID_EXIST
            );
        }
        return $this;
    }

    /**
     * Checks if collection is not empty
     * @return $this
     * @throws MappedLinkedListException
     */
    public function checkNotEmpty(): self
    {
        if(!$this->count()) {
            throw new MappedLinkedListException(
                "collection is empty", MappedLinkedListException::STATUS_EMPTY
            );
        }
        return $this;
    }

    /**
     * Returns LinkedList object of collection
     * @return LinkedList
     */
    public function getList(): LinkedList
    {
        return $this->list;
    }

    /**
     * Returns LinkedList object of collection
     * @return MappedCollection
     */
    public function getPositionsMap(): MappedCollection
    {
        return $this->positionsMap;
    }

    /**
     * @inheritDoc
     * @return MappedLinkedListIterator
     */
    public function getIterator(): MappedLinkedListIterator
    {
        return new MappedLinkedListIterator($this);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->list->count();
    }

    /**
     * Clones object
     */
    public function __clone()
    {
        $this->list = clone $this->list;
        $this->positionsMap = clone $this->positionsMap;
        $this->positionsMap->replaceAll($this->list->getPositionsArray(function(LinkedListItem $item) {
            return $item->getExtra();
        }));
    }
}