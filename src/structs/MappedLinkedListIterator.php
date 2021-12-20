<?php


namespace Smoren\Structs\structs;


use Iterator;

/**
 * Class MappedLinkedListIterator
 */
class MappedLinkedListIterator implements Iterator
{
    /**
     * @var MappedLinkedList iterator owner
     */
    protected MappedLinkedList $owner;
    /**
     * @var LinkedListItem|null current position
     */
    protected ?LinkedListItem $position = null;

    /**
     * LinkedListIterator constructor.
     * @param MappedLinkedList $owner iterator owner
     */
    public function __construct(MappedLinkedList $owner)
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
        return $this->position->getExtra();
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
        $this->position = $this->owner->getList()->getFirst();
    }
}