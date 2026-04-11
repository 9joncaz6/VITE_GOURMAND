<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document]
class CommandeStatut
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\Field(type: "int")]
    private int $commandeId;

    #[MongoDB\Field(type: "string")]
    private string $ancienStatut;

    #[MongoDB\Field(type: "string")]
    private string $nouveauStatut;

    #[MongoDB\Field(type: "date")]
    private \DateTime $date;

    public function getId()
    {
        return $this->id;
    }

    public function getCommandeId(): int
    {
        return $this->commandeId;
    }

    public function setCommandeId(int $commandeId): self
    {
        $this->commandeId = $commandeId;
        return $this;
    }

    public function getAncienStatut(): string
    {
        return $this->ancienStatut;
    }

    public function setAncienStatut(string $ancienStatut): self
    {
        $this->ancienStatut = $ancienStatut;
        return $this;
    }

    public function getNouveauStatut(): string
    {
        return $this->nouveauStatut;
    }

    public function setNouveauStatut(string $nouveauStatut): self
    {
        $this->nouveauStatut = $nouveauStatut;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }
}