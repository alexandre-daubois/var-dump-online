<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class GlobalStats
{
    public const BEAUTIFIER_USE_KEY = 'BEAUTIFIER_USE_KEY';
    public const EXPORTER_JSON_KEY = 'EXPORTER_JSON_KEY';
    public const EXPORTER_XML_KEY = 'EXPORTER_XML_KEY';
    public const EXPORTER_VARDUMP_KEY = 'EXPORTER_VARDUMP_KEY';

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'stats_key', type: 'string', length: 256)]
    private string $key;

    #[ORM\Column(name: 'stats_value', type: 'integer', options: ['default' => 0])]
    private int $value = 0;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): GlobalStats
    {
        $this->id = $id;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): GlobalStats
    {
        $this->key = $key;
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): GlobalStats
    {
        $this->value = $value;
        return $this;
    }
}
