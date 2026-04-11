<?php

namespace App\Controller;

use App\Service\PanierManager;
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
        $userId = $user->getId();

        return $this->render('panier/show.html.twig', [
            'items' => $panierManager->getPanierForTwig($userId),
            'total' => $panierManager->getTotal($userId),
        ]);
    }

    #[Route('/add/{menuId}', name: 'app_panier_add')]
    public function add(int $menuId, PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        $panierManager->addItem($user->getId(), $menuId, 1);

        return $this->redirectToRoute('app_panier_show');
    }

    #[Route('/update/{menuId}/{action}', name: 'app_panier_update')]
    public function update(int $menuId, string $action, PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $userId = $user->getId();

        if ($action === 'plus') {
            $panierManager->addItem($userId, $menuId, 1);
        } elseif ($action === 'minus') {
            $panierManager->addItem($userId, $menuId, -1);
        }

        return $this->redirectToRoute('app_panier_show');
    }

    #[Route('/remove/{menuId}', name: 'app_panier_remove')]
    public function remove(int $menuId, PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        $panierManager->removeItem($user->getId(), $menuId);

        return $this->redirectToRoute('app_panier_show');
    }
}