<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class CommandeLog
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int")]
    private int $commandeId;

    #[ODM\Field(type: "string")]
    private string $action;

    #[ODM\Field(type: "date")]
    private \DateTime $date;

    #[ODM\Field(type: "hash")]
    private array $metadata = [];

    public function __construct(int $commandeId, string $action, array $metadata = [])
    {
        $this->commandeId = $commandeId;
        $this->action = $action;
        $this->date = new \DateTime();
        $this->metadata = $metadata;
    }
}