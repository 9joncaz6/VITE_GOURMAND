<?php

namespace App\Controller;

use App\Repository\PlatRepository;
use App\Service\SearchTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/search')]
class SearchController extends AbstractController
{
    #[Route('/', name: 'app_search', methods: ['GET'])]
    public function search(
        Request $request,
        PlatRepository $platRepository,
        SearchTracker $tracker
    ): Response {
        $query = $request->query->get('q', '');

        if ($this->getUser()) {
    /** @var \App\Entity\Utilisateur $user */
    $user = $this->getUser();
    $tracker->track($user->getId(), $query);
}


        $results = $platRepository->createQueryBuilder('p')
            ->where('p.nom LIKE :q')
            ->setParameter('q', "%$query%")
            ->getQuery()
            ->getResult();

        return $this->render('search/results.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}