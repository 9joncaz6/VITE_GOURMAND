<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class AvisArchive
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int")]
    private int $avisId;

    #[ODM\Field(type: "int")]
    private int $note;

    #[ODM\Field(type: "string")]
    private string $commentaire;

    #[ODM\Field(type: "bool")]
    private bool $valide;

    #[ODM\Field(type: "date")]
    private \DateTime $dateArchivage;

    #[ODM\Field(type: "hash")]
    private array $metadata = [];

    public function __construct(int $avisId, int $note, string $commentaire, bool $valide, array $metadata = [])
    {
        $this->avisId = $avisId;
        $this->note = $note;
        $this->commentaire = $commentaire;
        $this->valide = $valide;
        $this->dateArchivage = new \DateTime();
        $this->metadata = $metadata;
    }
}