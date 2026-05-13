<?php

namespace App\DataFixtures;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Service\NoSQL\AllergenesService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class NoSqlAllergenesFixtures extends Fixture implements DependentFixtureInterface
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
        // 🔥 Purge NoSQL avant réinjection
        $this->service->purge();

        // ✔ Les menus existent maintenant (MenuFixtures a déjà tourné)
        $menus = $this->menuRepo->findAll();

        foreach ($menus as $menu) {

            $nb = rand(0, 4);
            $allergenes = [];

            shuffle($this->liste);

            for ($i = 0; $i < $nb; $i++) {
                $allergenes[] = $this->liste[$i];
            }

            // ✔ Enregistrement NoSQL
            $this->service->setAllergenesForMenu($menu->getId(), $allergenes);
        }
    }

    // ✔ Cette fixture DOIT tourner après MenuFixtures
    public function getDependencies(): array
    {
        return [
            MenuFixtures::class,
        ];
    }
}
