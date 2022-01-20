<?php


namespace Smoren\Containers\Structs;


use Smoren\Containers\Exceptions\GraphException;

class GraphItem
{
    /**
     * @var string item ID
     */
    protected string $id;
    /**
     * @var mixed item data
     */
    protected $data;
    /**
     * @var GraphItem[][] next items mapped by item IDs and type
     */
    protected array $nextItemMap = [];
    /**
     * @var GraphItem[][] previous items mapped by item IDs and type
     */
    protected array $prevItemMap = [];

    /**
     * GraphItem constructor.
     * @param string $id item ID
     * @param mixed $data item data
     */
    public function __construct(string $id, $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Returns item ID
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sets item ID
     * @param string $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns item data
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets item data
     * @param mixed $data
     * @return self
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns previous item by ID
     * @param string $id item ID
     * @param string $type link type
     * @return GraphItem
     * @throws GraphException
     */
    public function getPrevItem(string $id, string $type): GraphItem
    {
        if(!isset($this->prevItemMap[$type][$id])) {
            throw new GraphException(
                "ID '{$id}' not exists", GraphException::STATUS_ID_NOT_EXIST
            );
        }

        return $this->prevItemMap[$type][$id];
    }

    /**
     * Returns previous items list
     * @param string|null $type link type
     * @return GraphItem[]
     * @throws GraphException
     */
    public function getPrevItems(?string $type = null): array
    {
        $result = [];

        if($type === null) {
            foreach($this->prevItemMap as $itemMap) {
                foreach($itemMap as $itemId => $item) {
                    $result[$itemId] = $item;
                }
            }
        } else {
            if(!isset($this->prevItemMap[$type])) {
                throw new GraphException(
                    "type '{$type}' not exist",
                    GraphException::STATUS_TYPE_NOT_EXIST
                );
            }

            foreach($this->prevItemMap[$type] as $itemId => $item) {
                $result[$itemId] = $item;
            }
        }

        return array_values($result);
    }

    /**
     * Returns previous items map ([linkType => [itemId, ...], ...])
     * @param array|null $typesOnly
     * @param array|null $typesExclude
     * @return string[][]
     */
    public function getPrevItemsMap(?array $typesOnly = null, ?array $typesExclude = null): array
    {
        $result = [];

        foreach($this->prevItemMap as $type => $itemMap) {
            if(
                $typesOnly !== null && !in_array($type, $typesOnly) ||
                $typesExclude !== null && in_array($type, $typesExclude)
            ) {
                continue;
            }

            $result[$type] = [];
            foreach($itemMap as $itemId => $item) {
                $result[$type][] = $itemId;
            }
        }

        return $result;
    }

    /**
     * Adds link to previous item
     * @param GraphItem $item item to link
     * @param string $type link type
     * @return $this
     */
    public function addPrevItem(GraphItem $item, string $type): self
    {
        $itemId = $item->getId();

        if(!isset($this->prevItemMap[$type])) {
            $this->prevItemMap[$type] = [];
        }

        $this->prevItemMap[$type][$itemId] = $item;

        return $this;
    }

    /**
     * Deletes link to previous item
     * @param string $itemId ID of item to delete
     * @param string|null $type link type
     * @return $this
     */
    public function deletePrevItem(string $itemId, ?string $type = null): self
    {
        if($type === null) {
            foreach($this->prevItemMap as $type => $itemMap) {
                $this->deletePrevItem($itemId, $type);
            }

            return $this;
        }

        if(isset($this->prevItemMap[$type][$itemId])) {
            unset($this->prevItemMap[$type][$itemId]);

            if(!count($this->prevItemMap[$type])) {
                unset($this->prevItemMap[$type]);
            }
        }

        return $this;
    }

    /**
     * Returns next item by ID
     * @param string $id next item ID
     * @param string $type link type
     * @return GraphItem
     * @throws GraphException
     */
    public function getNextItem(string $id, string $type): GraphItem
    {
        if(!isset($this->nextItemMap[$type][$id])) {
            throw new GraphException(
                "ID '{$id}' not exists", GraphException::STATUS_ID_NOT_EXIST
            );
        }

        return $this->nextItemMap[$type][$id];
    }

    /**
     * Returns previous items list
     * @param string|null $type
     * @return GraphItem[]
     * @throws GraphException
     */
    public function getNextItems(?string $type = null): array
    {
        $result = [];

        if($type === null) {
            foreach($this->nextItemMap as $itemMap) {
                foreach($itemMap as $itemId => $item) {
                    $result[$itemId] = $item;
                }
            }
        } else {
            if(!isset($this->nextItemMap[$type])) {
                throw new GraphException(
                    "type '{$type}' not exist",
                    GraphException::STATUS_TYPE_NOT_EXIST
                );
            }

            foreach($this->nextItemMap[$type] as $itemId => $item) {
                $result[$itemId] = $item;
            }
        }

        return array_values($result);
    }

    /**
     * Returns next items map ([linkType => [itemId, ...], ...])
     * @param array|null $typesOnly
     * @param array|null $typesExclude
     * @return string[][]
     */
    public function getNextItemsMap(?array $typesOnly = null, ?array $typesExclude = null): array
    {
        $result = [];

        foreach($this->nextItemMap as $type => $itemMap) {
            if(
                $typesOnly !== null && !in_array($type, $typesOnly) ||
                $typesExclude !== null && in_array($type, $typesExclude)
            ) {
                continue;
            }

            $result[$type] = [];
            foreach($itemMap as $itemId => $item) {
                $result[$type][] = $itemId;
            }
        }

        return $result;
    }

    /**
     * Adds link to next item
     * @param GraphItem $item item to link
     * @param string $type link type
     * @return $this
     */
    public function addNextItem(GraphItem $item, string $type): self
    {
        $itemId = $item->getId();

        if(!isset($this->nextItemMap[$type])) {
            $this->nextItemMap[$type] = [];
        }

        $this->nextItemMap[$type][$itemId] = $item;

        return $this;
    }

    /**
     * Deletes link to next item
     * @param string $itemId ID of item to delete
     * @param string|null $type link type
     * @return $this
     */
    public function deleteNextItem(string $itemId, ?string $type = null): self
    {
        if($type === null) {
            foreach($this->nextItemMap as $type => $itemMap) {
                $this->deleteNextItem($itemId, $type);
            }

            return $this;
        }

        if(isset($this->nextItemMap[$type][$itemId])) {
            unset($this->nextItemMap[$type][$itemId]);

            if(!count($this->nextItemMap[$type])) {
                unset($this->nextItemMap[$type]);
            }
        }

        return $this;
    }

    /**
     * Representates as array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'data' => $this->data,
            'previous' => $this->getPrevItemsMap(),
            'next' => $this->getNextItemsMap(),
        ];
    }
}
