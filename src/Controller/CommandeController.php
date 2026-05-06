<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Entity\CommandeStatut;
use App\Entity\Utilisateur;
use App\Service\PanierManager;
use App\Service\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

#[Route('/commande')]
class CommandeController extends AbstractController
{
    /**
     * PAGE DE VALIDATION
     */
    #[Route('/validation', name: 'app_commande_validation')]
    public function validation(PanierManager $panierManager): Response
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $items = $panierManager->getPanierForTwig($user->getId());

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier_show');
        }

        return $this->render('commande/validation.html.twig', [
            'items' => $items,
            'total' => $panierManager->getTotal($user->getId()),
        ]);
    }

    /**
     * CREATION DE COMMANDE
     */
    #[Route('/create', name: 'app_commande_create')]
    public function create(
        PanierManager $panierManager,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        StatsService $statsService
    ): Response {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $items = $panierManager->getPanierForTwig($user->getId());

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier_show');
        }

        // 1) Vérifications
        foreach ($items as $item) {
            $menu = $item['menu'];
            $qte  = $item['quantite'];

            if ($qte < $menu->getNbPersonnesMin()) {
                $this->addFlash('error', 'Le menu "' . $menu->getTitre() . '" nécessite au minimum ' . $menu->getNbPersonnesMin() . ' personnes.');
                return $this->redirectToRoute('app_panier_show');
            }

            if ($menu->getStockDisponible() <= 0) {
                $this->addFlash('error', 'Le menu "' . $menu->getTitre() . '" n’est plus disponible.');
                return $this->redirectToRoute('app_panier_show');
            }
        }

        // 2) Calcul du total
        $total = 0;
        foreach ($items as $item) {
            $menu = $item['menu'];
            $qte  = $item['quantite'];

            $prixParPersonne = $menu->getPrixBase() / $menu->getNbPersonnesMin();
            $prixTotalMenu   = $prixParPersonne * $qte;

            if ($qte >= $menu->getNbPersonnesMin() + 5) {
                $prixTotalMenu *= 0.90;
            }

            $total += $prixTotalMenu;
        }

        // 3) Livraison
        $adresse  = $user->getAdressePostale();
        $distance = $adresse ? $this->calculerDistance($adresse) : 0;
        $livraison = $distance === 0 ? 0 : 5 + ($distance * 0.59);
        $total += $livraison;

        // 4) Création commande
        $commande = new Commande();
        $commande->setUtilisateur($user);
        $commande->setTotal($total);
        $commande->setFraisLivraison($livraison);

        foreach ($items as $item) {
            $menu = $item['menu'];
            $qte  = $item['quantite'];

            $commandeItem = new CommandeItem();
            $commandeItem->setCommande($commande);
            $commandeItem->setMenu($menu);
            $commandeItem->setQuantite($qte);
            $commandeItem->setPrixUnitaire($menu->getPrixBase());

            $commande->addItem($commandeItem);

            // Décrémentation du stock
            $menu->setStockDisponible($menu->getStockDisponible() - 1);
        }

        // 5) Statut initial
        $statut = new CommandeStatut();
        $statut->setCommande($commande);
        $statut->setStatut('en_attente');
        $statut->setDateMaj(new \DateTimeImmutable());

        $em->persist($commande);
        $em->persist($statut);
        $em->flush();

        // 6) Mise à jour des stats
        $statsService->updateStats($commande);

        // 7) Email
        $email = (new Email())
            ->from('no-reply@vitegourmand.fr')
            ->to($user->getEmail())
            ->subject('Confirmation de votre commande')
            ->html($this->renderView('emails/confirmation_commande.html.twig', [
                'user' => $user,
                'commande' => $commande,
                'items' => $items,
                'total' => $total
            ]));

        $mailer->send($email);

        return $this->redirectToRoute('app_commande_confirmation', [
            'id' => $commande->getId(),
        ]);
    }

    /**
     * PAGE DE CONFIRMATION
     */
    #[Route('/confirmation/{id}', name: 'app_commande_confirmation')]
    public function confirmation(Commande $commande, PanierManager $panierManager): Response
    {
        // On vide le panier après la commande
        $panierManager->clearPanier($commande->getUtilisateur()->getId());

        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    /**
     * Distance fictive
     */
    private function calculerDistance(string $adresse): float
    {
        if (stripos($adresse, 'bordeaux') !== false) {
            return 0;
        }
        return 12;
    }
}
