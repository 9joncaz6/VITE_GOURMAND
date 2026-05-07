<?php

namespace App\Service\NoSQL;

use MongoDB\Client;
use App\Entity\Avis;

class AvisArchiver
{
    private $collection;

    public function __construct()
    {
        $client = new Client("mongodb://localhost:27017");
        $db = $client->selectDatabase('symfony');
        $this->collection = $db->selectCollection('avis_archive');
    }

    public function archive(Avis $avis, array $metadata = []): void
    {
        $this->collection->insertOne([
            'avisId' => $avis->getId(),
            'note' => $avis->getNote(),
            'commentaire' => $avis->getCommentaire(),
            'valide' => $avis->isValide(),
            'menuId' => $avis->getMenu()?->getId(),
            'commandeId' => $avis->getCommande()?->getId(),
            'utilisateurId' => $avis->getUtilisateur()?->getId(),
            'metadata' => $metadata,
            'dateArchivage' => new \DateTimeImmutable()
        ]);
    }
}
