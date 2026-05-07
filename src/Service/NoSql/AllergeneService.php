<?php

namespace App\Service\NoSQL;

use MongoDB\Client;

class AllergeneService
{
    private $collection;

    public function __construct()
    {
        // Connexion MongoDB locale
        $client = new Client("mongodb://localhost:27017");

        // Base "symfony"
        $db = $client->selectDatabase('symfony');

        // Collection "allergenes"
        $this->collection = $db->selectCollection('allergenes');
    }

    /**
     * Récupère les allergènes d’un menu
     */
    public function getAllergenesForMenu(int $menuId): array
    {
        $doc = $this->collection->findOne(['menuId' => $menuId]);

        if (!$doc) {
            return [];
        }

        return $doc['allergenes'] ?? [];
    }

    /**
     * Définit les allergènes d’un menu
     */
    public function setAllergenesForMenu(int $menuId, array $allergenes): void
    {
        $this->collection->updateOne(
            ['menuId' => $menuId],
            [
                '$set' => [
                    'menuId' => $menuId,
                    'allergenes' => $allergenes
                ]
            ],
            ['upsert' => true] // crée le document s’il n’existe pas
        );
    }
}
