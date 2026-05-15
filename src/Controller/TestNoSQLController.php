<?php

namespace App\Controller;

use App\Service\NoSQL\AdminLogService;
use App\Service\NoSQL\UserLogger;
use App\Service\NoSQL\CommandeLogger;
use App\Service\NoSQL\AvisArchiver;
use App\Service\NoSQL\StatsService;
use App\Service\NoSQL\PanierManager;
use App\Service\NoSQL\AllergenesService;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\CommandeStatutLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestNoSQLController extends AbstractController
{
    #[Route('/test-nosql', name: 'test_nosql')]
    public function test(
        AdminLogService $adminLog,
        UserLogger $userLogger,
        CommandeLogger $commandeLogger,
        AvisArchiver $avisArchiver,
        StatsService $statsService,
        PanierManager $panierManager,
        AllergenesService $allergenesService,
        AvisRepository $avisRepo,
        CommandeRepository $commandeRepo,
        UtilisateurRepository $userRepo,
        DocumentManager $dm
    ): Response {

        /** @var \App\Entity\Utilisateur|null $admin */
        $admin = $this->getUser() ?? $userRepo->findOneBy([]);

        if (!$admin) {
            return new Response("Aucun utilisateur trouvé pour les tests.");
        }

        $adminLog->log($admin, 'test_admin', ['foo' => 'bar']);
        $userLogger->log($admin->getId(), 'test_user', ['ip' => '127.0.0.1']);
        $commandeLogger->log(55, 'test_commande', ['montant' => 42.50]);

        /** @var \App\Entity\Avis|null $avis */
        $avis = $avisRepo->findOneBy([]);
        if ($avis) {
            $avisArchiver->archive($avis, ['raison' => 'test']);
        }

        /** @var \App\Entity\Commande|null $commande */
        $commande = $commandeRepo->findOneBy([]);
        if ($commande) {
            $statsService->updateStats($commande);
        }

        // 🔥 ici : nouvelle API
        $panierManager->add($admin->getId(), 7, 2);
        $allergenesService->addAllergenes(7, "TestAllergene");

        if ($commande) {
            $statut = new CommandeStatutLog();
            $statut->setCommandeId($commande->getId());
            $statut->setAncienStatut("ancien_test");
            $statut->setNouveauStatut("nouveau_test");
            $statut->setDate(new \DateTime());

            $dm->persist($statut);
            $dm->flush();
        }

        return new Response("Tests NoSQL exécutés avec succès !");
    }
}
