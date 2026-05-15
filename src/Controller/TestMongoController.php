<?php

namespace App\Controller;

use App\Document\SearchHistory;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestMongoController extends AbstractController
{
    #[Route('/test-mongo')]
    public function testMongo(DocumentManager $dm): Response
    {
        $entry = new SearchHistory(1, 'test depuis route');
        $dm->persist($entry);
        $dm->flush();

        return new Response('OK');
    }
}
