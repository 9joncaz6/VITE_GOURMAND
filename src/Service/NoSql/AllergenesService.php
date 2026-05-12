<?php

namespace App\Service\NoSQL;

use MongoDB\Client;
use MongoDB\Model\BSONArray;

class AllergenesService
{
    private $collection;

    public function __construct()
    {
        $client = new Client('mongodb://127.0.0.1:27017');
        $this->collection = $client->vitegourmand->allergenes;
    }

    public function setAllergenesForMenu(int $menuId, array $allergenes): void
    {
        // On force un array simple (au cas où)
        $allergenes = array_values($allergenes);

        $this->collection->updateOne(
            ['menuId' => $menuId],
            ['$set' => ['allergenes' => $allergenes]],
            ['upsert' => true]
        );
    }

    public function getAllergenesForMenu(int $menuId): array
    {
        $doc = $this->collection->findOne(['menuId' => $menuId]);

        if (!$doc || !isset($doc['allergenes'])) {
            return [];
        }

        $allergenes = $doc['allergenes'];

        // Si Mongo renvoie un BSONArray → conversion obligatoire
        if ($allergenes instanceof BSONArray) {
            return $allergenes->getArrayCopy();
        }

        // Si c'est déjà un array PHP
        if (is_array($allergenes)) {
            return $allergenes;
        }

        // Sécurité ultime
        return [];
    }

    public function purge(): void
{
    $this->collection->deleteMany([]);
}

}
