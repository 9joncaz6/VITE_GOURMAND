<?php

namespace App\Service\NoSQL;

use MongoDB\Client;
use App\Entity\Commande;

class StatsService
{
    private $collection;

    public function __construct()
    {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('stats');
    }

    /**
     * Mise à jour complète des stats après une commande
     */
    public function updateStats(Commande $commande): void
    {
        foreach ($commande->getItems() as $item) {

            $menuId = $item->getMenu()->getId();
            $qte = $item->getQuantite();
            $totalMenu = $item->getTotal();

            $this->collection->updateOne(
                ['menuId' => $menuId],
                [
                    '$inc' => [
                        'ventes' => $qte,
                        'revenu' => $totalMenu
                    ]
                ],
                ['upsert' => true]
            );
        }

        // Stat global
        $this->collection->updateOne(
            ['_id' => 'global'],
            [
                '$inc' => [
                    'caTotal' => $commande->getTotal(),
                    'totalCommandes' => 1
                ]
            ],
            ['upsert' => true]
        );
    }

    public function getStats(): array
    {
        $global = $this->collection->findOne(['_id' => 'global']) ?? [
            'caTotal' => 0,
            'totalCommandes' => 0
        ];

        $caTotal = $global['caTotal'] ?? 0;
        $totalCommandes = $global['totalCommandes'] ?? 0;

        $panierMoyen = $totalCommandes > 0
            ? round($caTotal / $totalCommandes, 2)
            : 0;

        return [
            'caTotal' => $caTotal,
            'totalCommandes' => $totalCommandes,
            'panierMoyen' => $panierMoyen
        ];
    }

    public function getCommandesParMenu(): array
    {
        $cursor = $this->collection->find(['menuId' => ['$exists' => true]]);
        $result = [];

        foreach ($cursor as $doc) {
            $result[$doc['menuId']] = $doc['ventes'] ?? 0;
        }

        return $result;
    }

    public function getCaParMenu(): array
    {
        $cursor = $this->collection->find(['menuId' => ['$exists' => true]]);
        $result = [];

        foreach ($cursor as $doc) {
            $result[$doc['menuId']] = $doc['revenu'] ?? 0;
        }

        return $result;
    }
}
