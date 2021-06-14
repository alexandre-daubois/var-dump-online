<?php

namespace App\Exception;

use App\Entity\Formatter\Node;

class FormatterResultCheckFailedException extends \Exception
{
    public ?Node $root = null;

    public function __construct(Node $root = null)
    {
        parent::__construct();
        $this->root = $root;
    }
}
