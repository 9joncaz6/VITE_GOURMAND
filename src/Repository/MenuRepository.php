<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * Retourne les menus filtrés selon les critères :
     * - theme (string)
     * - prixMin (float)
     * - prixMax (float)
     */
    public function findByCriteria(array $criteria): array
    {
        $qb = $this->createQueryBuilder('m');

        // Filtre : Thème (relation ManyToOne → Theme.nom)
        if (!empty($criteria['theme'])) {
            $qb->join('m.theme', 't')
               ->andWhere('t.nom = :theme')
               ->setParameter('theme', $criteria['theme']);
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

    public function findByCriteriaWithRelations(array $criteria)
{
    $qb = $this->createQueryBuilder('m')
        ->leftJoin('m.plats', 'p')->addSelect('p')
        ->leftJoin('m.theme', 't')->addSelect('t')
        ->leftJoin('m.regime', 'r')->addSelect('r');

    if ($criteria['theme']) {
        $qb->andWhere('t.nom = :theme')->setParameter('theme', $criteria['theme']);
    }

    if ($criteria['prixMin']) {
        $qb->andWhere('m.prixBase >= :min')->setParameter('min', $criteria['prixMin']);
    }

    if ($criteria['prixMax']) {
        $qb->andWhere('m.prixBase <= :max')->setParameter('max', $criteria['prixMax']);
    }

    return $qb->getQuery()->getResult();
}

}
