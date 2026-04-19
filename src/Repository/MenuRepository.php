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

    /**
     * Recherche dynamique utilisée par la vue globale + AJAX
     */
    public function searchMenus(array $criteria): array
    {
        $qb = $this->createQueryBuilder('m');

        // Prix maximum
        if (!empty($criteria['prixMax'])) {
            $qb->andWhere('m.prixBase <= :prixMax')
               ->setParameter('prixMax', $criteria['prixMax']);
        }

        // Prix minimum
        if (!empty($criteria['prixMin'])) {
            $qb->andWhere('m.prixBase >= :prixMin')
               ->setParameter('prixMin', $criteria['prixMin']);
        }

        // Thème
        if (!empty($criteria['theme'])) {
            $qb->andWhere('m.theme = :theme')
               ->setParameter('theme', $criteria['theme']);
        }

        // Régime
        if (!empty($criteria['regime'])) {
            $qb->andWhere('m.regime = :regime')
               ->setParameter('regime', $criteria['regime']);
        }

        // Nombre minimum de personnes
        if (!empty($criteria['nbPersonnesMin'])) {
            $qb->andWhere('m.nbPersonnesMin <= :nb')
               ->setParameter('nb', $criteria['nbPersonnesMin']);
        }

        return $qb->orderBy('m.prixBase', 'ASC')
                  ->getQuery()
                  ->getResult();
    }
}
