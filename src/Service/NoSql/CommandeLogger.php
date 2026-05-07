<?php

namespace App\Service\NoSQL;

use MongoDB\Client;

class CommandeLogger
{
    private $collection;

    public function __construct()
    {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('commande_logs');
    }

    public function log(int $commandeId, string $action, array $metadata = []): void
    {
        $this->collection->insertOne([
            'commandeId' => $commandeId,
            'action' => $action,
            'metadata' => $metadata,
            'date' => new \DateTimeImmutable()
        ]);
    }
}
