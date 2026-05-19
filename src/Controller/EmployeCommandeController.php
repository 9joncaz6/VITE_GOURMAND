<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeStatut;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe/commandes')]
#[IsGranted('ROLE_EMPLOYE')]
class EmployeCommandeController extends AbstractController
{
    #[Route('', name: 'employe_commandes_index')]
    public function index(Request $request, CommandeRepository $repo): Response
    {
        $statutFiltre = $request->query->get('statut');

        $commandes = $statutFiltre
            ? $repo->findByStatutActuel($statutFiltre)
            : $repo->findAllOrdered();

        return $this->render('employe/commandes/index.html.twig', [
            'commandes' => $commandes,
            'statutFiltre' => $statutFiltre,
            'countEnAttente' => $repo->countByStatut('en_attente'),
            'countEnPreparation' => $repo->countByStatut('en_preparation'),
            'countTerminees' => $repo->countByStatut('terminee'),
            'countAnnulees' => $repo->countByStatut('annulee'),
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

        // Récupération du statut actuel
        $statutActuel = $commande->getStatutActuel();

        // L’employé ne peut annuler que si en_attente ou en_preparation
        if ($nouveauStatut === 'annulee') {
            if (!in_array($statutActuel, ['en_attente', 'en_preparation'])) {
                $this->addFlash('error', 'Vous ne pouvez pas annuler cette commande.');
                return $this->redirectToRoute('employe_commandes_show', ['id' => $commande->getId()]);
            }
        }

        // Ajout dans l’historique
        $statut = new CommandeStatut();
        $statut->setCommande($commande);
        $statut->setStatut($nouveauStatut);
        $statut->setCommentaire($commentaire);
        $statut->setDateMaj(new \DateTimeImmutable());

        $em->persist($statut);
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour.');

        return $this->redirectToRoute('employe_commandes_index');
    }

    #[Route('/{id}/annuler', name: 'employe_commandes_annuler', methods: ['POST'])]
    public function annulerCommande(
        Commande $commande,
        EntityManagerInterface $em
    ): Response {
        $statutActuel = $commande->getStatutActuel();

        // RÈGLE OPTION A
        if (!in_array($statutActuel, ['en_attente', 'en_preparation'])) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler cette commande.');
            return $this->redirectToRoute('employe_commandes_show', ['id' => $commande->getId()]);
        }

        $statut = new CommandeStatut();
        $statut->setCommande($commande);
        $statut->setStatut('annulee');
        $statut->setCommentaire('Commande annulée par un employé.');
        $statut->setDateMaj(new \DateTimeImmutable());

        $em->persist($statut);
        $em->flush();

        $this->addFlash('success', 'Commande annulée avec succès.');

        return $this->redirectToRoute('employe_commandes_index');
    }
}
