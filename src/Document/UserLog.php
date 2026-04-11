<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class UserLog
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int")]
    private int $userId;

    #[ODM\Field(type: "string")]
    private string $action;

    #[ODM\Field(type: "date")]
    private \DateTime $date;

    #[ODM\Field(type: "hash")]
    private array $details = [];

    public function __construct(int $userId, string $action, array $details = [])
    {
        $this->userId = $userId;
        $this->action = $action;
        $this->date = new \DateTime();
        $this->details = $details;
    }
}