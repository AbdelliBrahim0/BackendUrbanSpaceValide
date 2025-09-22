<?php

namespace App\Repository;

use App\Entity\Sale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sale>
 */
class SaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    public function findActiveSales()
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('s')
            ->where('s.isActive = :isActive')
            ->andWhere('s.startDate <= :now')
            ->andWhere('s.endDate >= :now')
            ->setParameter('isActive', true)
            ->setParameter('now', $now)
            ->orderBy('s.discountPercentage', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findUpcomingSales()
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('s')
            ->where('s.startDate > :now')
            ->setParameter('now', $now)
            ->orderBy('s.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findExpiredSales()
    {
        $now = new \DateTime();

        return $this->createQueryBuilder('s')
            ->where('s.endDate < :now')
            ->setParameter('now', $now)
            ->orderBy('s.endDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les produits en solde avec leurs détails
     *
     * @return Sale[]
     */
    public function findAllWithProductDetails(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('s')
            ->select('s', 'p', 'c', 'sc')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('p.subCategories', 'sc')
            ->where('s.isActive = :isActive')
            ->andWhere('s.startDate <= :now')
            ->andWhere('s.endDate >= :now')
            ->setParameter('isActive', true)
            ->setParameter('now', $now)
            ->orderBy('s.discountPercentage', 'DESC')
            ->getQuery()
            ->getResult();
    }
}