<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profil')]
class ProfilController extends AbstractController
{
    #[Route('/edit', name: 'app_profil_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour.');
            return $this->redirectToRoute('app_commande_validation');
        }

        return $this->render('profil/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
