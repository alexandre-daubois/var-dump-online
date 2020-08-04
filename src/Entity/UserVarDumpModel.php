<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UserVarDumpModel
{
    /**
     * Maximum length of 128KB.
     *
     * @var string
     * @Assert\Length(max="131072")
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
