<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Service\NoSQL\PanierManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_show')]
    public function show(PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->getAdressePostale()) {
            $this->addFlash('error', 'Veuillez renseigner votre adresse avant de continuer.');
            return $this->redirectToRoute('app_compte_edit', [
                'redirect' => 'panier'
            ]);
        }

        $items = $panierManager->getPanierForTwig($user->getId());
        $total = $panierManager->getTotal($user->getId());

        return $this->render('panier/show.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/add/{menuId}/{redirect}', name: 'app_panier_add', defaults: ['redirect' => null])]
    public function add(
        int $menuId,
        ?string $redirect,
        PanierManager $panierManager,
        MenuRepository $menuRepository
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérification du menu
        $menu = $menuRepository->find($menuId);
        if (!$menu) {
            $this->addFlash('error', 'Menu introuvable.');
            return $this->redirectToRoute('app_menu_index');
        }

        // 🚨 BLOCAGE SI STOCK = 0
        if ($menu->getStockDisponible() <= 0) {
            $this->addFlash('error', 'Ce menu est en rupture de stock.');
            return $this->redirectToRoute('app_menu_show', ['id' => $menuId]);
        }

        // Ajout au panier
        $panierManager->add($user->getId(), $menuId, 1);

        // Redirection spéciale "Commander maintenant"
        if ($redirect === 'validation') {
            return $this->redirectToRoute('app_commande_validation');
        }

        return $this->redirectToRoute('app_panier_show');
    }

    #[Route('/update/{menuId}/{quantite}', name: 'app_panier_update')]
    public function update(int $menuId, int $quantite, PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $panierManager->update($user->getId(), $menuId, $quantite);

        return $this->redirectToRoute('app_panier_show');
    }

    #[Route('/remove/{menuId}', name: 'app_panier_remove')]
    public function remove(int $menuId, PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $panierManager->remove($user->getId(), $menuId);

        return $this->redirectToRoute('app_panier_show');
    }
}
