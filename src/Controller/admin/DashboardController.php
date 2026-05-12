<?php

namespace App\Controller\admin;

use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Repository\UtilisateurRepository;
use App\Service\NoSQL\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        CommandeRepository $commandeRepo,
        MenuRepository $menuRepo,
        UtilisateurRepository $userRepo,
        StatsService $statsService
    ): Response {

        $commandes = $commandeRepo->findBy([], ['createdAt' => 'DESC'], 5);
        $menus     = $menuRepo->findBy([], ['id' => 'DESC'], 5);
        $users     = $userRepo->findBy([], ['id' => 'DESC'], 5);

        $statsGlobales = $statsService->getStats();

        $stats = [
            'totalCommandes' => $statsGlobales['totalCommandes'],
            'totalMenus'     => $menuRepo->count([]),
            'totalUsers'     => $userRepo->count([]),
            'caTotal'        => $statsGlobales['caTotal'],
            'panierMoyen'    => $statsGlobales['panierMoyen'],
        ];

        return $this->render('admin/dashboard.html.twig', [
            'commandes' => $commandes,
            'menus'     => $menus,
            'users'     => $users,
            'stats'     => $stats,
        ]);
    }
}
