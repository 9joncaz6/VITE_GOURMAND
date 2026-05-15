<?php

namespace App\Service\NoSQL;

use App\Document\AvisArchive;
use App\Entity\Avis;
use Doctrine\ODM\MongoDB\DocumentManager;

class AvisArchiver
{
    public function __construct(
        private DocumentManager $dm
    ) {}

    public function archive(Avis $avis, array $metadata = []): void
    {
        $meta = array_merge($metadata, [
            'menuId'        => $avis->getMenu()?->getId(),
            'commandeId'    => $avis->getCommande()?->getId(),
            'utilisateurId' => $avis->getUtilisateur()?->getId(),
        ]);

        $doc = new AvisArchive(
            $avis->getId(),
            $avis->getNote(),
            $avis->getCommentaire(),
            $avis->isValide(),
            $meta
        );

        $this->dm->persist($doc);
        $this->dm->flush();
    }
}
