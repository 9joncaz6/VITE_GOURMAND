<?php

namespace App\Controller\admin;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/menus')]
class MenuController extends AbstractController
{
    #[Route('/', name: 'admin_menu_index')]
    public function index(MenuRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        return $this->render('admin/menu/index.html.twig', [
            'menus' => $repo->findAll(),
        ]);
    }

    #[Route('/create', name: 'admin_menu_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu, [
            'menu_id' => null, // pas de retour possible
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image
            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('menu_') . '.' . $image->guessExtension();
                $image->move('uploads/menus', $filename);
                $menu->setImage($filename);
            }

            $em->persist($menu);
            $em->flush();

            $this->addFlash('success', 'Menu créé avec succès.');
            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_menu_edit')]
    public function edit(Menu $menu, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        // ⭐ On passe l’ID du menu au formulaire
        $form = $this->createForm(MenuType::class, $menu, [
            'menu_id' => $menu->getId(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image si nouvelle image
            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('menu_') . '.' . $image->guessExtension();
                $image->move('uploads/menus', $filename);
                $menu->setImage($filename);
            }

            $em->flush();

            $this->addFlash('success', 'Menu modifié avec succès.');
            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/edit.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_menu_delete')]
    public function delete(Menu $menu, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $em->remove($menu);
        $em->flush();

        $this->addFlash('success', 'Menu supprimé.');
        return $this->redirectToRoute('admin_menu_index');
    }
}
