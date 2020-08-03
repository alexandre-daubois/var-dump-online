<?php

namespace App\Entity\Formatter;

class Node
{
    const TYPE_ARRAY = 'array';
    const TYPE_FLOAT = 'float';
    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_OBJECT = 'object';
    const TYPE_BOOLEAN = 'bool';
    const TYPE_NULL = 'NULL';
    // @todo resource
    const TYPE_NONE = 'none';

    /**
     * @var string
     */
    protected $type = self::TYPE_NONE;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var array for exemple, the size of an array, the internal PHP identifier of an object, etc
     */
    protected $extraData;

    /**
     * @var Node[]
     */
    protected $children;

    /**
     * @var Node
     */
    protected $parent;

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

    /**
     * @param $key
     * @param $data
     *
     * @return $this
     */
    public function addExtraData($key, $data): Node
    {
        $this->extraData[$key] = $data;

        return $this;
    }

    /**
     * @return Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param Node[] $children
     */
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
