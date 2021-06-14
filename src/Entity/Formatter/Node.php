<?php

namespace App\Entity\Formatter;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("all")
 */
final class Node
{
    public const TYPE_ARRAY = 'array';
    public const TYPE_FLOAT = 'float';
    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string';
    public const TYPE_OBJECT = 'object';
    public const TYPE_BOOLEAN = 'bool';
    public const TYPE_RESOURCE = 'resource';
    public const TYPE_NULL = 'NULL';
    public const TYPE_NONE = 'none';

    /**
     * @Serializer\Expose()
     */
    private string $type = self::TYPE_NONE;

    /**
     * @Serializer\Expose()
     */
    private string $value;

    private int $depth;

    /**
     * @Serializer\Expose()
     */
    private array $extraData;

    /**
     * @Serializer\Expose()
     */
    private array $children;

    private Node $parent;

    public function __construct()
    {
        $this->children = [];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Node
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): Node
    {
        $this->value = $value;

        return $this;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): Node
    {
        $this->depth = $depth;

        return $this;
    }

    public function getExtraData(): array
    {
        return $this->extraData;
    }

    public function setExtraData(array $extraData): Node
    {
        $this->extraData = $extraData;

        return $this;
    }

    public function addExtraData($key, $data): Node
    {
        $this->extraData[$key] = $data;

        return $this;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function addChild(Node $node): Node
    {
        $node->setDepth($this->getDepth() + 1);
        $this->children[] = $node;
        $node->setParent($this);

        return $node;
    }

    public function getParent(): Node
    {
        return $this->parent;
    }

    public function setParent(Node $parent): void
    {
        $this->parent = $parent;
    }
}
