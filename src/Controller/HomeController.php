<?php

// src/Controller/HomeController.php
namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(MenuRepository $menuRepository): Response
    {
        $menus = $menuRepository->findBy([], ['id' => 'DESC'], 4); 
        // On affiche les 4 derniers menus comme dans ton mockup

        return $this->render('home/home.html.twig', [
            'menus' => $menus,
        ]);
    }
}
