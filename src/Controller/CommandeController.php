<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Service\PanierManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    #[Route('/validation', name: 'app_commande_validation')]
    public function validation(PanierManager $panierManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userId = $user->getId();
        $items = $panierManager->getPanierForTwig($userId);

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier_show');
        }

        $total = $panierManager->getTotal($userId);

        return $this->render('commande/validation.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/create', name: 'app_commande_create')]
    public function create(
        PanierManager $panierManager,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userId = $user->getId();
        $items = $panierManager->getPanierForTwig($userId);

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier_show');
        }

        // ================================
        // 1) Vérifications préalables
        // ================================
        foreach ($items as $item) {
            $menu = $item['menu'];
            $quantity = $item['quantite'];

            // Minimum de personnes
            if ($quantity < $menu->getNbPersonnesMin()) {
                $this->addFlash('error', 'Le menu "' . $menu->getTitre() . '" nécessite au minimum ' . $menu->getNbPersonnesMin() . ' personnes.');
                return $this->redirectToRoute('app_panier_show');
            }

            // Stock
            if ($menu->getStockDisponible() <= 0) {
                $this->addFlash('error', 'Le menu "' . $menu->getTitre() . '" n’est plus disponible.');
                return $this->redirectToRoute('app_panier_show');
            }
        }

        // ================================
        // 2) Calcul du prix total avancé
        // ================================
        $total = 0;

        foreach ($items as $item) {
            $menu = $item['menu'];
            $quantity = $item['quantite'];

            // Prix par personne
            $prixParPersonne = $menu->getPrixBase() / $menu->getNbPersonnesMin();

            // Prix total du menu
            $prixTotalMenu = $prixParPersonne * $quantity;

            // Réduction 10% si +5 personnes
            if ($quantity >= $menu->getNbPersonnesMin() + 5) {
                $prixTotalMenu *= 0.90;
            }

            $total += $prixTotalMenu;
        }

        // ================================
        // 3) Calcul livraison
        // ================================
        $adresse = $user->getAdressePostale(); // ✔ correction ici

        $distance = $adresse ? $this->calculerDistance($adresse) : 0;

        $livraison = $distance === 0
            ? 0
            : 5 + ($distance * 0.59);

        $total += $livraison;

        // ================================
        // 4) Création de la commande
        // ================================
        $commande = new Commande();
        $commande->setUtilisateur($user);
        $commande->setTotal($total);

        if (method_exists($commande, 'setFraisLivraison')) {
            $commande->setFraisLivraison($livraison);
        }

        foreach ($items as $item) {
            $menu = $item['menu'];
            $quantity = $item['quantite'];

            $commandeItem = new CommandeItem();
            $commandeItem->setCommande($commande);
            $commandeItem->setMenu($menu);
            $commandeItem->setQuantite($quantity);
            $commandeItem->setPrixUnitaire($menu->getPrixBase());

            $commande->addItem($commandeItem);

            // Décrémentation du stock
            $menu->setStockDisponible($menu->getStockDisponible() - 1);
        }

        $em->persist($commande);
        $em->flush();

        // ================================
        // 5) Email de confirmation
        // ================================
        $email = (new Email())
            ->from('no-reply@vitegourmand.fr')
            ->to($user->getEmail())
            ->subject('Confirmation de votre commande')
            ->html(
                $this->renderView('emails/confirmation_commande.twig', [
                    'commande' => $commande,
                ])
            );

        $mailer->send($email);

        // Vider le panier
        $panierManager->clearPanier($userId);

        return $this->redirectToRoute('app_commande_confirmation', [
            'id' => $commande->getId(),
        ]);
    }

    #[Route('/confirmation/{id}', name: 'app_commande_confirmation')]
    public function confirmation(Commande $commande): Response
    {
        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    // ================================
    // Fonction interne : distance
    // ================================
    private function calculerDistance(string $adresse): float
    {
        if (stripos($adresse, 'bordeaux') !== false) {
            return 0;
        }

        // Distance fictive pour le devoir
        return 12;
    }
}
