<?php

namespace App\Controller\admin;

use App\Entity\Horaire;
use App\Form\HoraireType;
use App\Repository\HoraireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/horaires')]
class HoraireController extends AbstractController
{
    #[Route('/', name: 'admin_horaire_index')]
    public function index(HoraireRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        return $this->render('admin/horaire/index.html.twig', [
            'horaires' => $repo->findAll(),
        ]);
    }

    #[Route('/create', name: 'admin_horaire_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $horaire = new Horaire();
        $form = $this->createForm(HoraireType::class, $horaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($horaire);
            $em->flush();

            $this->addFlash('success', 'Horaire ajouté.');
            return $this->redirectToRoute('admin_horaire_index');
        }

        return $this->render('admin/horaire/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_horaire_edit')]
    public function edit(Horaire $horaire, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $form = $this->createForm(HoraireType::class, $horaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Horaire modifié.');
            return $this->redirectToRoute('admin_horaire_index');
        }

        return $this->render('admin/horaire/edit.html.twig', [
            'form' => $form->createView(),
            'horaire' => $horaire,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_horaire_delete')]
    public function delete(Horaire $horaire, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $em->remove($horaire);
        $em->flush();

        $this->addFlash('success', 'Horaire supprimé.');
        return $this->redirectToRoute('admin_horaire_index');
    }
}
