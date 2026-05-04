<?php

namespace App\Controller\admin;

use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Repository\UtilisateurRepository;
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
        UtilisateurRepository $userRepo
    ): Response {
        
        $commandes = $commandeRepo->findBy([], ['createdAt' => 'DESC'], 5);
        $menus = $menuRepo->findBy([], ['id' => 'DESC'], 5);
        $users = $userRepo->findBy([], ['id' => 'DESC'], 5);

        $stats = [
            'totalCommandes' => $commandeRepo->count([]),
            'totalMenus' => $menuRepo->count([]),
            'totalUsers' => $userRepo->count([]),
            'caTotal' => $commandeRepo->getTotalCA(), // méthode à créer
        ];

        return $this->render('admin/dashboard.html.twig', [
            'commandes' => $commandes,
            'menus' => $menus,
            'users' => $users,
            'stats' => $stats,
        ]);
    }
}