<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: "allergenes")]
class Allergenes
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int")]
    private int $menuId;

    #[ODM\Field(type: "collection")]
    private array $allergenes = [];

    public function __construct(int $menuId, array $allergenes = [])
    {
        $this->menuId = $menuId;
        $this->allergenes = array_values($allergenes);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function getAllergenes(): array
    {
        return $this->allergenes;
    }

    public function setAllergenes(array $allergenes): void
    {
        $this->allergenes = array_values($allergenes);
    }
}
