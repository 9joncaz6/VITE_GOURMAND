<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\ThemeFixtures;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MenuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $menus = [
            [
                'titre' => 'Menu Mexicain',
                'description' => 'Un menu épicé aux saveurs du Mexique.',
                'prix' => 14.90,
                'image' => 'mexicain.jpg',
            ],
            [
                'titre' => 'Menu Pizza',
                'description' => 'Une pizza généreuse et savoureuse.',
                'prix' => 11.90,
                'image' => 'pizza.jpg',
            ],
            [
                'titre' => 'Menu Asiatique',
                'description' => 'Un voyage culinaire en Asie.',
                'prix' => 13.50,
                'image' => 'asiatique.jpg',
            ],
            [
                'titre' => 'Menu Végétarien',
                'description' => 'Un menu sain et équilibré.',
                'prix' => 12.50,
                'image' => 'vegetarien.jpg',
            ],
            [
                'titre' => 'Menu Burger',
                'description' => 'Un burger gourmand et généreux.',
                'prix' => 14.90,
                'image' => 'burger.jpg',
            ],
            [
                'titre' => 'Menu Italien',
                'description' => 'Les saveurs authentiques de l’Italie.',
                'prix' => 15.90,
                'image' => 'italien.jpg',
            ],
        ];

        foreach ($menus as $index => $data) {
            $menu = new Menu();
            $menu->setTitre($data['titre']);
            $menu->setDescription($data['description']);
            $menu->setPrixBase($data['prix']);
            $menu->setNbPersonnesMin(2);
            $menu->setStockDisponible(10);

            // Images JSON
            $menu->setImages([$data['image']]);
            $menu->setImage($data['image']);

            // ✔ Associer un thème parmi les 4 existants
            /** @var \App\Entity\Theme $theme */
            $theme = $this->getReference('theme_' . rand(0, 3), \App\Entity\Theme::class);
            $menu->setTheme($theme);

            $manager->persist($menu);

            // ✔ Ajouter une référence pour PlatFixtures
            $this->addReference('menu_' . $index, $menu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ThemeFixtures::class,
        ];
    }
}
