<?php


namespace Smoren\Containers\Structs;


use ArrayIterator;
use Countable;
use IteratorAggregate;

class GraphTraversePath implements IteratorAggregate, Countable
{
    /**
     * @var GraphLink[]
     */
    protected array $links;

    /**
     * GraphTraversePath constructor.
     * @param GraphLink[] $links
     */
    public function __construct(array $links)
    {
        $this->links = $links;
    }

    /**
     * Returns first item of path
     * @return GraphItem
     */
    public function getFirstItem(): GraphItem
    {
        return $this->links[0]->getLeftItem();
    }

    /**
     * Returns last item of path
     * @return GraphItem
     */
    public function getLastItem(): GraphItem
    {
        return $this->links[count($this->links)-1]->getRightItem();
    }

    /**
     * Reverse path
     * @return $this
     */
    public function reverse(): self
    {
        $this->links = array_reverse($this->links);

        foreach($this->links as $link) {
            $link->swap();
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->links);
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->links);
    }

    /**
     * Representates as array
     * @param bool $itemIdsOnly
     * @return array
     */
    public function toArray(bool $itemIdsOnly = false): array
    {
        $result = [];

        foreach($this->links as $link) {
            if($itemIdsOnly) {
                $result[] = $link->getLeftItem()->getId();
            } else {
                $result[] = $link->toArray();
            }
        }

        if($itemIdsOnly && isset($link)) {
            $result[] = $link->getRightItem()->getId();
        }

        return $result;
    }
}