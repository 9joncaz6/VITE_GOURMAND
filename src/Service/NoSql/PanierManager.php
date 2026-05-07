<?php

namespace App\Service\NoSQL;

use MongoDB\Client;
use App\Repository\MenuRepository;

class PanierManager
{
    private $collection;
    private MenuRepository $menuRepository;

    public function __construct(MenuRepository $menuRepository)
    {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('paniers');

        $this->menuRepository = $menuRepository;
    }

    // ... le reste du code
}
