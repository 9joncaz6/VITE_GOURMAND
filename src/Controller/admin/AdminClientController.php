<?php

namespace App\Controller\admin;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/clients')]
class AdminClientController extends AbstractController
{
    #[Route('/', name: 'admin_clients_index')]
    public function index(UtilisateurRepository $repo): Response
    {
        $clients = $repo->findClients();

        return $this->render('admin/client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/{id}', name: 'admin_clients_show')]
    public function show(Utilisateur $client): Response
    {
        return $this->render('admin/client/show.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_clients_edit')]
    public function edit(
        Utilisateur $client,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(UtilisateurType::class, $client);
        $form->remove('password'); // On ne modifie pas le mdp ici
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Client mis à jour.');
            return $this->redirectToRoute('admin_clients_index');
        }

        return $this->render('admin/client/edit.html.twig', [
            'client' => $client,
            'form'   => $form->createView(),
        ]);
    }

    #[Route('/{id}/toggle', name: 'admin_clients_toggle')]
    public function toggle(
        Utilisateur $client,
        EntityManagerInterface $em
    ): Response {
        $client->setActif(!$client->isActif());
        $em->flush();

        $this->addFlash('success', $client->isActif()
            ? 'Le client est maintenant actif.'
            : 'Le client a été désactivé.'
        );

        return $this->redirectToRoute('admin_clients_index');
    }

    #[Route('/{id}/delete', name: 'admin_clients_delete', methods: ['POST'])]
    public function delete(
        Utilisateur $client,
        EntityManagerInterface $em
    ): Response {

        // Sécurité : on ne supprime pas un admin ou un employé
        if (in_array('ROLE_ADMIN', $client->getRoles()) || in_array('ROLE_EMPLOYE', $client->getRoles())) {
            $this->addFlash('danger', 'Impossible de supprimer ce type de compte.');
            return $this->redirectToRoute('admin_clients_index');
        }

        $em->remove($client);
        $em->flush();

        $this->addFlash('success', 'Client supprimé avec succès.');

        return $this->redirectToRoute('admin_clients_index');
    }
}
