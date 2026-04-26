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
        ->join('c.menus', 'm')
        ->where('c.user = :user')
        ->andWhere('m = :menu')
        ->andWhere('c.statut = :statut')
        ->setParameter('user', $user)
        ->setParameter('menu', $menu)
        ->setParameter('statut', 'terminee')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

}