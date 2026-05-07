<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    /**
     * Retourne le chiffre d'affaires total
     */
    public function getTotalCA(): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne les commandes d’un utilisateur (si tu veux l’utiliser pour le compte client)
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.utilisateur = :uid')
            ->setParameter('uid', $userId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

   public function findCommandeEligiblePourAvis($user, $menu)
{
    return $this->createQueryBuilder('c')
        ->join('c.items', 'i')
        ->join('i.menu', 'm')
        ->join('c.commandeStatuts', 's')
        ->where('c.utilisateur = :user')
        ->andWhere('m = :menu')
        ->andWhere('s.statut = :statut')
        ->setParameter('user', $user)
        ->setParameter('menu', $menu)
        ->setParameter('statut', 'terminee')
        ->orderBy('s.dateMaj', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}



public function findByStatutActuel(string $statut): array
{
    return $this->createQueryBuilder('c')
        ->join('c.commandeStatuts', 's')
        ->andWhere('s.statut = :statut')
        ->setParameter('statut', $statut)
        ->orderBy('c.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

public function findAllOrdered(): array
{
    return $this->createQueryBuilder('c')
        ->orderBy('c.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
}

public function countByStatut(string $statut): int
{
    return $this->createQueryBuilder('c')
        ->select('COUNT(c.id)')
        ->join('c.commandeStatuts', 's')
        ->andWhere('s.statut = :statut')
        ->setParameter('statut', $statut)
        ->getQuery()
        ->getSingleScalarResult();
}


}