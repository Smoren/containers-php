<?php


namespace Smoren\Containers\Structs;

/**
 * Class LinkedListItem
 */
class LinkedListItem
{
    /**
     * @var mixed data value
     */
    protected $data;
    /**
     * @var mixed|null extra data
     */
    protected $extra = null;
    /**
     * @var LinkedListItem|null previous element position
     */
    protected ?LinkedListItem $prev;
    /**
     * @var LinkedListItem|null next element position
     */
    protected ?LinkedListItem $next;

    /**
     * LinkedListItem constructor.
     * @param mixed $data data value
     * @param LinkedListItem|null $prev previous element position
     * @param LinkedListItem|null $next next element position
     * @param null $extra extra data
     */
    public function __construct($data, ?LinkedListItem $prev, ?LinkedListItem $next, $extra = null)
    {
        $this->data = $data;
        $this->prev = $prev;
        $this->next = $next;
        $this->extra = $extra;
    }

    /**
     * Returns data value
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets data value
     * @param mixed $data data value
     * @return $this
     */
    public function setData($data): LinkedListItem
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns previous element position
     * @return LinkedListItem|null
     */
    public function getPrev(): ?LinkedListItem
    {
        return $this->prev;
    }

    /**
     * Returns next element position
     * @return LinkedListItem|null
     */
    public function getNext(): ?LinkedListItem
    {
        return $this->next;
    }

    /**
     * Sets previous element position
     * @param LinkedListItem|null $item previous element position
     * @return $this
     */
    public function setPrev(?LinkedListItem $item): self
    {
        $this->prev = $item;
        return $this;
    }

    /**
     * Sets next element position
     * @param LinkedListItem|null $item
     * @return $this
     */
    public function setNext(?LinkedListItem $item): self
    {
        $this->next = $item;
        return $this;
    }

    /**
     * Gets extra data
     * @return mixed|null
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Sets extra data
     * @param mixed $extra extra data
     * @return $this
     */
    public function setExtra($extra): self
    {
        $this->extra = $extra;
        return $this;
    }

    /**
     * Clones object
     */
    public function __clone()
    {
        if(is_object($this->data)) {
            $this->data = clone $this->data;
        }

        if(is_object($this->extra)) {
            $this->extra = clone $this->extra;
        }

        $this->prev = null;
        $this->next = null;
    }
}
