<?php

namespace App\Controller;

use App\Entity\Allergene;
use App\Form\AllergeneType;
use App\Repository\AllergeneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/allergenes')]
final class AllergenesController extends AbstractController
{
    #[Route(name: 'app_allergenes_index', methods: ['GET'])]
    public function index(AllergeneRepository $allergeneRepository): Response
    {
        return $this->render('allergenes/index.html.twig', [
            'allergenes' => $allergeneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_allergenes_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $allergene = new Allergene();
        $form = $this->createForm(AllergeneType::class, $allergene);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($allergene);
            $entityManager->flush();

            return $this->redirectToRoute('app_allergenes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('allergenes/new.html.twig', [
            'allergene' => $allergene,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_allergenes_show', methods: ['GET'])]
    public function show(Allergene $allergene): Response
    {
        return $this->render('allergenes/show.html.twig', [
            'allergene' => $allergene,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_allergenes_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Allergene $allergene, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AllergeneType::class, $allergene);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_allergenes_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('allergenes/edit.html.twig', [
            'allergene' => $allergene,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_allergenes_delete', methods: ['POST'])]
    public function delete(Request $request, Allergene $allergene, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$allergene->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($allergene);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_allergenes_index', [], Response::HTTP_SEE_OTHER);
    }
}
