<?php

namespace App\Exception;

use App\Entity\Formatter\Node;

class FormatterResultCheckFailedException extends \Exception
{
    /**
     * @var Node
     */
    public $root = null;

    public function __construct(Node $root = null)
    {
        parent::__construct();
        $this->root = $root;
    }
}
