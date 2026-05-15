<?php

namespace App\Service\NoSQL;

use App\Document\Allergenes;
use Doctrine\ODM\MongoDB\DocumentManager;

class AllergenesService
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * Ajoute un allergène à un menu (sans écraser les autres)
     */
    public function addAllergenes(int $menuId, string $allergene): void
    {
        $repo = $this->dm->getRepository(Allergenes::class);

        /** @var Allergenes|null $doc */
        $doc = $repo->findOneBy(['menuId' => $menuId]);

        if (!$doc) {
            $doc = new Allergenes($menuId, [$allergene]);
        } else {
            $current = $doc->getAllergenes();
            if (!in_array($allergene, $current, true)) {
                $current[] = $allergene;
                $doc->setAllergenes($current);
            }
        }

        $this->dm->persist($doc);
        $this->dm->flush();
    }

    /**
     * Définit la liste complète des allergènes pour un menu
     */
    public function setAllergenesForMenu(int $menuId, array $allergenes): void
    {
        $allergenes = array_values($allergenes);

        $repo = $this->dm->getRepository(Allergenes::class);

        /** @var Allergenes|null $doc */
        $doc = $repo->findOneBy(['menuId' => $menuId]);

        if (!$doc) {
            $doc = new Allergenes($menuId, $allergenes);
        } else {
            $doc->setAllergenes($allergenes);
        }

        $this->dm->persist($doc);
        $this->dm->flush();
    }

    /**
     * Récupère les allergènes d’un menu
     */
    public function getAllergenesForMenu(int $menuId): array
    {
        /** @var Allergenes|null $doc */
        $doc = $this->dm->getRepository(Allergenes::class)
            ->findOneBy(['menuId' => $menuId]);

        return $doc ? $doc->getAllergenes() : [];
    }

    /**
     * Purge la collection
     */
    public function purge(): void
    {
        $collection = $this->dm->getDocumentCollection(Allergenes::class);
        $collection->deleteMany([]);
    }
}
