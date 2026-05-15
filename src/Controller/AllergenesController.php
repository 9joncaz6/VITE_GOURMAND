<?php

namespace App\Controller;
use App\Service\NoSQL\AllergenesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/allergenes')]
class AllergenesController extends AbstractController
{
    #[Route('/test-mongo', name: 'test_mongo')]
    public function test(AllergenesService $service): Response
    {
        // Test d’écriture
        $service->setAllergenesForMenu(1, ['gluten', 'soja']);

        // Test de lecture
        $data = $service->getAllergenesForMenu(1);

        return $this->json($data);
    }
}
