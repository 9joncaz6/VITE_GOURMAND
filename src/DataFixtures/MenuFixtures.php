<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $menus = [
            [
                'titre' => 'Menu Mexicain',
                'description' => 'Un menu épicé aux saveurs du Mexique.',
                'prix' => 14.90,
                'image' => 'mexicain.jpg'
            ],
            [
                'titre' => 'Menu Pizza',
                'description' => 'Une pizza généreuse et savoureuse.',
                'prix' => 11.90,
                'image' => 'pizza.jpg'
            ],
            [
                'titre' => 'Menu Asiatique',
                'description' => 'Un voyage culinaire en Asie.',
                'prix' => 13.50,
                'image' => 'asiatique.jpg'
            ],
            [
                'titre' => 'Menu Végétarien',
                'description' => 'Un menu sain et équilibré.',
                'prix' => 12.50,
                'image' => 'vegetarien.jpg'
            ],
            [
                'titre' => 'Menu Burger',
                'description' => 'Un burger gourmand et généreux.',
                'prix' => 14.90,
                'image' => 'burger.jpg'
            ],
            [
                'titre' => 'Menu Italien',
                'description' => 'Les saveurs authentiques de l’Italie.',
                'prix' => 15.90,
                'image' => 'italien.jpg'
            ],
        ];

        foreach ($menus as $data) {
            $menu = new Menu();
            $menu->setTitre($data['titre']);
            $menu->setDescription($data['description']);
            $menu->setPrixBase($data['prix']);
            $menu->setNbPersonnesMin(2);
            $menu->setStockDisponible(10);

            // Option A : 1 seule image
            $menu->setImages([$data['image']]);

            $manager->persist($menu);
        }

        $manager->flush();
    }
}
