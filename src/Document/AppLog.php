<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class AppLog
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "string")]
    private string $type;

    #[ODM\Field(type: "string")]
    private string $message;

    #[ODM\Field(type: "date")]
    private \DateTime $date;

    #[ODM\Field(type: "hash")]
    private array $context = [];

    public function __construct(string $type, string $message, array $context = [])
    {
        $this->type = $type;
        $this->message = $message;
        $this->date = new \DateTime();
        $this->context = $context;
    }
}