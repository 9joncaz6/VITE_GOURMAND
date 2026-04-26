<?php

namespace App\DataFixtures;

use App\Repository\MenuRepository;
use App\Service\NoSql\AllergeneService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NoSqlAllergenesFixtures extends Fixture
{
    private array $liste = [
        'gluten',
        'lactose',
        'arachides',
        'soja',
        'sésame',
        'fruits à coque',
        'œufs',
        'poisson',
        'moutarde'
    ];

    public function __construct(
        private MenuRepository $menuRepo,
        private AllergeneService $service
    ) {}

    public function load(ObjectManager $manager): void
    {
        $menus = $this->menuRepo->findAll();

        foreach ($menus as $menu) {
            $nb = rand(0, 4);
            $allergenes = [];

            shuffle($this->liste);

            for ($i = 0; $i < $nb; $i++) {
                $allergenes[] = $this->liste[$i];
            }

            $this->service->setAllergenesForMenu($menu->getId(), $allergenes);
        }
    }
}
