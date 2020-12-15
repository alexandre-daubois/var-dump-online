<?php

namespace App\Service;

use App\Entity\Formatter\Node;
use App\Entity\UserVarDumpModel;

class FormatterResultChecker
{
    /**
     * @var UserVarDumpExporter
     */
    private $exporter;

    public function __construct(UserVarDumpExporter $exporter)
    {
        $this->exporter = $exporter;
    }

    /**
     * To check the format has passed, we're doing it simple:
     * - We export the $root as a formatted var_dump, and we remove all blank spaces (tabulation, new line, ...)
     * - We remove all blank spaces of user's content
     * - Compare. If there's a difference, user's content may be invalid.
     *
     * @param Node $root
     * @param UserVarDumpModel $model
     * @return bool
     * @throws \Exception
     */
    public function checkFormatResult(Node $root, UserVarDumpModel $model): bool
    {
        $trimmedFormatted = $this->removeWhiteSpaces($this->exporter->export($root, UserVarDumpExporter::FORMAT_VARDUMP));
        $trimmedModel = $this->removeWhiteSpaces($model->getContent());

        return strlen($trimmedFormatted) === strlen($trimmedModel);
    }

    protected function removeWhiteSpaces(string $sentence): string
    {
        return trim(preg_replace('/\s+/', '', $sentence));
    }
}
