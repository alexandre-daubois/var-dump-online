<?php

namespace App\Entity;

class UserVarDumpModel
{
    /**
     * @var string
     */
    protected $content;

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): UserVarDumpModel
    {
        $this->content = $content;

        return $this;
    }
}
