<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

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
     * Méthode générique permettant d'appliquer tous les filtres dynamiques.
     * Elle sera utilisée par l'AJAX de la page "Vue globale des menus".
     *
     * $criteria = [
     *     'prixMax' => float,
     *     'prixMin' => float,
     *     'theme' => int,
     *     'regime' => int,
     *     'nbPersonnesMin' => int
     * ]
     */
    public function searchMenus(array $criteria): array
    {
        $qb = $this->createQueryBuilder('m');

        // Filtre : prix maximum
        if (!empty($criteria['prixMax'])) {
            $qb->andWhere('m.prixBase <= :prixMax')
               ->setParameter('prixMax', $criteria['prixMax']);
        }

        // Filtre : prix minimum
        if (!empty($criteria['prixMin'])) {
            $qb->andWhere('m.prixBase >= :prixMin')
               ->setParameter('prixMin', $criteria['prixMin']);
        }

        // Filtre : thème
        if (!empty($criteria['theme'])) {
            $qb->andWhere('m.theme = :theme')
               ->setParameter('theme', $criteria['theme']);
        }

        // Filtre : régime
        if (!empty($criteria['regime'])) {
            $qb->andWhere('m.regime = :regime')
               ->setParameter('regime', $criteria['regime']);
        }

        // Filtre : nombre de personnes minimum
        if (!empty($criteria['nbPersonnesMin'])) {
            $qb->andWhere('m.nbPersonnesMin <= :nb')
               ->setParameter('nb', $criteria['nbPersonnesMin']);
        }

        return $qb->orderBy('m.prixBase', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Filtre simple : prix maximum
     */
    public function filterByMaxPrice(float $max): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.prixBase <= :max')
            ->setParameter('max', $max)
            ->orderBy('m.prixBase', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre simple : fourchette de prix
     */
    public function filterByPriceRange(float $min, float $max): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.prixBase BETWEEN :min AND :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->orderBy('m.prixBase', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre simple : thème
     */
    public function filterByTheme(int $themeId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.theme = :theme')
            ->setParameter('theme', $themeId)
            ->orderBy('m.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre simple : régime
     */
    public function filterByRegime(int $regimeId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.regime = :regime')
            ->setParameter('regime', $regimeId)
            ->orderBy('m.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtre simple : nombre de personnes minimum
     */
    public function filterByNbPersonnes(int $min): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.nbPersonnesMin <= :min')
            ->setParameter('min', $min)
            ->orderBy('m.nbPersonnesMin', 'ASC')
            ->getQuery()
            ->getResult();
    }
}