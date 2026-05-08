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

    /**
     * Récupère le panier brut depuis MongoDB
     */
    public function getPanier(int $userId): array
    {
        $doc = $this->collection->findOne(['userId' => $userId]);

        return $doc['items'] ?? [];
    }

    /**
     * Ajoute un menu au panier
     */
    public function addItem(int $userId, int $menuId, int $quantite = 1): void
    {
        $this->collection->updateOne(
            ['userId' => $userId],
            [
                '$inc' => ["items.$menuId" => $quantite]
            ],
            ['upsert' => true]
        );
    }

    /**
     * Retire un menu du panier
     */
    public function removeItem(int $userId, int $menuId): void
    {
        $this->collection->updateOne(
            ['userId' => $userId],
            [
                '$unset' => ["items.$menuId" => ""]
            ]
        );
    }

    /**
     * Vide complètement le panier
     */
    public function clearPanier(int $userId): void
    {
        $this->collection->deleteOne(['userId' => $userId]);
    }

    /**
     * Convertit le panier MongoDB en objets utilisables dans Twig
     */
    public function getPanierForTwig(int $userId): array
    {
        $items = $this->getPanier($userId);
        $result = [];

        foreach ($items as $menuId => $quantite) {
            $menu = $this->menuRepository->find($menuId);

            if ($menu) {
                $result[] = [
                    'menu' => $menu,
                    'quantite' => $quantite,
                ];
            }
        }

        return $result;
    }

    /**
     * Calcule le total du panier
     */
    public function getTotal(int $userId): float
    {
        $items = $this->getPanierForTwig($userId);
        $total = 0;

        foreach ($items as $item) {
            $menu = $item['menu'];
            $qte  = $item['quantite'];

            $prixParPersonne = $menu->getPrixBase() / $menu->getNbPersonnesMin();
            $prixTotalMenu   = $prixParPersonne * $qte;

            if ($qte >= $menu->getNbPersonnesMin() + 5) {
                $prixTotalMenu *= 0.90;
            }

            $total += $prixTotalMenu;
        }

        return $total;
    }
}
