<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserVarDump.
 *
 * @ORM\Entity()
 * @ORM\Table()
 */
class GlobalStats
{
    const BEAUTIFIER_USE_KEY = 'BEAUTIFIER_USE_KEY';
    const EXPORTER_JSON_KEY = 'EXPORTER_JSON_KEY';
    const EXPORTER_XML_KEY = 'EXPORTER_XML_KEY';
    const EXPORTER_VARDUMP_KEY = 'EXPORTER_VARDUMP_KEY';

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="string", length=256)
     */
    protected $key;

    /**
     * @var
     * @ORM\Column(type="integer")
     */
    protected $value;

    public function __construct(string $key)
    {
        $this->key = $key;
        $this->value = 0;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue(int $value): void
    {
        $this->value = $value;
    }
}
