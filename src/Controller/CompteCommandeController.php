<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/compte')]
#[IsGranted('ROLE_USER')]
class CompteCommandeController extends AbstractController
{
    #[Route('/commandes', name: 'compte_commandes')]
    public function index(CommandeRepository $repo): Response
    {
        $user = $this->getUser();
        $commandes = $repo->findBy(['utilisateur' => $user], ['createdAt' => 'DESC']);

        return $this->render('compte/commandes/index.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/commandes/{id}', name: 'compte_commandes_show')]
    public function show(int $id, CommandeRepository $repo): Response
    {
        $commande = $repo->find($id);

        if (!$commande || $commande->getUtilisateur() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        return $this->render('compte/commandes/show.html.twig', [
            'commande' => $commande
        ]);
    }
}
