<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: "paniers")]
class Panier
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int")]
    private int $userId;

    #[ODM\Field(type: "collection")]
    private array $items = [];

    #[ODM\Field(type: "date")]
    private \DateTime $lastUpdate;

    public function __construct(int $userId = 0)
    {
        $this->userId = $userId;
        $this->items = [];
        $this->lastUpdate = new \DateTime();
    }

    /* ============================
       USER ID
       ============================ */

    public function getId(): ?string
{
    return $this->id;
}


    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
        $this->lastUpdate = new \DateTime();
    }

    /* ============================
       AJOUT / MISE À JOUR
       ============================ */

    public function addItem(int $menuId, int $quantite): void
    {
        foreach ($this->items as &$item) {
            if ($item['menuId'] === $menuId) {
                $item['quantite'] += $quantite;

                if ($item['quantite'] <= 0) {
                    $this->removeItem($menuId);
                }

                $this->lastUpdate = new \DateTime();
                return;
            }
        }

        if ($quantite > 0) {
            $this->items[] = [
                "menuId" => $menuId,
                "quantite" => $quantite,
            ];
        }

        $this->lastUpdate = new \DateTime();
    }

    /* ============================
       SUPPRESSION
       ============================ */

    public function removeItem(int $menuId): void
    {
        foreach ($this->items as $key => $item) {
            if ($item['menuId'] === $menuId) {
                unset($this->items[$key]);
                $this->items = array_values($this->items);
                break;
            }
        }

        $this->lastUpdate = new \DateTime();
    }

    /* ============================
       GETTERS UTILES
       ============================ */

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
        $this->lastUpdate = new \DateTime();
    }

    public function getTotal(callable $priceResolver): float
    {
        $total = 0;

        foreach ($this->items as $item) {
            $prix = $priceResolver($item['menuId']);
            $total += $prix * $item['quantite'];
        }

        return $total;
    }
}
