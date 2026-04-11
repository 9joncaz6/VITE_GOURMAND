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
        $userId = $user->getId();

        $items = $panierManager->getPanierForTwig($userId);
        $total = $panierManager->getTotal($userId);

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier_show');
        }

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
        $userId = $user->getId();

        $items = $panierManager->getPanierForTwig($userId);
        $total = $panierManager->getTotal($userId);

        if (empty($items)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier_show');
        }

        /** @var \App\Entity\Commande $commande */
        $commande = new Commande();
        $commande->setTotal($total);

        foreach ($items as $item) {
            $menu = $item['menu'];
            $quantity = $item['quantite'];

            $commandeItem = new CommandeItem();
            $commandeItem->setCommande($commande);
            $commandeItem->setMenu($menu);
            $commandeItem->setQuantite($quantity);
            $commandeItem->setPrixUnitaire($menu->getPrixBase());

            $commande->addItem($commandeItem);
        }

        $em->persist($commande);
        $em->flush();

        // Email
        $email = (new Email())
            ->from('no-reply@vitegourmand.fr')
            ->to('client@example.com')
            ->subject('Confirmation de votre commande')
            ->html(
                $this->renderView('emails/confirmation_commande.twig', [
                    'commande' => $commande,
                ])
            );

        $mailer->send($email);

        // Vider panier
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
}