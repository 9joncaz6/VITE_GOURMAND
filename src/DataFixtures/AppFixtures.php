<?php

namespace App\DataFixtures;

use App\Entity\Allergene;
use App\Entity\Regime;
use App\Entity\Theme;
use App\Entity\Plat;
use App\Entity\Menu;
use App\Entity\Utilisateur;
use App\Entity\Commande;
use App\Entity\CommandeStatut;
use App\Entity\Avis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        /* ============================================================
           1. ALLERGENES
        ============================================================ */
        $allergenes = [];
        $listeAllergenes = ["Gluten", "Lactose", "Arachides", "Soja", "Œufs", "Poisson", "Fruits à coque"];

        foreach ($listeAllergenes as $nom) {
            $a = new Allergene();
            $a->setNom($nom);
            $manager->persist($a);
            $allergenes[] = $a;
        }

        /* ============================================================
           2. REGIMES
        ============================================================ */
        $regimes = [];
        $listeRegimes = ["Vegan", "Végétarien", "Sans gluten", "Halal", "Casher"];

        foreach ($listeRegimes as $nom) {
            $r = new Regime();
            $r->setNom($nom);
            $manager->persist($r);
            $regimes[] = $r;
        }

        /* ============================================================
           3. THEMES
        ============================================================ */
        $themes = [];
        $listeThemes = ["Italien", "Asiatique", "Français", "Oriental", "Américain"];

        foreach ($listeThemes as $nom) {
            $t = new Theme();
            $t->setNom($nom);
            $manager->persist($t);
            $themes[] = $t;
        }

        /* ============================================================
           4. PLATS
        ============================================================ */
        $plats = [];

        for ($i = 1; $i <= 10; $i++) {
            $p = new Plat();
            $p->setNom("Plat $i");
            $p->setDescription("Description du plat $i");
            $p->setType("Type $i");

            // Allergène aléatoire
            $p->addAllergene($allergenes[array_rand($allergenes)]);

            $manager->persist($p);
            $plats[] = $p;
        }

        /* ============================================================
           5. MENUS
        ============================================================ */
        $menus = [];

        for ($i = 1; $i <= 5; $i++) {
            $m = new Menu();
            $m->setTitre("Menu $i");
            $m->setDescription("Description du menu $i");
            $m->setNbPersonnesMin(rand(2, 10));
            $m->setPrixBase(rand(20, 60));
            $m->setConditions("Conditions du menu $i");
            $m->setStockDisponible(rand(5, 20));

            // Relations
            $m->setTheme($themes[array_rand($themes)]);
            $m->setRegime($regimes[array_rand($regimes)]);

            // Ajouter 2 plats
            $m->addPlat($plats[array_rand($plats)]);
            $m->addPlat($plats[array_rand($plats)]);

            $manager->persist($m);
            $menus[] = $m;
        }

        /* ============================================================
           6. UTILISATEURS
        ============================================================ */
        $utilisateurs = [];

        for ($i = 1; $i <= 5; $i++) {
            $u = new Utilisateur();
            $u->setNom("Nom $i");
            $u->setPrenom("Prenom $i");
            $u->setEmail("user$i@example.com");
            $u->setGsm("06000000$i");
            $u->setAdressePostale("Adresse $i");
            $u->setMotDePasse("password$i");
            $u->setRoles(["ROLE_USER"]);
            $u->setActif(true);

            $manager->persist($u);
            $utilisateurs[] = $u;
        }

        /* ============================================================
           7. COMMANDES
        ============================================================ */
        $commandes = [];

        for ($i = 1; $i <= 5; $i++) {
            $c = new Commande();
            $c->setDateCommande(new \DateTime());
            $c->setDatePrestation((new \DateTime())->modify("+".rand(1, 10)." days"));
            $c->setHeurePrestation(new \DateTime("12:00"));
            $c->setAdresseLivraison("Rue de la livraison $i");
            $c->setVilleLivraison("Ville $i");
            $c->setDistanceKm(rand(1, 30));
            $c->setPrixLivraison(rand(5, 20));
            $c->setNbPersonnes(rand(2, 10));
            $c->setPrixTotal(rand(50, 200));
            $c->setStatutActuel("En attente");

            $c->setUtilisateur($utilisateurs[array_rand($utilisateurs)]);
            $c->setMenu($menus[array_rand($menus)]);

            $manager->persist($c);
            $commandes[] = $c;
        }

        /* ============================================================
           8. STATUTS DE COMMANDE (historique)
        ============================================================ */
        foreach ($commandes as $commande) {
            for ($i = 1; $i <= 2; $i++) {
                $s = new CommandeStatut();
                $s->setStatut($i === 1 ? "En attente" : "En préparation");
                $s->setDateMaj(new \DateTime());
                $s->setCommentaire("Statut $i pour commande ".$commande->getId());
                $s->setCommande($commande);

                $manager->persist($s);
            }
        }

        /* ============================================================
           9. AVIS
        ============================================================ */
        foreach ($commandes as $commande) {
            $avis = new Avis();
            $avis->setNote(rand(1, 5));
            $avis->setCommentaire("Avis pour la commande ".$commande->getId());
            $avis->setValide(true);
            $avis->setUtilisateur($commande->getUtilisateur());
            $avis->setCommande($commande);

            $manager->persist($avis);
        }

        /* ============================================================
           FLUSH FINAL
        ============================================================ */
        $manager->flush();
    }
}