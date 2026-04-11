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
    public function index(): Response
    {
        return $this->render('compte/index.html.twig');
    }

    #[Route('/profil', name: 'app_compte_profil')]
    public function profil(Request $request, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();

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

        $commandes = $commandeRepo->findBy([], ['createdAt' => 'DESC']);

        return $this->render('compte/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commande/{id}', name: 'app_compte_commande_detail')]
    public function commandeDetail(Commande $commande): Response
    {
        return $this->render('compte/commande_detail.html.twig', [
            'commande' => $commande,
        ]);
    }
}