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
     * @param bool $clone need clone object
     * @param bool $cloneItems need clone items
     * @return GraphTraversePath
     */
    public function reverse(bool $clone = false, bool $cloneItems = false): self
    {
        if($clone) {
            $path = clone $this;
        } else {
            $path = $this;
        }

        $path->links = array_reverse($path->links);

        foreach($path->links as $link) {
            $link->swap($cloneItems);
        }

        return $path;
    }

    /**
     * @inheritDoc
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->links);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
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

    /**
     * Clones object
     */
    public function __clone()
    {
        foreach($this->links as $i => $link) {
            $this->links[$i] = clone $link;
        }
    }
}
