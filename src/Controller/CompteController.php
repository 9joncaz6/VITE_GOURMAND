<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeStatut;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Form\ChangePasswordType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/compte')]
class CompteController extends AbstractController
{
    #[Route('/', name: 'app_compte_index')]
    public function index(): Response
    {
        return $this->render('compte/index.html.twig');
    }

    #[Route('/edit/{redirect}', name: 'app_compte_edit', defaults: ['redirect' => null])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        ?string $redirect
    ): Response {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Informations mises à jour.');

            if ($redirect === 'panier') {
                return $this->redirectToRoute('app_panier_show');
            }

            if ($redirect === 'validation') {
                return $this->redirectToRoute('app_commande_validation');
            }

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
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('plainPassword')->getData();
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
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $commandes = $em->getRepository(Commande::class)
            ->findBy(['utilisateur' => $user], ['createdAt' => 'DESC']);

        return $this->render('compte/historique.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commande/{id}', name: 'compte_commandes_show')]
    public function showCommande(
        Commande $commande,
        EntityManagerInterface $em,
        AvisRepository $avisRepository
    ): Response {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user || $commande->getUtilisateur() !== $user) {
            return $this->redirectToRoute('app_login');
        }

        $peutLaisserAvis = false;
        $menuEligible = null;

        foreach ($commande->getItems() as $item) {
            $menu = $item->getMenu();

            $avisExistant = $avisRepository->findOneBy([
                'menu' => $menu,
                'utilisateur' => $user,
            ]);

            if (!$avisExistant) {
                $peutLaisserAvis = true;
                $menuEligible = $menu;
                break;
            }
        }

        return $this->render('compte/commandes/show.html.twig', [
            'commande'       => $commande,
            'peutLaisserAvis'=> $peutLaisserAvis,
            'menuEligible'   => $menuEligible,
        ]);
    }

    #[Route('/commande/{id}/annuler', name: 'compte_commande_annuler', methods: ['POST'])]
    public function annulerCommande(
        Commande $commande,
        EntityManagerInterface $em
    ): Response {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user || $commande->getUtilisateur() !== $user) {
            return $this->redirectToRoute('app_login');
        }

        if ($commande->getStatutActuel() !== 'en_attente') {
            $this->addFlash('error', 'Vous ne pouvez plus annuler cette commande.');
            return $this->redirectToRoute('compte_commandes_show', ['id' => $commande->getId()]);
        }

        $statut = new CommandeStatut();
        $statut->setCommande($commande);
        $statut->setStatut('annulee');
        $statut->setCommentaire('Commande annulée par le client.');
        $statut->setDateMaj(new \DateTimeImmutable());

        $commande->setStatus('annulee');

        $em->persist($statut);
        $em->flush();

        $this->addFlash('success', 'Votre commande a été annulée.');
        return $this->redirectToRoute('app_compte_historique');
    }

    #[Route('/avis', name: 'app_compte_avis')]
    public function mesAvis(AvisRepository $avisRepository): Response
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $avis = $avisRepository->findBy(
            ['utilisateur' => $user],
            ['date' => 'DESC']
        );

        return $this->render('compte/avis/index.html.twig', [
            'avis' => $avis,
        ]);
    }

    #[Route('/supprimer', name: 'app_compte_supprimer')]
    public function supprimer(
        Request $request,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): Response {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        foreach ($user->getAvis() as $avis) {
            $em->remove($avis);
        }

        foreach ($user->getCommandes() as $commande) {
            $em->remove($commande);
        }

        $em->remove($user);
        $em->flush();

        $tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        return $this->redirectToRoute('app_home');
    }
}
