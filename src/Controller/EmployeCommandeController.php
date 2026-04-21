<?php

namespace App\Controller;

use App\Entity\Commande;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\CommandeStatut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe/commandes')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployeCommandeController extends AbstractController
{
    #[Route('', name: 'employe_commandes_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $commandes = $em->getRepository(Commande::class)
                        ->findBy([], ['createdAt' => 'DESC']);

        return $this->render('employe/commandes/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/{id}', name: 'employe_commandes_show')]
public function show(Commande $commande): Response
{
    return $this->render('employe/commandes/show.html.twig', [
        'commande' => $commande,
    ]);
}

#[Route('/{id}/statut', name: 'employe_commandes_statut', methods: ['POST'])]
public function changeStatut(
    Request $request,
    Commande $commande,
    EntityManagerInterface $em
): Response {
    $nouveauStatut = $request->request->get('statut');
    $commentaire = $request->request->get('commentaire');

    if (!$nouveauStatut) {
        $this->addFlash('error', 'Aucun statut sélectionné.');
        return $this->redirectToRoute('employe_commandes_show', ['id' => $commande->getId()]);
    }

    // Création du nouveau statut
    $statut = new CommandeStatut();
    $statut->setCommande($commande);
    $statut->setStatut($nouveauStatut);
    $statut->setCommentaire($commentaire);
    $statut->setDateMaj(new \DateTimeImmutable());

    $em->persist($statut);
    $em->flush();

    $this->addFlash('success', 'Statut mis à jour.');

    return $this->redirectToRoute('employe_commandes_show', ['id' => $commande->getId()]);
}


}
