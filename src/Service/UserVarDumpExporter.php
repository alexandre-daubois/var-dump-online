<?php

namespace App\Service;

use App\Entity\Formatter\Node;
use App\Entity\GlobalStats;
use JMS\Serializer\SerializerInterface;
use Twig\Environment;

class UserVarDumpExporter
{
    public const FORMAT_JSON = 'json';
    public const FORMAT_XML = 'xml';
    public const FORMAT_VARDUMP = 'var_dump';

    protected SerializerInterface $serializer;

    protected Environment $twig;

    protected GlobalStatsManager $globalStatsManager;

    public function __construct(SerializerInterface $serializer, Environment $twig, GlobalStatsManager $globalStatsManager)
    {
        $this->serializer = $serializer;
        $this->twig = $twig;
        $this->globalStatsManager = $globalStatsManager;
    }

    public function export(Node $root, string $format): string
    {
        if (!\in_array($format, self::getSupportedFormats(), true)) {
            throw new \InvalidArgumentException(sprintf('Format %s is not supported.', $format));
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

        return $this->formatVarDump($root->getChildren()[0]);
    }

    public static function getSupportedFormats(): array
    {
        return [
            self::FORMAT_JSON,
            self::FORMAT_XML,
            self::FORMAT_VARDUMP,
        ];
    }

    public function formatVarDump(Node $node): string
    {
        return $this->twig->render('export/node.txt.twig', ['node' => $node]);
    }
}
