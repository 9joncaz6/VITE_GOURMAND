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
            $panier = new Panier($userId);
            $this->dm->persist($panier);
            $this->dm->flush();
        }

        return $panier;
    }

    /**
     * Ajoute ou met à jour un item
     * Format items : [ ["menuId" => X, "quantite" => Y], ... ]
     */
    public function addItem(int $userId, int $menuId, int $quantite = 1): void
    {
        $panier = $this->getPanier($userId);
        $items = $panier->getItems();

        $found = false;

        foreach ($items as &$item) {
            if ($item['menuId'] === $menuId) {
                $item['quantite'] += $quantite;

                if ($item['quantite'] <= 0) {
                    $items = array_filter($items, fn($i) => $i['menuId'] !== $menuId);
                }

                $found = true;
                break;
            }
        }

        if (!$found && $quantite > 0) {
            $items[] = [
                'menuId'   => $menuId,
                'quantite' => $quantite,
            ];
        }

        $panier->setItems(array_values($items));
        $this->dm->flush();
    }

    /**
     * Retire complètement un item
     */
    public function removeItem(int $userId, int $menuId): void
    {
        $panier = $this->getPanier($userId);
        $items = $panier->getItems();

        $items = array_filter($items, fn($i) => $i['menuId'] !== $menuId);

        $panier->setItems(array_values($items));
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

    /**
     * Total du panier (menus uniquement)
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
