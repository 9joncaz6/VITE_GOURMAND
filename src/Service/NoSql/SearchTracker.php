<?php

namespace App\Service\NoSQL;

use MongoDB\Client;

class SearchTracker
{
    private $collection;

    public function __construct()
    {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('search_history');
    }

    public function track(int $userId, string $query): void
    {
        $this->collection->insertOne([
            'userId' => $userId,
            'query' => $query,
            'date' => new \DateTimeImmutable()
        ]);
    }
}
