<?php

namespace App\Service;

use App\Entity\Formatter\Node;
use JMS\Serializer\SerializerInterface;
use Twig\Environment;

class UserVarDumpExporter
{
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_VARDUMP = 'var_dump';

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var Environment
     */
    protected $twig;

    public function __construct(SerializerInterface $serializer, Environment $twig)
    {
        $this->serializer = $serializer;
        $this->twig = $twig;
    }

    public function export(Node $root, string $format): string
    {
        if (!\in_array($format, self::getSupportedFormats(), true)) {
            throw new \Exception("Format is not supported (got ${$format})");
        }

        if (self::FORMAT_JSON === $format || self::FORMAT_XML === $format) {
            return $this->serializer->serialize($root->getChildren()[0], $format);
        }

        return $this->formatVardump($root->getChildren()[0]);
    }

    public static function getSupportedFormats(): array
    {
        return [
            self::FORMAT_JSON,
            self::FORMAT_XML,
            self::FORMAT_VARDUMP,
        ];
    }

    private function formatVardump(Node $node): string
    {
        return $this->twig->render('export/node.txt.twig', ['node' => $node]);
    }
}
