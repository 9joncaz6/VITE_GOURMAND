<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Repository\AvisRepository;
use App\Service\NoSQL\AllergenesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/menus')]
class MenuPublicController extends AbstractController
{
    #[Route('/', name: 'app_menu_index')]
    public function index(
        Request $request,
        MenuRepository $menuRepository,
        AvisRepository $avisRepository
    ): Response {

        $criteria = [
            'theme'   => $request->query->get('theme'),
            'regime'  => $request->query->get('regime'),
            'prixMin' => $request->query->get('prixMin'),
            'prixMax' => $request->query->get('prixMax'),
        ];

        $menus = $menuRepository->findByCriteria($criteria);
        $avis = $avisRepository->findLatestAvis(3);

        return $this->render('menu/index.html.twig', [
            'menus'    => $menus,
            'criteria' => $criteria,
            'avis'     => $avis,
        ]);
    }

    #[Route('/{id}', name: 'app_menu_show')]
    public function show(
        Menu $menu,
        AvisRepository $avisRepository,
        CommandeRepository $commandeRepository,
        AllergenesService $allergeneService
    ): Response {

        $user = $this->getUser();

        // 1) Avis
        $avis = $avisRepository->findBy(
            ['menu' => $menu],
            ['date' => 'DESC']
        );

        // 2) Moyenne
        $moyenne = null;
        if (count($avis) > 0) {
            $total = array_sum(array_map(fn($a) => $a->getNote(), $avis));
            $moyenne = $total / count($avis);
        }

        // 3) Avis existant
        $avisExistant = null;
        $aDejaLaisseAvis = false;

        if ($user) {
            foreach ($avis as $a) {
                if ($a->getUtilisateur() === $user) {
                    $avisExistant = $a;
                    $aDejaLaisseAvis = true;
                    break;
                }
            }
        }

        // 4) Peut laisser un avis ?
        $peutLaisserAvis = false;
        $commandeEligible = null;

        if ($user) {
            $commandeEligible = $commandeRepository->findCommandeEligiblePourAvis($user, $menu);

            if ($commandeEligible && !$aDejaLaisseAvis) {
                $peutLaisserAvis = true;
            }
        }

        // 5) 🔥 Récupérer les allergènes depuis MongoDB
        $allergenesMenu = $allergeneService->getAllergenesForMenu($menu->getId());

        // 6) Rendu
        return $this->render('menu/show.html.twig', [
            'menu' => $menu,
            'avis' => $avis,
            'moyenne' => $moyenne,
            'aDejaLaisseAvis' => $aDejaLaisseAvis,
            'avisExistant' => $avisExistant,
            'peutLaisserAvis' => $peutLaisserAvis,
            'commandeEligible' => $commandeEligible,
            'allergenesMenu' => $allergenesMenu,
        ]);
    }
}
