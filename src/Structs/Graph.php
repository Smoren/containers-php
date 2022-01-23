<?php


namespace Smoren\Containers\Structs;


use ArrayIterator;
use Countable;
use IteratorAggregate;
use Smoren\Containers\Exceptions\GraphException;

class Graph implements Countable, IteratorAggregate
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
     * Get all traverse paths from item to left
     * @param string $itemId item ID
     * @param array|null $typesOnly list of types to use in traverse
     * @param array|null $typesExclude list of types not to use in traverse
     * @param int|null $maxPathLength max path length
     * @param bool $stopOnLoop stop on loop
     * @param callable|null $callback callback for every traverse link
     * @return GraphTraversePath[]
     * @throws GraphException
     */
    public function traverseLeft(
        string $itemId, ?array $typesOnly = null, ?array $typesExclude = null,
        ?int $maxPathLength = null, bool $stopOnLoop = true, ?callable $callback = null
    ): array
    {
        return $this->makeTraversePathCollection(
            $this->traverseRecursive(
                'getPrevItemsMap', $itemId, $typesOnly, $typesExclude,
                $callback, $maxPathLength, $stopOnLoop
            )
        );
    }

    /**
     * Get all traverse paths from item to right
     * @param string $itemId item ID
     * @param array|null $typesOnly list of types to use in traverse
     * @param array|null $typesExclude list of types not to use in traverse
     * @param int|null $maxPathLength max path length
     * @param bool $stopOnLoop stop on loop
     * @param callable|null $callback callback for every traverse link
     * @return GraphTraversePath[]
     * @throws GraphException
     */
    public function traverseRight(
        string $itemId, ?array $typesOnly = null, ?array $typesExclude = null,
        ?int $maxPathLength = null, bool $stopOnLoop = true, ?callable $callback = null
    ): array
    {
        return $this->makeTraversePathCollection(
            $this->traverseRecursive(
                'getNextItemsMap', $itemId, $typesOnly, $typesExclude, $callback,
                $maxPathLength, $stopOnLoop
            )
        );
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

    /**
     * Makes list of GraphTraversePaths by result of recursive traverse
     * @param GraphLink[][] $traverseData input data
     * @return GraphTraversePath[]
     */
    protected function makeTraversePathCollection(array $traverseData): array
    {
        $result = [];

        foreach($traverseData as $links) {
            $result[] = new GraphTraversePath($links);
        }

        return $result;
    }

    /**
     * Recursive method to find all traverse paths from some item to all dead ends
     * @param string $getLinkedItemsMethodName method name for getting linked items
     * @param string $itemId id of item to traverse from
     * @param array|null $typesOnly list of types to use in traverse
     * @param array|null $typesExclude list of types not to use in traverse
     * @param callable|null $callback callback for every traverse link
     * @param int|null $maxPathLength max path length
     * @param GraphItem|null $relatedItem related item from previous recursive iteration
     * @param string|null $type link type with related item
     * @param array $currentPath current state of traversed path
     * @return GraphLink[][]
     * @throws GraphException
     */
    protected function traverseRecursive(
        string $getLinkedItemsMethodName, string $itemId,
        ?array $typesOnly = null, ?array $typesExclude = null,
        ?callable $callback = null, ?int $maxPathLength = null, bool $stopOnLoop = true,
        GraphItem $relatedItem = null, ?string $type = null, array $currentPath = []
    ): array
    {
        $paths = [];
        $item = $this->getItem($itemId);
        $prevItemMap = $item->$getLinkedItemsMethodName($typesOnly, $typesExclude);

        if($relatedItem !== null) {
            $link = new GraphLink($relatedItem, $item, $type);

            if($callback !== null) {
                $callback($link, $currentPath);
            }

            if($stopOnLoop && isset($currentPath[$item->getId()])) {
                $currentPath[] = $link;
                $paths[] = array_values($currentPath);
                return $paths;
            } else {
                $currentPath[$relatedItem->getId()] = $link;
            }
        }

        if(count($prevItemMap) && ($maxPathLength === null || count($currentPath) < $maxPathLength-1)) {
            foreach($prevItemMap as $type => $itemMap) {
                foreach($itemMap as $itemId) {
                    $paths = array_merge(
                        $paths,
                        $this->traverseRecursive(
                            $getLinkedItemsMethodName, $itemId, $typesOnly, $typesExclude,
                            $callback, $maxPathLength, $stopOnLoop, $item, $type, $currentPath
                        )
                    );
                }
            }
        } elseif(count($currentPath)) {
            $paths[] = array_values($currentPath);
        }

        return $paths;
    }

    /**
     * @inheritDoc
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->itemsMap);
    }
}