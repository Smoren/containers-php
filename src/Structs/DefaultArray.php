<?php

namespace Smoren\Containers\Structs;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class DefaultArray implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var array
     */
    protected array $source;
    /**
     * @var callable|mixed
     */
    protected $default;

    public function __construct(array $source, $default)
    {
        $this->source = $source;
        $this->default = $default;
    }

    public function offsetExists($offset): bool
    {
        return true;
    }

    public function offsetGet($offset)
    {
        if(!isset($this->source[$offset])) {
            $this->source[$offset] = $this->getDefault($offset);
        }

        return $this->source[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->source[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->source[$offset]);
    }

    public function count(): int
    {
        return count($this->source);
    }

    public function getIterator(): iterable
    {
        return new ArrayIterator($this->source);
    }

    protected function getDefault($offset)
    {
        if(is_callable($this->default)) {
            return ($this->default)($offset);
        }

        return $offset;
    }
}
