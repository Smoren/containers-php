<?php


namespace Smoren\Structs\structs;


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
    public function next()
    {
        $this->position = $this->position->getNext();
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->position !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->position = $this->owner->getFirst();
    }
}