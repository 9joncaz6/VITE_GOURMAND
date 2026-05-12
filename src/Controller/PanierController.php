<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Service\NoSQL\PanierManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_show')]
    public function show(PanierManager $panierManager): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userId = $user->getId();

        return $this->render('panier/show.html.twig', [
            'items' => $panierManager->getPanierForTwig($userId),
            'total' => $panierManager->getTotal($userId),
        ]);
    }

    #[Route('/add/{menuId}', name: 'app_panier_add')]
    public function add(
        int $menuId,
        PanierManager $panierManager,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $menu = $em->getRepository(\App\Entity\Menu::class)->find($menuId);
        if (!$menu) {
            $this->addFlash('error', 'Menu introuvable.');
            return $this->redirectToRoute('app_panier_show');
        }

        $quantite = $menu->getNbPersonnesMin() ?? 1;

        $panierManager->addItem($user->getId(), $menuId, $quantite);

        // 🔥 Redirection spéciale pour le bouton "Commander"
        $redirect = $request->query->get('redirect');
        if ($redirect === 'validation') {
            return $this->redirectToRoute('app_commande_validation');
        }

        return $this->redirectToRoute('app_panier_show');
    }

    #[Route('/update/{menuId}/{action}', name: 'app_panier_update')]
    public function update(
        int $menuId,
        string $action,
        PanierManager $panierManager
    ): Response {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

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
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $panierManager->removeItem($user->getId(), $menuId);

        return $this->redirectToRoute('app_panier_show');
    }
}
