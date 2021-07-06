<?php

namespace App\Twig;

use App\Entity\GlobalStats;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class StatsExtension extends AbstractExtension
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): \Generator
    {
        yield new TwigFunction('getUsageCount', [$this, 'getUsageCount']);
    }

    public function getUsageCount(): int
    {
        $stats = $this->entityManager->getRepository(GlobalStats::class)->findOneBy(['key' => GlobalStats::BEAUTIFIER_USE_KEY]);

        return $stats?->getValue() ?? 0;
    }
}