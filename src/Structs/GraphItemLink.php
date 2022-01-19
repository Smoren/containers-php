<?php


namespace Smoren\Containers\Structs;


class GraphItemLink
{
    /**
     * @var GraphItem link target
     */
    protected GraphItem $target;
    /**
     * @var string link type
     */
    protected string $type;

    /**
     * GraphItemLink constructor.
     * @param GraphItem $target link target
     * @param string $type link type
     */
    public function __construct(GraphItem $target, string $type)
    {
        $this->target = $target;
        $this->type = $type;
    }

    /**
     * @return GraphItem
     */
    public function getTarget(): GraphItem
    {
        return $this->target;
    }

    /**
     * @param GraphItem $target
     * @return self
     */
    public function setTarget(GraphItem $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return GraphItemLink
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function toArray(): array
    {
        return ['target' => $this->target->getId(), 'type' => $this->type];
    }
}