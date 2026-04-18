<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MenuFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $menusData = [
            [
                'titre' => 'Menu Mexicain',
                'description' => 'Tacos maison, guacamole frais, riz épicé et churros en dessert.',
                'nb_personnes_min' => 1,
                'prix_base' => 16.90,
                'conditions' => 'Option épicée disponible.',
                'stock_disponible' => 18,
                'images' => ['mexicain1.jpg'],
                'image' => 'mexicain1.jpg',
            ],
            [
                'titre' => 'Menu Pizza',
                'description' => 'Pizza 4 fromages ou Regina, pâte fine et croustillante.',
                'nb_personnes_min' => 1,
                'prix_base' => 11.90,
                'conditions' => 'Cuisson au four à pierre.',
                'stock_disponible' => 12,
                'images' => ['pizza1.jpg'],
                'image' => 'pizza1.jpg',
            ],
            [
                'titre' => 'Menu Asiatique',
                'description' => 'Nouilles sautées, poulet caramélisé, légumes croquants et dessert perles de coco.',
                'nb_personnes_min' => 2,
                'prix_base' => 19.90,
                'conditions' => 'Épicé.',
                'stock_disponible' => 8,
                'images' => ['asia1.jpg'],
                'image' => 'asia1.jpg',
            ],
            [
                'titre' => 'Menu Végétarien',
                'description' => 'Un menu sain et équilibré : légumes grillés, quinoa, sauce tahini.',
                'nb_personnes_min' => 1,
                'prix_base' => 12.50,
                'conditions' => 'Sans gluten.',
                'stock_disponible' => 20,
                'images' => ['vege1.jpg'],
                'image' => 'vege1.jpg',
            ],
            [
                'titre' => 'Menu Burger',
                'description' => 'Burger maison, frites croustillantes et boisson au choix.',
                'nb_personnes_min' => 1,
                'prix_base' => 14.90,
                'conditions' => 'Disponible uniquement le midi.',
                'stock_disponible' => 15,
                'images' => ['burger1.jpg'],
                'image' => 'burger1.jpg',
            ],
            [
    'titre' => 'Menu Italien',
    'description' => 'Pâtes fraîches, sauce tomate maison, basilic et parmesan.',
    'nb_personnes_min' => 1,
    'prix_base' => 15.90,
    'conditions' => 'Disponible tous les jours.',
    'stock_disponible' => 14,
    'images' => ['italien1.jpg'],
    'image' => 'italien1.jpg',
],

        ];

        foreach ($menusData as $data) {
            $menu = new Menu();
            $menu->setTitre($data['titre']);
            $menu->setDescription($data['description']);
            $menu->setNbPersonnesMin($data['nb_personnes_min']);
            $menu->setPrixBase($data['prix_base']);
            $menu->setConditions($data['conditions']);
            $menu->setStockDisponible($data['stock_disponible']);
            $menu->setImages($data['images']);
            $menu->setImage($data['image']);

            $manager->persist($menu);
        }

        $manager->flush();
    }
}
