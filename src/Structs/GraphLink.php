<?php

namespace Smoren\Containers\Structs;

class GraphLink
{
    /**
     * @var GraphItem left item
     */
    protected GraphItem $leftItem;
    /**
     * @var GraphItem right item
     */
    protected GraphItem $rightItem;
    /**
     * @var string link type
     */
    protected string $type;

    /**
     * GraphItemLink constructor.
     * @param GraphItem $leftItem left item
     * @param GraphItem $rightItem right item
     * @param string $type link type
     */
    public function __construct(GraphItem $leftItem, GraphItem $rightItem, string $type)
    {
        $this->leftItem = $leftItem;
        $this->rightItem = $rightItem;
        $this->type = $type;
    }

    /**
     * Swaps left and right items
     * @param bool $clone need clone items
     * @return $this
     */
    public function swap(bool $clone = false): self
    {
        $lhs = $clone ? clone $this->leftItem : $this->leftItem;
        $rhs = $clone ? clone $this->rightItem : $this->rightItem;

        $this->leftItem = $rhs;
        $this->rightItem = $lhs;

        return $this;
    }

    /**
     * @return GraphItem
     */
    public function getLeftItem(): GraphItem
    {
        return $this->leftItem;
    }

    /**
     * @return GraphItem
     */
    public function getRightItem(): GraphItem
    {
        return $this->rightItem;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Clones items
     * @return $this
     */
    public function cloneItems(): self
    {
        $this->leftItem = clone $this->leftItem;
        $this->rightItem = clone $this->rightItem;

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return "{$this->leftItem->getId()}_{$this->rightItem->getId()}";
    }

    /**
     * Representates as array
     * @param bool $itemIdsOnly
     * @return array
     */
    public function toArray(bool $itemIdsOnly = false): array
    {
        if($itemIdsOnly) {
            return [$this->leftItem->getId(), $this->rightItem->getId(), $this->type];
        }

        return [$this->leftItem->toArray(), $this->rightItem->toArray(), $this->type];
    }
}
