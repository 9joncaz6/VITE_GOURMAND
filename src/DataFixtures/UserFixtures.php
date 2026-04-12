<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ADMIN
        $admin = new Utilisateur();
        $admin->setNom("Admin");
        $admin->setPrenom("Test");
        $admin->setEmail("admin@test.com");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setActif(true);
        $admin->setGsm("0600000000"); // ← IMPORTANT

        $admin->setPassword(
            $this->hasher->hashPassword($admin, "admin123")
        );

        $manager->persist($admin);

        // CLIENT
        $client = new Utilisateur();
        $client->setNom("Client");
        $client->setPrenom("Test");
        $client->setEmail("client@test.com");
        $client->setRoles(["ROLE_USER"]);
        $client->setActif(true);
        $client->setGsm("0600000000"); // ← IMPORTANT

        $client->setPassword(
            $this->hasher->hashPassword($client, "client123")
        );

        $manager->persist($client);

        $manager->flush();
    }
}