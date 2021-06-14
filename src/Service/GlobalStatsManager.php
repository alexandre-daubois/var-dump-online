<?php

namespace App\Service;

use App\Entity\GlobalStats;
use Doctrine\ORM\EntityManagerInterface;

final class GlobalStatsManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getStats(string $key): ?int
    {
        if (null === $stats = $this->entityManager->getRepository(GlobalStats::class)->findOneBy(['key' => $key])) {
            return null;
        }

        return $stats->getValue();
    }

    public function setStats(string $key, int $value): ?GlobalStats
    {
        if (null === $stats = $this->entityManager->getRepository(GlobalStats::class)->findOneBy(['key' => $key])) {
            return null;
        }

        $stats->setValue($value);

        return $stats;
    }

    public function incrementStat(string $key): ?GlobalStats
    {
        if (null === $stats = $this->entityManager->getRepository(GlobalStats::class)->findOneBy(['key' => $key])) {
            return null;
        }

        $stats->setValue($stats->getValue() + 1);

        return $stats;
    }
}
