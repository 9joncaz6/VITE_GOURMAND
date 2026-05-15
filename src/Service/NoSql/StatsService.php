<?php

namespace App\Service\NoSQL;

use App\Document\Stats;
use App\Entity\Commande;
use Doctrine\ODM\MongoDB\DocumentManager;

class StatsService
{
    public function __construct(
        private DocumentManager $dm
    ) {}

    public function updateStats(Commande $commande): void
    {
        $collection = $this->dm->getDocumentCollection(Stats::class);

        foreach ($commande->getItems() as $item) {
            $menuId    = $item->getMenu()->getId();
            $qte       = $item->getQuantite();
            $totalMenu = $item->getTotal();

            // 🔥 Correction : 1 commande = +1 vente
            $collection->updateOne(
                ['menuId' => $menuId],
                [
                    '$inc' => [
                        'ventes' => 1,        // <-- ici la correction
                        'revenu' => $totalMenu,
                    ],
                ],
                ['upsert' => true]
            );
        }

        // Stat global
        $collection->updateOne(
            ['_id' => 'global'],
            [
                '$inc' => [
                    'caTotal'        => $commande->getTotal(),
                    'totalCommandes' => 1,
                ],
            ],
            ['upsert' => true]
        );
    }

    public function getStats(): array
    {
        $collection = $this->dm->getDocumentCollection(Stats::class);

        $global = $collection->findOne(['_id' => 'global']) ?? [
            'caTotal'        => 0,
            'totalCommandes' => 0,
        ];

        $caTotal        = $global['caTotal'] ?? 0;
        $totalCommandes = $global['totalCommandes'] ?? 0;

        $panierMoyen = $totalCommandes > 0
            ? round($caTotal / $totalCommandes, 2)
            : 0;

        return [
            'caTotal'        => $caTotal,
            'totalCommandes' => $totalCommandes,
            'panierMoyen'    => $panierMoyen,
        ];
    }

    public function getCommandesParMenu(): array
    {
        $collection = $this->dm->getDocumentCollection(Stats::class);
        $cursor     = $collection->find(['menuId' => ['$exists' => true]]);
        $result     = [];

        foreach ($cursor as $doc) {
            $result[$doc['menuId']] = $doc['ventes'] ?? 0;
        }

        return $result;
    }

    public function getCaParMenu(): array
    {
        $collection = $this->dm->getDocumentCollection(Stats::class);
        $cursor     = $collection->find(['menuId' => ['$exists' => true]]);
        $result     = [];

        foreach ($cursor as $doc) {
            $result[$doc['menuId']] = $doc['revenu'] ?? 0;
        }

        return $result;
    }
}
