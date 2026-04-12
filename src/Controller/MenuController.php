<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/menu')]
final class MenuController extends AbstractController
{
    #[Route(name: 'app_menu_index', methods: ['GET'])]
    public function index(MenuRepository $menuRepository): Response
    {
        return $this->render('menu/index.html.twig', [
            'menus' => $menuRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_menu_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload multiple images
            $uploadedFiles = $form->get('images')->getData();

            if ($uploadedFiles) {
                $images = [];

                foreach ($uploadedFiles as $file) {
                    $filename = uniqid() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('menus_directory'), $filename);
                    $images[] = $filename;
                }

                $menu->setImages($images);
            }

            $entityManager->persist($menu);
            $entityManager->flush();

            return $this->redirectToRoute('app_menu_index');
        }

        return $this->render('menu/new.html.twig', [
            'menu' => $menu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_menu_show', methods: ['GET'])]
    public function show(Menu $menu): Response
    {
        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_menu_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload multiple images (en conservant les anciennes)
            $uploadedFiles = $form->get('images')->getData();

            if ($uploadedFiles) {
                $images = $menu->getImages();

                foreach ($uploadedFiles as $file) {
                    $filename = uniqid() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('menus_directory'), $filename);
                    $images[] = $filename;
                }

                $menu->setImages($images);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_menu_index');
        }

        return $this->render('menu/edit.html.twig', [
            'menu' => $menu,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete-image', name: 'app_menu_delete_image', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteImage(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_image' . $menu->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_menu_edit', ['id' => $menu->getId()]);
        }

        $imageToDelete = $request->request->get('image');

        if ($imageToDelete) {
            $images = $menu->getImages();

            // Retirer du tableau
            $images = array_filter($images, fn($img) => $img !== $imageToDelete);

            // Supprimer le fichier physique
            $filePath = $this->getParameter('menus_directory') . '/' . $imageToDelete;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $menu->setImages($images);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_menu_edit', ['id' => $menu->getId()]);
    }

    #[Route('/{id}', name: 'app_menu_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Menu $menu, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $menu->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($menu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_menu_index');
    }
}