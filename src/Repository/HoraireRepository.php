<?php

namespace App\Repository;

use App\Entity\Horaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Horaire>
 */
class HoraireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Horaire::class);
    }

    /**
     * Retourne uniquement les horaires actifs
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.actif = :val')
            ->setParameter('val', true)
            ->orderBy('h.jour', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
