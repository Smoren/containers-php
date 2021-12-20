<?php


namespace Smoren\Structs\structs;


use ArrayIterator;
use Countable;
use IteratorAggregate;
use Smoren\Structs\exceptions\MappedCollectionException;

/**
 * Class MappedCollection
 */
class MappedCollection implements IteratorAggregate, Countable
{
    /**
     * @var array data map
     */
    protected array $itemsMap;

    /**
     * MappedCollection constructor.
     * @param array|null $itemsMap default data map
     */
    public function __construct(?array $itemsMap = null)
    {
        $this->itemsMap = $itemsMap ?? [];
    }

    /**
     * Adds element to collection
     * @param string $id element ID
     * @param mixed $item data value of element
     * @return $this
     * @throws MappedCollectionException
     */
    public function add(string $id, $item): self
    {
        $this->checkNotExist($id);
        $this->itemsMap[$id] = $item;
        return $this;
    }

    /**
     * Removes element from collection by ID
     * @param string $id element ID
     * @return $this
     * @throws MappedCollectionException
     */
    public function delete(string $id): self
    {
        $this->checkExist($id);
        unset($this->itemsMap[$id]);
        return $this;
    }

    /**
     * Removes element from collection by ID
     * @param string $id element ID
     * @param mixed $data data value
     * @return $this
     * @throws MappedCollectionException
     */
    public function replace(string $id, $data): self
    {
        $this->delete($id);
        $this->add($id, $data);
        return $this;
    }

    /**
     * Returns element by ID
     * @param string $id element ID
     * @param null $default default value if element is not found
     * @return mixed data value of element
     * @throws MappedCollectionException
     */
    public function get(string $id, $default = null)
    {
        try {
            $this->checkExist($id);
        } catch(MappedCollectionException $e) {
            if($default !== null) {
                return $default;
            } else {
                throw $e;
            }
        }

        return $this->itemsMap[$id];
    }

    /**
     * Returns true if element with such ID exists in collection
     * @param string $id element ID
     * @return bool
     */
    public function exist(string $id): bool
    {
        return isset($this->itemsMap[$id]);
    }

    /**
     * Checks if element with such ID exists
     * @param string $id element ID
     * @return $this
     * @throws MappedCollectionException
     */
    public function checkExist(string $id): self
    {
        if(!$this->exist($id)) {
            throw new MappedCollectionException(
                "ID '{$id}' not exists", MappedCollectionException::STATUS_ID_NOT_EXIST
            );
        }
        return $this;
    }

    /**
     * Checks if element with such ID does not exist
     * @param string $id element ID
     * @return $this
     * @throws MappedCollectionException
     */
    public function checkNotExist(string $id): self
    {
        if($this->exist($id)) {
            throw new MappedCollectionException(
                "ID '{$id}' exists", MappedCollectionException::STATUS_ID_EXIST
            );
        }
        return $this;
    }

    /**
     * Returns map as associative array
     * @return array
     */
    public function getMap(): array
    {
        return $this->itemsMap;
    }

    /**
     * Converts collection to array
     * @return array
     */
    public function toArray(): array
    {
        return array_values($this->itemsMap);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->itemsMap);
    }

    /**
     * Sorts map via comparator callback
     * @param callable $comparator comparator callback
     * @return $this
     */
    public function sort(callable $comparator): self
    {
        uasort($this->itemsMap, $comparator);
        return $this;
    }

    /**
     * Clears collection
     * @return $this
     */
    public function clear(): self
    {
        $this->itemsMap = [];
        return $this;
    }

    /**
     * Magic method for cloning
     */
    public function __clone()
    {
        $itemsMap = $this->itemsMap;
        $this->itemsMap = [];
        foreach($itemsMap as $id => $value) {
            if(is_object($value)) {
                $this->itemsMap[$id] = clone $value;
            } else {
                $this->itemsMap[$id] = $value;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->itemsMap);
    }
}
