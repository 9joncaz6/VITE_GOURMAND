<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class SearchHistory
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int")]
    private int $userId;

    #[ODM\Field(type: "string")]
    private string $query;

    #[ODM\Field(type: "date")]
    private \DateTime $date;

    public function __construct(int $userId, string $query)
    {
        $this->userId = $userId;
        $this->query = $query;
        $this->date = new \DateTime();
    }
}