<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commander')]
class CommanderController extends AbstractController
{
    #[Route('/', name: 'app_commander', methods: ['GET'])]
    public function index(MenuRepository $menuRepository): Response
    {
        return $this->render('commander/index.html.twig', [
            'menus' => $menuRepository->findAll(),
        ]);
    }
}