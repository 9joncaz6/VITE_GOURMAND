<?php

namespace App\Service\NoSQL;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;

class SearchTracker
{
    private Collection $collection;

    public function __construct()
    {
        $client = new Client('mongodb://localhost:27017');
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('search_history');
    }

    /**
     * Enregistre une recherche utilisateur
     */
    public function track(?int $userId, string $query = '', ?string $page = null): void
    {
        $query = trim($query);
        if ($query === '') {
            return;
        }

        $this->collection->insertOne([
            'userId' => $userId,
            'query'  => mb_strtolower($query),
            'page'   => $page,
            'date' => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Top recherches
     */
    public function getTopSearches(int $limit = 10): array
    {
        $pipeline = [
            ['$group' => ['_id' => '$query', 'count' => ['$sum' => 1]]],
            ['$sort'  => ['count' => -1]],
            ['$limit' => $limit],
        ];

        return iterator_to_array($this->collection->aggregate($pipeline));
    }

    /**
     * Recherches d’un utilisateur
     */
    public function getSearchesByUser(int $userId): array
    {
        return iterator_to_array(
            $this->collection->find(['userId' => $userId], ['sort' => ['date' => -1]])
        );
    }
}
