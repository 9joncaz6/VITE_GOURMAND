<?php

namespace App\DataFixtures;

use App\Entity\Plat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\DataFixtures\MenuFixtures;

class PlatFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /**
         * 1) ANCIENS PLATS
         */
        $anciens = [
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

        $types = ['entrée', 'plat', 'dessert'];

        foreach ($anciens as $nom) {
            $plat = new Plat();
            $plat->setNom($nom);
            $plat->setDescription("Un plat délicieux : $nom.");
            $plat->setType($types[array_rand($types)]);
            $plat->setImage(null);

            $manager->persist($plat);
        }

        /**
         * 2) NOUVEAUX PLATS PAR MENU
         */
        $data = [
            0 => [ /* Mexicain */ 
                ['Nachos au fromage', 'entrée', 'Nachos croustillants gratinés au cheddar.'],
                ['Soupe tortilla', 'entrée', 'Soupe mexicaine traditionnelle légèrement épicée.'],
                ['Guacamole maison', 'entrée', 'Avocat frais, citron vert, coriandre.'],
                ['Tacos au bœuf', 'plat', 'Tacos garnis de bœuf épicé et légumes frais.'],
                ['Fajitas poulet', 'plat', 'Poulet mariné servi avec poivrons et oignons.'],
                ['Chili con carne', 'plat', 'Chili mijoté avec haricots rouges.'],
                ['Churros au chocolat', 'dessert', 'Churros croustillants et sauce chocolat.'],
                ['Flan mexicain', 'dessert', 'Flan traditionnel au caramel.'],
            ],

            1 => [ /* Pizza */ 
                ['Bruschetta tomate basilic', 'entrée', 'Pain grillé, tomate fraîche, basilic.'],
                ['Salade caprese', 'entrée', 'Tomate, mozzarella, basilic, huile d’olive.'],
                ['Pain à l’ail gratiné', 'entrée', 'Pain grillé au beurre d’ail et fromage.'],
                ['Pizza Margherita', 'plat', 'Tomate, mozzarella, basilic.'],
                ['Pizza 4 fromages', 'plat', 'Mozzarella, gorgonzola, parmesan, emmental.'],
                ['Pizza Reine', 'plat', 'Jambon, champignons, mozzarella.'],
                ['Tiramisu', 'dessert', 'Dessert italien traditionnel.'],
                ['Panna cotta vanille', 'dessert', 'Crème vanille et coulis de fruits rouges.'],
            ],

            2 => [ /* Asiatique */ 
                ['Soupe miso', 'entrée', 'Bouillon miso japonais traditionnel.'],
                ['Nems au poulet', 'entrée', 'Nems croustillants accompagnés de sauce nuoc-mâm.'],
                ['Salade thaï', 'entrée', 'Salade fraîche et légèrement épicée.'],
                ['Poulet teriyaki', 'plat', 'Poulet grillé sauce teriyaki maison.'],
                ['Nouilles sautées', 'plat', 'Nouilles sautées au wok avec légumes.'],
                ['Riz cantonais', 'plat', 'Riz sauté aux œufs, jambon et petits pois.'],
                ['Perles de coco', 'dessert', 'Dessert asiatique à la noix de coco.'],
                ['Mochi glacé', 'dessert', 'Mochi japonais fourré glace.'],
            ],

            3 => [ /* Végétarien */ 
                ['Velouté de potiron', 'entrée', 'Soupe crémeuse au potiron.'],
                ['Salade quinoa avocat', 'entrée', 'Salade fraîche et équilibrée.'],
                ['Houmous & crudités', 'entrée', 'Houmous maison et légumes croquants.'],
                ['Curry de légumes', 'plat', 'Curry doux aux légumes de saison.'],
                ['Lasagnes végétariennes', 'plat', 'Lasagnes aux légumes et fromage.'],
                ['Galette de lentilles', 'plat', 'Galette végétale riche en protéines.'],
                ['Salade de fruits frais', 'dessert', 'Fruits frais de saison.'],
                ['Brownie vegan', 'dessert', 'Brownie au chocolat sans produits animaux.'],
            ],

            4 => [ /* Burger */ 
                ['Onion rings', 'entrée', 'Rondelles d’oignon croustillantes.'],
                ['Frites cheddar', 'entrée', 'Frites nappées de cheddar fondu.'],
                ['Chicken bites', 'entrée', 'Morceaux de poulet croustillants.'],
                ['Burger classique', 'plat', 'Steak, cheddar, salade, tomate.'],
                ['Burger bacon', 'plat', 'Steak, bacon grillé, cheddar.'],
                ['Burger végétarien', 'plat', 'Galette végétale, légumes frais.'],
                ['Milkshake vanille', 'dessert', 'Milkshake crémeux à la vanille.'],
                ['Cookie XXL', 'dessert', 'Grand cookie moelleux.'],
            ],

            5 => [ /* Italien */ 
                ['Carpaccio de bœuf', 'entrée', 'Fines tranches de bœuf marinées.'],
                ['Antipasti variés', 'entrée', 'Sélection d’antipasti italiens.'],
                ['Salade César', 'entrée', 'Salade, poulet, parmesan, croûtons.'],
                ['Pâtes carbonara', 'plat', 'Pâtes à la crème et pancetta.'],
                ['Lasagnes bolognaises', 'plat', 'Lasagnes traditionnelles italiennes.'],
                ['Risotto aux champignons', 'plat', 'Risotto crémeux aux champignons.'],
                ['Tiramisu', 'dessert', 'Dessert italien traditionnel.'],
                ['Gelato pistache', 'dessert', 'Glace artisanale à la pistache.'],
            ],
        ];

        /**
         * 3) CRÉATION DES PLATS + ASSOCIATION AUX MENUS
         */
        foreach ($data as $menuIndex => $plats) {

            /** @var \App\Entity\Menu $menu */
            $menu = $this->getReference('menu_' . $menuIndex, \App\Entity\Menu::class);

            foreach ($plats as [$nom, $type, $description]) {

                $plat = new Plat();
                $plat->setNom($nom);
                $plat->setType($type);
                $plat->setDescription($description);
                $plat->setImage(null);

                $manager->persist($plat);

                $menu->addPlat($plat);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MenuFixtures::class,
        ];
    }
}
