<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Form\AvisTypeClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AvisController extends AbstractController
{
    #[Route('/compte/commande/{id}/avis', name: 'compte_avis')]
    public function avis(
        Commande $commande,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        
        // 1) Vérifier que la commande appartient au client connecté
        if ($commande->getUtilisateur() !== $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas laisser un avis pour cette commande.');
            return $this->redirectToRoute('compte_commandes');
        }

        // 2) Vérifier que la commande est terminée
        if ($commande->getStatutActuel() !== 'terminée') {
            $this->addFlash('error', 'Vous ne pouvez laisser un avis que pour une commande terminée.');
            return $this->redirectToRoute('compte_commandes');
        }

        // 3) Vérifier qu’il n’y a pas déjà un avis
        if ($commande->getAvis()) {
            $this->addFlash('info', 'Vous avez déjà laissé un avis pour cette commande.');
            return $this->redirectToRoute('compte_commandes');
        }

        // 4) Créer un nouvel avis
        $avis = new Avis();
        $form = $this->createForm(AvisTypeClient::class, $avis);
        $form->handleRequest($request);

        // 5) Traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            $avis->setUtilisateur($this->getUser());
            $avis->setCommande($commande);
            $avis->setDate(new \DateTimeImmutable());

            $em->persist($avis);
            $em->flush();

            $this->addFlash('success', 'Merci pour votre avis !');
            return $this->redirectToRoute('compte_commandes');
        }

        return $this->render('avis/form.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande
        ]);
    }
    
    #[Route('/compte/avis/{id}/modifier', name: 'compte_avis_modifier')]
public function modifier(Request $request, Avis $avis, EntityManagerInterface $em): Response
{
    // Vérifier que l'avis appartient bien à l'utilisateur connecté
    if ($avis->getUtilisateur() !== $this->getUser()) {
        throw $this->createAccessDeniedException();
    }

    $form = $this->createForm(AvisTypeClient::class, $avis);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        return $this->redirectToRoute('app_menu_show', [
            'id' => $avis->getMenu()->getId()
        ]);
    }

    return $this->render('avis/modifier.html.twig', [
        'form' => $form,
        'avis' => $avis,
    ]);
}

}