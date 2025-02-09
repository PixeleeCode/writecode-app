<?php

namespace App\Repository;

use App\Entity\Throttler;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Throttler|null find($id, $lockMode = null, $lockVersion = null)
 * @method Throttler|null findOneBy(array $criteria, array $orderBy = null)
 * @method Throttler[]    findAll()
 * @method Throttler[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThrottlerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Throttler::class);
    }
}
