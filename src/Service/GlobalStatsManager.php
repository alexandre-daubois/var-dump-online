<?php

namespace App\Service;

use App\Entity\GlobalStats;
use Doctrine\ORM\EntityManagerInterface;

class GlobalStatsManager
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getStats(string $key)
    {
        $stats = $this->em->find(GlobalStats::class, $key);
        if (null === $stats) {
            return null;
        }

        return $stats->getValue();
    }

    public function setStats(string $key, int $value)
    {
        $stats = $this->em->find(GlobalStats::class, $key);
        if (null === $stats) {
            return null;
        }

        $stats->setValue($value);

        return $stats;
    }

    public function incrementStat(string $key)
    {
        $stats = $this->em->find(GlobalStats::class, $key);
        if (null === $stats) {
            return null;
        }

        $stats->setValue($stats->getValue() + 1);

        return $stats;
    }
}
