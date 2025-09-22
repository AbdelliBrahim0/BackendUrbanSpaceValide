<?php

namespace App\Repository;

use App\Entity\BlackHour;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlackHour>
 */
class BlackHourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlackHour::class);
    }

    // Méthode pour trouver les promotions actives
    public function findActivePromotions()
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('bh')
            ->where('bh.startTime <= :now')
            ->andWhere('bh.endTime >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    // Méthode pour trouver les promotions à venir
    public function findUpcomingPromotions()
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('bh')
            ->where('bh.startTime > :now')
            ->setParameter('now', $now)
            ->orderBy('bh.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Méthode pour trouver les promotions expirées
    public function findExpiredPromotions()
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('bh')
            ->where('bh.endTime < :now')
            ->setParameter('now', $now)
            ->orderBy('bh.endTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les produits en promotion Black Hour avec leurs détails
     *
     * @return BlackHour[]
     */
    public function findAllWithProductDetails(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('bh')
            ->select('bh', 'p', 'c', 'sc')
            ->leftJoin('bh.product', 'p')
            ->leftJoin('p.categories', 'c')
            ->leftJoin('p.subCategories', 'sc')
            ->where('bh.startTime <= :now')
            ->andWhere('bh.endTime >= :now')
            ->setParameter('now', $now)
            ->orderBy('bh.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
