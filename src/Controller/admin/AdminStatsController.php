<?php

namespace App\Controller\Admin;

use App\Repository\MenuRepository;
use App\Service\NoSQL\StatsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/stats')]
class AdminStatsController extends AbstractController
{
    #[Route('/', name: 'admin_stats')]
    public function index(StatsService $statsService, MenuRepository $menuRepo): Response
    {
        $stats = $statsService->getStats();
        $menus = $menuRepo->findAll();

        return $this->render('admin/stats/index.html.twig', [
            'stats' => $stats,
            'menus' => $menus,
            'commandesParMenu' => $statsService->getCommandesParMenu(),
            'caParMenu' => $statsService->getCaParMenu(),
        ]);
    }

    #[Route('/reset', name: 'admin_stats_reset', methods: ['POST'])]
    public function resetStats(Request $request, StatsService $statsService): Response
    {
        if (!$this->isCsrfTokenValid('reset_stats', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_stats');
        }

        $statsService->resetStats();

        $this->addFlash('success', 'Les statistiques ont été remises à zéro.');
        return $this->redirectToRoute('admin_stats');
    }
}
