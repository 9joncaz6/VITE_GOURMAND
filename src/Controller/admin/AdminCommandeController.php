<?php

namespace App\Controller\admin;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/commandes')]
class AdminCommandeController extends AbstractController
{
    #[Route('/', name: 'admin_commandes_index')]
    public function index(CommandeRepository $repo): Response
    {
        return $this->render('admin/commandes/index.html.twig', [
            'commandes' => $repo->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

    #[Route('/{id}/status/{status}', name: 'admin_commandes_status')]
    public function changeStatus(Commande $commande, string $status, EntityManagerInterface $em): Response
{
    $commande->setStatus($status);
    $em->flush();

    $this->addFlash('success', 'Statut mis à jour.');
    return $this->redirectToRoute('admin_commandes_show', ['id' => $commande->getId()]);
}

    #[Route('/{id}', name: 'admin_commandes_show')]
    public function show(Commande $commande): Response
    {
    return $this->render('admin/commandes/show.html.twig', [
        'commande' => $commande
    ]);
}
}