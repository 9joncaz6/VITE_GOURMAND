<?php

namespace App\Controller\admin;

use App\Entity\Plat;
use App\Form\PlatType;
use App\Repository\PlatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/plats')]
class PlatController extends AbstractController
{
    #[Route('/', name: 'admin_plat_index')]
    public function index(PlatRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        return $this->render('admin/plat/index.html.twig', [
            'plats' => $repo->findAll(),
        ]);
    }

    #[Route('/create', name: 'admin_plat_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $plat = new Plat();
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image
            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('plat_') . '.' . $image->guessExtension();
                $image->move('uploads/plats', $filename);
                $plat->setImage($filename);
            }

            $em->persist($plat);
            $em->flush();

            $this->addFlash('success', 'Plat créé avec succès.');
            return $this->redirectToRoute('admin_plat_index');
        }

        return $this->render('admin/plat/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_plat_edit')]
    public function edit(Plat $plat, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image si nouvelle image
            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('plat_') . '.' . $image->guessExtension();
                $image->move('uploads/plats', $filename);
                $plat->setImage($filename);
            }

            $em->flush();

            $this->addFlash('success', 'Plat modifié avec succès.');
            return $this->redirectToRoute('admin_plat_index');
        }

        return $this->render('admin/plat/edit.html.twig', [
            'form' => $form->createView(),
            'plat' => $plat,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_plat_delete')]
    public function delete(Plat $plat, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $em->remove($plat);
        $em->flush();

        $this->addFlash('success', 'Plat supprimé.');
        return $this->redirectToRoute('admin_plat_index');
    }
}
