<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UserVarDumpModel
{
    /**
     * Maximum length of 128KB.
     */
    #[Assert\Length(max: 131072)]
    #[Assert\NotBlank]
    private string $content;

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
