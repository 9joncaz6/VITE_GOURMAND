<?php

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThemeFixtures extends Fixture
{
    public const THEMES = [
        'Classique',
        'Évènement',
        'Fête',
        'Noël',
        'Pâques',
        'Anniversaire',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::THEMES as $index => $nom) {
    $theme = new Theme();
    $theme->setNom($nom);

    $manager->persist($theme);

    $this->addReference('theme_' . $index, $theme);
}


        $manager->flush();
    }
}
