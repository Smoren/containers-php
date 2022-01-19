<?php


namespace Smoren\Containers\Structs;


use Smoren\Containers\Exceptions\GraphException;
use Smoren\ExtendedExceptions\BaseException;

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
     * @var GraphItem[] next items mapped by IDs
     */
    protected array $nextItemsMap;
    /**
     * @var GraphItem[] previous items mapped by IDs
     */
    protected array $prevItemsMap;

    /**
     * GraphItem constructor.
     * @param string $id item ID
     * @param mixed $data item data
     * @param array $prevItems previous items list
     * @param array $nextItems next items list
     */
    public function __construct(string $id, $data, array $prevItems = [], array $nextItems = [])
    {
        $this->id = $id;
        $this->data = $data;
        $this->setPrevItems($prevItems);
        $this->setNextItems($nextItems);
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
     * @param string $id
     * @return GraphItem
     * @throws GraphException
     */
    public function getPrevItem(string $id): GraphItem
    {
        if(!isset($this->prevItemsMap[$id])) {
            throw new GraphException(
                "ID '{$id}' not exists", GraphException::STATUS_ID_NOT_EXIST
            );
        }

        return $this->prevItemsMap[$id];
    }

    /**
     * Returns previous items list
     * @return GraphItem[]
     */
    public function getPrevItems(): array
    {
        return array_values($this->prevItemsMap);
    }

    /**
     * Returns previous items' IDs list
     * @return string[]
     */
    public function getPrevItemIds(): array
    {
        return array_keys($this->prevItemsMap);
    }

    /**
     * Returns previous items mapped by IDs
     * @return GraphItem[]
     */
    public function getPrevItemsMap(): array
    {
        return $this->prevItemsMap;
    }

    /**
     * Sets previous items
     * @param GraphItem[] $prevItems previous items list
     * @return self
     */
    public function setPrevItems(array $prevItems): self
    {
        $this->prevItemsMap = [];

        foreach($prevItems as $prevItem) {
            $this->prevItemsMap[$prevItem->getId()] = $prevItem;
        }

        return $this;
    }

    /**
     * Add link to previous item
     * @param GraphItem $item item to link
     * @return $this
     */
    public function addPrevItem(GraphItem $item): self
    {
        $this->prevItemsMap[$item->getId()] = $item;
        return $this;
    }

    /**
     * Deletes link to previous item
     * @param string $itemId ID of item to delete
     * @return $this
     * @throws GraphException
     */
    public function deletePrevItem(string $itemId): self
    {
        $this->getPrevItem($itemId);
        unset($this->prevItemsMap[$itemId]);
        return $this;
    }

    /**
     * Returns next item by ID
     * @param string $id next item ID
     * @return GraphItem
     * @throws GraphException
     */
    public function getNextItem(string $id): GraphItem
    {
        if(!isset($this->nextItemsMap[$id])) {
            throw new GraphException(
                "ID '{$id}' not exists", GraphException::STATUS_ID_NOT_EXIST
            );
        }

        return $this->nextItemsMap[$id];
    }

    /**
     * Returns previous items list
     * @return GraphItem[]
     */
    public function getNextItems(): array
    {
        return array_values($this->nextItemsMap);
    }

    /**
     * Returns previous items mapped by IDs
     * @return GraphItem[]
     */
    public function getNextItemsMap(): array
    {
        return $this->nextItemsMap;
    }

    /**
     * Returns previous items' IDs list
     * @return string[]
     */
    public function getNextItemIds(): array
    {
        return array_keys($this->nextItemsMap);
    }

    /**
     * Sets next items
     * @param GraphItem[] $nextItems next items list
     * @return self
     */
    public function setNextItems(array $nextItems): self
    {
        $this->nextItemsMap = [];

        foreach($nextItems as $nextItem) {
            $this->nextItemsMap[$nextItem->getId()] = $nextItem;
        }

        return $this;
    }

    /**
     * Deletes link to next item
     * @param GraphItem $item item to link
     * @return $this
     */
    public function addNextItem(GraphItem $item): self
    {
        $this->nextItemsMap[$item->getId()] = $item;
        return $this;
    }

    /**
     * Deletes link to next item
     * @param string $itemId ID of item to delete
     * @return $this
     * @throws GraphException
     */
    public function deleteNextItem(string $itemId): self
    {
        $this->getNextItem($itemId);
        unset($this->nextItemsMap[$itemId]);
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
            'previous' => $this->getPrevItemIds(),
            'next' => $this->getNextItemIds(),
        ];
    }
}
