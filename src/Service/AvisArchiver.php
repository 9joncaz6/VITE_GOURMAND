<?php

namespace App\Service;

use App\Document\AvisArchive;
use App\Entity\Avis;
use Doctrine\ODM\MongoDB\DocumentManager;

class AvisArchiver
{
    public function __construct(private DocumentManager $dm) {}

    public function archive(Avis $avis, array $metadata = []): void
    {
        $archive = new AvisArchive(
            $avis->getId(),
            $avis->getNote(),
            $avis->getCommentaire(),
            $avis->isValide(),
            $metadata
        );

        $this->dm->persist($archive);
        $this->dm->flush();
    }
}