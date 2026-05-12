<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compte')]
class CompteController extends AbstractController
{
    #[Route('/', name: 'app_compte_index')]
    public function index(): Response
    {
        return $this->render('compte/index.html.twig');
    }

    #[Route('/edit', name: 'app_compte_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Informations mises à jour.');
            return $this->redirectToRoute('app_compte_index');
        }

        return $this->render('compte/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/password', name: 'app_compte_password')]
    public function password(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        /** @var Utilisateur $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPassword = $form->get('plainPassword')->getData();

            // Hash + mise à jour
            $hashed = $hasher->hashPassword($user, $newPassword);
            $user->setPassword($hashed);

            $em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour.');
            return $this->redirectToRoute('app_compte_index');
        }

        return $this->render('compte/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/historique', name: 'app_compte_historique')]
public function historique(EntityManagerInterface $em): Response
{
    /** @var Utilisateur $user */
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Récupérer toutes les commandes de l'utilisateur
    $commandes = $em->getRepository(\App\Entity\Commande::class)
        ->findBy(['utilisateur' => $user], ['createdAt' => 'DESC']);

    return $this->render('compte/historique.html.twig', [
        'commandes' => $commandes,
    ]);
}

#[Route('/commande/{id}', name: 'compte_commandes_show')]
public function showCommande(
    \App\Entity\Commande $commande,
    EntityManagerInterface $em,
    \App\Repository\AvisRepository $avisRepository
): Response {
    /** @var Utilisateur $user */
    $user = $this->getUser();

    if (!$user || $commande->getUtilisateur() !== $user) {
        return $this->redirectToRoute('app_login');
    }

    // --- LOGIQUE POUR LE BOUTON "LAISSER UN AVIS" ---
    $peutLaisserAvis = false;
    $menuEligible = null;

    foreach ($commande->getItems() as $item) {
        $menu = $item->getMenu();

        // Vérifier si l'utilisateur a déjà laissé un avis
        $avisExistant = $avisRepository->findOneBy([
            'menu' => $menu,
            'utilisateur' => $user
        ]);

        if (!$avisExistant) {
            $peutLaisserAvis = true;
            $menuEligible = $menu;
            break;
        }
    }

    return $this->render('compte/commandes/show.html.twig', [
        'commande' => $commande,
        'peutLaisserAvis' => $peutLaisserAvis,
        'menuEligible' => $menuEligible,
    ]);
}


}
