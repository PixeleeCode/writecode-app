<?php

namespace App\Repository;

use App\Entity\Technologie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Technologie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Technologie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Technologie[]    findAll()
 * @method Technologie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TechnologieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Technologie::class);
    }
}
