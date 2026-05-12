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
                'image' => 'mexicain.jpg',
                'type' => 'mexicain'
            ],
            [
                'titre' => 'Menu Pizza',
                'description' => 'Une pizza généreuse et savoureuse.',
                'prix' => 11.90,
                'image' => 'pizza.jpg',
                'type' => 'pizza'
            ],
            [
                'titre' => 'Menu Asiatique',
                'description' => 'Un voyage culinaire en Asie.',
                'prix' => 13.50,
                'image' => 'asiatique.jpg',
                'type' => 'asiatique'
            ],
            [
                'titre' => 'Menu Végétarien',
                'description' => 'Un menu sain et équilibré.',
                'prix' => 12.50,
                'image' => 'vegetarien.jpg',
                'type' => 'vegetarien'
            ],
            [
                'titre' => 'Menu Burger',
                'description' => 'Un burger gourmand et généreux.',
                'prix' => 14.90,
                'image' => 'burger.jpg',
                'type' => 'burger'
            ],
            [
                'titre' => 'Menu Italien',
                'description' => 'Les saveurs authentiques de l’Italie.',
                'prix' => 15.90,
                'image' => 'italien.jpg',
                'type' => 'italien'
            ],
        ];

        foreach ($menus as $data) {
            $menu = new Menu();
            $menu->setTitre($data['titre']);
            $menu->setDescription($data['description']);
            $menu->setPrixBase($data['prix']);
            $menu->setNbPersonnesMin(2);
            $menu->setStockDisponible(10);

            // Images
            $menu->setImages([$data['image']]);

            // ✔ Ajout du type obligatoire
            

            $manager->persist($menu);
        }

        $manager->flush();
    }
}
