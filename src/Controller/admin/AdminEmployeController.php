<?php

namespace App\Controller\admin;

use App\Entity\Utilisateur;
use App\Form\EmployeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/employes')]
class AdminEmployeController extends AbstractController
{
    #[Route('/', name: 'admin_employes_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $employes = $em->getRepository(Utilisateur::class)->findAll();

        return $this->render('admin/employe/index.html.twig', [
            'employes' => $employes,
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_employe_edit')]
    public function edit(
        Utilisateur $employe,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Employé mis à jour avec succès.');

            // 🔥 Correction ici : la bonne route est admin_employes_index
            return $this->redirectToRoute('admin_employes_index');
        }

        return $this->render('admin/employe/edit.html.twig', [
            'form' => $form->createView(),
            'employe' => $employe,
        ]);
    }
}
