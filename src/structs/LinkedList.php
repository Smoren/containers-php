<?php


namespace Smoren\Structs\structs;


use Countable;
use Exception;
use IteratorAggregate;
use Smoren\Helpers\LoopHelper;
use Smoren\Structs\exceptions\LinkedListException;

/**
 * Class LinkedList
 */
class LinkedList implements IteratorAggregate, Countable
{
    /**
     * @var LinkedListItem|null first element of list
     */
    protected ?LinkedListItem $first = null;
    /**
     * @var LinkedListItem|null last element of list
     */
    protected ?LinkedListItem $last = null;
    /**
     * @var int List size
     */
    protected int $length = 0;

    /**
     * Create new list by merging several another lists
     * @param LinkedList ...$lists
     * @return LinkedList
     */
    public static function merge(LinkedList ...$lists): LinkedList
    {
        $result = new LinkedList();

        foreach($lists as $list) {
            foreach($list as $value) {
                $result->pushBack($value);
            }
        }

        return $result;
    }

    /**
     * LinkedList constructor.
     * @param array $input default list content
     */
    public function __construct(array $input = [])
    {
        foreach($input as $item) {
            $this->pushBack($item);
        }
    }

    /**
     * Pushes element to front of list
     * @param mixed $data data value of element
     * @return LinkedListItem
     */
    public function pushFront($data): LinkedListItem
    {
        return $this->pushAfter(null, $data);
    }

    /**
     * Pushes element to back of list
     * @param mixed $data data value of element
     * @return LinkedListItem
     */
    public function pushBack($data): LinkedListItem
    {
        return $this->pushBefore(null, $data);
    }

    /**
     * Pushes new element to after target element position
     * @param LinkedListItem|null $item target element position
     * @param mixed $data data value of new element
     * @return LinkedListItem
     */
    public function pushAfter(?LinkedListItem $item, $data): LinkedListItem
    {
        $newItem = new LinkedListItem($data, null, null);

        if($item === null) {
            if($this->first !== null) {
                return $this->pushBefore($this->first, $data);
            }
            $this->first = $newItem;
            $this->last = $newItem;
        } else {
            $bufNext = $item->getNext();
            $item->setNext($newItem);
            $newItem->setPrev($item);
            $newItem->setNext($bufNext);

            if($bufNext === null) {
                $this->last = $newItem;
            } else {
                $bufNext->setPrev($newItem);
            }
        }

        $this->length++;

        return $newItem;
    }

    /**
     * Pushes new element to before target element position
     * @param LinkedListItem|null $item target element position
     * @param mixed $data data value of new element
     * @return LinkedListItem
     */
    public function pushBefore(?LinkedListItem $item, $data): LinkedListItem
    {
        $newItem = new LinkedListItem($data, null, null);

        if($item === null) {
            if($this->last !== null) {
                return $this->pushAfter($this->last, $data);
            }
            $this->first = $newItem;
            $this->last = $newItem;
        } else {
            $bufPrev = $item->getPrev();
            $item->setPrev($newItem);
            $newItem->setNext($item);
            $newItem->setPrev($bufPrev);

            if($bufPrev === null) {
                $this->first = $newItem;
            } else {
                $bufPrev->setNext($newItem);
            }
        }

        $this->length++;

        return $newItem;
    }

    /**
     * Removes element from the front of list
     * @return mixed data value of removed element
     * @throws LinkedListException
     */
    public function popFront()
    {
        if(!$this->length) {
            throw new LinkedListException('empty', LinkedListException::STATUS_EMPTY);
        }
        return $this->popFrontPosition()->getData();
    }

    /**
     * Removes element from the back of list
     * @return mixed data value of removed element
     * @throws LinkedListException
     */
    public function popBack()
    {
        return $this->popBackPosition()->getData();
    }

    /**
     * Removes element from the front of list
     * @return LinkedListItem removed element position
     * @throws LinkedListException
     */
    public function popFrontPosition(): LinkedListItem
    {
        if(!$this->length) {
            throw new LinkedListException('empty', LinkedListException::STATUS_EMPTY);
        }
        return $this->pop($this->first);
    }

    /**
     * Removes element from the back of list
     * @return LinkedListItem removed element position
     * @throws LinkedListException
     */
    public function popBackPosition(): LinkedListItem
    {
        if(!$this->length) {
            throw new LinkedListException('empty', LinkedListException::STATUS_EMPTY);
        }
        return $this->pop($this->last);
    }

    /**
     * Removes element from target element position
     * @param LinkedListItem $item target element position
     * @return LinkedListItem old position of element
     */
    public function pop(LinkedListItem $item): LinkedListItem
    {
        $prev = $item->getPrev();
        $next = $item->getNext();

        if($prev !== null) {
            $prev->setNext($next);
        } else {
            $this->first = $next;
        }

        if($next !== null) {
            $next->setPrev($prev);
        } else {
            $this->last = $prev;
        }

        $this->length--;

        return $item;
    }

    /**
     * Swaps two elements
     * @param LinkedListItem $lhs first element position
     * @param LinkedListItem $rhs second element position
     * @return $this
     */
    public function swap(LinkedListItem $lhs, LinkedListItem $rhs): self
    {
        $lhsPrev = $lhs->getPrev();
        $lhsNext = $lhs->getNext();
        $rhsPrev = $rhs->getPrev();
        $rhsNext = $rhs->getNext();

        if($lhsNext === $rhs) {
            $rhs->setNext($lhs);
            $lhs->setPrev($rhs);

            $rhs->setPrev($lhsPrev);
            $this->setNextFor($lhsPrev, $rhs);

            $lhs->setNext($rhsNext);
            $this->setPrevFor($rhsNext, $lhs);
        } elseif($rhsNext === $lhs) {
            $lhs->setNext($rhs);
            $rhs->setPrev($lhs);

            $lhs->setPrev($rhsPrev);
            $this->setNextFor($rhsPrev, $lhs);

            $rhs->setNext($lhsNext);
            $this->setPrevFor($lhsNext, $rhs);
        } else {
            $lhs->setNext($rhsNext);
            $this->setPrevFor($rhsNext, $lhs);

            $lhs->setPrev($rhsPrev);
            $this->setNextFor($rhsPrev, $lhs);

            $rhs->setNext($lhsNext);
            $this->setPrevFor($lhsNext, $rhs);

            $rhs->setPrev($lhsPrev);
            $this->setNextFor($lhsPrev, $rhs);
        }

        //$this->checkIntegrity();

        return $this;
    }

    /**
     * Clears list
     * @return $this
     */
    public function clear(): self
    {
        $this->first = null;
        $this->last = null;
        $this->length = 0;
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
        if($this->length <= 1) {
            return $this;
        }

        do {
            $flagStop = true;
            $it = $this->getIterator();
            $it->rewind();
            for($i=0; $i<$this->length-1; $i++) {
                $lhs = $it->current();
                /** @var LinkedListItem $lhsItem */
                $lhsItem = $it->key();

                $it->next();
                $rhs = $it->current();
                /** @var LinkedListItem $rhsItem */
                $rhsItem = $it->key();

                if($comparator($lhs, $rhs)) {
                    $this->swap($lhsItem, $rhsItem);
                    $flagStop = false;
                }
            }
        } while(!$flagStop);

        return $this;
    }

    /**
     * Converts list to array
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach($this as $val) {
            $result[] = $val;
        }

        return $result;
    }

    /**
     * Returns first position of list
     * @return LinkedListItem|null
     */
    public function getFirst(): ?LinkedListItem
    {
        return $this->first;
    }

    /**
     * Returns last position of list
     * @return LinkedListItem|null
     */
    public function getLast(): ?LinkedListItem
    {
        return $this->last;
    }

    /**
     * Returns list iterator
     * @return LinkedListIterator
     */
    public function getIterator(): LinkedListIterator
    {
        return new LinkedListIterator($this);
    }

    /**
     * Returns list size
     * @return int
     */
    public function count(): int
    {
        return $this->length;
    }

    /**
     * Inserts element before specified position
     * @param LinkedListItem|null $positionFor position to insert before
     * @param LinkedListItem $element element to insert
     * @return $this
     */
    protected function setPrevFor(?LinkedListItem $positionFor, LinkedListItem $element): self
    {
        if($positionFor !== null) {
            $positionFor->setPrev($element);
        } else {
            $this->last = $element;
        }

        return $this;
    }

    /**
     * Inserts element after specified position
     * @param LinkedListItem|null $positionFor position to insert after
     * @param LinkedListItem $element element to insert
     * @return $this
     */
    protected function setNextFor(?LinkedListItem $positionFor, LinkedListItem $element): self
    {
        if($positionFor !== null) {
            $positionFor->setNext($element);
        } else {
            $this->first = $element;
        }

        return $this;
    }

    /**
     * Returns positions array
     * @return LinkedListItem[]
     */
    public function getPositionsArray(?callable $mapper = null): array
    {
        $result = [];

        foreach($this as $pos => $val) {
            if(is_callable($mapper)) {
                $result[$mapper($pos)] = $pos;
            } else {
                $result[] = $pos;
            }
        }

        return $result;
    }

    /**
     * Returns index of position in list
     * @param LinkedListItem $position position to get index
     * @return int
     */
    public function getIndexOf(LinkedListItem $position): int
    {
        $index = 0;
        foreach($this as $pos => $val) {
            if($position === $pos) {
                break;
            }
            ++$index;
        }

        return $index;
    }

    /**
     * Debug method to check integrity of list structure
     * @return $this
     * @throws LinkedListException
     */
    public function checkIntegrity(): self
    {
        if($this->first->getPrev() !== null) {
            throw new LinkedListException(
                'integrity violation',
                LinkedListException::STATUS_INTEGRITY_VIOLATION,
                null,
                [
                    'reason' => 'first_hav_prev',
                    'index' => 0,
                    'position' => $this->first,
                    'value' => $this->first->getData(),
                ]
            );
        }

        if($this->last->getNext() !== null) {
            throw new LinkedListException(
                'integrity violation',
                LinkedListException::STATUS_INTEGRITY_VIOLATION,
                null,
                [
                    'reason' => 'last_get_next',
                    'index' => $this->count()-1,
                    'position' => $this->last,
                    'value' => $this->last->getData(),
                ]
            );
        }

        $objMap = [];
        $index = 0;
        foreach($this as $pos => $val) {
            $objId = spl_object_id($pos);
            if(isset($objMap[$objId])) {
                throw new LinkedListException(
                    'integrity violation',
                    LinkedListException::STATUS_INTEGRITY_VIOLATION,
                    null,
                    [
                        'reason' => 'duplicate',
                        'index' => $index,
                        'position' => $pos,
                        'value' => $val,
                    ]
                );
            }
            ++$index;
            $objMap[$objId] = $pos;
        }

        return $this;
    }

    /**
     * Clones object
     */
    public function __clone()
    {
        $buf = [];
        foreach($this as $pos => $val) {
            $buf[] = clone $pos;
        }

        LoopHelper::eachPair($buf, function(LinkedListItem $lhs, LinkedListItem $rhs) {
            $lhs->setNext($rhs);
            $rhs->setPrev($lhs);
        });

        if(count($buf)) {
            $this->first = $buf[0];
            $this->last = $buf[count($buf)-1];
        }
    }
}