<?php

namespace App\Service;

use App\Entity\Formatter\Node;
use App\Entity\GlobalStats;
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

    /**
     * @var GlobalStatsManager
     */
    protected $globalStatsManager;

    public function __construct(SerializerInterface $serializer, Environment $twig, GlobalStatsManager $globalStatsManager)
    {
        $this->serializer = $serializer;
        $this->twig = $twig;
        $this->globalStatsManager = $globalStatsManager;
    }

    /**
     * @throws \Exception
     */
    public function export(Node $root, string $format): string
    {
        if (!\in_array($format, self::getSupportedFormats(), true)) {
            throw new \Exception('Format is not supported (got '.$format.')');
        }

        if (self::FORMAT_JSON === $format) {
            $this->globalStatsManager->incrementStat(GlobalStats::EXPORTER_JSON_KEY);

            return $this->serializer->serialize($root->getChildren()[0], $format);
        }

        if (self::FORMAT_XML === $format) {
            $this->globalStatsManager->incrementStat(GlobalStats::EXPORTER_XML_KEY);

            return $this->serializer->serialize($root->getChildren()[0], $format);
        }

        $this->globalStatsManager->incrementStat(GlobalStats::EXPORTER_VARDUMP_KEY);

        return $this->formatVardump($root->getChildren()[0]);
    }

    /**
     * @return string[]
     */
    public static function getSupportedFormats(): array
    {
        return [
            self::FORMAT_JSON,
            self::FORMAT_XML,
            self::FORMAT_VARDUMP,
        ];
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function formatVardump(Node $node): string
    {
        return $this->twig->render('export/node.txt.twig', ['node' => $node]);
    }
}
