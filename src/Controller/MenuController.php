<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;
use App\Service\NoSql\AllergeneService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    #[Route('/menus', name: 'app_menu_index')]
    public function index(
        MenuRepository $menuRepository,
        AvisRepository $avisRepository,
        AllergeneService $allergeneService
    ): Response {
        $menus = $menuRepository->findAll();
        $avis = $avisRepository->findLatestAvis(3);

        return $this->render('menu/index.html.twig', [
            'menus' => $menus,
            'avis' => $avis,
            'allergeneService' => $allergeneService,
        ]);
    }

    #[Route('/menu/{id}', name: 'app_menu_show')]
    public function show(
        Menu $menu,
        AvisRepository $avisRepository,
        CommandeRepository $commandeRepository,
        AllergeneService $allergeneService
    ): Response {

        // Allergènes NoSQL
        $allergenesMenu = $allergeneService->getAllergenesForMenu($menu->getId());

        // Avis du menu
        $avis = $avisRepository->findBy(['menu' => $menu], ['date' => 'DESC']);

        // Moyenne des notes
        $notes = array_map(fn($a) => $a->getNote(), $avis);
        $moyenne = count($notes) > 0 ? array_sum($notes) / count($notes) : null;

        // Variables pour le template
        $aDejaLaisseAvis = false;
        $peutLaisserAvis = false;
        $avisExistant = null;
        $commandeEligible = null;

        // Si l'utilisateur est connecté
        if ($this->getUser()) {

            // 1) Vérifier s'il a déjà laissé un avis
            $avisExistant = $avisRepository->findOneBy([
                'menu' => $menu,
                'user' => $this->getUser()
            ]);

            if ($avisExistant) {
                $aDejaLaisseAvis = true;
            }

            // 2) Vérifier s'il a une commande terminée contenant ce menu
            $commandeEligible = $commandeRepository->findCommandeEligiblePourAvis(
                $this->getUser(),
                $menu
            );

            if ($commandeEligible && !$avisExistant) {
                $peutLaisserAvis = true;
            }
        }

        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
            'allergenesMenu' => $allergenesMenu,
            'avis' => $avis,
            'moyenne' => $moyenne,
            'aDejaLaisseAvis' => $aDejaLaisseAvis,
            'peutLaisserAvis' => $peutLaisserAvis,
            'avisExistant' => $avisExistant,
            'commandeEligible' => $commandeEligible,
        ]);
    }
}
