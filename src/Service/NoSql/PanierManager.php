<?php

namespace App\Service\NoSQL;

use App\Document\Panier;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Repository\MenuRepository;

class PanierManager
{
    public function __construct(
        private DocumentManager $dm,
        private MenuRepository $menuRepository
    ) {}

    public function getPanier(int $userId): Panier
    {
        $panier = $this->dm->getRepository(Panier::class)->findOneBy(['userId' => $userId]);

        if (!$panier) {
            $panier = new Panier($userId);
            $this->dm->persist($panier);
            $this->dm->flush();
        }

        return $panier;
    }

    /**
     * Ajoute un menu au panier en respectant le minimum de personnes
     */
    public function add(int $userId, int $menuId, int $quantite = 1): void
    {
        $panier = $this->getPanier($userId);
        $items = $panier->getItems();

        // Récupération du menu pour connaître le minimum
        $menu = $this->menuRepository->find($menuId);
        if (!$menu) {
            return;
        }

        $min = max(1, (int) $menu->getNbPersonnesMin());

        // Si on ajoute un menu pour la première fois → quantité = minimum
        if ($quantite < $min) {
            $quantite = $min;
        }

        $found = false;

        foreach ($items as &$item) {
            if ($item['menuId'] === $menuId) {
                // Si déjà présent → on ajoute normalement
                $item['quantite'] += $quantite;

                // Mais on ne descend jamais sous le minimum
                if ($item['quantite'] < $min) {
                    $item['quantite'] = $min;
                }

                $found = true;
                break;
            }
        }

        if (!$found) {
            $items[] = [
                'menuId'   => $menuId,
                'quantite' => $quantite,
            ];
        }

        $panier->setItems($items);
        $this->dm->flush();
    }

    /**
     * Met à jour la quantité d’un menu en respectant le minimum
     */
    public function update(int $userId, int $menuId, int $quantite): void
    {
        $panier = $this->getPanier($userId);
        $items = $panier->getItems();

        $menu = $this->menuRepository->find($menuId);
        if (!$menu) {
            return;
        }

        $min = max(1, (int) $menu->getNbPersonnesMin());

        foreach ($items as &$item) {
            if ($item['menuId'] === $menuId) {

                // Si quantité <= 0 → suppression
                if ($quantite <= 0) {
                    $items = array_filter($items, fn($i) => $i['menuId'] !== $menuId);
                } else {
                    // Sinon on applique le minimum
                    $item['quantite'] = max($quantite, $min);
                }

                break;
            }
        }

        $panier->setItems(array_values($items));
        $this->dm->flush();
    }

    public function remove(int $userId, int $menuId): void
    {
        $panier = $this->getPanier($userId);
        $items = $panier->getItems();

        $items = array_filter($items, fn($i) => $i['menuId'] !== $menuId);

        $panier->setItems(array_values($items));
        $this->dm->flush();
    }

    public function clearPanier(int $userId): void
    {
        $panier = $this->getPanier($userId);
        $panier->setItems([]);
        $this->dm->flush();
    }

    public function getPanierForTwig(int $userId): array
    {
        $panier = $this->getPanier($userId);
        $items = $panier->getItems();
        $result = [];

        foreach ($items as $item) {
            $menu = $this->menuRepository->find($item['menuId']);

            if ($menu) {
                $result[] = [
                    'menu'     => $menu,
                    'quantite' => (int) $item['quantite'],
                ];
            }
        }

        return $result;
    }

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

    /**
     * Compatibilité ancienne API
     */
    public function addItem(int $userId, int $menuId, int $quantite = 1): void
    {
        $this->add($userId, $menuId, $quantite);
    }

    public function removeItem(int $userId, int $menuId): void
    {
        $this->remove($userId, $menuId);
    }
}
