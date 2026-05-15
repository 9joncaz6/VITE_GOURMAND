<?php

namespace App\Controller;

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

        $items = $panierManager->getPanierForTwig($user->getId());
        $total = $panierManager->getTotal($user->getId());

        return $this->render('panier/show.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/add/{menuId}', name: 'app_panier_add')]
    public function add(int $menuId, PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $panierManager->add($user->getId(), $menuId, 1);

        $redirect = $_GET['redirect'] ?? null;

        if ($redirect === 'panier') {
            return $this->redirectToRoute('app_panier_show');
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
