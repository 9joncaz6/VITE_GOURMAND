<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\ProfilType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte')]
class CompteController extends AbstractController
{
    #[Route('/', name: 'app_compte')]
    public function index(CommandeRepository $commandeRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Historique des commandes de l'utilisateur connecté
        $commandes = $commandeRepo->findBy(
            ['utilisateur' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('compte/index.html.twig', [
            'user' => $user,
            'commandes' => $commandes,
        ]);
    }

    #[Route('/profil', name: 'app_compte_profil')]
    public function profil(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_compte_profil');
        }

        return $this->render('utilisateur/profil.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/commandes', name: 'app_compte_commandes')]
    public function commandes(CommandeRepository $commandeRepo): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Historique filtré par utilisateur
        $commandes = $commandeRepo->findBy(
            ['utilisateur' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('compte/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commande/{id}', name: 'app_compte_commande_detail')]
    public function commandeDetail(Commande $commande): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Sécurisation : un utilisateur ne peut voir que ses commandes
        if ($commande->getUtilisateur() !== $user) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette commande.");
        }

        return $this->render('compte/commande_detail.html.twig', [
            'commande' => $commande,
        ]);
    }
}