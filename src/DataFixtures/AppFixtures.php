<?php

namespace App\DataFixtures;

use App\Entity\GlobalStats;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $beautifierStats = new GlobalStats(GlobalStats::BEAUTIFIER_USE_KEY);
        $exportJSON = new GlobalStats(GlobalStats::EXPORTER_JSON_KEY);
        $exportXML = new GlobalStats(GlobalStats::EXPORTER_XML_KEY);
        $exportVardump = new GlobalStats(GlobalStats::EXPORTER_VARDUMP_KEY);

        $manager->persist($beautifierStats);
        $manager->persist($exportJSON);
        $manager->persist($exportXML);
        $manager->persist($exportVardump);

        $manager->flush();
    }
}
