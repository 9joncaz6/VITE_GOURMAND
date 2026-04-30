<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        /* ============================
           ADMIN
        ============================ */
        $admin = new Utilisateur();
        $admin->setEmail("admin@test.com");
        $admin->setNom("Admin");
        $admin->setPrenom("Super");
        $admin->setGsm("0600000000");
        $admin->setAdressePostale("Adresse admin");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setActif(true);
        $admin->setPassword($this->hasher->hashPassword($admin, "admin123"));
        $manager->persist($admin);

        /* ============================
           EMPLOYÉ
        ============================ */
        $employe = new Utilisateur();
        $employe->setEmail("employe@test.com");
        $employe->setNom("Employe");
        $employe->setPrenom("Test");
        $employe->setGsm("0600000002");
        $employe->setAdressePostale("Adresse employé");
        $employe->setRoles(["ROLE_EMPLOYE"]);
        $employe->setActif(true);
        $employe->setPassword($this->hasher->hashPassword($employe, "employe123"));
        $manager->persist($employe);

        /* ============================
           CLIENT
        ============================ */
        $client = new Utilisateur();
        $client->setEmail("client@test.com");
        $client->setNom("Client");
        $client->setPrenom("Test");
        $client->setGsm("0600000001");
        $client->setAdressePostale("Adresse client");
        $client->setRoles(["ROLE_USER"]);
        $client->setActif(true);
        $client->setPassword($this->hasher->hashPassword($client, "client123"));
        $manager->persist($client);

        /* ============================
           5 UTILISATEURS DE TEST
        ============================ */
        for ($i = 1; $i <= 5; $i++) {
            $user = new Utilisateur();
            $user->setEmail("user$i@test.com");
            $user->setNom("Utilisateur$i");
            $user->setPrenom("Jean$i");
            $user->setGsm("060000000$i");
            $user->setAdressePostale("Adresse $i");
            $user->setActif(true);
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->hasher->hashPassword($user, 'password'));
            $user->setActif(true);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
