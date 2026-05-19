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
     * Chiffre d'affaires total (exclut les commandes annulées)
     */
    public function getTotalCA(): float
    {
        return (float) $this->createQueryBuilder('c')
            ->select('SUM(c.total)')
            ->join('c.commandeStatuts', 's')
            ->andWhere('s.dateMaj = (
                SELECT MAX(s2.dateMaj)
                FROM App\Entity\CommandeStatut s2
                WHERE s2.commande = c
            )')
            ->andWhere('s.statut != :annulee')
            ->setParameter('annulee', 'annulee')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Commandes d’un utilisateur
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

    /**
     * Vérifie si un utilisateur peut laisser un avis sur un menu
     */
    public function findCommandeEligiblePourAvis($user, $menu)
    {
        return $this->createQueryBuilder('c')
            ->join('c.items', 'i')
            ->join('i.menu', 'm')
            ->join('c.commandeStatuts', 's')
            ->where('c.utilisateur = :user')
            ->andWhere('m = :menu')
            ->andWhere('s.dateMaj = (
                SELECT MAX(s2.dateMaj)
                FROM App\Entity\CommandeStatut s2
                WHERE s2.commande = c
            )')
            ->andWhere('s.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('menu', $menu)
            ->setParameter('statut', 'terminee')
            ->orderBy('s.dateMaj', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Filtre par statut ACTUEL (pas les anciens)
     */
    public function findByStatutActuel(string $statut): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.commandeStatuts', 's')
            ->andWhere('s.dateMaj = (
                SELECT MAX(s2.dateMaj)
                FROM App\Entity\CommandeStatut s2
                WHERE s2.commande = c
            )')
            ->andWhere('s.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Liste toutes les commandes avec leur statut ACTUEL
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.commandeStatuts', 's')
            ->andWhere('s.dateMaj = (
                SELECT MAX(s2.dateMaj)
                FROM App\Entity\CommandeStatut s2
                WHERE s2.commande = c
            )')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les commandes par statut ACTUEL
     */
    public function countByStatut(string $statut): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->join('c.commandeStatuts', 's')
            ->andWhere('s.dateMaj = (
                SELECT MAX(s2.dateMaj)
                FROM App\Entity\CommandeStatut s2
                WHERE s2.commande = c
            )')
            ->andWhere('s.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
