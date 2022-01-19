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
     * @var GraphItemLink[] next items wrapped by link and mapped by item IDs
     */
    protected array $nextItemLinksMap = [];
    /**
     * @var GraphItemLink[] previous items wrapped by link and mapped by item IDs
     */
    protected array $prevItemLinksMap = [];

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
     * @param string $id
     * @return GraphItem
     * @throws GraphException
     */
    public function getPrevItem(string $id): GraphItem
    {
        return $this->getPrevItemLink($id)->getTarget();
    }

    /**
     * Returns previous item link by ID
     * @param string $id
     * @return GraphItemLink
     * @throws GraphException
     */
    public function getPrevItemLink(string $id): GraphItemLink
    {
        if(!isset($this->prevItemLinksMap[$id])) {
            throw new GraphException(
                "ID '{$id}' not exists", GraphException::STATUS_ID_NOT_EXIST
            );
        }

        return $this->prevItemLinksMap[$id];
    }

    /**
     * Returns previous items list
     * @param string|null $type
     * @return GraphItem[]
     */
    public function getPrevItems(?string $type = null): array
    {
        $links = $this->getPrevItemLinks($type);
        $result = [];

        foreach($links as $link) {
            $result[] = $link->getTarget();
        }

        return $result;
    }

    /**
     * Returns previous item links list
     * @param string|null $type
     * @return GraphItemLink[]
     */
    public function getPrevItemLinks(?string $type = null): array
    {
        $result = [];

        foreach($this->prevItemLinksMap as $link) {
            if($type !== null && $type !== $link->getType()) {
                continue;
            }
            $result[] = $link;
        }

        return $result;
    }

    /**
     * Returns previous items map ([itemId => linkType, ...])
     * @return string[]
     */
    public function getPrevItemsMap(?string $type = null): array
    {
        $links = $this->getPrevItemLinks($type);
        $result = [];

        foreach($links as $link) {
            $result[$link->getTarget()->getId()] = $link->getType();
        }

        return $result;
    }

    /**
     * Adds link to previous item
     * @param GraphItem $item item to link
     * @param string $type
     * @return $this
     */
    public function addPrevItem(GraphItem $item, string $type): self
    {
        $this->prevItemLinksMap[$item->getId()] = new GraphItemLink($item, $type);
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
        unset($this->prevItemLinksMap[$itemId]);
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
        return $this->getNextItemLink($id)->getTarget();
    }

    /**
     * Returns next item link by ID
     * @param string $id next item ID
     * @return GraphItemLink
     * @throws GraphException
     */
    public function getNextItemLink(string $id): GraphItemLink
    {
        if(!isset($this->nextItemLinksMap[$id])) {
            throw new GraphException(
                "ID '{$id}' not exists", GraphException::STATUS_ID_NOT_EXIST
            );
        }

        return $this->nextItemLinksMap[$id];
    }

    /**
     * Returns previous items list
     * @param string|null $type
     * @return GraphItem[]
     */
    public function getNextItems(?string $type = null): array
    {
        $links = $this->getNextItemLinks($type);
        $result = [];

        foreach($links as $link) {
            $result[] = $link->getTarget();
        }

        return $result;
    }

    /**
     * Returns next item links list
     * @param string|null $type
     * @return GraphItemLink[]
     */
    public function getNextItemLinks(?string $type = null): array
    {
        $result = [];

        foreach($this->nextItemLinksMap as $link) {
            if($type !== null && $type !== $link->getType()) {
                continue;
            }
            $result[] = $link;
        }

        return $result;
    }

    /**
     * Returns next items map ([itemId => linkType, ...])
     * @param string|null $type
     * @return string[]
     */
    public function getNextItemsMap(?string $type = null): array
    {
        $links = $this->getNextItemLinks($type);
        $result = [];

        foreach($links as $link) {
            $result[$link->getTarget()->getId()] = $link->getType();
        }

        return $result;
    }

    /**
     * Adds link to next item
     * @param GraphItem $item item to link
     * @param string $type
     * @return $this
     */
    public function addNextItem(GraphItem $item, string $type): self
    {
        $this->nextItemLinksMap[$item->getId()] = new GraphItemLink($item, $type);
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
        unset($this->nextItemLinksMap[$itemId]);
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
