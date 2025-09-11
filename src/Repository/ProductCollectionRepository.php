<?php

namespace App\Repository;

use App\Entity\ProductCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductCollection>
 *
 * @method ProductCollection|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductCollection|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductCollection[]    findAll()
 * @method ProductCollection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductCollection::class);
    }

    public function save(ProductCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ProductCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllOrderedByName()
    {
        return $this->createQueryBuilder('pc')
            ->orderBy('pc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithProducts($id)
    {
        return $this->createQueryBuilder('pc')
            ->leftJoin('pc.products', 'p')
            ->addSelect('p')
            ->where('pc.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllWithProducts()
    {
        return $this->createQueryBuilder('pc')
            ->leftJoin('pc.products', 'p')
            ->addSelect('p')
            ->orderBy('pc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
