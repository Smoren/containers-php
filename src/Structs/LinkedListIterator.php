<?php


namespace Smoren\Containers\Structs;


use Iterator;

/**
 * Class LinkedListIterator
 */
class LinkedListIterator implements Iterator
{
    /**
     * @var LinkedList iterator owner
     */
    protected LinkedList $owner;
    /**
     * @var LinkedListItem|null current position
     */
    protected ?LinkedListItem $position = null;

    /**
     * LinkedListIterator constructor.
     * @param LinkedList $owner iterator owner
     */
    public function __construct(LinkedList $owner)
    {
        $this->owner = $owner;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->position->getData();
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->position = $this->position->getNext();
    }

    /**
     * @inheritDoc
     * @return LinkedListItem
     */
    public function key(): LinkedListItem
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->position !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = $this->owner->getFirst();
    }
}