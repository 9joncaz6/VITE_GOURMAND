<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Repository\AvisRepository;
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

        // 1) Récupérer les filtres depuis l’URL
        $criteria = [
            'theme'   => $request->query->get('theme'),
            'regime'  => $request->query->get('regime'),
            'prixMin' => $request->query->get('prixMin'),
            'prixMax' => $request->query->get('prixMax'),
        ];

        // 2) Récupérer les menus filtrés
        $menus = $menuRepository->findByCriteria($criteria);

        // 3) Récupérer les derniers avis (3 par défaut)
        $avis = $avisRepository->findLatestAvis(3);

        // 4) Envoyer les données au template
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
    CommandeRepository $commandeRepository
): Response {

    $user = $this->getUser();

    // 1) Récupérer les avis du menu
    $avis = $avisRepository->findBy(
        ['menu' => $menu],
        ['date' => 'DESC']
    );

    // 2) Calculer la moyenne
    $moyenne = null;
    if (count($avis) > 0) {
        $total = array_sum(array_map(fn($a) => $a->getNote(), $avis));
        $moyenne = $total / count($avis);
    }

    // 3) Vérifier si l’utilisateur a déjà laissé un avis
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

    // 4) Vérifier si l’utilisateur peut laisser un avis
    $peutLaisserAvis = false;
    $commandeEligible = null;

    if ($user) {
        $commandeEligible = $commandeRepository->findCommandeEligiblePourAvis($user, $menu);

        if ($commandeEligible && !$aDejaLaisseAvis) {
            $peutLaisserAvis = true;
        }
    }

    // 5) 🔥 Récupérer les allergènes du menu
    $allergenesMenu = [];

    foreach ($menu->getPlats() as $plat) {
        foreach ($plat->getAllergenes() as $allergene) {
            $allergenesMenu[] = $allergene->getNom();
        }
    }

    $allergenesMenu = array_unique($allergenesMenu);

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
