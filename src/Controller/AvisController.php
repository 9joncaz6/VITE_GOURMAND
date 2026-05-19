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

        $user = $this->getUser();

        if ($commande->getUtilisateur() !== $user) {
            $this->addFlash('error', 'Vous ne pouvez pas laisser un avis pour cette commande.');
            return $this->redirectToRoute('app_compte_historique');
        }

        if ($commande->getStatutActuel() !== 'terminee') {
            $this->addFlash('error', 'Vous ne pouvez laisser un avis que pour une commande terminée.');
            return $this->redirectToRoute('app_compte_historique');
        }

        if ($commande->getAvis()) {
            $this->addFlash('info', 'Vous avez déjà laissé un avis pour cette commande.');
            return $this->redirectToRoute('app_compte_historique');
        }

        $avis = new Avis();
        $form = $this->createForm(AvisTypeClient::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $avis->setUtilisateur($user);
            $avis->setCommande($commande);

            $menu = $commande->getItems()->first()->getMenu();
            $avis->setMenu($menu);

            $avis->setDate(new \DateTimeImmutable());

            $em->persist($avis);
            $em->flush();

            $this->addFlash('success', 'Merci pour votre avis !');
            return $this->redirectToRoute('app_compte_avis');
        }

        return $this->render('avis/form.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande
        ]);
    }

    #[Route('/compte/avis/{id}/modifier', name: 'compte_avis_modifier')]
    public function modifier(Request $request, Avis $avis, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($avis->getUtilisateur() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(AvisTypeClient::class, $avis);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Votre avis a été modifié.');
            return $this->redirectToRoute('app_compte_avis');
        }

        return $this->render('avis/modifier.html.twig', [
            'form' => $form->createView(),
            'avis' => $avis,
        ]);
    }

    #[Route('/compte/avis/{id}/supprimer', name: 'compte_avis_supprimer')]
    public function supprimer(Avis $avis, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($avis->getUtilisateur() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($avis);
        $em->flush();

        $this->addFlash('success', 'Avis supprimé.');
        return $this->redirectToRoute('app_compte_avis');
    }
}
