<?php

namespace App\DataFixtures;

use App\Repository\MenuRepository;
use App\Service\NoSQL\AllergenesService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class NoSqlAllergenesFixtures extends Fixture implements FixtureGroupInterface
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
        private AllergenesService $service
    ) {}

    public function load(ObjectManager $manager): void
    {
        // 🔥 Purge MongoDB avant de réinjecter
        $this->service->purge();

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

    public static function getGroups(): array
    {
        return ['nosql'];
    }
}
