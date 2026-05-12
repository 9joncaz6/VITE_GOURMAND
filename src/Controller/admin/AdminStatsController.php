<?php

namespace App\Controller\admin;

use App\Repository\MenuRepository;
use App\Service\NoSQL\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/stats')]
class AdminStatsController extends AbstractController
{
    #[Route('/', name: 'admin_stats')]
    public function index(StatsService $statsService, MenuRepository $menuRepo): Response
    {
        $stats = $statsService->getStats(); // total CA, total commandes, etc.
        $menus = $menuRepo->findAll();

        $commandesParMenu = $statsService->getCommandesParMenu();
        $caParMenu = $statsService->getCaParMenu();

        return $this->render('admin/stats/index.html.twig', [
            'stats' => $stats,
            'menus' => $menus,
            'commandesParMenu' => $commandesParMenu,
            'caParMenu' => $caParMenu,
        ]);
    }
}
