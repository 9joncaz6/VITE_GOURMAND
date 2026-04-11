<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Document\CommandeStatut;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeStatutController extends AbstractController
{
    #[Route('/admin/commande/{id}/statut', name: 'app_commande_changer_statut', methods: ['POST'])]
    public function changerStatut(
        Commande $commande,
        Request $request,
        EntityManagerInterface $em,
        DocumentManager $dm
    ): Response {
        // Nouveau statut envoyé par le formulaire
        $nouveauStatut = $request->request->get('statut');

        // Ancien statut pour l'historique MongoDB
        $ancienStatut = $commande->getStatus();

        // 1) Mise à jour SQL
        $commande->setStatus($nouveauStatut);
        $em->flush();

        // 2) Historique MongoDB
        $statut = new CommandeStatut();
        $statut->setCommandeId($commande->getId());
        $statut->setAncienStatut($ancienStatut);
        $statut->setNouveauStatut($nouveauStatut);
        $statut->setDate(new \DateTime());

        $dm->persist($statut);
        $dm->flush();

        // Redirection vers la page détail de la commande
        return $this->redirectToRoute('app_commande_details', [
            'id' => $commande->getId()
        ]);
    }
}