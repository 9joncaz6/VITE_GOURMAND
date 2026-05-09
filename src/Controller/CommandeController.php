<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Entity\CommandeStatut;
use App\Entity\Utilisateur;
use App\Service\NoSQL\PanierManager;
use App\Service\NoSQL\StatsService;
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
     * PAGE DE VALIDATION (affiche frais AVANT confirmation)
     */
    #[Route('/validation', name: 'app_commande_validation')]
    public function validation(
        PanierManager $panierManager,
        EntityManagerInterface $em
    ): Response {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userId = $user->getId();

        // Items du panier
        $items = $panierManager->getPanierForTwig($userId);
        $totalMenus = $panierManager->getTotal($userId);

        // Adresse utilisateur
        $adresse = $user->getAdressePostale();
        $distance = $adresse ? $this->calculerDistance($adresse) : 0;

        // Calcul frais livraison
        $livraison = $distance === 0 ? 0 : 5 + ($distance * 0.59);

        // Total final
        $totalFinal = $totalMenus + $livraison;

        return $this->render('commande/validation.html.twig', [
            'items' => $items,
            'totalMenus' => $totalMenus,
            'livraison' => $livraison,
            'totalFinal' => $totalFinal,
            'user' => $user,
        ]);
    }

    /**
     * CONFIRMATION DE LA COMMANDE (création SQL)
     */
    #[Route('/confirmer', name: 'app_commande_confirmer')]
    public function confirmer(
        PanierManager $panierManager,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        StatsService $statsService
    ): Response {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $userId = $user->getId();

        // Récupération du panier
        $items = $panierManager->getPanierForTwig($userId);
        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier_show');
        }

        // Total menus
        $totalMenus = $panierManager->getTotal($userId);

        // Livraison
        $adresse = $user->getAdressePostale();
        $distance = $adresse ? $this->calculerDistance($adresse) : 0;
        $livraison = $distance === 0 ? 0 : 5 + ($distance * 0.59);

        // Total final
        $totalFinal = $totalMenus + $livraison;

        // Création commande
        $commande = new Commande();
        $commande->setUtilisateur($user);
        $commande->setTotal($totalFinal);
        $commande->setFraisLivraison($livraison);

        // Ajout des items
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
            $menu->setStockDisponible($menu->getStockDisponible() - $qte);
        }

        // Statut initial
        $commande->setStatus('en_attente');

        $statut = new CommandeStatut();
        $statut->setCommande($commande);
        $statut->setStatut('en_attente');
        $statut->setDateMaj(new \DateTimeImmutable());

        $em->persist($commande);
        $em->persist($statut);
        $em->flush();

        // Mise à jour stats NoSQL
        $statsService->updateStats($commande);

        // Email confirmation
        $email = (new Email())
            ->from('no-reply@vitegourmand.fr')
            ->to($user->getEmail())
            ->subject('Confirmation de votre commande')
            ->html($this->renderView('emails/confirmation_commande.html.twig', [
                'user' => $user,
                'commande' => $commande,
                'items' => $items,
                'total' => $totalFinal
            ]));

        $mailer->send($email);

        // Vider le panier
        $panierManager->clearPanier($userId);

        return $this->redirectToRoute('app_commande_confirmation', [
            'id' => $commande->getId(),
        ]);
    }

    /**
     * PAGE DE CONFIRMATION FINALE
     */
    #[Route('/confirmation/{id}', name: 'app_commande_confirmation')]
    public function confirmation(Commande $commande): Response
    {
        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    /**
     * CALCUL DISTANCE
     */
    private function calculerDistance(string $adresse): float
    {
        if (stripos($adresse, 'bordeaux') !== false) {
            return 0;
        }
        return 12;
    }
}
