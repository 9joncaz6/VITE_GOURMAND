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

    #[ODM\Field(type: "string", nullable: true)]
    private ?string $page = null;

    #[ODM\Field(type: "date")]
    private \DateTime $date;

    public function __construct(int $userId, string $query, ?string $page = null)
    {
        $this->userId = $userId;
        $this->query = $query;
        $this->page  = $page;
        $this->date  = new \DateTime();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }
}
