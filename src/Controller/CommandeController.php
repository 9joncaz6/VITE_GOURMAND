<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Entity\CommandeStatut;
use App\Entity\Utilisateur;
use App\Service\NoSQL\PanierManager;
use App\Service\NoSQL\StatsService;
use App\Service\NoSQL\SearchTracker;
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
    public function validation(
        PanierManager $panierManager,
        EntityManagerInterface $em,
        SearchTracker $tracker
    ): Response {
        /** @var Utilisateur|null $user */
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

        // 🔥 Vérification du minimum de personnes
        foreach ($items as $item) {
            $menu = $item['menu'];
            $qte  = $item['quantite'];
            $min  = $menu->getNbPersonnesMin();

            if ($qte < $min) {
                $this->addFlash('error',
                    "Le menu « {$menu->getTitre()} » nécessite au minimum {$min} personnes."
                );
                return $this->redirectToRoute('app_panier_show');
            }
        }

        // Tracking vue de validation panier
        $tracker->track(
            $userId,
            'cart_view',
            'commande_validation'
        );

        if (!$user->getAdressePostale()) {
            $this->addFlash('error', 'Veuillez renseigner votre adresse avant de valider la commande.');
            return $this->redirectToRoute('app_compte_edit');
        }

        $totalMenus = $panierManager->getTotal($userId);
        $livraison = $this->calculerFraisLivraison($user);
        $totalFinal = $totalMenus + $livraison;

        return $this->render('commande/validation.html.twig', [
            'items'      => $items,
            'totalMenus' => $totalMenus,
            'livraison'  => $livraison,
            'totalFinal' => $totalFinal,
            'user'       => $user,
        ]);
    }

    #[Route('/confirmer', name: 'app_commande_confirmer')]
    public function confirmer(
        PanierManager $panierManager,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        StatsService $statsService,
        SearchTracker $tracker
    ): Response {
        /** @var Utilisateur|null $user */
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

        if (!$user->getAdressePostale()) {
            $this->addFlash('error', 'Veuillez renseigner votre adresse avant de confirmer la commande.');
            return $this->redirectToRoute('app_compte_edit');
        }

        // 🔥 Vérification du minimum de personnes (sécurité)
        foreach ($items as $item) {
            $menu = $item['menu'];
            $qte  = $item['quantite'];
            $min  = $menu->getNbPersonnesMin();

            if ($qte < $min) {
                $this->addFlash('error',
                    "Le menu « {$menu->getTitre()} » nécessite au minimum {$min} personnes."
                );
                return $this->redirectToRoute('app_panier_show');
            }
        }

        $totalMenus = $panierManager->getTotal($userId);
        $livraison  = $this->calculerFraisLivraison($user);
        $totalFinal = $totalMenus + $livraison;

        $commande = new Commande();
        $commande->setUtilisateur($user);
        $commande->setTotal($totalFinal);
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

            // Mise à jour du stock
            $menu->setStockDisponible($menu->getStockDisponible() - $qte);
        }

        $statut = new CommandeStatut();
        $statut->setCommande($commande);
        $statut->setStatut('en_attente');
        $statut->setDateMaj(new \DateTimeImmutable());

        $em->persist($commande);
        $em->persist($statut);
        $em->flush();

        // Tracking commande réussie
        $tracker->track(
            $userId,
            'order_success:' . $commande->getId(),
            'commande_confirmer'
        );

        $statsService->updateStats($commande);

        // Email confirmation
        $email = (new Email())
            ->from('no-reply@vitegourmand.fr')
            ->to($user->getEmail())
            ->subject('Confirmation de votre commande')
            ->html($this->renderView('emails/confirmation_commande.html.twig', [
                'user'     => $user,
                'commande' => $commande,
                'items'    => $items,
                'total'    => $totalFinal,
            ]));

        $mailer->send($email);

        // Nettoyage du panier
        $panierManager->clearPanier($userId);

        return $this->redirectToRoute('app_commande_confirmation', [
            'id' => $commande->getId()
        ]);
    }

    #[Route('/confirmation/{id}', name: 'app_commande_confirmation')]
    public function confirmation(Commande $commande): Response
    {
        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    private function calculerFraisLivraison(Utilisateur $user): float
    {
        $adresse = $user->getAdressePostale() ?? '';
        $distance = $this->calculerDistance($adresse);
        $livraison = 5 + ($distance * 0.59);
        return max(5, min($livraison, 25));
    }

    private function calculerDistance(string $adresse): float
    {
        $adresse = strtolower($adresse);

        if (str_contains($adresse, 'bordeaux')) {
            return 0;
        }

        $agglo = [
            'merignac', 'pessac', 'talence', 'cenon', 'begles', 'loirac',
            'bruges', 'lormont', 'eysines', 'gradignan', 'villeneuve-d\'ornon'
        ];

        foreach ($agglo as $ville) {
            if (str_contains($adresse, $ville)) {
                return 5;
            }
        }

        $gironde = [
            'arcachon', 'libourne', 'langon', 'blaye', 'lesparre', 'andernos',
            'lacana', 'soulac', 'gujan', 'leognan', 'parempuyre'
        ];

        foreach ($gironde as $ville) {
            if (str_contains($adresse, $ville)) {
                return 12;
            }
        }

        $france = [
            'paris', 'lyon', 'marseille', 'lille', 'nice', 'toulouse', 'nantes',
            'montpellier', 'strasbourg', 'rennes', 'reims', 'dijon', 'angers',
            'tours', 'clermont', 'metz', 'nancy', 'brest', 'caen', 'rouen'
        ];

        foreach ($france as $ville) {
            if (str_contains($adresse, $ville)) {
                return 25;
            }
        }

        if (preg_match('/\b(\d{5})\b/', $adresse, $matches)) {
            $cp = (int) $matches[1];
            if ($cp >= 1000 && $cp <= 95999) {
                return 25;
            }
        }

        return 40;
    }
}
