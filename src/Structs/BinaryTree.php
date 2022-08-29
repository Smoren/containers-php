<?php

namespace Smoren\Containers\Structs;

use Countable;
use IteratorAggregate;

class BinaryTree implements IteratorAggregate, Countable
{
    protected ?BinaryTreeNode $root = null;

    public function __construct($input)
    {
        if($input instanceof BinaryTreeNode) {
            $this->root = $input;
        } else {
            foreach($input as $item) {
                if($item instanceof BinaryTreeNode) {
                    $node = $item;
                } elseif(is_array($item)) {
                    $node = new BinaryTreeNode(...$item);
                } else {
                    $node = new BinaryTreeNode($item, $item);
                }

                if($this->root === null) {
                    $this->root = $node;
                } else {
                    $this->root = $this->root->insertNode($node);
                    //$this->root = $this->root->insertNode($node)->balance();
                }
            }
        }
    }

    public function getIterator(): iterable
    {
        if($this->root === null) {
            yield from [];
        } else {
            yield from $this->root->getIterator();
        }
    }

    public function getRoot(): BinaryTreeNode
    {
        return $this->root;
    }

    public function rotateLeft(): void
    {
        $this->root = $this->root->rotateLeft();
    }

    public function rotateRight(): void
    {
        $this->root = $this->root->rotateRight();
    }

    public function toArray(): array
    {
        if($this->root === null) {
            return [];
        }

        return $this->root->toArray();
    }

    public function count(): int
    {
        return $this->root === null ? 0 : count($this->root);
    }
}