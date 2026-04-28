<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    

 public function findByCriteria(array $criteria): array
{
    $qb = $this->createQueryBuilder('m');

    // Filtre : Thème (ManyToOne → ID)
    if (!empty($criteria['theme'])) {
        $qb->andWhere('IDENTITY(m.theme) = :theme')
           ->setParameter('theme', $criteria['theme']);
    }

    // Filtre : Régime (ManyToOne → ID)
    if (!empty($criteria['regime'])) {
        $qb->andWhere('IDENTITY(m.regime) = :regime')
           ->setParameter('regime', $criteria['regime']);
    }

    // Filtre : Prix minimum
    if (!empty($criteria['prixMin'])) {
        $qb->andWhere('m.prixBase >= :prixMin')
           ->setParameter('prixMin', $criteria['prixMin']);
    }

    // Filtre : Prix maximum
    if (!empty($criteria['prixMax'])) {
        $qb->andWhere('m.prixBase <= :prixMax')
           ->setParameter('prixMax', $criteria['prixMax']);
    }

    return $qb->orderBy('m.prixBase', 'ASC')
              ->getQuery()
              ->getResult();
}



}