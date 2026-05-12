<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: "allergenes")]
class Allergene
{
    #[ODM\Id]
    private $id;

    #[ODM\Field(type: "int")]
    private $menuId;

    #[ODM\Field(type: "collection")]
    private $allergenes = [];

    public function getId()
    {
        return $this->id;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function setMenuId(int $menuId): void
    {
        $this->menuId = $menuId;
    }

    public function getAllergenes(): array
    {
        return $this->allergenes;
    }

    public function setAllergenes(array $allergenes): void
    {
        $this->allergenes = $allergenes;
    }
}
