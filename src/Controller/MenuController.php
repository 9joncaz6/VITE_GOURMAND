<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use App\Repository\ThemeRepository;
use App\Repository\RegimeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/menu')]
final class MenuController extends AbstractController
{
    #[Route('', name: 'app_menu_index', methods: ['GET'])]
    public function index(
        Request $request,
        MenuRepository $menuRepository,
        ThemeRepository $themeRepo,
        RegimeRepository $regimeRepo
    ): Response {
        // Récupération des critères
        $criteria = [
            'prixMax' => $request->query->get('prixMax'),
            'prixMin' => $request->query->get('prixMin'),
            'theme' => $request->query->get('theme'),
            'regime' => $request->query->get('regime'),
            'nbPersonnesMin' => $request->query->get('nb'),
        ];

        // Recherche filtrée
        $menus = $menuRepository->searchMenus($criteria);

        return $this->render('menu/index.html.twig', [
            'menus' => $menus,
            'criteria' => $criteria,
            'themes' => $themeRepo->findAll(),
            'regimes' => $regimeRepo->findAll(),
        ]);
    }

    #[Route('/filter', name: 'app_menu_filter')]
    public function filter(Request $request, MenuRepository $repo): JsonResponse
    {
        $menus = $repo->searchMenus($request->query->all());

        return $this->json([
            'html' => $this->renderView('menu/_list.html.twig', [
                'menus' => $menus
            ])
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
    // Récupération des avis liés au menu
    $avis = $menu->getAvis();

    // Calcul de la moyenne des notes
    $moyenne = null;
    if (count($avis) > 0) {
        $total = 0;
        foreach ($avis as $a) {
            $total += $a->getNote();
        }
        $moyenne = $total / count($avis);
    }

    // Vérifier si l'utilisateur peut laisser un avis
    /** @var \App\Entity\Utilisateur $utilisateur */
    $utilisateur = $this->getUser();
    $peutLaisserAvis = false;
    $commandeEligible = null;
    $aDejaLaisseAvis = false;
    $avisExistant = null;

    if ($utilisateur) {
        foreach ($utilisateur->getCommandes() as $commande) {

            foreach ($commande->getItems() as $item) {
                if ($item->getMenu() === $menu) {

                    // Si un avis existe déjà
                    if ($commande->getAvis()) {
                        $aDejaLaisseAvis = true;
                        $avisExistant = $commande->getAvis();
                    }

                    // Sinon, commande terminée et pas d'avis
                    if ($commande->getStatutActuel() === 'terminée' && !$commande->getAvis()) {
                        $peutLaisserAvis = true;
                        $commandeEligible = $commande;
                    }
                }
            }
        }
    }

    return $this->render('menu/show.html.twig', [
        'menu' => $menu,
        'avis' => $avis,
        'moyenne' => $moyenne,
        'peutLaisserAvis' => $peutLaisserAvis,
        'commandeEligible' => $commandeEligible,
        'aDejaLaisseAvis' => $aDejaLaisseAvis,
        'avisExistant' => $avisExistant,
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
