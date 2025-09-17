<?php

namespace App\Repository;

use App\Entity\BlackFriday;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlackFriday>
 */
class BlackFridayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlackFriday::class);
    }

    /**
     * Trouve une entrée BlackFriday par produit
     */
    public function findByProduit($produit): ?BlackFriday
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.produit = :val')
            ->setParameter('val', $produit)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Trouve tous les produits en promotion pendant la Black Friday
     * avec pagination
     */
    public function findAllWithPagination($page = 1, $limit = 10)
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.dateCreation', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Compte le nombre total d'entrées Black Friday
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
