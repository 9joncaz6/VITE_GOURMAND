<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;
use App\Service\NoSQL\AllergenesService;
use App\Service\NoSQL\SearchTracker;
use Doctrine\ORM\EntityManagerInterface;
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
        AvisRepository $avisRepository,
        EntityManagerInterface $em
    ): Response {
        $criteria = [
            'theme'   => $request->query->get('theme'),
            'prixMin' => $request->query->get('prixMin'),
            'prixMax' => $request->query->get('prixMax'),
        ];

        $menus = $menuRepository->findByCriteria($criteria);

        $themes = $em->getRepository(Menu::class)
            ->createQueryBuilder('m')
            ->join('m.theme', 't')
            ->select('DISTINCT t.nom')
            ->orderBy('t.nom', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $avis = $avisRepository->findLatestAvis(3);

        return $this->render('menu/index.html.twig', [
            'menus'    => $menus,
            'themes'   => $themes,
            'criteria' => $criteria,
            'avis'     => $avis,
        ]);
    }

    #[Route('/{id}', name: 'app_menu_show')]
    public function show(
        Menu $menu,
        AvisRepository $avisRepository,
        CommandeRepository $commandeRepository,
        AllergenesService $allergeneService,
        SearchTracker $tracker
    ): Response {
        /** @var \App\Entity\Utilisateur|null $user */
        $user = $this->getUser();

        // Tracking vue de menu
        $tracker->track(
            $user?->getId(),
            'menu_view:' . $menu->getId(),
            'menu_show'
        );

        $avis = $avisRepository->findBy(
            ['menu' => $menu],
            ['date' => 'DESC']
        );

        $moyenne = null;
        if (count($avis) > 0) {
            $total = array_sum(array_map(fn($a) => $a->getNote(), $avis));
            $moyenne = $total / count($avis);
        }

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

        $peutLaisserAvis = false;
        $commandeEligible = null;

        if ($user) {
            $commandeEligible = $commandeRepository->findCommandeEligiblePourAvis($user, $menu);

            if ($commandeEligible && !$aDejaLaisseAvis) {
                $peutLaisserAvis = true;
            }
        }

        $allergenesMenu = $allergeneService->getAllergenesForMenu($menu->getId());

        return $this->render('menu/show.html.twig', [
            'menu'             => $menu,
            'avis'             => $avis,
            'moyenne'          => $moyenne,
            'aDejaLaisseAvis'  => $aDejaLaisseAvis,
            'avisExistant'     => $avisExistant,
            'peutLaisserAvis'  => $peutLaisserAvis,
            'commandeEligible' => $commandeEligible,
            'allergenesMenu'   => $allergenesMenu,
        ]);
    }
}
