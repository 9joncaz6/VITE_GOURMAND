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
    $menus = $menuRepository->findAll(); // Récupère les 5 menus

    return $this->render('home/home.html.twig', [
        'menus' => $menus, // ENVOI À TWIG
    ]);
}

}
