<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: "stats")]
class Stats
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int", nullable: true)]
    private ?int $menuId = null;

    #[ODM\Field(type: "int")]
    private int $ventes = 0;

    #[ODM\Field(type: "float")]
    private float $revenu = 0.0;

    #[ODM\Field(type: "float")]
    private float $caTotal = 0.0;

    #[ODM\Field(type: "int")]
    private int $totalCommandes = 0;

    public function __construct(?int $menuId = null)
    {
        $this->menuId = $menuId;
    }
}
