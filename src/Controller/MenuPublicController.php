<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Theme;
use App\Repository\MenuRepository;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;
use App\Service\NoSql\AllergenesService;
use App\Service\NoSql\SearchTracker;
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
        AllergenesService $allergenesService,
        EntityManagerInterface $em
    ): Response {

        // Critères de filtrage
        $criteria = [
            'theme'   => $request->query->get('theme'),
            'prixMin' => $request->query->get('prixMin'),
            'prixMax' => $request->query->get('prixMax'),
        ];

        // Menus avec relations
        $menus = $menuRepository->findByCriteriaWithRelations($criteria);

        // Ajouter les allergènes
        foreach ($menus as $menu) {
            $menu->setAllergenes(
                $allergenesService->getAllergenesForMenu($menu->getId())
            );
        }

        // 🔥 Tous les thèmes, même ceux sans menu
        $themes = $em->getRepository(Theme::class)->findBy([], ['nom' => 'ASC']);

        // Avis récents
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

        $user = $this->getUser();

        // Tracking
        /** @var \App\Entity\Utilisateur|null $user */
        $user = $this->getUser();

        $tracker->track(
            $user?->getId(),
            'menu_view:' . $menu->getId(),
            'menu_show'
        );

        // Avis du menu
        $avis = $avisRepository->findBy(
            ['menu' => $menu],
            ['date' => 'DESC']
        );

        // Moyenne
        $moyenne = null;
        if ($avis) {
            $total = array_sum(array_map(fn($a) => $a->getNote(), $avis));
            $moyenne = $total / count($avis);
        }

        // Vérifier si l'utilisateur a déjà laissé un avis
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

        // Peut-il laisser un avis ?
        $peutLaisserAvis = false;
        $commandeEligible = null;

        if ($user) {
            $commandeEligible = $commandeRepository->findCommandeEligiblePourAvis($user, $menu);

            if ($commandeEligible && !$aDejaLaisseAvis) {
                $peutLaisserAvis = true;
            }
        }

        // Allergènes
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
