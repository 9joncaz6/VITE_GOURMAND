<?php

namespace App\Service\NoSQL;

use MongoDB\Client;

class UserLogger
{
    private $collection;

    public function __construct()
    {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('user_logs');
    }

    public function log(int $userId, string $action, array $details = []): void
    {
        $this->collection->insertOne([
            'userId' => $userId,
            'action' => $action,
            'details' => $details,
            'date' => new \DateTimeImmutable()
        ]);
    }
}
