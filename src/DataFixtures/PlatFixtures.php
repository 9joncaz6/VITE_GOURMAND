<?php

namespace App\DataFixtures;

use App\Entity\Plat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PlatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $noms = [
            'Salade César',
            'Soupe miso',
            'Burger classique',
            'Pizza Margherita',
            'Sushi saumon',
            'Tiramisu',
            'Pâtes carbonara',
            'Poulet rôti',
            'Riz cantonais',
            'Crème brûlée'
        ];

        // Types possibles
        $types = ['entrée', 'plat', 'dessert'];

        foreach ($noms as $nom) {
            $plat = new Plat();
            $plat->setNom($nom);
            $plat->setDescription("Un plat délicieux : $nom.");
            $plat->setPrix(9.90);

            // ✔ Type obligatoire
            $plat->setType($types[array_rand($types)]);

            // ✔ Image optionnelle
            $plat->setImage(null);

            $manager->persist($plat);
        }

        $manager->flush();
    }
}
