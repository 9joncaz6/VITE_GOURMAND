<?php

namespace App\Service\NoSQL;

use MongoDB\Client;
use MongoDB\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminLogService
{
    private Collection $collection;

    public function __construct()
    {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('admin_logs');
    }

    /**
     * Enregistre une action admin
     */
    public function log(UserInterface $admin, string $action, array $details = []): void
    {
        /** @var \App\Entity\Utilisateur $admin */  // ✔ PHPStorm comprend getId()

        $this->collection->insertOne([
            'adminId' => $admin->getId(),   // ✔ plus souligné
            'action'  => $action,
            'details' => $details,
            'date'    => new \DateTimeImmutable(),
        ]);
    }

    /**
     * Récupère les logs récents
     */
    public function getRecentLogs(int $limit = 20): array
    {
        return iterator_to_array(
            $this->collection->find(
                [],
                ['sort' => ['date' => -1], 'limit' => $limit]
            )
        );
    }
}
