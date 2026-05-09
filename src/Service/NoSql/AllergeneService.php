<?php

namespace App\Service\NoSQL;

use Doctrine\ODM\MongoDB\DocumentManager;

class AllergeneService
{
    private $collection;

    public function __construct(DocumentManager $dm)
    {
        // On récupère la collection MongoDB via ODM
        $this->collection = $dm->getDocumentCollection(\App\Document\Allergene::class);
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
            ['upsert' => true]
        );
    }
}
