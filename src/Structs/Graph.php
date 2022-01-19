<?php


namespace Smoren\Containers\Structs;


use Countable;
use Smoren\Containers\Exceptions\GraphException;

class Graph implements Countable
{
    /**
     * @var GraphItem[]
     */
    protected array $itemsMap = [];

    /**
     * Graph constructor.
     * @param array $dataMap map of items data by ID ([ID => data, ...])
     * @param array $links items links ([[leftItemId, rightItemId, linkType], ...])
     * @throws GraphException
     */
    public function __construct(array $dataMap = [], array $links = [])
    {
        foreach($dataMap as $id => $data) {
            $this->insert($id, $data);
        }

        foreach($links as $link) {
            $this->link(...$link);
        }
    }

    /**
     * Inserts item into graph
     * @param string $id item ID
     * @param mixed $data item data
     * @return GraphItem
     * @throws GraphException
     */
    public function insert(string $id, $data): GraphItem
    {
        $this->checkNotExist($id);

        $item = new GraphItem($id, $data);
        $this->itemsMap[$id] = $item;

        return $item;
    }

    /**
     * Deletes item from graph
     * @param string $id item ID
     * @return mixed
     * @throws GraphException
     */
    public function delete(string $id)
    {
        $item = $this->getItem($id);

        foreach($item->getPrevItems() as $prevItem) {
            $prevItem->deleteNextItem($id);
        }

        foreach($item->getNextItems() as $nextItem) {
            $nextItem->deletePrevItem($id);
        }

        unset($this->itemsMap[$id]);

        return $item->getData();
    }

    /**
     * Makes link of 2 items
     * @param string $lhsId left item ID
     * @param string $rhsId right item ID
     * @return $this
     * @throws GraphException
     */
    public function link(string $lhsId, string $rhsId, string $type = 'default'): self
    {
        $lhs = $this->get($lhsId);
        $rhs = $this->get($rhsId);

        $lhs->addNextItem($rhs, $type);
        $rhs->addPrevItem($lhs, $type);

        return $this;
    }

    /**
     * Deletes link of 2 items
     * @param string $lhsId left item ID
     * @param string $rhsId right item ID
     * @param string|null $type link type
     * @return $this
     * @throws GraphException
     */
    public function unlink(string $lhsId, string $rhsId, ?string $type = null): self
    {
        $lhs = $this->get($lhsId);
        $rhs = $this->get($rhsId);

        $lhs->deleteNextItem($rhs->getId(), $type);
        $rhs->deletePrevItem($lhs->getId(), $type);

        return $this;
    }

    /**
     * Returns item data by ID
     * @param string $id item ID
     * @param null $default default value if item is not found
     * @return mixed data value of item
     * @throws GraphException
     */
    public function get(string $id, $default = null)
    {
        try {
            $this->checkExist($id);
        } catch(GraphException $e) {
            if($default !== null) {
                return $default;
            } else {
                throw $e;
            }
        }

        return $this->itemsMap[$id];
    }

    /**
     * Returns item by ID
     * @param string $id item ID
     * @param null $default default value if item is not found
     * @return mixed data value of item
     * @throws GraphException
     */
    public function getItem(string $id, $default = null): GraphItem
    {
        try {
            $this->checkExist($id);
        } catch(GraphException $e) {
            if($default !== null) {
                return $default;
            } else {
                throw $e;
            }
        }

        return $this->itemsMap[$id];
    }

    /**
     * Returns true if item with such ID exists in graph
     * @param string $id item ID
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
     * @throws GraphException
     */
    public function checkExist(string $id): self
    {
        if(!$this->exist($id)) {
            throw new GraphException(
                "ID '{$id}' not exists", GraphException::STATUS_ID_NOT_EXIST
            );
        }
        return $this;
    }

    /**
     * Checks if element with such ID does not exist
     * @param string $id element ID
     * @return $this
     * @throws GraphException
     */
    public function checkNotExist(string $id): self
    {
        if($this->exist($id)) {
            throw new GraphException(
                "ID '{$id}' exists", GraphException::STATUS_ID_EXIST
            );
        }
        return $this;
    }

    /**
     * Clears graph
     * @return $this
     */
    public function clear(): self
    {
        $this->itemsMap = [];
        return $this;
    }

    /**
     * Representates graph as array
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach($this->itemsMap as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @return int
     */
    public function count(): int
    {
        return count($this->itemsMap);
    }
}