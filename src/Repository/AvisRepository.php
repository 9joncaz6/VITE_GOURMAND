<?php

namespace App\Repository;

use App\Entity\Avis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    /**
     * Retourne les derniers avis (triés par date décroissante)
     */
    public function findLatestAvis(int $limit = 3): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }


public function searchAvis(?int $note, ?int $menuId): array
{
    $qb = $this->createQueryBuilder('a')
        ->orderBy('a.date', 'DESC');

    if ($note !== null) {
        $qb->andWhere('a.note = :note')
           ->setParameter('note', $note);
    }

    if ($menuId !== null) {
        $qb->andWhere('a.menu = :menu')
           ->setParameter('menu', $menuId);
    }

    return $qb->getQuery()->getResult();
}

}

