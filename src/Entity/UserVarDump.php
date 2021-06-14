<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class UserVarDump
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTime $submittedAt;

    #[ORM\Column(type: 'text')]
    private string $token;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $seen = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): UserVarDump
    {
        $this->id = $id;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): UserVarDump
    {
        $this->content = $content;
        return $this;
    }

    public function getSubmittedAt(): ?\DateTime
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(?\DateTime $submittedAt): UserVarDump
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): UserVarDump
    {
        $this->token = $token;
        return $this;
    }

    public function getSeen(): int
    {
        return $this->seen;
    }

    public function setSeen(int $seen): UserVarDump
    {
        $this->seen = $seen;
        return $this;
    }
}
