<?php

namespace App\Service;

use App\Document\Panier;
use App\Repository\MenuRepository;
use Doctrine\ODM\MongoDB\DocumentManager;

class PanierManager
{
    public function __construct(
        private DocumentManager $dm,
        private MenuRepository $menuRepository
    ) {}

    /* ============================
       RÉCUPÉRATION DU PANIER
       ============================ */

    public function getPanier(int $userId): Panier
    {
        $repo = $this->dm->getRepository(Panier::class);
        $panier = $repo->findOneBy(['userId' => $userId]);

        if (!$panier) {
            $panier = new Panier($userId);
            $this->dm->persist($panier);
            $this->dm->flush();
        }

        return $panier;
    }

    /* ============================
       AJOUT / MODIFICATION
       ============================ */

    public function addItem(int $userId, int $menuId, int $quantite): void
    {
        $panier = $this->getPanier($userId);
        $panier->addItem($menuId, $quantite);

        $this->dm->flush();
    }

    /* ============================
       SUPPRESSION
       ============================ */

    public function removeItem(int $userId, int $menuId): void
    {
        $panier = $this->getPanier($userId);
        $panier->removeItem($menuId);

        $this->dm->flush();
    }

    /* ============================
       FORMAT POUR TWIG
       ============================ */

    public function getPanierForTwig(int $userId): array
    {
        $panier = $this->getPanier($userId);
        $items = [];

        foreach ($panier->getItems() as $item) {

            $menu = $this->menuRepository->find($item['menuId']);

            // Si le menu n'existe plus → on ignore
            if (!$menu) {
                continue;
            }

            $quantite = $item['quantite'];

            $items[] = [
                'menu' => $menu,
                'quantite' => $quantite,
                'subtotal' => $menu->getPrixBase() * $quantite,
            ];
        }

        return $items;
    }

    /* ============================
       TOTAL
       ============================ */

    public function getTotal(int $userId): float
    {
        $panier = $this->getPanier($userId);

        return $panier->getTotal(function ($menuId) {
            $menu = $this->menuRepository->find($menuId);
            return $menu ? $menu->getPrixBase() : 0;
        });
    }

    /* ============================
       VIDER LE PANIER
       ============================ */

    public function clearPanier(int $userId): void
    {
        $panier = $this->getPanier($userId);
        $panier->setItems([]);

        $this->dm->flush();
    }
}