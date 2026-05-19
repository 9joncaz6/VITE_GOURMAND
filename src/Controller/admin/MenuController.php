<?php

namespace App\Controller\admin;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\PlatRepository;
use App\Repository\MenuRepository;
use App\Service\NoSQL\AllergenesService;
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
    public function create(
        Request $request,
        EntityManagerInterface $em,
        AllergenesService $allergenesService,
        PlatRepository $platRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $menu = new Menu();
        $form = $this->createForm(MenuType::class, $menu, [
            'menu_id' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('menu_') . '.' . $image->guessExtension();
                $image->move('uploads/menus', $filename);
                $menu->setImage($filename);
            }

            $platsIds = $request->request->all('plats') ?? [];
            $plats = $platRepo->findBy(['id' => $platsIds]);
            $menu->setPlats($plats);

            $em->persist($menu);
            $em->flush();

            $allergenesInput = $form->get('allergenes')->getData();
            $allergenes = array_filter(array_map('trim', explode(',', $allergenesInput)));
            $allergenesService->setAllergenesForMenu($menu->getId(), $allergenes);

            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/create.html.twig', [
            'form' => $form->createView(),
            'plats' => $platRepo->findAll(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_menu_edit')]
    public function edit(
        Menu $menu,
        Request $request,
        EntityManagerInterface $em,
        AllergenesService $allergenesService,
        PlatRepository $platRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $allergenes = $allergenesService->getAllergenesForMenu($menu->getId());

        $form = $this->createForm(MenuType::class, $menu, [
            'menu_id' => $menu->getId(),
        ]);
        $form->get('allergenes')->setData(implode(', ', $allergenes));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('menu_') . '.' . $image->guessExtension();
                $image->move('uploads/menus', $filename);
                $menu->setImage($filename);
            }

            $platsIds = $request->request->all('plats') ?? [];
            $plats = $platRepo->findBy(['id' => $platsIds]);
            $menu->setPlats($plats);

            $em->flush();

            $allergenesInput = $form->get('allergenes')->getData();
            $allergenes = array_filter(array_map('trim', explode(',', $allergenesInput)));
            $allergenesService->setAllergenesForMenu($menu->getId(), $allergenes);

            return $this->redirectToRoute('admin_menu_index');
        }

        return $this->render('admin/menu/edit.html.twig', [
            'form' => $form->createView(),
            'menu' => $menu,
            'plats' => $platRepo->findAll(),
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_menu_delete')]
    public function delete(Menu $menu, EntityManagerInterface $em, AllergenesService $allergenesService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $allergenesService->setAllergenesForMenu($menu->getId(), []);

        $em->remove($menu);
        $em->flush();

        return $this->redirectToRoute('admin_menu_index');
    }
}
