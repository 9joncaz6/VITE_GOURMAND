<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Trouve tous les utilisateurs possédant un rôle donné.
     * Exemple : findByRole('ROLE_EMPLOYE')
     */
    public function findByRole(string $role)
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :role')
        ->setParameter('role', '%"'.$role.'"%')
        ->getQuery()
        ->getResult();
}


    /**
     * Trouve tous les employés actifs
     */
    public function findActiveEmployes(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->andWhere('u.actif = 1')
            ->setParameter('role', json_encode('ROLE_EMPLOYE'))
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->getResult();
    }

/**
 * Retourne uniquement les clients (ROLE_USER mais pas employé ni admin)
 */
public function findClients(): array
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :roleUser')
        ->andWhere('u.roles NOT LIKE :roleEmploye')
        ->andWhere('u.roles NOT LIKE :roleAdmin')
        ->setParameter('roleUser', '%"ROLE_USER"%')
        ->setParameter('roleEmploye', '%"ROLE_EMPLOYE"%')
        ->setParameter('roleAdmin', '%"ROLE_ADMIN"%')
        ->orderBy('u.id', 'DESC')
        ->getQuery()
        ->getResult();
}

/**
 * Retourne uniquement les employés
 */
public function findEmployes(): array
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :roleEmploye')
        ->setParameter('roleEmploye', '%"ROLE_EMPLOYE"%')
        ->orderBy('u.id', 'DESC')
        ->getQuery()
        ->getResult();
}

/**
 * Retourne uniquement les administrateurs
 */
public function findAdmins(): array
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :roleAdmin')
        ->setParameter('roleAdmin', '%"ROLE_ADMIN"%')
        ->orderBy('u.id', 'DESC')
        ->getQuery()
        ->getResult();
}

}
