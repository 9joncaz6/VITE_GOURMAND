<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Entity\CommandeStatut;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AvisTestFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $avisRepo = $manager->getRepository(Avis::class);

        // --- CRÉATION DE PLUSIEURS UTILISATEURS ---
        $utilisateurs = [];

        for ($i = 1; $i <= 5; $i++) {
            $email = "avis_user$i@test.com";

            $user = $manager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new Utilisateur();
                $user->setEmail($email);
                $user->setNom("Testeur$i");
                $user->setPrenom("Jean$i");
                $user->setGsm("060000000$i");
                $user->setActif(true);
                $user->setPassword($this->hasher->hashPassword($user, 'password'));
                $manager->persist($user);
            }

            $utilisateurs[] = $user;
        }

        // --- RÉCUPÉRER TOUS LES MENUS EXISTANTS ---
        $menus = $manager->getRepository(Menu::class)->findAll();

        // --- COMMENTAIRES POSSIBLES ---
        $commentaires = [
            "Excellent menu, je recommande !",
            "Très bon rapport qualité/prix.",
            "Livraison rapide et menu délicieux.",
            "Portions généreuses, je suis satisfait.",
            "Un peu trop salé mais globalement très bon.",
            "Service impeccable, merci !",
            "Je commanderai à nouveau.",
            "Bonne surprise, je ne m'attendais pas à ça."
        ];

        foreach ($menus as $menu) {

            // Chaque menu reçoit entre 1 et 4 avis
            $nbAvis = rand(1, 4);

            for ($i = 0; $i < $nbAvis; $i++) {

                $user = $utilisateurs[array_rand($utilisateurs)];

                // Vérifier si un avis existe déjà pour ce menu + utilisateur
                $existing = $avisRepo->findOneBy([
                    'menu' => $menu,
                    'utilisateur' => $user
                ]);

                if ($existing) {
                    continue;
                }

                // --- COMMANDE ---
                $commande = new Commande();
                $commande->setUtilisateur($user);
                $commande->setTotal($menu->getPrixBase());
                $commande->setFraisLivraison(0);
                $manager->persist($commande);

                // --- STATUT TERMINÉ ---
                $statut = new CommandeStatut();
                $statut->setCommande($commande);
                $statut->setStatut('terminée');
                $statut->setDateMaj(new \DateTimeImmutable());
                $manager->persist($statut);

                // --- ITEM ---
                $item = new CommandeItem();
                $item->setCommande($commande);
                $item->setMenu($menu);
                $item->setQuantite(1);
                $manager->persist($item);

                // --- AVIS ---
                $avis = new Avis();
                $avis->setCommande($commande);
                $avis->setMenu($menu);
                $avis->setUtilisateur($user);
                $avis->setNote(rand(3, 5));
                $avis->setCommentaire($commentaires[array_rand($commentaires)]);
                $avis->setDate(new \DateTimeImmutable());
                $manager->persist($avis);

                $commande->setAvis($avis);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            MenuFixtures::class,
            UserFixtures::class,
        ];
    }
}
