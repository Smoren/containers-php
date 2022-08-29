<?php

namespace Smoren\Containers\Structs;

use Countable;
use IteratorAggregate;

class BinaryTreeNode implements IteratorAggregate, Countable
{
    /**
     * @var scalar
     */
    protected $value;
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @var BinaryTreeNode|null
     */
    protected ?BinaryTreeNode $leftNode = null;
    /**
     * @var BinaryTreeNode|null
     */
    protected ?BinaryTreeNode $rightNode = null;
    protected int $size = 1;

    /**
     * @param scalar $value
     * @param mixed $data
     */
    public function __construct($value, $data)
    {
        $this->value = $value;
        $this->data = $data;
    }

    /**
     * @param BinaryTreeNode $node
     * @return BinaryTreeNode
     */
    public function insertNode(BinaryTreeNode $node): BinaryTreeNode
    {
        if($node->getValue() >= $this->getValue()) {
            if($this->rightNode === null) {
                $this->setRight($node);
            } else {
                $this->setRight($this->popRight()->insertNode($node));
            }
        } else {
            if($this->leftNode === null) {
                $this->setLeft($node);
            } else {
                $this->setLeft($this->popLeft()->insertNode($node));
            }
        }

        return $this->balance();
    }

    public function balance(): BinaryTreeNode
    {
        $result = $this;

        while($result->getBalance() > 0) {
            $result = $result->rotateLeft();
        }
        while($result->getBalance() < -1) {
            $result = $result->rotateRight();
        }

//        if($result->getBalance() > 1) {
//            if($result->leftNode !== null) {
//                $result->setLeft($result->popLeft()->balance());
//            }
//            if($result->rightNode !== null) {
//                $result->setRight($result->popRight()->balance());
//            }
//            return $result->balance();
//        }

        return $result;
    }

    public function getBalance(): int
    {
        return $this->countRight() - $this->countLeft();
    }

    /**
     * @return scalar
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function countLeft(): int
    {
        if($this->leftNode === null) {
            return 0;
        }
        return count($this->leftNode);
    }

    /**
     * @return BinaryTreeNode|null
     */
    public function popLeft(): ?BinaryTreeNode
    {
        $result = $this->leftNode;
        $this->leftNode = null;

        if($result !== null) {
            $this->size -= $result->size;
        }

        return $result;
    }

    /**
     * @param BinaryTreeNode|null $node
     */
    public function setLeft(?BinaryTreeNode $node): void
    {
        $this->size -= $this->countLeft();
        $this->leftNode = $node;
        $this->size += $this->countLeft();
    }

    /**
     * @return int
     */
    public function countRight(): int
    {
        if($this->rightNode === null) {
            return 0;
        }
        return count($this->rightNode);
    }

    /**
     * @return BinaryTreeNode|null
     */
    public function popRight(): ?BinaryTreeNode
    {
        $result = $this->rightNode;
        $this->rightNode = null;

        if($result !== null) {
            $this->size -= $result->size;
        }

        return $result;
    }

    /**
     * @param BinaryTreeNode|null $node
     */
    public function setRight(?BinaryTreeNode $node): void
    {
        $this->size -= $this->countRight();
        $this->rightNode = $node;
        $this->size += $this->countRight();
    }

    public function rotateLeft(): BinaryTreeNode
    {
        if($this->rightNode === null) {
            return $this;
        }

        $right = $this->popRight();
        $this->setRight($right->popLeft());
        $right->setLeft($this);

        return $right;
    }

    public function rotateRight(): BinaryTreeNode
    {
        if($this->leftNode === null) {
            return $this;
        }

        $left = $this->popLeft();
        $this->setLeft($left->popRight());
        $left->setRight($this);

        return $left;
    }

    /**
     * @return iterable<scalar, mixed>
     */
    public function getIterator(): iterable
    {
        if($this->leftNode !== null) {
            yield from $this->leftNode->getIterator();
        }

        yield $this->getValue() => $this->getData();

        if($this->rightNode !== null) {
            yield from $this->rightNode->getIterator();
        }
    }

    public function toArray(): array
    {
        $result = [];
        foreach($this->getIterator() as $value) {
            $result[] = $value;
        }
        return $result;
    }

    public function count(): int
    {
        return $this->size;
    }
}
