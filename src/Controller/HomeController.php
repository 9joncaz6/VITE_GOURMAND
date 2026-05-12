<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use App\Repository\AvisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(MenuRepository $menuRepository, AvisRepository $avisRepository): Response
    {
        $menus = $menuRepository->findAll();

        // Les mêmes avis que sur la page Menus
        $avis = $avisRepository->findLatestAvis(3);

        return $this->render('home/home.html.twig', [
            'menus' => $menus,
            'avis' => $avis,
        ]);
    }
}
