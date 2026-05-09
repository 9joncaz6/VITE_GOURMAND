<?php

namespace App\Service\NoSQL;

use App\Document\Panier;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Repository\MenuRepository;

class PanierManager
{
    private DocumentManager $dm;
    private MenuRepository $menuRepository;

    public function __construct(DocumentManager $dm, MenuRepository $menuRepository)
    {
        $this->dm = $dm;
        $this->menuRepository = $menuRepository;
    }

    /**
     * Récupère ou crée le panier d’un utilisateur
     */
    public function getPanier(int $userId): Panier
    {
        $panier = $this->dm->getRepository(Panier::class)->findOneBy(['userId' => $userId]);

        if (!$panier) {
            $panier = new Panier();
            $panier->setUserId($userId);
            $panier->setItems([]);
            $this->dm->persist($panier);
            $this->dm->flush();
        }

        return $panier;
    }

    /**
     * Ajoute un item
     */
    public function addItem(int $userId, int $menuId, int $quantite = 1): void
    {
        $menuId = (int) $menuId; // 🔥 FIX : éviter les clés string

        $panier = $this->getPanier($userId);
        $items = $panier->getItems();

        $items[$menuId] = ($items[$menuId] ?? 0) + $quantite;

        // Si quantité <= 0 → suppression
        if ($items[$menuId] <= 0) {
            unset($items[$menuId]);
        }

        $panier->setItems($items);
        $this->dm->flush();
    }

    /**
     * Retire un item
     */
    public function removeItem(int $userId, int $menuId): void
    {
        $menuId = (int) $menuId;

        $panier = $this->getPanier($userId);
        $items = $panier->getItems();

        unset($items[$menuId]);

        $panier->setItems($items);
        $this->dm->flush();
    }

    /**
     * Vide le panier
     */
    public function clearPanier(int $userId): void
    {
        $panier = $this->getPanier($userId);
        $panier->setItems([]);
        $this->dm->flush();
    }

    /**
     * Format pour Twig
     */
    public function getPanierForTwig(int $userId): array
    {
        $panier = $this->getPanier($userId);
        $items = $panier->getItems();
        $result = [];

        foreach ($items as $menuIdString => $quantite) {

            // 🔥 FIX CRITIQUE : MongoDB renvoie les clés en string
            $menuId = (int) $menuIdString;

            $menu = $this->menuRepository->find($menuId);

            if ($menu) {
                $result[] = [
                    'menu' => $menu,
                    'quantite' => (int) $quantite,
                ];
            }
        }

        return $result;
    }

    /**
     * Total du panier
     */
    public function getTotal(int $userId): float
    {
        $items = $this->getPanierForTwig($userId);
        $total = 0;

        foreach ($items as $item) {
            $menu = $item['menu'];
            $qte  = (int) $item['quantite'];

            $prixBase = (float) $menu->getPrixBase();
            $nbMin    = max(1, (int) $menu->getNbPersonnesMin());

            $prixParPersonne = $prixBase / $nbMin;
            $prixTotalMenu   = $prixParPersonne * $qte;

            if ($qte >= $nbMin + 5) {
                $prixTotalMenu *= 0.90;
            }

            $total += $prixTotalMenu;
        }

        return $total;
    }
}
